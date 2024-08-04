<?php
require('connection.php');
session_start();

$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Blood Types</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style>
        /* .bg-img {
            background-image: url('./img/bg.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
         */
    </style>
</head>
<body class="font-Roboto bg-white">
<div class="">
                    <img src="./img/footer.png" alt="Slide 2" class="w-full h-28 rounded">
                </div>
    <section class="max-w-7xl mx-auto mt-0 p-3">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Available Blood Types</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
            <?php foreach ($bloodTypes as $index => $bloodType): ?>
                <form method="POST" action="bloodTypeResult.php" class="bg-white shadow-md rounded-lg overflow-hidden transform transition-transform  hover:scale-105">
                    <input type="hidden" name="blood_type" value="<?= htmlspecialchars($bloodType) ?>">
                    <button type="submit" class="w-full h-24 <?= $color ?> text-black text-xl font-bold flex items-center justify-center">
                        <?= htmlspecialchars($bloodType) ?>
                    </button>
                    <div class="p-4">
                        <p class="text-gray-600 text-center text-sm">Find nearest donors of bloodtype <?= htmlspecialchars($bloodType) ?>.</p>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>
