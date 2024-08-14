<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}
$request_query = "SELECT br.id, br.donor_id, br.donor_email, br.requester_name, br.requester_email, br.requester_phone, br.donation_address, br.quantity, br.message, br.request_date, br.status, u.fullname AS donor_name
                  FROM blood_requests br
                  JOIN users u ON br.donor_id = u.id
                  ORDER BY br.request_date DESC";

$result = $con->query($request_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>


</head>
<body>
<body class="bg-gray-100">
    <!-- <?php include("donormenu.php"); ?> -->

    <div class=" flex flex-col items-center ml-32">
        <main class="m-full max-w-5xl p-3  rounded-lg">
            <h1 class="text-2xl font-bold mb-4 text-center justify-center">View Blood Requests</h1>

            <?php if ($result->num_rows > 0): ?>
                <table class="min-w-full bg-white border shadow-md p-1 rounded-lg">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Donor Name</th>
                            <th class="py-2 px-4 border-b">Donor Email</th>
                            <th class="py-2 px-4 border-b">Requester Name</th>
                            <th class="py-2 px-4 border-b">Requester Email</th>
                            <th class="py-2 px-4 border-b">Requester Phone</th>
                            <th class="py-2 px-4 border-b">Donation Address</th>
                            <th class="py-2 px-4 border-b">Quantity</th>
                            <th class="py-2 px-4 border-b">Message</th>
                            <th class="py-2 px-4 border-b">Request Date</th>
                            <th class="py-2 px-4 border-b">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['donor_name']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['donor_email']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_name']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_email']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_phone']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['donation_address']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['quantity']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['message']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['request_date']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['status']) ?></td>
                               
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-700">No blood requests found.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php
$con->close();
?>

    
</body>
</html>