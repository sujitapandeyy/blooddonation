<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

$donor_email = $_SESSION['donoremail'];

// Fetch available blood banks for dropdown
$query = $con->prepare("SELECT id, fullname FROM users WHERE user_type = 'BloodBank'");
$query->execute();
$blood_banks = $query->get_result();

// Fetch donor details
$query = $con->prepare("SELECT u.fullname, u.phone, u.address, d.donor_blood_type, d.dob, d.weight, d.gender, d.last_donation_date FROM users u JOIN donor d ON u.id = d.id WHERE u.email = ? AND u.user_type = 'Donor'");
$query->bind_param("s", $donor_email);
$query->execute();
$donor = $query->get_result()->fetch_assoc();

if (!$donor) {
    header("Location: donors.php?error=Donor not found");
    exit();
}

// Calculate days until eligible to donate
$lastDonationDate = $donor['last_donation_date'];
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

// Initialize blood bank ID from URL if available
$blood_bank_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch the blood bank name based on the ID
$blood_bank_name = null; 
if ($blood_bank_id) {
    $query = $con->prepare("SELECT fullname FROM users WHERE id = ? AND user_type = 'BloodBank'");
    $query->bind_param("i", $blood_bank_id);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $blood_bank = $result->fetch_assoc();
        $blood_bank_name = htmlspecialchars($blood_bank['fullname']);
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the donor is eligible to donate
    if ($availabilityStatus === "Not Available") {
        header("Location: donateRequest.php?error=You cannot donate until $daysUntilEligible days from your last donation.");
        exit();
    }

    $blood_bank_id = intval($_POST['blood_bank']);
    $quantity = intval($_POST['quantity']);
    $request_message = htmlspecialchars(trim($_POST['message']));

    // Insert donation request into the database
    $query = $con->prepare("INSERT INTO donation_requests (donor_email, blood_bank_id, quantity, message, request_date) VALUES (?, ?, ?, ?, NOW())");
    $query->bind_param("siss", $donor_email, $blood_bank_id, $quantity, $request_message);

    if ($query->execute()) {
        header("Location: donateRequest.php?success=Request submitted successfully!");
        exit();
    } else {
        header("Location: donateRequest.php?error=Request submission failed");
        exit();
    }
}

// Fetch previous donation requests
$query = $con->prepare("SELECT dr.id, dr.quantity, dr.message, dr.request_date, dr.status, dr.appointment_time, bb.fullname as blood_bank FROM donation_requests dr JOIN users bb ON dr.blood_bank_id = bb.id WHERE dr.donor_email = ? ORDER BY dr.request_date DESC");
$query->bind_param("s", $donor_email);
$query->execute();
$previous_requests = $query->get_result();

// Fetch donation history
$query = $con->prepare("SELECT bd.collection, bd.bloodqty, u.fullname as blood_bank FROM blood_details bd JOIN users u ON bd.bloodbank_id = u.id WHERE bd.donor_email = ? ORDER BY bd.collection DESC");
$query->bind_param("s", $donor_email);
$query->execute();
$donation_history = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
</head>
<body>
    <main class="py-2 px-8">
        <?php @include'donormenu.php'?>
        <section class="ml-64 mt-4">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Request Blood Donation</h1>
                <?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
                    <?php
                    $message = isset($_GET['error']) ? $_GET['error'] : $_GET['success'];
                    $messageClass = isset($_GET['error']) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                    ?>
                    <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $messageClass; ?>">
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>
                <form action="donateRequest.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700">Blood Bank</label>
                        <input type="text" value="<?php echo htmlspecialchars($blood_bank_name); ?>" class="w-full p-2 border border-gray-300 rounded" readonly>
                        <input type="hidden" name="blood_bank" value="<?php echo htmlspecialchars($blood_bank_id); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-gray-700">Quantity (ml)</label>
                        <input type="number" id="quantity" name="quantity" min="100" max="500" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-gray-700">Message</label>
                        <textarea id="message" name="message" rows="4" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-150">Submit Request</button>
                </form>
            </div>

            <!-- Display previous donation requests -->
            <div class="bg-white p-6 mt-8 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Previous Donation Requests</h2>
                <table class="min-w-full bg-white border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-500 text-white">
                            <th class="px-4 py-2 border border-gray-300">Request Date</th>
                            <th class="px-4 py-2 border border-gray-300">Blood Bank</th>
                            <th class="px-4 py-2 border border-gray-300">Quantity (ml)</th>
                            <th class="px-4 py-2 border border-gray-300">Message</th>
                            <th class="px-4 py-2 border border-gray-300">Status</th>
                            <th class="px-4 py-2 border border-gray-300">Appointment Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = $previous_requests->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['request_date']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['blood_bank']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['quantity']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['message']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['status']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['appointment_time']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Display donation history -->
            <div class="bg-white p-6 mt-8 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Donation History</h2>
                <table class="min-w-full bg-white border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-500 text-white">
                            <th class="px-4 py-2 border border-gray-300">Collection Date</th>
                            <th class="px-4 py-2 border border-gray-300">Blood Quantity</th>
                            <th class="px-4 py-2 border border-gray-300">Blood Bank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($history = $donation_history->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($history['collection']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($history['bloodqty']); ?></td>
                                <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($history['blood_bank']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
