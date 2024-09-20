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
$bankSql = "SELECT b.id, u.fullname, u.email, u.phone, u.address, b.service_type, b.service_start_time, b.service_end_time, b.image 
            FROM bloodbank AS b
            JOIN users AS u ON b.id = u.id
            WHERE b.id = ?";
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
    $donor_id = $bloodBank['id']; // Use blood bank's id as donor_id
    $donor_email = $bloodBank['email']; // Use the blood bank's email

    // Start a transaction
    $con->begin_transaction();

    try {
        // Check for duplicate request
        $check_sql = "SELECT COUNT(*) FROM blood_requests WHERE bloodgroup = ? AND requester_email = ? AND quantity = ? AND bloodbank_id = ? AND status = 'Pending'";
        $check_stmt = $con->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        $check_stmt->bind_param('ssii', $bloodgroup, $requester_email, $quantity, $bloodBankId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_row()[0];

        if ($count > 0) {
            throw new Exception('Duplicate request detected.');
        }

        // Prepare and execute insert statement
        $insert_sql = "INSERT INTO blood_requests (bloodbank_id, bloodgroup, quantity, requester_name, requester_email, requester_phone, message, donation_address, donor_id, donor_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $con->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }

        $insert_stmt->bind_param('isisssssis', $bloodBankId, $bloodgroup, $quantity, $requester_name, $requester_email, $requester_phone, $message, $donation_address, $donor_id, $donor_email);
        $insert_stmt->execute();

        if ($insert_stmt->affected_rows <= 0) {
            throw new Exception('Error submitting request.');
        }

        // Update blood quantity in blood_details
        $update_sql = "UPDATE blood_details SET bloodqty = bloodqty - ? WHERE bloodbank_id = ? AND bloodgroup = ?";
        $update_stmt = $con->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }

        $update_stmt->bind_param('iis', $quantity, $bloodBankId, $bloodgroup);
        $update_stmt->execute();

        if ($update_stmt->affected_rows <= 0) {
            throw new Exception('Error updating blood quantity.');
        }

        // Commit the transaction
        $con->commit();
        $success_message = 'Request submitted successfully.';

    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $con->rollback();
        $error_message = $e->getMessage();
    }
}




// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);

    // Check if user has already rated this blood bank
    $checkRatingStmt = $con->prepare("SELECT * FROM blood_bank_ratings WHERE blood_bank_id = ? AND user_id = ?");
    $checkRatingStmt->bind_param("ii", $bloodBankId, $user_id);
    $checkRatingStmt->execute();
    $existingRating = $checkRatingStmt->get_result()->fetch_assoc();

    if (!$existingRating) {
        // Insert rating into the database
        $insertRatingStmt = $con->prepare("INSERT INTO blood_bank_ratings (blood_bank_id, user_id, rating) VALUES (?, ?, ?)");
        $insertRatingStmt->bind_param("iii", $bloodBankId, $user_id, $rating);
        if ($insertRatingStmt->execute()) {
            $success_message = "Your rating has been submitted successfully!";
        } else {
            $error_message = "Failed to submit the rating: " . $con->error;
        }
    } else {
        $error_message = "You have already rated this blood bank.";
    }
}

