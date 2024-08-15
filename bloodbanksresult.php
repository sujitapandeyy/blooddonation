<?php
require('connection.php');
session_start();

// Initialize messages
$success_message = '';
$error_message = '';

// Default values for logged-in user
$default_name = '';
$default_email = '';
$default_phone = '';
$default_address = '';

// Fetch user details if logged in
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
        $user_id = $user['id'];

        // Set default values for logged-in user
        $default_name = htmlspecialchars($user['fullname']);
        $default_email = htmlspecialchars($user['email']);
        $default_phone = htmlspecialchars($user['phone']);
        $default_address = htmlspecialchars($user['address']);
    } else {
        $error_message = "User not found.";
        $user_id = null;
    }
} else {
    $user_id = null; // No user logged in
}

// Check if blood bank ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing blood bank ID.");
}

$bloodBankId = intval($_GET['id']);

// Fetch blood details
$sql = "SELECT bloodgroup, SUM(bloodqty) AS total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param('i', $bloodBankId);
$stmt->execute();
$bloodDetailsResult = $stmt->get_result();

// Fetch blood bank details
$bankSql = "SELECT * FROM users WHERE id = ?";
$bankStmt = $con->prepare($bankSql);
if (!$bankStmt) {
    die("Prepare failed: " . $con->error);
}
$bankStmt->bind_param('i', $bloodBankId);
$bankStmt->execute();
$bankResult = $bankStmt->get_result();
$bloodBank = $bankResult->fetch_assoc();

// Handle blood request form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodgroup = htmlspecialchars($_POST['bloodgroup']);
    $quantity = intval($_POST['quantity']);
    $requester_name = htmlspecialchars($_POST['requester_name']);
    $requester_email = htmlspecialchars($_POST['requester_email']);
    $requester_phone = htmlspecialchars($_POST['requester_phone']);
    $message = htmlspecialchars($_POST['message']);
    $donation_address = htmlspecialchars($_POST['donation_address']); 

    // Set donor_id and donor_email based on login status
    // if ($user_id) {
    //     $donor_id = $user_id; // Use the logged-in user's ID
    //     $donor_email = $default_email;
    // } else {
        $donor_id = $bloodBank['id']; // No user logged in
        $donor_email = $bloodBank['email']; // Use the blood bank's email
    // }

    // Prepare and execute insert statement
    $insert_sql = "INSERT INTO blood_requests (bloodbank_id, bloodgroup, quantity, requester_name, requester_email, requester_phone, message, donation_address, donor_id, donor_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $con->prepare($insert_sql);
    if (!$insert_stmt) {
        die("Prepare failed: " . $con->error);
    }

    $insert_stmt->bind_param('isisssssis', $bloodBankId, $bloodgroup, $quantity, $requester_name, $requester_email, $requester_phone, $message, $donation_address, $donor_id, $donor_email);
    $insert_stmt->execute();

    if ($insert_stmt->affected_rows > 0) {
        $success_message = 'Request submitted successfully.';
    } else {
        $error_message = 'Error submitting request.';
    }
}

$con->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Details for <?php echo htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hero-image {
            background-image: url('img/land2.png'); /* Update the path to your image */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gray-200 font-sans">
    <?php include('header.php'); ?>

    <section class="pt-24 pb-16">
        <div class="hero-image w-full h-60 bg-gray-300 relative flex flex-cols">
            <div class="">
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="flex-1  text-white font-bold bg-transparent w-full p-6  shadow-md mb-6 lg:mb-0">
                        <h2 class="text-3xl text-center text-white font-semibold mb-4">Blood Bank Information</h2>
                        <p class="text-white text-center mb-2"><strong>Name:</strong> <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-white text-center mb-2"><strong>Email:</strong> <?= htmlspecialchars($bloodBank['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-white text-center mb-2"><strong>Phone:</strong> <?= htmlspecialchars($bloodBank['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-white text-center mb-2"><strong>Address:</strong> <?= htmlspecialchars($bloodBank['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 mt-10">
            <div class="flex flex-col lg:flex-row lg:space-x-10">
                <div class="flex-cols gap-2">
                    <!-- Blood Details Table -->
                    <div class="flex-1 bg-white p-6 rounded-lg shadow-md mt-6 lg:mb-0">
                        <h2 class="text-2xl font-semibold mb-4">Available Blood Details</h2>
                        <?php if ($bloodDetailsResult->num_rows > 0): ?>
                            <div class="overflow-x-auto mb-6">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100 text-gray-600">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-md font-medium">Blood Group</th>
                                            <th class="px-6 py-3 text-left text-md font-medium">Total Blood Quantity (ml)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = $bloodDetailsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($row['total_qty'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-lg font-semibold text-red-600">Currently no blood available for this blood bank.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true) : ?>
                    <!-- Blood Request Form -->
                    <div class="w-full lg:w-2/3 bg-white p-6 rounded-lg shadow-md">
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
                        <form action="bloodbanksresult.php?id=<?php echo htmlspecialchars($bloodBankId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                            <input type="hidden" name="bloodbank_id" value="<?= htmlspecialchars($bloodBank['id'], ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-4">
                                <label for="bloodgroup" class="block text-sm font-medium text-gray-700">Blood Group</label>
                                <select name="bloodgroup" id="bloodgroup" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                                    <?php
                                    // Populate blood group options based on available details
                                    $bloodDetailsResult->data_seek(0); // Reset pointer to the start
                                    while ($row = $bloodDetailsResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity (ml)</label>
                                <input type="number" name="quantity" id="quantity" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                            </div>

                            <div class="mb-4">
                                <label for="requester_name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="requester_name" id="requester_name" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= htmlspecialchars($default_name, ENT_QUOTES, 'UTF-8') ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                            </div>

                            <div class="mb-4">
                                <label for="requester_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="requester_email" id="requester_email" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= htmlspecialchars($default_email, ENT_QUOTES, 'UTF-8') ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                            </div>

                            <div class="mb-4">
                                <label for="requester_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" name="requester_phone" id="requester_phone" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= htmlspecialchars($default_phone, ENT_QUOTES, 'UTF-8') ?>" <?= isset($_SESSION['useremail']) ? '' : 'required' ?>>
                            </div>

                            <div class="mb-4">
                                <label for="donation_address" class="block text-sm font-medium text-gray-700">Donation Address</label>
                                <input type="text" name="donation_address" id="donation_address" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="block text-sm font-medium text-gray-700">Message (Optional)</label>
                                <textarea name="message" id="message" rows="4" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"></textarea>
                            </div>

                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Submit Request
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</body>

</html>
