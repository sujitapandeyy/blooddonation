<?php
require('connection.php');

// Query to fetch the total number of donors
$sqlDonors = "SELECT COUNT(id) AS total_donors FROM donor";
$resultDonors = $con->query($sqlDonors);
$totalDonors = 0;
if ($resultDonors->num_rows > 0) {
    $row = $resultDonors->fetch_assoc();
    $totalDonors = $row['total_donors'];
}

// Query to fetch the total number of blood units collected
$sqlBloodUnits = "SELECT SUM(bloodqty) AS total_units FROM blood_details"; 
// Assuming you have a field called `units_collected` in `blood_collections` table
$resultBloodUnits = $con->query($sqlBloodUnits);
$totalBloodUnits = 0;
if ($resultBloodUnits->num_rows > 0) {
    $row = $resultBloodUnits->fetch_assoc();
    $totalBloodUnits = $row['total_units'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .slant-left {
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%);
        }

        /* Custom Slant for Blood Units Collected Section */
        .slant-right {
            clip-path: polygon(0 0, 90% 0, 100% 100%, 10% 100%);
        }

        /* Ensures perfect height for the slanted section */
        .slanted-section {
            height: 150px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Donor Registered Section -->
        <div class="bg-red-200 text-center text-white flex-1 slant-left py-10">
            <h2 class="text-4xl font-bold"><?php echo $totalDonors; ?></h2>
            <p class="text-lg mt-2">Donor Registered</p>
        </div>
        <!-- Blood Units Collected Section -->
        <div class="bg-red-800 text-center text-white flex-1 slant-right py-10">
            <h2 class="text-4xl font-bold"><?php echo $totalBloodUnits; ?></h2>
            <p class="text-lg mt-2">Blood Units Collected</p>
        </div>
    </div>
</body>
</html>
