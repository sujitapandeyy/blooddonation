<?php
require ('../connection.php');
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

$sql = "SELECT * FROM campaigns WHERE bloodbank_id = ?";
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
    <title>View Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-200">
    <?php @include ("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8">
        <h1 class="text-3xl font-bold mb-8">View Campaigns</h1>
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2 border border-gray-300">Campaign Name</th>
                    <th class="px-4 py-2 border border-gray-300">Contact Number</th>
                    <th class="px-4 py-2 border border-gray-300">Campaign Date</th>
                    <th class="px-4 py-2 border border-gray-300">Description</th>
                    <th class="px-4 py-2 border border-gray-300">Location</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['campaign_name']); ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['contact_number']); ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['campaign_date']); ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['description']); ?>
                        </td>

                        <td class="px-4 py-2 border border-gray-300"> <?php
                        // $location = $row['location'];
                        // $words = explode(' ', $location);
                        // $firstTwoWords = implode(' ', array_slice($words, 0, 2));
                        // echo htmlspecialchars($firstTwoWords);
                        echo htmlspecialchars($row['location']);;
                        ?>
                        </td>
                    </tr>
                <?php } ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="px-4 py-2 border border-gray-300 text-center">No data available</td>
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