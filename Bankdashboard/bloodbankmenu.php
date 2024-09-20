<?php
require ('../connection.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
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
        /* .custom-shadow {
    filter: drop-shadow(10px 10px 10px rgba(255, 0, 0, 0.5)); Adjust color and opacity */
/* } */

    </style>
</head>

<body class="bg-gray-100 font-sans antialiased">
    <!-- Sidebar -->
    <aside class="bg-white shadow-md fixed inset-y-0 left-0 w-64 flex flex-col">
    <div class="flex items-center justify-center mt-6 mb-4">
            <a href="index.php">
                <img src="../img/logo11.png" alt="Logo" class="h-16 w-auto">
            </a>
        </div>
        <nav class="flex-1">
                <div class=" justify-between">
                <div class="px-4 py-2 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">BloodBank Dashboard</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="Bbankdashboard.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i> Dashboard
                        </a>
                    </li>
                </ul>
            </div>
                <div class="px-4 py-2 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">blood details</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="addBlood.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i>Add blood details
                        </a>
                    </li>
                    <li>
                        <a href="viewBloodDetail.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-building mr-3"></i> view blood details
                        </a>
                    </li>
                </ul>
            </div>
                <div class="px-4 py-2 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">Campaign details</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="addCampaigns.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i>Add campaign details
                        </a>
                    </li>
                    <li>
                        <a href="viewCampaigns.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-building mr-3"></i> view campaigns details
                        </a>
                    </li>
                </ul>
            </div>
                <div class="px-4 py-2 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">Donation request</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="ViewDonationRequest.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i>view Donation Request
                        </a>
                    </li>
                    <li>
                        <a href="viewBloodRequest.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i>view Blood Request
                        </a>
                    </li>
                   
                </ul>
            </div>
                    <div class="flex flex-col items-center mt-20">
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
                        </aside>
    <section class="ml-72 p-8">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">
                Welcome to RaktaSewa &nbsp;<span class="text-red-600"><?php echo htmlspecialchars($_SESSION['bankname']); ?></span>
            </h1>
            <a href="manage-donors.php"
                class="text-white font-semibold p-4 rounded-lg hover:bg-cyan-700 transition flex items-center">
                <i class="fas fa-user mr-3"></i> Edit account
            </a>
        </div>
    </section>
    
</body>

</html>
