<?php
require('../connection.php');
session_start();

if (!isset($_SESSION['bankemail'])) {
    header("Location: login.php?error=Login first");
    exit();
}

$bankEmail = $_SESSION['bankemail'];
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $bankEmail);
$stmt->execute();
$result = $stmt->get_result();
$bank = $result->fetch_assoc();
$bankId = $bank['id'];

// Fetch donation requests for the logged-in blood bank
$sql = "SELECT DISTINCT dr.*, u.fullname AS donor_name, u.email AS donor_email, u.phone AS donor_phone, 
                  u.address AS donor_address, u.donor_blood_type AS donor_blood_type 
        FROM donation_requests dr
        JOIN users u ON dr.donor_email = u.email
        WHERE dr.blood_bank_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bankId);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Donation Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200">
    <?php @include("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8">
        <h1 class="text-3xl font-bold mb-8">View Donation Requests</h1>
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2 border border-gray-300">Blood Type</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Name</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Email</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Phone</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Address</th>
                    <th class="px-4 py-2 border border-gray-300">Message</th>
                    <th class="px-4 py-2 border border-gray-300">Request Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($requests->num_rows > 0): ?>
                <?php while ($row = $requests->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_blood_type']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_name']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_email']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_phone']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_address']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['message']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['request_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="px-4 py-2 border border-gray-300 text-center">No requests available</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>
</html>

<?php
$stmt->close();
$con->close();
?>
