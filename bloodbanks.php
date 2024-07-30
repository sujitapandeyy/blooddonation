<?php
require('connection.php');
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        .bg-img {
            background-image: url('./img/slide5.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
    </style>
</head>

<body class="font-Roboto">
    <section class="w-full p-10 mt-12 bg-orange-100">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Available Blood Banks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-3xl mx-auto">
            <?php while ($row = $bloodBankResult->fetch_assoc()) { ?>
                <div class="bg-white shadow-lg rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <div class="p-7 flex flex-col items-center justify-center ">
                        <img src="img/slide1.png" alt="Logo" class="mb-4 w-60 h-24 object-contain">
                        <div class="justify-start">
                            <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['fullname']); ?></h3>
                            <p class="text-gray-600 mt-2"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                            <p class="text-gray-600 mt-2"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></p>
                            <p class="text-gray-600 mt-2"><i class="fa-solid fa-home"></i> <?php echo htmlspecialchars($row['address']); ?></p>
                            <a href="bloodbanksresult.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">View Blood Details</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</body>

</html>
