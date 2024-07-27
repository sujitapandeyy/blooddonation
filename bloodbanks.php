<?php
require ('connection.php');
session_start();

$bloodBankQuery = $con->prepare("SELECT * FROM users WHERE user_type = 'BloodBank'");
$bloodBankQuery->execute();
$bloodBankResult = $bloodBankQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Blood Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>

</head>

<body class="bg-gray-100">
    <?php @include ("dashboard.php") ?>

    <section class="container max-w-7xl mx-auto p-5 h-screen mt-12">
    <h2 class="text-3xl font-bold text-center mb-6 text-red-500">Available Blood Banks</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <?php while ($row = $bloodBankResult->fetch_assoc()) { ?>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 flex flex-col items-center justify-center">
                    <img src="img/land4.png" alt="Logo" width="180" height="100" class="mb-4">
                    <div class="justify-start">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['fullname']); ?></h3>
                    <p class="text-gray-600 mt-2"><i class="fa-solid fa-envelope"></i>
                        <?php echo htmlspecialchars($row['email']); ?></p>
                    <p class="text-gray-600 mt-2"><i class="fa-solid fa-phone"></i>
                        <?php echo htmlspecialchars($row['phone']); ?></p>
                    <p class="text-gray-600 mt-2"><i class="fa-solid fa-home"></i>
                        <?php echo htmlspecialchars($row['address']); ?></p>
                        </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

</body>

</html>