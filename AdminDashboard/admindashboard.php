<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['Adminemail'])) {
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-white flex h-screen">
    <div class="flex w-full">
        <div class="w-64 bg-gray-100 text-gray-800 flex flex-col items-center p-5">
            <div class="mb-12">
                <img src="../img/logo11.png" alt="Logo" class="w-full h-auto filter drop-shadow-lg">
            </div>
            <ul class="w-full">
                <li class="w-full mb-3">
                    <a href="#" data-content="dashboard" class="block w-full py-2 text-center bg-blue-500 rounded transition duration-300 hover:bg-red-600 text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="w-full mb-3">
                    <a href="#" data-content="users" class="block w-full py-2 text-center bg-blue-500 rounded transition duration-300 hover:bg-red-600 text-white"><i class="fas fa-users"></i> Manage User</a>
                </li>
                <li class="w-full mb-3">
                    <a href="#" data-content="bloodbank" class="block w-full py-2 text-center bg-blue-500 rounded transition duration-300 hover:bg-red-600 text-white"><i class="fas fa-tint"></i> Add BBank</a>
                </li>
                <li class="w-full mb-3">
                    <a href="#" data-content="view" class="block w-full py-2 text-center bg-blue-500 rounded transition duration-300 hover:bg-red-600 text-white"><i class="fas fa-user"></i> View BloodBanks</a>
                </li>
            </ul>
            <button id="logout-btn" class="mt-auto w-32 py-2 bg-red-600 rounded text-center text-lg transition duration-300 hover:bg-gray-600 text-white">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
        <div class="flex-grow p-5 bg-white overflow-y-auto">
            <div class="bg-gray-200 p-5 rounded-lg drop-shadow-lg mb-5" id="welcome-panel">
                <h2 class="text-xl font-semibold text-gray-700">Welcome Admin</h2>
            </div>
            <div id="dynamic-content">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.w-full a').on('click', function(e) {
                e.preventDefault();
                var content = $(this).data('content');
                if (content === 'users') {
                    $.ajax({
                        url: 'manageUser.php',
                        method: 'GET',
                        success: function(data) {
                            $('#dynamic-content').html(data);
                        }
                    });
                }
                var content = $(this).data('content');
                if (content === 'dashboard') {
                    $.ajax({
                        url: 'Dashboards.php',
                        method: 'GET',
                        success: function(data) {
                            $('#dynamic-content').html(data);
                        }
                    });
                }
                var content = $(this).data('content');
                if (content === 'bloodbank') {
                    $.ajax({
                        url: 'bankregister.php',
                        method: 'GET',
                        success: function(data) {
                            $('#dynamic-content').html(data);
                        }
                    });
                }
                var content = $(this).data('content');
                if (content === 'view') {
                    $.ajax({
                        url: 'ViewBloodBank.php',
                        method: 'GET',
                        success: function(data) {
                            $('#dynamic-content').html(data);
                        }
                    });
                }
            });
            $('#logout-btn').on('click', function() {
                window.location.href = '../logout.php';
            });
        });
    </script>
</body>
</html>
