<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: login.php?error=Login first");
    exit();
}

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
        /* .bgcolor{
            background-color:#fFFAFA;
        } */
    </style>
</head>

<body class="font-Roboto">
            <!-- <?php include("donorMenu.php"); ?> -->

    <section class=" w-full p-10">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Available Blood Banks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-3xl mx-auto">
            <?php while ($row = $bloodBankResult->fetch_assoc()) { ?>
                <div class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <!-- Image for the blood bank -->
                    <img src="../img/slide1.png" alt="Blood Bank Image" class="w-full h-60 object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg ">
                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['fullname']); ?></h3>
                        <p class="text-gray-600 mt-2"><i class="fa-solid fa-home"></i> <?php echo htmlspecialchars($row['address']); ?></p>
                        <a href="bloodbanksresult.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            View Blood Details
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</body>

</html>

<?php
$con->close();
?>
