<?php
require('connection.php');
session_start();

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
$bloodGroupQuery = $con->prepare("SELECT bloodgroup FROM blood_details WHERE bloodbank_id = ?");
$bloodGroupQuery->bind_param('i', $bloodBankId);
$bloodGroupQuery->execute();
$bloodGroupResult = $bloodGroupQuery->get_result();
$selectedBank['available_blood_types'] = [];
while ($row = $bloodGroupResult->fetch_assoc()) {
    $selectedBank['available_blood_types'][] = $row['bloodgroup'];
}


// Initialize messages
$success_message = '';
$error_message = '';
if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] === true): 
    $email = $_SESSION['useremail'];
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    }
endif;

// Check if blood bank ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing blood bank ID.");
}

$bloodBankId = intval($_GET['id']);

// Fetch current date
$currentDate = date('Y-m-d');
// Fetch blood details with expiration date check
$sql = "SELECT bloodgroup, SUM(bloodqty) AS total_qty 
        FROM blood_details 
        WHERE bloodbank_id = ? AND expire > ? 
        GROUP BY bloodgroup";

$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param('is', $bloodBankId, $currentDate);
$stmt->execute();
$bloodDetailsResult = $stmt->get_result();

// Fetch blood bank details
$bankSql = "SELECT b.id, u.fullname, u.email, u.phone, u.address, b.service_start_time, b.service_end_time, b.image 
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
            if ($updateRatingStmt->execute()) {
                $success_message = "Your rating has been updated successfully!";
            } else {
                $error_message = "Failed to update the rating: " . $con->error;
            }
        } else {
            // Insert new rating
            $insertRatingStmt = $con->prepare("INSERT INTO blood_bank_ratings (blood_bank_id, user_id, rating) VALUES (?, ?, ?)");
            $insertRatingStmt->bind_param("iii", $bloodBankId, $user_id, $rating);
            if ($insertRatingStmt->execute()) {
                $success_message = "Your rating has been submitted successfully!";
            } else {
                $error_message = "Failed to submit the rating: " . $con->error;
            }
        }
    }
}

// Fetch average rating
$avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
$avgRatingStmt->bind_param("i", $bloodBankId);
$avgRatingStmt->execute();
$avgResult = $avgRatingStmt->get_result()->fetch_assoc();
$averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';

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

//         // Insert comment into the database
//         $insertCommentStmt = $con->prepare("INSERT INTO blood_bank_comments (blood_bank_id, user_id, comment) VALUES (?, ?, ?)");
//         $insertCommentStmt->bind_param("iis", $bloodBankId, $user_id, $comment);
//         if ($insertCommentStmt->execute()) {
//             // Redirect to the same page to see the new comment
//             header("Location: " . $_SERVER['REQUEST_URI']);
//             exit();
//         } else {
//             $error_message = "Failed to submit the comment: " . $con->error;
//         }
//     }
// }
// Fetch service hours from the database
$service_hours_query = $con->prepare("SELECT service_start_time, service_end_time FROM bloodbank WHERE id = ?");
$service_hours_query->bind_param("i", $bloodBankId);
$service_hours_query->execute();
$service_hours_result = $service_hours_query->get_result();

$service_hours = '';
if ($service_hours_result && $service_hours_result->num_rows > 0) {
    $service_hours_data = $service_hours_result->fetch_assoc();
    $service_start_time = date("g:i A", strtotime($service_hours_data['service_start_time'])); // Format to 1:00 AM
    $service_end_time = date("g:i A", strtotime($service_hours_data['service_end_time'])); // Format to 3:00 PM
    $service_hours = $service_start_time . ' to ' . $service_end_time;
}
$con->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Details for <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hero-image {
            background-image: url('img/land2.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <?php include('header.php'); ?>
    
    
    <section class="pt-24 pb-16">
        <div class="hero-image w-full h-60  relative flex flex-cols">
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="text-white font-bold w-full p-6  mb-6">
                    <h2 class="text-3xl text-center text-white font-semibold mb-4">
                        <?= htmlspecialchars($bloodBank['fullname'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                </div>
            </div>
            <!-- Display similar blood banks -->
            
            <div class="flex container mx-auto  justify-center  ">
            <div class="px-4 mt-10 w-2/3">
                <div class="flex-cols gap-2">
                    <div class="text-black p-6 bg-white w-full shadow-md">
                   <h2 class="text-3xl text-center text-black font-semibold mb-4">Blood Bank Information</h2>
                        <?php if ($success_message): ?>
                            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded-md">
                                <?= htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="bg-red-100 text-red-800 p-4 mb-4 rounded-md">
                                <?= htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="flex justify-center">
                            <div class="mb-4 text-center">
                                <?php if (!empty($bloodBank['image'])): ?>
                                    <img src="upload/<?= htmlspecialchars($bloodBank['image'], ENT_QUOTES, 'UTF-8'); ?>"
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
                        <div class="bg-white shadow-md p-6 mt-4">
                            <!-- <h2 class="text-3xl text-center text-black font-semibold mb-4">Submit a Rating</h2> -->
                            <form method="POST" action="">
                                <div class="mb-4 text-center">
                                    <input type="number" name="rating" min="1" max="5" required class="border border-gray-400 p-2 rounded">
                                    <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Submit Rating</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white shadow-md p-6 mt-4">
                        <h2 class="text-3xl text-center text-black font-semibold mb-4">Available Blood Groups</h2>
                        <table class="min-w-full table-auto border-collapse border border-gray-400">
                            <thead>
                                <tr>
                                    <th class="border border-gray-400 px-4 py-2">Blood Group</th>
                                    <th class="border border-gray-400 px-4 py-2">Available Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($bloodDetail = $bloodDetailsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td class="border border-gray-400 px-4 py-2">
                                            <?= htmlspecialchars($bloodDetail['bloodgroup'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="border border-gray-400 px-4 py-2">
                                            <?= htmlspecialchars($bloodDetail['total_qty'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true): ?>

                        <a href="request_bloodTobank.php?id=<?= $bloodBankId; ?>" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Request Blood</a>
                            <?php endif;?>
                    </div>


                    <!-- <div class="bg-white shadow-md p-6 mt-4">
                        <h2 class="text-3xl text-center text-black font-semibold mb-4">Comments</h2>
                        <form method="POST" action="">
                            <div class="mb-4 text-center">
                                <textarea name="comment" rows="4" required class="border border-gray-400 p-2 rounded"></textarea>
                                <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true): ?>
                                <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Submit Comment</button>
                                <?php endif;?>

                            </div> -->
                        <!-- </form>
                        <div class="mt-4">
                            <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                                <div class="border-b border-gray-300 py-2">
                                    <strong><?= htmlspecialchars($comment['fullname'], ENT_QUOTES, 'UTF-8'); ?>:</strong>
                                    <p><?= htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div> -->
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