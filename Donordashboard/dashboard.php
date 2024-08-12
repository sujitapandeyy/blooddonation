<?php
require('../connection.php');
session_start();

if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit(); 
}

$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Type Distribution</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
   
    </script>
</head>

<body class="bg-white">
    <!-- <?php include("donorMenu.php"); ?> -->
    <Section class="ml-64 px-4">
        <h2 class="text-2xl font-bold mb-4 "></h2>
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Recent Activities</h2>
        <!-- Example of adding recent activities or notifications -->
        <ul class="space-y-2">
            <li class="border-b border-gray-300 py-2">
                <span class="font-medium">Donation Request:</span> You have a new donation request.
            </li>
            <li class="border-b border-gray-300 py-2">
                <span class="font-medium">Upcoming Campaign:</span> Your next campaign is scheduled for next week.
            </li>
        </ul>
    </div>
    </Section>
</body>

</html>
