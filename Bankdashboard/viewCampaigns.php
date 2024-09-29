<?php
require ('../connection.php');
session_start();

if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
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


if (isset($_GET['id'])) {
    $delete_id = $_GET['id'];

    // Prepare and execute delete query
    $stmt = $con->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->bind_param('i', $delete_id);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: viewCampaigns.php?success=campaigns deleted successfully!");
    } else {
        // Redirect with error message
        header("Location: viewCampaigns.php?error=Failed to delete campaigns!");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-white">
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
                    <th class="px-4 py-2 border border-gray-300">Action</th>
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
                        <td class="px-4 py-2 border border-gray-300">
                        <a href="viewCampaigns.php?id=<?php echo $row['id']; ?>" class="delete-btn text-red-500 hover:text-red-700">
                            <i class="fas fa-trash justify-center"></i>
                        </a>
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

<div class="delete-box fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-5 rounded-lg text-center">
        <p>Are you sure you want to delete?</p>
        <button class="confirm-btn bg-red-600 text-white rounded hover:bg-red-500 px-4 py-1 m-2">Delete</button>
        <button class="cancel-btn bg-gray-400 text-white rounded hover:bg-gray-500 px-4 py-1 m-2">Cancel</button>
    </div>
</div>

</body>
<script src="../javascript/delete.js"></script>
</html>