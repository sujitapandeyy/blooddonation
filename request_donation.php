<?php
require('connection.php');
session_start();

// Initialize default values
$default_name = '';
$default_email = '';
$default_phone = '';
$default_address = '';

// Check if the user is logged in
if (isset($_SESSION['useremail'])) {
    $user_email = $_SESSION['useremail'];
    $user_stmt = $con->prepare("SELECT id, fullname, email, phone, address FROM users WHERE email = ?");
    if (!$user_stmt) {
        die("Prepare failed: " . $con->error);
    }
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];  // Get the user's ID for further queries

        // Set default values for logged-in user
        $default_name = htmlspecialchars($user['fullname']);
        $default_email = htmlspecialchars($user['email']);
        $default_phone = htmlspecialchars($user['phone']);
        $default_address = htmlspecialchars($user['address']);
    } else {
        echo "User not found.";
        exit();
    }
}

// Initialize success and error message variables
$success_message = '';
$error_message = '';

// Handle POST request to fetch donor details and submit a donation request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donor_id'])) {
    $donor_id = $_POST['donor_id'];

    // Fetch donor details from both `users` and `donor` tables
    $donor_stmt = $con->prepare("
        SELECT u.fullname, u.email, u.phone, u.latitude, u.longitude, d.donor_blood_type, d.dob, d.weight, d.gender, d.last_donation_date
        FROM users u
        JOIN donor d ON u.id = d.id
        WHERE u.id = ?
    ");
    if (!$donor_stmt) {
        die("Prepare failed: " . $con->error);
    }
    $donor_stmt->bind_param("i", $donor_id);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();

    if ($donor_result->num_rows > 0) {
        $donor = $donor_result->fetch_assoc();
        $donor_blood_type = $donor['donor_blood_type'];  // Fetch donor's blood type

        if (isset($_POST['request'])) {
            $user_name = $_POST['name'];
            $user_email = $_POST['email'];
            $user_phone = $_POST['phone'];
            $donation_address = $_POST['donation_address'];
            $quantity = $_POST['quantity'];
            $message = $_POST['message'];
            $donor_email = $donor['email'];  // Fetch donor's email for insertion

            // Insert donation request into the database
            $request_stmt = $con->prepare("
                INSERT INTO donorblood_request 
                (donor_id, donor_email, requester_email, requester_name, requester_phone, donation_address, quantity, message, request_date, status, bloodgroup) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', ?)
            ");
            if (!$request_stmt) {
                die("Prepare failed: " . $con->error);
            }
            $request_stmt->bind_param("issssssss", $donor_id, $donor_email, $user_email, $user_name, $user_phone, $donation_address, $quantity, $message, $donor_blood_type);
            $request_stmt->execute();

            if ($request_stmt->affected_rows > 0) {
                $success_message = 'Request submitted successfully.';
            } else {
                $error_message = 'Error submitting request.';
            }
        }
    } else {
        $error_message = 'Donor not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="javascript/addressInput.js"></script>
</head>
<body class="">
    <?php include("header.php"); ?>

    <div class="pt-24 flex flex-col items-center">
        <main class="w-full max-w-4xl p-8 bg-white shadow-md rounded-lg flex">
            <!-- Donor Details Section -->
            <div class="w-1/3 pr-4">
                <h2 class="text-xl font-bold mb-4">Donor Details</h2>
                <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                    <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($donor['fullname'] ?? '') ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($donor['email'] ?? '') ?></p>
                    <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($donor['phone'] ?? '') ?></p>
                    <p class="mb-2"><strong>Blood Type:</strong> <?= htmlspecialchars($donor['donor_blood_type'] ?? '') ?></p>
                    <p class="mb-2"><strong>Gender:</strong> <?= htmlspecialchars($donor['gender'] ?? '') ?></p>
                    <p class="mb-2"><strong>DOB:</strong> <?= htmlspecialchars($donor['dob'] ?? '') ?></p>
                    <!-- <p class="mb-2"><strong>Last Donation Date:</strong> <?= htmlspecialchars($donor['last_donation_date'] ?? '') ?></p> -->
                    <!-- <p class="mb-2"><strong>Availability:</strong> <?= htmlspecialchars($donor['availability'] ?? '') ?></p> -->
                </div>
            </div>

            <!-- Request Form Section -->
            <div class="w-2/3 pl-4">
                <h1 class="text-2xl font-bold mb-4">Request Blood</h1>
                <?php if ($success_message) : ?>
                    <div class="p-4 rounded-md text-center font-semibold bg-green-100 text-green-800">
                        <p><?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($error_message) : ?>
                    <div class="p-4 rounded-md text-center font-semibold bg-red-100 text-red-800">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                <form action="request_donation.php" method="POST">
                    <input type="hidden" name="donor_id" value="<?= htmlspecialchars($donor_id) ?>">

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="name">Your Name</label>
                        <input
                            class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                            type="text" id="name" name="name" placeholder="Enter your name" value="<?= $default_name ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-bold mb-2">Your Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your Email" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        value="<?= $default_email ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-bold mb-2">Your Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="Enter your phone" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        value="<?= $default_phone ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                    </div>

                    <div class="mb-4">
                        <label for="donation_address" class="block text-gray-700 font-bold mb-2">Donation Address</label>
                        <input type="text" id="donation_address" name="donation_address" placeholder="Enter donation address" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required value="<?= $default_address ?>">
                    </div>

                    <div class="mb-4">
                        <label for="quantity" class="block text-gray-700 font-bold mb-2">Quantity</label>
                        <input type="number" id="quantity" name="quantity"min="100" max="500" placeholder="Enter quantity" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="block text-gray-700 font-bold mb-2">Message (optional)</label>
                        <textarea id="message" name="message" placeholder="Enter a message" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" name="request" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php @include 'footor.php'; ?>
</body>
</html>
