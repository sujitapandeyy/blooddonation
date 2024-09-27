<?php
require('../connection.php');
session_start();

if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}
// deleteExpiredBloodRecords($con);


// Fetch logged-in donor details
$sql = "SELECT id, fullname, email FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$loggedInEmail = $_SESSION['donoremail'];

if ($stmt === false) {
    die("MySQL prepare statement failed: " . htmlspecialchars($con->error));
}

$stmt->bind_param("s", $loggedInEmail);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

$donorId = $donor['id'];
$fullname = $donor['fullname'];

// Fetch total donations count for this donor
$query = "
    SELECT COUNT(bd.id) AS total_donations
    FROM blood_details bd 
    WHERE bd.donor_id = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalDonations = $row['total_donations'] ?? 0;

// Fetch donor's last donation date and availability status
$statusql = "
    SELECT d.last_donation_date 
    FROM donor d 
    WHERE d.id = ?";
$stmt = $con->prepare($statusql);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$donorData = $result->fetch_assoc();

$lastDonationDate = $donorData['last_donation_date'];
$availabilityStatus = "Available";
$daysUntilEligible = 0;

if ($lastDonationDate) {
    // Calculate the difference between current date and last donation date
    $currentDate = new DateTime();
    $lastDonationDateObj = new DateTime($lastDonationDate);
    $interval = $currentDate->diff($lastDonationDateObj);
    $daysSinceLastDonation = $interval->days;

    // Check if 56 days have passed
    if ($daysSinceLastDonation < 56) {
        $availabilityStatus = "Not Available";
        $daysUntilEligible = 56 - $daysSinceLastDonation;
    }
}

// Fetch total pending donation requests for this donor
$totalPendingQuery = "SELECT COUNT(*) AS total_pending_requests FROM donorblood_request WHERE status = 'Pending' AND donor_id = ?";
$stmt = $con->prepare($totalPendingQuery);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$rowPending = $result->fetch_assoc();
$totalPendingRequests = $rowPending['total_pending_requests'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>

</head>

<body class="bg-white">
        <!-- <?php include("donorMenu.php"); ?> -->

    <Section class="ml-64 px-4">
        <div class="bg-white p-6 rounded-lg">
        <!-- <h2 class="text-4xl  font-serif mb-12 text-red-600">Dashboard</h2> -->

            <!-- Summary Card Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                
                <!-- Total Donation Requests -->
                <div class="bg-blue-500 text-white p-6 rounded-lg">
                    <h3 class="text-xl font-bold">Blood Requests</h3>
                    <p class="text-2xl"><?php echo htmlspecialchars($totalPendingRequests); ?></p>
                </div>

                <!-- Total Donations -->
                <div class="bg-yellow-400 text-white p-6 rounded-lg">
                    <h3 class="text-xl font-bold">Total Donations</h3>
                    <p class="text-2xl"><?php echo htmlspecialchars($totalDonations); ?></p>
                </div>
                <!-- Availability Status -->
                <div class="<?php echo ($availabilityStatus === 'Available') ? 'bg-green-500' : 'bg-red-500'; ?> text-white p-6 rounded-lg">
                 <h3 class="text-xl font-bold">Availability Status</h3>
                 <p class="text-xl"><?php echo htmlspecialchars($availabilityStatus === "Available" ? "Available" : "Donate after $daysUntilEligible days"); ?></p>
                </div>


            </div>
        </div>
        <div class="ml-2">
        <?php @include "algorithm.php"?>
      <?php  @include '../campalgo.php';?>

    </div>
    </Section>
</body>

</html>




