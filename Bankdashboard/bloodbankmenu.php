<?php
require ('../connection.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
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
    filter: drop-shadow(10px 10px 10px rgba(255, 0, 0, 0.5)); /* Adjust color and opacity */
}

    </style>
</head>

<body class="bg-gray-200">
    <!-- Sidebar -->
    <section class="bg-gray-700 p-4 shadow-md fixed w-72 h-full flex flex-col">
        <div class="h-full flex flex-col">
                <!-- <h1>BloodBank </h1> -->
                <div class="custom-shadow">

                <a href="index.php">
                <img src="../img/ll.png" alt="Logo" class="custom-shadow mt-6 h-auto w-3/4">
                </a>
        </div>
            <nav class="justify-between">
                <div class="mt-20 justify-between">
                    <ul>
                        <li class="hover:bg-gray-900 bg-gray-800 rounded-full m-1">
                            <a href="Bbankdashboard.php"
                                class="flex items-center text-white font-semibold p-4 m rounded-lg transition">
                                <i class="fas fa-tachometer-alt mr-3"></i> BloodBank Dashboard
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 bg-gray-800 rounded-full m-1">
                            <a href="addBlood.php"
                                class="flex items-center text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition">
                                <i class="fas fa-tint mr-3"></i> Add blood details
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 bg-gray-800 rounded-full m-1">
                            <a href="viewBloodDetail.php"
                                class="flex items-center text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition">
                                <i class="fas fa-building mr-3"></i> view blood details
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 bg-gray-800 rounded-full m-1">
                            <a href="addCampaigns.php"
                                class="flex items-center text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition">
                                <i class="fas fa-flag mr-3"></i> Add campaigns
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 bg-gray-800 rounded-full m-1">
                            <a href="viewCampaigns.php"
                                class="flex items-center text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition">
                                <i class="fas fa-building mr-3"></i> view campaigns
                            </a>
                        </li>
                     
                    </ul>
                    <div class="flex flex-col items-center mt-48">
                        <?php if (isset($_SESSION['bankemail'])) { ?>
                            <a class="flex items-center bg-red-500 text-white font-bold px-5 py-3 rounded-full hover:bg-red-600 transition"
                                href="../logout.php">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </nav>
        </div>
    </section>
    <section class="ml-72 p-8">
        <div class="bg-gray-800 p-4 rounded-lg shadow-lg flex items-center justify-between">
            <h1 class="text-white text-3xl flex items-center">
                Welcome to RaktaSewa<span class="ml-2"><?php echo htmlspecialchars($_SESSION['bankname']); ?></span>
            </h1>
            <a href="manage-donors.php"
                class="text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition flex items-center">
                <i class="fas fa-user mr-3"></i> Edit account
            </a>
        </div>
    </section>
    
</body>

</html>
