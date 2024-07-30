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
            /* filter: drop-shadow(10px 10px 20px gray); */
        }
    </style>
</head>

<body class="bg-gray-200">
   <?php 
   @include ("bloodbankmenu.php");?>
 
</body>

</html>