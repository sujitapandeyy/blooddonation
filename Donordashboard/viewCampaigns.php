<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: login.php?error=Login first");
    exit();
}
// Fetch all campaigns
$currentDate = date('Y-m-d');
$sql = "SELECT id, campaign_name, campaign_date FROM campaigns WHERE campaign_date >= ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $currentDate);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>

</head>

<body class="">
        <!-- <?php include("donorMenu.php"); ?> -->

<section class=" bg-white w-full px-10">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Campaigns </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <!-- Image for the campaign -->
                        <img src="../img/slide1.png" alt="Campaign Image" class="w-full h-60 object-cover">
                        <div class="absolute inset-x-0 bottom-0 bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg ">
                        <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['campaign_name']); ?></h2>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['campaign_date']); ?></p>
                            <a href="campaign_detail.php?id=<?php echo $row['id']; ?>" class="inline-block mt-2 bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-lg shadow-lg transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                View More
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-gray-600">No campaigns available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>

<?php
$con->close();
?>
