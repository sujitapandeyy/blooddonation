<?php
require('connection.php');
session_start();?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
          .custom-shadow {
            filter: drop-shadow(1px 1px 20px red);
          }
    </style>
    <title>Document</title>
</head>

<body>
<section class="bg-white p-4 shadow-md py-4 fixed w-full z-50">
    <div class="container mx-auto h-14 flex items-center justify-between">
        
        <a href="index.php">
            <img src="img/logo1.png" alt="Logo" width="240" height="100" class="custom-shadow">
        </a>
        <div class="flex items-center font-bold">
            <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] === true) { ?>
                <a class="flex items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                <!-- <span class="ml-4"><?php echo htmlspecialchars($_SESSION['useremail']); ?></span> -->
            <?php } else { ?>
                <a class="flex items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="login.php">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            <?php } ?>
        </div>
    </div>
</section>
</body>

</html>
