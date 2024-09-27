<?php
require('connection.php');
session_start();

// Fetch logged-in user's information if available
$user = null;
if (isset($_SESSION['useremail'])) {
    $user_email = $_SESSION['useremail'];
    $user_stmt = $con->prepare("SELECT id, fullname, email FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];  // Get the user's ID for further queries
    } else {
        echo "User not found.";
        exit();
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['blood_type'])) {
    $selectedBloodType = $_POST['blood_type'];

    // Fetch donor details
    $donor_stmt = $con->prepare("
        SELECT u.id, u.fullname, u.email, u.phone, u.address, d.dob, d.weight, d.gender,d.profile_image
        FROM users u
        INNER JOIN donor d ON u.id = d.id
        WHERE d.donor_blood_type = ?
    ");
    $donor_stmt->bind_param("s", $selectedBloodType);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();
    $donors = [];
    while ($row = $donor_result->fetch_assoc()) {
        $donors[] = $row;
    }

    // Fetch blood bank details with available blood type
   // Fetch blood bank details with available blood type
   $bank_stmt = $con->prepare("
   SELECT DISTINCT u.id, u.fullname, u.email, u.phone, u.address
   FROM users u
   INNER JOIN bloodbank b ON u.id = b.id
   INNER JOIN blood_details bd ON b.id = bd.bloodbank_id
   WHERE u.user_type = 'BloodBank' AND bd.bloodgroup = ?
");

$bank_stmt->bind_param("s", $selectedBloodType);
$bank_stmt->execute();
$bank_result = $bank_stmt->get_result();
$bloodBanks = [];
while ($row = $bank_result->fetch_assoc()) {
$bloodBanks[] = $row;
}

} else {
    header('Location: index.php'); // Redirect to the main page if accessed directly
    exit();
}
$default_image_path = 'img/defaultimage.png';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors and Blood Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        .hero-image {
            background-image: url('img/land2.png');
            /* Update the path to your image */
            background-size: cover;
            background-position: center;
            height: 20px;
        }
    </style>
</head>

<body class="bg-white">
    <?php @include("header.php") ?>

    <div class="pt-24 flex flex-col items-center">
        <!-- Hero Image and Overlay -->
        <div class="hero-image w-full h-40 bg-gray-300 relative">
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <h1 class="text-4xl font-extrabold text-white">Donors and Blood Banks with Blood Type
                    <?= htmlspecialchars($selectedBloodType) ?>
                </h1>
            </div>
        </div>

        <!-- Display Logged-In User's Info if available -->
        <?php if ($user): ?>
            <div class="">
                <!-- <h2 class="text-2xl font-extrabold text-gray-900">Welcome, <?= htmlspecialchars($user['fullname']) ?></h2>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p> -->
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <h2 class=" mt-1 w-full p-3 text-4xl font-thin font-serif text-center mb-12 text-red-500">Donors
            <?= htmlspecialchars($selectedBloodType) ?>
        </h2>
        <main class="w-full max-w-7xl px-8">
            <section class="container mx-auto">
                <!-- Donors Section -->
                <div id="donor-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 ">
                    <?php if (count($donors) > 0): ?>
                        <?php foreach ($donors as $donor): ?>
                            <div class="bg-white shadow-md rounded-lg p-6 text-center">
                                <div class="flex justify-center">
                                    <div class="mb-4 text-center">
                                        <?php $profile_image = !empty($donor['profile_image']) ? 'upload/' . htmlspecialchars($donor['profile_image']) : $default_image_path; ?>
                                        <img src="<?php echo $profile_image; ?>" alt="Profile Image"
                                            class="w-20 h-20 rounded-full mx-auto"
                                            onerror="this.onerror=null; this.src='<?php echo $default_image_path; ?>';">
                                    </div>
                                </div>
                                <p class="text-lg font-bold">
                                    <?= htmlspecialchars($donor['fullname']) ?>
                                </p>
                                <p>Email: <?= htmlspecialchars($donor['email']) ?></p>
                                <p>Phone: <?= htmlspecialchars($donor['phone']) ?></p>
                                <p>
                                    Address:
                                    <?php
                                    $address = htmlspecialchars($donor['address']);
                                    $words = explode(' ', $address); // Split address into words
                                    $firstThreeWords = implode(' ', array_slice($words, 0, 1)); // Get the first three words
                                    echo $firstThreeWords;
                                    ?>
                                </p>
                                <p>Gender: <?= htmlspecialchars($donor['gender']) ?></p>
                                <!-- <p>Date of Birth: <?= htmlspecialchars($donor['dob']) ?></p> -->
                                <!-- <p>Weight: <?= htmlspecialchars($donor['weight']) ?> kg</p> -->
                                <!-- <p>Height: <?= htmlspecialchars($donor['height']) ?> cm</p> -->
                                <!-- <p>Status:<span class="text-green-500"></span> <?= htmlspecialchars($donor['availability']) ?> -->
                                </p>
                                <?php
                                if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true) {
                                    echo '
                                    <form action="request_donation.php" method="POST">
                                        <input type="hidden" name="donor_id" value="' . htmlspecialchars($donor["id"]) . '">
                                        <button type="submit" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            Request Blood
                                        </button>
                                    </form>
                                    ';
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex justify-center">
    <p class="text-center text-gray-600">No donors found for this blood type.</p>
</div>
                    <?php endif; ?>
                </div>
            </section>

            <h2 class=" mt-10 w-full p-3 text-4xl text-center font-serif mb-12 text-red-500">Blood Banks
                for <?= htmlspecialchars($selectedBloodType) ?></h2>
            <section class="container mx-auto">
                <!-- Blood Banks Section -->
                <div id="blood-bank-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                
                    <?php if (count($bloodBanks) > 0): ?>
                        <?php foreach ($bloodBanks as $bank): ?>


                            <?php
                            $bloodBankId = $bank['id'];
                            $avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
                            $avgRatingStmt->bind_param("i", $bloodBankId);
                            $avgRatingStmt->execute();
                            $avgResult = $avgRatingStmt->get_result()->fetch_assoc();
                            $averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';
                            ?>
                            <div
                                class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105 mb-10">

                                <img src="img/slide1.png" alt="Blood Bank Image" class="w-full h-50 object-cover p-5">
                                <div class="bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg" style="min-height: 230px;">
                                    <h3 class="text-xl font-semibold font-serif text-gray-900">
                                        <?= htmlspecialchars($bank['fullname']) ?></h3>
                                    <p class="text-gray-600">
                                        <i class="fa-solid fa-home"></i>
                                        <?php
                                        $address = htmlspecialchars($bank['address']);
                                        $words = explode(' ', $address);
                                        $firstThreeWords = implode(' ', array_slice($words, 0, 3)); // Get the first three words
                                        echo $firstThreeWords;
                                        ?>
                                    </p>
                                    <h3 class="">
                                        <?php
                                        if ($averageRating === 'No ratings yet') {
                                            echo htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8');
                                        } else {
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $averageRating) {
                                                    echo '<span class="text-2xl text-yellow-500">&#9733;</span>'; // Filled star
                                                } else {
                                                    echo '<span class="text-2xl">&#9734;</span>'; // Empty star
                                                }
                                            }
                                            echo ' (' . htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') . ')'; // Display numeric rating
                                        }
                                        ?>
                                    </h3>
                                    <!-- <img src="upload/<?php echo $bank['image']; ?>"alt="Blood Bank Image" class="w-full h-60 object-cover"> -->

                                    <a href="bloodbanksresult.php?id=<?= htmlspecialchars($bank['id']) ?>"
                                        class="mt-4 inline-block bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        View Blood Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-600">No blood banks found for this blood type.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    <?php @include 'footor.php'; ?>

</body>

</html>

<?php
$con->close();
?>