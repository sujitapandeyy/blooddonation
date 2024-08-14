<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

$campaignId = $_GET['id'];

$sql = "SELECT * FROM campaigns WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $campaignId);
$stmt->execute();
$result = $stmt->get_result();
$campaign = $result->fetch_assoc();

if (!$campaign) {
    echo "Campaign not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>

</head>

<body class="">
        <!-- <?php include("donorMenu.php"); ?> -->

    <section class="ml-64 px-8 container mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center"><?php echo htmlspecialchars($campaign['campaign_name']); ?></h1>
        <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl">
        <img src="../img/slide1.png" alt="Campaign Image" class="w-full h-60 object-cover">

            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($campaign['contact_number']); ?></p>
            <p><strong>Campaign Date:</strong> <?php echo htmlspecialchars($campaign['campaign_date']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($campaign['description']); ?></p>
            <!-- <p><strong>Location:</strong> <?php echo htmlspecialchars($campaign['location']); ?></p> -->
            <p ><strong>Location:</strong><?php
                        // $location = $row['location'];
                        // $words = explode(' ', $location);
                        // $firstTwoWords = implode(' ', array_slice($words, 0, 2));
                        // echo htmlspecialchars($firstTwoWords);
                        echo htmlspecialchars($campaign['location']);?></p>
        </div>
        <a href="viewCampaigns.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 transition-colors duration-300 text-white py-2 px-4 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            Back to Campaigns
        </a>
    </section>
</body>

</html>

<?php
$con->close();
?>
