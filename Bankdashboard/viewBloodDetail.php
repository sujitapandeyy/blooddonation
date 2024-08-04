<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: login.php?error=Login first");
    exit(); // Ensure script execution stops after redirection
}

// Get the blood bank ID from the users table
$bankEmail = $_SESSION['bankemail'];
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $bankEmail);
$stmt->execute();
$result = $stmt->get_result();
$bank = $result->fetch_assoc();
$bankId = $bank['id'];

// Fetch blood details for the logged-in blood bank
$sql = "SELECT * FROM blood_details WHERE bloodbank_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bankId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blood Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-200">
<?php @include("bloodbankmenu.php"); ?>
<section class="ml-72 p-8">
    <h1 class="text-3xl font-bold mb-8">Available Blood Details</h1>
    <table class="min-w-full bg-white border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-700  text-white">
                <!-- <th class="px-4 py-2 border border-gray-300">ID</th> -->
                <th class="px-4 py-2 border border-gray-300">Blood Group</th>
                <th class="px-4 py-2 border border-gray-300">Name</th>
                <th class="px-4 py-2 border border-gray-300">Gender</th>
                <th class="px-4 py-2 border border-gray-300">Date of Birth</th>
                <th class="px-4 py-2 border border-gray-300">Weight</th>
                <th class="px-4 py-2 border border-gray-300">Address</th>
                <th class="px-4 py-2 border border-gray-300">Contact</th>
                <th class="px-4 py-2 border border-gray-300">Blood Quantity</th>
                <th class="px-4 py-2 border border-gray-300">Collection Date</th>
                <th class="px-4 py-2 border border-gray-300">Expire Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <!-- <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['id']); ?></td> -->
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['bloodgroup']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['dob']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['weight']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['address']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['contact']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['bloodqty']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['collection']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['expire']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    </section>
</body>

</html>
