<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['Adminemail'])) {
    header("Location: login.php?error=Login first");
    exit(); // Ensure script execution stops after redirection
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        .custom-shadow {
    filter: drop-shadow(10px 10px 20px white);
}
    </style>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <section class="bg-blue-600 p-4 shadow-md fixed w-70 h-full flex flex-col">
        <div class="h-full flex flex-col justify-between">
            <div class="custom-shadow">
            <img src="../img/logo1.png" alt="Logo"  class="w-80">
            <a href="index.php">
            </a>
        </div>
        <nav class=" ">
            <ul>
                <li>
                    <a href="dashboard.php" class="flex items-center text-white font-semibold py-2 px-4 rounded-lg hover:bg-cyan-700 transition">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="manage-donors.php" class="flex items-center text-white font-semibold py-2 px-4 rounded-lg hover:bg-cyan-700 transition">
                        <i class="fas fa-users mr-3"></i> Manage Donors
                    </a>
                </li>
                <li>
                    <a href="bankregister.php" class="flex items-center text-white font-semibold py-2 px-4 rounded-lg hover:bg-cyan-700 transition">
                        <i class="fas fa-building mr-3"></i> Add Blood Banks
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="flex items-center text-white font-semibold py-2 px-4 rounded-lg hover:bg-cyan-700 transition">
                        <i class="fas fa-chart-line mr-3"></i> Reports
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center text-white font-semibold py-2 px-4 rounded-lg hover:bg-cyan-700 transition">
                        <i class="fas fa-cogs mr-3"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>
        <div class="flex flex-col items-center mt-4">
                <?php if (isset($_SESSION['Adminemail'])) { ?>
                    <a class="flex items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="../logout.php">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                <?php }?>
            </div>
            </div>
    </section>

    <!-- Main Content -->
    <div class="  p-10 bg-gray-100 ">
        <!-- Your main content goes here -->
    </div>
</body>

</html>
