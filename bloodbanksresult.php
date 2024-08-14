<?php
require('connection.php');
session_start();

// if (!isset($_SESSION['bankemail'])) {
//     header("Location: login.php?error=Login first");
//     exit(); 
// }

if (!isset($_GET['id'])) {
    die("No blood bank selected.");
}

$bloodBankId = intval($_GET['id']);

$sql = "SELECT bloodgroup, SUM(bloodqty) AS total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bloodBankId);
$stmt->execute();
$bloodDetailsResult = $stmt->get_result();

$bankSql = "SELECT fullname FROM users WHERE id = ?";
$bankStmt = $con->prepare($bankSql);
$bankStmt->bind_param('i', $bloodBankId);
$bankStmt->execute();
$bankResult = $bankStmt->get_result();
$bloodBank = $bankResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Details for <?php echo htmlspecialchars($bloodBank['fullname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
    
    </style>
</head>

<body class="bg-gray-200 font-sans">
    <?php @include('header.php'); ?>
    <section class="container max-w-5xl mx-auto pt-32 pb-16">
        <h1 class="text-4xl font-bold mb-8 text-gray-900 text-center">Available Blood Details of <?php echo htmlspecialchars($bloodBank['fullname']); ?></h1>
        <?php if ($bloodDetailsResult->num_rows > 0) { ?>
            <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-md font-lg">Blood Group</th>
                            <th class="px-6 py-3 text-left text-md font-lg">Total Blood Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white ">
                        <?php while ($row = $bloodDetailsResult->fetch_assoc()) { ?>
                            <tr>
                                <td class="px-6 py-4  text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['bloodgroup']); ?></td>
                                <td class="px-6 py-4  text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['total_qty']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-center text-lg font-semibold text-red-600">currently No blood available for this blood bank.</p>
        <?php } ?>
    </section>
</body>

</html>
