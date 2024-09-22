<?php
require('connection.php');
?>

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
            filter: drop-shadow(10px 10px 10px red);
        }
    </style>
    <title>Document</title>
</head>

<body>
<section class="bg-white p-4 shadow-md py-4 fixed w-full z-50">
    <div class="container mx-auto h-14 flex items-center justify-between mt-2">
        
        <a href="index.php">
            <img src="img/logo1.png" alt="Logo" width="240" height="100" class="custom-shadow">
        </a>
        <div class="flex items-center font-bold">

            <!-- User Section -->
            <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] === true): ?>
                <a class="flex mr-2 items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                <a class="flex items-center bg-blue-500 text-white font-bold py-2 px-4 rounded-full hover:bg-blue-600 transition" href="userhistory.php">
                    <i class="mr-2"></i> History
                </a>


             
         <div class="name_logo">
          
             <?php
                 $email = $_SESSION['useremail'];
                 $trimmedemail = trim($email); // Trim any whitespace from the beginning and end of the user's full name
                 $firstLetter = substr($trimmedemail, 0, 3); // Retrieve the first character of the trimmed user's full name
                 $firstLetterCap = ucfirst($firstLetter);
                 echo $firstLetterCap;
             ?>
            </p>
        </div>
              



            <?php endif; ?>

            <!-- Donor Section -->
            <?php if (isset($_SESSION['Dloggedin']) && $_SESSION['Dloggedin'] === true): ?>
                <a class="flex mr-2 items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                <a class="flex items-center bg-blue-500 text-white font-bold py-2 px-4 rounded-full hover:bg-blue-600 transition" href="Donordashboard/bloodrequest.php">
                    <i class="mr-2"></i> View Blood Request
                </a>
                <!-- <a class="flex items-center bg-blue-500 text-white font-bold py-2 px-4 rounded-full hover:bg-blue-600 transition" href="Donordashboard/donaterequest.php">
                    <i class="mr-2"></i> Donate Request
                </a> -->
            <?php endif; ?>

            <!-- Show Login if no user or donor is logged in -->
            <?php if (!isset($_SESSION['Uloggedin']) && !isset($_SESSION['Dloggedin'])): ?>
                <a class="flex items-center bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition" href="login.php">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            <?php endif; ?>

        </div>
    </div>
</section>
</body>

</html>
