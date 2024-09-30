<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
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

if (isset($_GET['id'])) {
    $delete_id = $_GET['id'];

    // Prepare and execute delete query
    $stmt = $con->prepare("DELETE FROM blood_details WHERE id = ?");
    $stmt->bind_param('i', $delete_id);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: viewBloodDetail.php?success=Detail deleted successfully!");
    } else {
        // Redirect with error message
        header("Location: viewBloodDetail.php?error=Failed to delete detail!");
    }
}

function calculateDaysUntilExpire($expireDate) {
    $currentDate = new DateTime();  // Get current date
    $expireDate = new DateTime($expireDate);  
    $interval = $currentDate->diff($expireDate);  
    
    // Check if it's already expired
    if ($expireDate < $currentDate) {
        return "Expired";
    } else {
        return $interval->days . " days left";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blood Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php @include("bloodbankmenu.php"); ?>
<section class="ml-72 p-8">
    <h1 class="text-3xl font-bold mb-8">Available Blood Details</h1>
    <table class="min-w-full bg-white border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-700 text-white">
                <th class="px-4 py-2 border border-gray-300">Blood Group</th>
                <th class="px-4 py-2 border border-gray-300">Name</th>
                <th class="px-4 py-2 border border-gray-300">Gender</th>
                <th class="px-4 py-2 border border-gray-300">Date of Birth</th>
                <th class="px-4 py-2 border border-gray-300">Weight</th>
                <th class="px-4 py-2 border border-gray-300">Address</th>
                <th class="px-4 py-2 border border-gray-300">Contact</th>
                <th class="px-4 py-2 border border-gray-300">Blood Quantity</th>
                <th class="px-4 py-2 border border-gray-300">Collection Date</th>
                <th class="px-4 py-2 border border-gray-300">Expire In</th>
                <th class="px-4 py-2 border border-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['bloodgroup']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['dob']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['weight']); ?></td>
                    <td class="px-4 py-2 border border-gray-300">
                        <?php 
                            $address = htmlspecialchars($row['address']);
                            $words = explode(' ', $address);
                            $firstThreeWords = implode(' ', array_slice($words, 0, 3));
                            echo $firstThreeWords;
                        ?>
                    </td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['contact']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['bloodqty']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['collection']); ?></td>
                    <td class="px-4 py-2 border border-gray-300">
                        <?php echo calculateDaysUntilExpire($row['expire']); ?>
                    </td>
                    <td class="px-4 py-2 border border-gray-300">
                        <a href="viewBloodDetail.php?id=<?php echo $row['id']; ?>" class="delete-btn text-red-500 hover:text-red-700">
                            <i class="fas fa-trash justify-center"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<!-- Delete Confirmation Box -->
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
