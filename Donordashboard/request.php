<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// Get selected blood bank details
$bloodBankId = intval($_GET['id']);
$selectedBankQuery = $con->prepare("SELECT u.id, u.fullname, u.latitude, u.longitude, AVG(r.rating) as average_rating 
                                    FROM users AS u 
                                    JOIN blood_bank_ratings AS r ON u.id = r.blood_bank_id 
                                    WHERE u.id = ?");
$selectedBankQuery->bind_param('i', $bloodBankId);
$selectedBankQuery->execute();
$selectedBank = $selectedBankQuery->get_result()->fetch_assoc();

// Fetch available blood types for the selected blood bank
// $bloodGroupQuery = $con->prepare("SELECT bloodgroup FROM blood_details WHERE bloodbank_id = ?");
// $bloodGroupQuery->bind_param('i', $bloodBankId);
// $bloodGroupQuery->execute();
// $bloodGroupResult = $bloodGroupQuery->get_result();
// $selectedBank['available_blood_types'] = [];
// while ($row = $bloodGroupResult->fetch_assoc()) {
//     $selectedBank['available_blood_types'][] = $row['bloodgroup'];
// }

// Initialize messages
$success_message = '';
$error_message = '';

// Get user ID from session
$email = $_SESSION['donoremail'];
$stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user_id = ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;

// Check if blood bank ID is valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing blood bank ID.");
}

// Fetch blood details
// $stmt = $con->prepare("SELECT bloodgroup, SUM(bloodqty) AS total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup");
// $stmt->bind_param('i', $bloodBankId);
// $stmt->execute();
// $bloodDetailsResult = $stmt->get_result();

// Fetch blood bank details
$bankStmt = $con->prepare("SELECT b.id, u.fullname, u.email, u.phone, u.address, b.service_start_time, b.service_end_time, b.image 
                            FROM bloodbank AS b
                            JOIN users AS u ON b.id = u.id
                            WHERE b.id = ?");
$bankStmt->bind_param('i', $bloodBankId);
$bankStmt->execute();
$bloodBank = $bankStmt->get_result()->fetch_assoc();

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$user_id) {
        $error_message = "You must be logged in to submit a rating.";
    } else {
        $rating = intval($_POST['rating']);

        // Check if user has already rated this blood bank
        $checkRatingStmt = $con->prepare("SELECT * FROM blood_bank_ratings WHERE blood_bank_id = ? AND user_id = ?");
        $checkRatingStmt->bind_param("ii", $bloodBankId, $user_id);
        $checkRatingStmt->execute();
        $existingRating = $checkRatingStmt->get_result()->fetch_assoc();

        if ($existingRating) {
            // Update existing rating
            $updateRatingStmt = $con->prepare("UPDATE blood_bank_ratings SET rating = ? WHERE blood_bank_id = ? AND user_id = ?");
            $updateRatingStmt->bind_param("iii", $rating, $bloodBankId, $user_id);
            $success_message = $updateRatingStmt->execute() ? "Your rating has been updated successfully!" : "Failed to update the rating: " . $con->error;
        } else {
            // Insert new rating
            $insertRatingStmt = $con->prepare("INSERT INTO blood_bank_ratings (blood_bank_id, user_id, rating) VALUES (?, ?, ?)");
            $insertRatingStmt->bind_param("iii", $bloodBankId, $user_id, $rating);
            $success_message = $insertRatingStmt->execute() ? "Your rating has been submitted successfully!" : "Failed to submit the rating: " . $con->error;
        }
    }
}

// Fetch average rating
$avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
$avgRatingStmt->bind_param("i", $bloodBankId);
$avgRatingStmt->execute();
$averageRating = $avgRatingStmt->get_result()->fetch_assoc()['average_rating'];
$averageRating = $averageRating ? round($averageRating, 1) : 'No ratings yet';

// Fetch comments for the specific blood bank
// $commentsStmt = $con->prepare("SELECT u.fullname, c.comment, c.created_at 
//                                FROM blood_bank_comments AS c 
//                                JOIN users AS u ON c.user_id = u.id 
//                                WHERE c.blood_bank_id = ? 
//                                ORDER BY c.created_at DESC");
// $commentsStmt->bind_param("i", $bloodBankId);
// $commentsStmt->execute();
// $commentsResult = $commentsStmt->get_result();

// Handle comment submission
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
//     if (!$user_id) {
//         $error_message = "You must be logged in to submit a comment.";
//     } else {
//         $comment = trim($_POST['comment']);
//         $insertCommentStmt = $con->prepare("INSERT INTO blood_bank_comments (blood_bank_id, user_id, comment) VALUES (?, ?, ?)");
//         $insertCommentStmt->bind_param("iis", $bloodBankId, $user_id, $comment);
//         if ($insertCommentStmt->execute()) {
//             header("Location: " . $_SERVER['REQUEST_URI']);
//             exit();
//         } else {
//             $error_message = "Failed to submit the comment: " . $con->error;
//         }
//     }
// }

// Fetch service hours
$service_hours_query = $con->prepare("SELECT service_start_time, service_end_time FROM bloodbank WHERE id = ?");
$service_hours_query->bind_param("i", $bloodBankId);
$service_hours_query->execute();
$service_hours_result = $service_hours_query->get_result();

$service_hours = '';
if ($service_hours_result && $service_hours_result->num_rows > 0) {
    $service_hours_data = $service_hours_result->fetch_assoc();
    $service_hours = date("g:i A", strtotime($service_hours_data['service_start_time'])) . ' to ' . date("g:i A", strtotime($service_hours_data['service_end_time']));
}

$con->close();
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
</head>

<body class="font-Roboto">
    <!-- <?php include("donorMenu.php"); ?> -->
    <section class=" w-full p-10 ml-40">
        <div class="flex  ml-20 flex-col lg:flex-row lg:space-x-10">
            <div class="px-4 ml-10 w-2/3">
                <div class="flex-cols gap-2">
                    <div class="text-black p-6 bg-white w-full shadow-md">
                        <h2 class="text-3xl text-center text-black font-semibold mb-4">Blood Bank Information</h2>

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

                        <div class="flex justify-center">
                        </div>
                        <p class="text-center mb-2">Name:
                            <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-center mb-2">Email:
                            <?= htmlspecialchars($bloodBank['email'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-center mb-2">Phone:
                            <?= htmlspecialchars($bloodBank['phone'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-center mb-2">Address:
                            <?= htmlspecialchars($bloodBank['address'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-center mb-2">Service Hours: <?php echo $service_hours; ?></p>

                        <h3 class="text-center">Average Rating:
                            <?php if ($averageRating === 'No ratings yet'): ?>
                                <?= htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8'); ?>
                            <?php else: ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span
                                        class="<?= $i <= $averageRating ? 'text-2xl text-yellow-500' : 'text-2xl' ?>"><?= $i <= $averageRating ? '&#9733;' : '&#9734;'; ?></span>
                                <?php endfor; ?>
                                (<?= htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8'); ?>)
                            <?php endif; ?>
                        </h3>

                        <form method="POST" action="">
                            <div class="mb-4 text-center">
                                <input type="number" name="rating" min="1" max="5" required
                                    class="border border-gray-400 p-2 rounded">
                                <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Submit
                                    Rating</button>
                            </div>
                        </form>
                    </div>

                    <a href="donaterequest.php?id=<?= $bloodBankId; ?>"
                        class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Donate Now</a>
                </div>

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

    <?php @include 'footer.php'; ?>
</body>

</html>