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

// Fetch user's request history
$request_history_stmt = $con->prepare("
    SELECT 
        donor_email, 
        bloodgroup, 
        requester_name, 
        requester_email, 
        requester_phone, 
        donation_address, 
        quantity, 
        message, 
        request_date, 
        status, 
        delivery_time 
    FROM 
        blood_requests 
    WHERE 
        requester_email = ?
    ORDER BY 
        request_date DESC
");
$request_history_stmt->bind_param("s", $user_email);
$request_history_stmt->execute();
$request_history_result = $request_history_stmt->get_result();
$user_requests = [];
while ($row = $request_history_result->fetch_assoc()) {
    $user_requests[] = $row;
}

// Handle POST request

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
       

                <!-- User Request History Section -->
                <h2 class="text-4xl font-extrabold text-center mb-12 text-red-600 mt-12">Your Request History</h2>
                <div id="request-history" class="mt-6 max-w-7xl mx-auto">
                    <?php if (count($user_requests) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200">Donor Email</th>
                                        <th class="py-2 px-4 border-b border-gray-200">Blood Group</th>
                                        <!-- <th class="py-2 px-4 border-b border-gray-200">Requester Name</th> -->
                                        <!-- <th class="py-2 px-4 border-b border-gray-200">Requester Email</th> -->
                                        <!-- <th class="py-2 px-4 border-b border-gray-200">Requester Phone</th> -->
                                        <th class="py-2 px-4 border-b border-gray-200">Donation Address</th>
                                        <th class="py-2 px-4 border-b border-gray-200">Quantity</th>
                                        <!-- <th class="py-2 px-4 border-b border-gray-200">Message</th> -->
                                        <th class="py-2 px-4 border-b border-gray-200">Request Date</th>
                                        <th class="py-2 px-4 border-b border-gray-200">Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200">Delivery within</th>
                                        <!-- <th class="py-2 px-4 border-b border-gray-200">Option</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_requests as $request): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['donor_email']) ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['bloodgroup']) ?></td>
                                            <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['requester_name']) ?></td> -->
                                            <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['requester_email']) ?></td> -->
                                            <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['requester_phone']) ?></td> -->
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['donation_address']) ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['quantity']) ?></td>
                                            <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['message']) ?></td> -->
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['request_date']) ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['status']) ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['delivery_time']) ?></td>
                                            <!-- <td class="py-2 px-4 border-b border-gray-200 text-red-500 hover:underline">Cancel</td> -->

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-600">No requests found.</p>
                    <?php endif; ?>
                </div>
            
    </div>
</body>

</html>
