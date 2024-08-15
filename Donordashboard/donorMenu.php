<?php
require ('../connection.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit(); 
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
            <!-- Dashboard Section -->
            <div class="px-4 py-6 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">Dashboard</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tachometer-alt mr-3"></i> Donor Dashboard
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Blood Management Section -->
            <div class="px-4 py-6 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">Donation Request</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="bloodrequest.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-tint mr-3"></i> View blood request
                        </a>
                    </li>
                    <li>
                        <a href="donaterequest.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-building mr-3"></i> Donate request
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Campaigns Section -->
            <div class="px-4 py-6 border-b border-gray-300">
                <h2 class="text-gray-600 text-sm font-semibold mb-2">Campaigns</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="viewCampaigns.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-flag mr-3"></i> Available Campaigns
                        </a>
                    </li>
                    <li>
                        <a href="viewBloodBank.php"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition duration-150">
                            <i class="fas fa-building mr-3"></i> View BloodBank
                        </a>
                    </li>
                </ul>

            </div>
        </nav>
        <div class="flex items-center justify-center  mb-6">
            <?php if (isset($_SESSION['bankemail'])) { ?>
                <a href="../logout.php"
                   class="flex items-center bg-red-500 text-white font-bold px-5 py-2 rounded-full hover:bg-red-600 transition duration-150">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            <?php } ?>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8">
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
            Welcome to RaktaSewa, <span class="text-red-600"><?php echo htmlspecialchars($_SESSION['donorname']); ?></span>
        </h1>
        <a href="editDonor.php?id=<?php echo urlencode($_SESSION['donoremail']); ?>"
           class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-150 flex items-center">
            <i class="fas fa-user mr-2"></i> Edit Profile
        </a>
    </div>

  
</main>

</body>

</html>
