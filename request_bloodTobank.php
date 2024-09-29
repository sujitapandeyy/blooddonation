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
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        // Set default values for logged-in user
        $default_name = htmlspecialchars($user['fullname']);
        $default_email = htmlspecialchars($user['email']);
        $default_phone = htmlspecialchars($user['phone']);
        $default_address = htmlspecialchars($user['address']);
    } else {
        $error_message = "User not found.";
    }
}

// Check if blood bank ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing blood bank ID.");
}

$bloodBankId = intval($_GET['id']);

// Fetch available quantity for the blood group
function getAvailableQuantity($con, $bloodBankId, $bloodGroup) {
    $sql = "SELECT SUM(bloodqty) as total_quantity FROM blood_details WHERE bloodbank_id = ? AND bloodgroup = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('is', $bloodBankId, $bloodGroup);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total_quantity'] ?? 0;
}

// Handle blood request form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodgroup = htmlspecialchars(trim($_POST['bloodgroup']));
    $quantity = intval(trim($_POST['quantity']));
    $requester_name = htmlspecialchars(trim($_POST['requester_name']));
    $requester_email = htmlspecialchars(trim($_POST['requester_email']));
    $requester_phone = htmlspecialchars(trim($_POST['requester_phone']));
    $message = htmlspecialchars(trim($_POST['message']));
    $donation_address = htmlspecialchars(trim($_POST['donation_address']));

    // Validation
    if (empty($bloodgroup)) {
        $error_message = "Blood group is required.";
    } elseif ($quantity <= 0) {
        $error_message = "Quantity must be greater than zero.";
    } elseif (empty($requester_name) || !preg_match("/^[a-zA-Z ]*$/", $requester_name)) {
        $error_message = "Valid name is required.";
    } elseif (empty($requester_email) || !filter_var($requester_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Valid email is required.";
    } elseif (empty($requester_phone) || !preg_match("/^\d{10}$/", $requester_phone)) {
        $error_message = "Valid phone number is required.";
    } elseif (empty($donation_address)) {
        $error_message = "Donation address is required.";
    } else {
        // Check for duplicate requests
        $duplicate_check_sql = "SELECT * FROM blood_requests WHERE bloodbank_id = ? AND bloodgroup = ? AND quantity = ? AND requester_email = ?";
        $duplicate_check_stmt = $con->prepare($duplicate_check_sql);
        $duplicate_check_stmt->bind_param("isis", $bloodBankId, $bloodgroup, $quantity, $requester_email);
        $duplicate_check_stmt->execute();
        $duplicate_result = $duplicate_check_stmt->get_result();

        if ($duplicate_result->num_rows > 0) {
            $error_message = "You have already requested this quantity of blood for this group.";
        } else {
            // Check available quantity
            $available_quantity = getAvailableQuantity($con, $bloodBankId, $bloodgroup);
            if ($quantity > $available_quantity) {
                $error_message = "Requested quantity exceeds available quantity.";
            } else {
                // Process the valid form data
                $insert_sql = "INSERT INTO blood_requests (bloodbank_id, bloodgroup, quantity, requester_name, requester_email, requester_phone, message, donation_address) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $con->prepare($insert_sql);
                $insert_stmt->bind_param("issssiss", $bloodBankId, $bloodgroup, $quantity, $requester_name, $requester_email, $requester_phone, $message, $donation_address);
                
                if ($insert_stmt->execute()) {
                    // Update blood quantity in blood_details
                    // $update_sql = "UPDATE blood_details SET bloodqty = bloodqty - ? WHERE bloodbank_id = ? AND bloodgroup = ?";
                    // $update_stmt = $con->prepare($update_sql);
                    // $update_stmt->bind_param('iis', $quantity, $bloodBankId, $bloodgroup);
                    // $update_stmt->execute();

                    $success_message = "Your request has been submitted successfully.";
                } else {
                    $error_message = "Error: " . $con->error;
                }
            }
        }
    }
}

// Fetch blood details for dropdown
$sql = "SELECT bloodgroup FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bloodBankId);
$stmt->execute();
$bloodDetailsResult = $stmt->get_result();

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200 font-sans">
    <?php include('header.php'); ?>

    <section class="container mx-auto pt-24 pb-16">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Request Blood</h2>

            <?php if ($success_message): ?>
                <div class="bg-green-100 text-green-800 p-4 mb-4 rounded-md"><?= htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 text-red-800 p-4 mb-4 rounded-md"><?= htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="bloodgroup" class="block text-sm font-medium text-gray-700">Blood Group</label>
                        <select name="bloodgroup" id="bloodgroup" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                            <option value="">Select Blood Group</option>
                            <?php while ($row = $bloodDetailsResult->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="quantity" min="1" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="requester_name" class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="requester_name" id="requester_name" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= $default_name; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="requester_email" class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" name="requester_email" id="requester_email" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= $default_email; ?>" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="requester_phone" class="block text-sm font-medium text-gray-700">Your Phone</label>
                        <input type="text" name="requester_phone" id="requester_phone" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= $default_phone; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="donation_address" class="block text-sm font-medium text-gray-700">Donation Address</label>
                        <input type="text" name="donation_address" id="donation_address" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" value="<?= $default_address; ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" id="message" rows="4" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="flex justify-center">
                    <a href="bloodbanksresult.php?id=<?= htmlspecialchars($bloodBankId); ?>" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg shadow hover:bg-blue-600">
                        Back
                    </a>                
                    <button type="submit" class="bg-red-500 text-white font-bold py-2 ml-3 px-4 rounded-lg shadow hover:bg-red-600">Submit Request</button>
                </div>
            </form>
        </div>
    </section>

    <?php @include 'footor.php'; ?>
</body>
</html>
