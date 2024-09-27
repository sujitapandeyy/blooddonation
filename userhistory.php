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

// Fetch user's request history from donorblood_request table (Donor records)
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
        donorblood_request 
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

// Fetch details from blood_requests table with blood bank email (Blood Bank records)
$blood_requests_stmt = $con->prepare("
    SELECT 
        br.bloodgroup,
        br.requester_name,
        br.requester_email,
        br.requester_phone,
        br.donation_address,
        br.quantity,
        br.message,
        br.request_date,
        br.status,
        br.delivery_time,
        u.email AS bloodbank_email
    FROM 
        blood_requests br
    JOIN 
        users u ON br.bloodbank_id = u.id
    WHERE 
        br.requester_email = ?
    ORDER BY 
        br.request_date DESC
");
$blood_requests_stmt->bind_param("s", $user_email);
$blood_requests_stmt->execute();
$blood_requests_result = $blood_requests_stmt->get_result();
$blood_requests = [];
while ($row = $blood_requests_result->fetch_assoc()) {
    $blood_requests[] = $row;
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
</head>
<body class="">
    <?php @include("header.php") ?>

    <div class="pt-24 flex flex-col items-center">
        <h2 class="text-5xl font-thin mb-8 text-center font-serif text-red-500 mt-12">Your Request History</h2>

        <!-- Donor Requests Table -->
        <div id="donor-requests" class="mt-6 max-w-7xl mx-auto shadow-ms">
            <h3 class="text-2xl font-thin mb-3 text-center font-serif text-gray-700">Donor Requests</h3>
            <?php if (count($user_requests) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-300">
                                <th class="py-2 px-4 border-b border-gray-200 ">Donor Email</th>
                                <th class="py-2 px-4 border-b border-gray-200">Blood Group</th>
                                <!-- <th class="py-2 px-4 border-b border-gray-200">Requester Name</th> -->
                                <th class="py-2 px-4 border-b border-gray-200">Donation Address</th>
                                <th class="py-2 px-4 border-b border-gray-200">Quantity</th>
                                <th class="py-2 px-4 border-b border-gray-200">Request Date</th>
                                <th class="py-2 px-4 border-b border-gray-200">Status</th>
                                <th class="py-2 px-4 border-b border-gray-200">Delivery Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_requests as $request): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['donor_email']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['bloodgroup']) ?></td>
                                    <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['requester_name']) ?></td> -->
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['donation_address']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['quantity']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['request_date']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['status']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($request['delivery_time']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No donor requests found.</p>
            <?php endif; ?>
        </div>

        <!-- Blood Bank Requests Table -->
        <div id="bloodbank-requests" class="mt-6 max-w-7xl mx-auto shadow-md">
            <h3 class="text-2xl font-thin mt-16 mb-3 text-center font-serif text-gray-700 ">Blood Bank Requests</h3>
            <?php if (count($blood_requests) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-300">
                                <th class="py-2 px-4 border-b border-gray-200">Blood Bank Email</th>
                                <th class="py-2 px-4 border-b border-gray-200">Blood Group</th>
                                <!-- <th class="py-2 px-4 border-b border-gray-200">Requester Name</th> -->
                                <th class="py-2 px-4 border-b border-gray-200">Donation Address</th>
                                <th class="py-2 px-4 border-b border-gray-200">Quantity</th>
                                <th class="py-2 px-4 border-b border-gray-200">Request Date</th>
                                <th class="py-2 px-4 border-b border-gray-200">Status</th>
                                <th class="py-2 px-4 border-b border-gray-200">Delivery Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blood_requests as $blood_request): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['bloodbank_email']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['bloodgroup']) ?></td>
                                    <!-- <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['requester_name']) ?></td> -->
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['donation_address']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['quantity']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['request_date']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['status']) ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?= htmlspecialchars($blood_request['delivery_time']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No blood bank requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
