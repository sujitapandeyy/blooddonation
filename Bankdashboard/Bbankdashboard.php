<?php
require('../connection.php');
session_start();

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
$bankId = $bank['id'];

$sql = "SELECT bloodgroup, SUM(bloodqty) as total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bankId);
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
                while ($row = $result->fetch_assoc()) {
                    echo "['".$row["bloodgroup"]."', ".$row["total_qty"]."],";
                }
                ?>
            ]);

            var options = {
                title: 'Total Available Blood',
                is3D: true,
                pieHole: 0.0,
                backgroundColor: '#e5e7eb', 
                // chartArea: {
                //     backgroundColor: '#e5e7eb' // Set the chart area background to white
                // }
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>
</head>

<body class="bg-gray-200">
    <!-- <?php include("bloodbankmenu.php"); ?> -->
    <div class="max-w-lg mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4 text-left">Blood Type Distribution</h2>
        <div id="piechart" class="w-full h-64"></div>
    </div>
</body>

</html>