// Fetch average rating
$avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
$avgRatingStmt->bind_param("i", $bloodBankId);
$avgRatingStmt->execute();
$avgResult = $avgRatingStmt->get_result()->fetch_assoc();
$averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';

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
            background-image: url('img/land2.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gray-200 font-sans">
    <?php include('header.php'); ?>

    <section class="pt-24 pb-16">
        <div class="hero-image w-full h-60 bg-gray-300 relative flex flex-cols">
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="text-white font-bold w-full p-6 shadow-md mb-6">
                    <h2 class="text-3xl text-center text-white font-semibold mb-4">
                        <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="flex container mx-auto flex-col lg:flex-row lg:space-x-10">
            <div class="px-4 mt-10">
                <div class="flex-cols gap-2">
                    <div class="text-black p-6 bg-white w-full shadow-md">
                        <h2 class="text-3xl text-center text-black font-semibold mb-4">Blood Bank Information</h2>
                        <div class="flex justify-center">
                            <div class="mb-4 text-center">
                                <?php if (!empty($bloodBank['image'])): ?>
                                    <img src="upload/<?php echo htmlspecialchars($bloodBank['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Blood Bank Image" class="w-100 h-20 object-cover mt-4 text-center">
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-center mb-2">Name:
                            <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-center mb-2">Email:
                            <?= htmlspecialchars($bloodBank['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-center mb-2">Phone:
                            <?= htmlspecialchars($bloodBank['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-center mb-2">Address:
                            <?= htmlspecialchars($bloodBank['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-center mb-2">Service Hours:
                            <?= htmlspecialchars($bloodBank['service_start_time'], ENT_QUOTES, 'UTF-8'); ?> -
                            <?= htmlspecialchars($bloodBank['service_end_time'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <h3 class=" text-center">Average Rating:
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
                        <!-- Rating Section -->
                        <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true): ?>
                            <form action="" method="POST" class="text-center mt-4">
                                <label class="mr-2">Rate this Blood Bank:</label>
                                <div class="flex justify-center items-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="cursor-pointer text-2xl star" data-rating="<?= $i; ?>">
                                            &#9733; <!-- Star icon -->
                                        </span>
                                    <?php endfor; ?><br />
                                    <input type="hidden" name="rating" id="rating" required>
                                    <button type="submit" class="ml-2 bg-blue-500 text-white py-1 px-3 rounded">Submit
                                        Rating</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-center">Please <a href="login.php">log in</a> to rate this blood bank.</p>
                        <?php endif; ?>


                    </div>
                </div>

                <div class="text-black p-6 bg-white w-full shadow-md">
                    <h2 class="text-3xl text-center text-black font-semibold mb-4">Available Blood Groups</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Blood Group</th>
                                <th
                                    class="px-6 py-3 bg-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Available Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $bloodDetailsResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= htmlspecialchars($row['total_qty'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class=" mt-10 w-4/6">

                <!-- Blood Request Form -->
                <div class="flex-1 bg-white p-6 rounded-lg shadow-md w-full">
                    <h2 class="text-2xl font-semibold mb-4">Request Blood</h2>

                    <?php if ($success_message): ?>
                        <div class="bg-green-100 text-green-800 p-4 mb-4 rounded-md">
                            <?= htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded-md">
                            <?= htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="bloodgroup" class="block text-sm font-medium text-gray-700">Blood
                                    Group</label>
                                <select name="bloodgroup" id="bloodgroup"
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    required>
                                    <?php
                                    // Populate blood group options based on available details
                                    $bloodDetailsResult->data_seek(0); // Reset pointer to the start
                                    while ($row = $bloodDetailsResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['bloodgroup'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity
                                    (units)</label>
                                <input type="number" id="quantity" name="quantity" min="1"
                                    class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label for="requester_name" class="block text-sm font-medium text-gray-700">Your
                                Name</label>
                            <input type="text" id="requester_name" name="requester_name"
                                value="<?= htmlspecialchars($default_name, ENT_QUOTES, 'UTF-8'); ?>"
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"
                                required>
                        </div>

                        <div>
                            <label for="requester_email" class="block text-sm font-medium text-gray-700">Your
                                Email</label>
                            <input type="email" id="requester_email" name="requester_email"
                                value="<?= htmlspecialchars($default_email, ENT_QUOTES, 'UTF-8'); ?>"
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"
                                required>
                        </div>

                        <div>
                            <label for="requester_phone" class="block text-sm font-medium text-gray-700">Your
                                Phone</label>
                            <input type="tel" id="requester_phone" name="requester_phone"
                                value="<?= htmlspecialchars($default_phone, ENT_QUOTES, 'UTF-8'); ?>"
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"
                                required>
                        </div>

                        <div>
                            <label for="donation_address" class="block text-sm font-medium text-gray-700">Your
                                Address</label>
                            <input type="text" id="donation_address" name="donation_address"
                                value="<?= htmlspecialchars($default_address, ENT_QUOTES, 'UTF-8'); ?>"
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"
                                required>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message
                                (Optional)</label>
                            <textarea id="message" name="message"
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50 p-2"></textarea>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="bg-red-500 text-white font-bold py-2 px-4 rounded-lg shadow hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('.star').forEach(function (star) {
            star.addEventListener('click', function () {
                const rating = this.getAttribute('data-rating');
                document.getElementById('rating').value = rating;

                // Remove the active class from all stars
                document.querySelectorAll('.star').forEach(function (star) {
                    star.style.color = ''; // Reset color
                });

                // Set the color of the selected stars
                for (let i = 1; i <= rating; i++) {
                    document.querySelectorAll('.star')[i - 1].style.color = 'gold'; // Set color for selected stars
                }
            });
        });
    </script>

<?php @include 'footor.php'; ?>
</body>

</html>