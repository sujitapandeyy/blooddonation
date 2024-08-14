<?php
require('connection.php');
session_start();

// Fetch logged-in user's information if available
$user = null;
if (isset($_SESSION['useremail'])) {
    $user_email = $_SESSION['useremail'];
    $user_stmt = $con->prepare("SELECT id, fullname, email, phone, address FROM users WHERE email = ?");
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

    // Fetch donor details including ID
    $donor_stmt = $con->prepare("SELECT id, fullname, email, phone, address, dob, weight, gender, height, last_donation_date FROM users WHERE donor_blood_type = ?");
    $donor_stmt->bind_param("s", $selectedBloodType);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();
    $donors = [];
    while ($row = $donor_result->fetch_assoc()) {
        $donors[] = $row;
    }

    // Fetch blood bank details with available blood type
    $bank_stmt = $con->prepare("
        SELECT u.id, u.fullname, u.email, u.phone, u.address
        FROM users u
        INNER JOIN blood_details bd ON u.id = bd.bloodbank_id
        WHERE u.user_type = 'BloodBank' AND bd.bloodgroup = ?
        GROUP BY u.id, u.fullname, u.email, u.phone, u.address
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
            background-image: url('img/land2.png'); /* Update the path to your image */
            background-size: cover;
            background-position: center;
            height: 20px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php @include("header.php") ?>

    <div class="pt-24 flex flex-col items-center">
        <!-- Hero Image and Overlay -->
        <div class="hero-image w-full h-80 bg-gray-300 relative">
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <h1 class="text-4xl font-extrabold text-white">Donors and Blood Banks with Blood Type <?= htmlspecialchars($selectedBloodType) ?></h1>
            </div>
        </div>

        <!-- Display Logged-In User's Info if available -->
        <?php if ($user): ?>
            <div class="w-full max-w-7xl p-8">
                <h2 class="text-2xl font-extrabold text-gray-900">Welcome, <?= htmlspecialchars($user['fullname']) ?></h2>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="w-full max-w-7xl p-8">
            <section class="container mx-auto">
                <!-- Donors Section -->
                <h2 class="text-4xl font-extrabold text-center mb-12 text-red-600">Donors <?= htmlspecialchars($selectedBloodType) ?></h2>
                <div id="donor-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php if (count($donors) > 0): ?>
                        <?php foreach ($donors as $donor): ?>
                            <div class="bg-white shadow-md rounded-lg p-6">
                                <p class="text-lg font-semibold"><strong>Name:</strong> <?= htmlspecialchars($donor['fullname']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($donor['email']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($donor['phone']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($donor['address']) ?></p>
                                <p><strong>Gender:</strong> <?= htmlspecialchars($donor['gender']) ?></p>
                                <form action="request_donation.php" method="POST">
                                    <input type="hidden" name="donor_id" value="<?= htmlspecialchars($donor['id']) ?>">
                                    <button type="submit" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Request Blood
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-600">No donors found for this blood type.</p>
                    <?php endif; ?>
                </div>

                <!-- Blood Banks Section -->
                <h2 class="text-4xl font-extrabold text-center mb-12 text-red-600 mt-6">Blood Banks for <?= htmlspecialchars($selectedBloodType) ?></h2>
                <div id="blood-bank-list" class="mt-12 max-w-xs">
                    <?php if (count($bloodBanks) > 0): ?>
                        <?php foreach ($bloodBanks as $bank): ?>
                            <div class="relative bg-white shadow-md rounded-lg overflow-hidden">
                                <img src="img/slide1.png" alt="Blood Bank Image" class="w-full h-60 object-cover">
                                <div class="absolute inset-x-0 bottom-0 bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg">
                                    <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($bank['fullname']) ?></h3>
                                    <p class="text-gray-600">
                                        <i class="fa-solid fa-home"></i> <?= htmlspecialchars($bank['address']) ?>
                                    </p>
                                    <a href="bloodbanksresult.php?id=<?= htmlspecialchars($bank['id']) ?>" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
</body>

</html>

<?php
$con->close();
?>
