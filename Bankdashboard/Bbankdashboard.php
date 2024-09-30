<?php
require('../connection.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
    exit(); 
}

$bankEmail = $_SESSION['bankemail'];
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $bankEmail);
$stmt->execute();
$result = $stmt->get_result();
$bank = $result->fetch_assoc();
$bloodBankId = $bank['id'];

// Fetch total donations count for this blood bank
$query = "
    SELECT COUNT(bd.id) AS total_donations
    FROM blood_details bd 
    WHERE bd.bloodbank_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $bloodBankId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalDonations = $row['total_donations'] ?? 0;

// Fetch total pending donation requests for this blood bank
$totalPendingQuery = "
    SELECT COUNT(*) AS total_pending_requests 
    FROM donation_requests 
    WHERE status = 'Pending' AND blood_bank_id = ?";
$stmt = $con->prepare($totalPendingQuery);
$stmt->bind_param("i", $bloodBankId);
$stmt->execute();
$result = $stmt->get_result();
$rowPending = $result->fetch_assoc();
$totalDonationRequests = $rowPending['total_pending_requests'] ?? 0;

// Fetch total donation requests for this blood bank
$totalDonationRequestsQuery = "
    SELECT COUNT(*) AS total_donation_requests 
    FROM blood_requests 
    WHERE bloodbank_id = ?";
$stmt = $con->prepare($totalDonationRequestsQuery);
$stmt->bind_param("i", $bloodBankId);
$stmt->execute();
$result = $stmt->get_result();
$rowRequests = $result->fetch_assoc();
$totalPendingRequests = $rowRequests['total_donation_requests'] ?? 0;

// Fetch blood type distribution
$sql = "SELECT bloodgroup, SUM(bloodqty) as total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bloodBankId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Type Distribution</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>  
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Blood Group', 'Quantity'],
                <?php
                // Resetting the result pointer to fetch blood group data
                while ($row = $result->fetch_assoc()) {
                    echo "['".$row["bloodgroup"]."', ".$row["total_qty"]."],";
                }
                ?>
            ]);

            var options = {
                title: 'Total Available Blood',
                is3D: true,
                pieHole: 0.0,
                backgroundColor: '#ffffff',
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>
</head>

<body class="bg-white">
    <!-- <?php include("bloodbankmenu.php"); ?> -->
    <div>

        <div class="mx-auto grid grid-cols-3 gap-4 mb-4 ml-80">
            <!-- Total Donation Requests -->
            <div class="bg-blue-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Pending Blood Requests</h3>
                <p class="text-2xl"><?php echo htmlspecialchars($totalPendingRequests); ?></p>
            </div>

            <!-- Total Donations -->
            <div class="bg-yellow-400 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Total Donations</h3>
                <p class="text-2xl"><?php echo htmlspecialchars($totalDonations); ?></p>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Total Donation Requests</h3>
                <p class="text-2xl"><?php echo htmlspecialchars($totalDonationRequests); ?></p>
            </div>
        </div>
        <div class="max-w-lg mx-auto p-1">
            <h2 class="text-2xl font-bold m-4 text-left">Blood Type Distribution</h2>
            <div id="piechart" class="w-full h-64"></div>
        </div>
    </div>
</body>
</html>
