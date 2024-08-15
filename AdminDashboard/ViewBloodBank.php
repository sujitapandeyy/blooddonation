<?php
require('../connection.php');
session_start();

// Check if donor is logged in
// if (!isset($_SESSION['donoremail'])) {
//     header("Location: login.php?error=Login first");
//     exit();
// }

// Handle the delete request
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $deleteQuery = $con->prepare("DELETE FROM users WHERE id = ? AND user_type = 'BloodBank'");
    $deleteQuery->bind_param('i', $deleteId);

    if ($deleteQuery->execute()) {
        // Redirect to the same page after deletion to avoid re-submission
        header("Location: admindashboard.php");
        exit();
    } else {
        echo "<script>alert('Failed to delete blood bank. Please try again.');</script>";
    }
}

$bloodBankQuery = $con->prepare("SELECT * FROM users WHERE user_type = 'BloodBank'");
$bloodBankQuery->execute();
$bloodBankResult = $bloodBankQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Blood Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            border-radius: 8px;
        }
    </style>
</head>

<body class="font-Roboto">
    <!-- <?php include("donorMenu.php"); ?> -->

    <section class="w-full p-10">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Available Blood Banks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-3xl mx-auto">
            <?php while ($row = $bloodBankResult->fetch_assoc()) { ?>
                <div class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <!-- Image for the blood bank -->
                    <img src="../img/slide1.png" alt="Blood Bank Image" class="w-full h-60 object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg">
                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['fullname']); ?></h3>
                        <p class="text-gray-600 mt-2"><i class="fa-solid fa-home"></i> <?php echo htmlspecialchars($row['address']); ?></p>
                        <div class="flex items-center space-x-4 mt-4">
                            <a href="BloodBankResult.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                View Blood Details
                            </a>
                            <button onclick="openDeleteModal(<?php echo htmlspecialchars($row['id']); ?>)" class="text-red-500 hover:text-red-700">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
            <p class="mb-4">Are you sure you want to delete this Blood Bank?</p>
            <div class="flex justify-end space-x-4">
                <button id="confirmDelete" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Delete</button>
                <button onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        var deleteModal = document.getElementById('deleteModal');
        var confirmDeleteButton = document.getElementById('confirmDelete');
        var deleteId = null;

        function openDeleteModal(id) {
            deleteModal.style.display = 'block';
            deleteId = id;
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }

        confirmDeleteButton.onclick = function() {
            if (deleteId !== null) {
                window.location.href = 'viewbloodbank.php?delete=' + deleteId;
            }
        }

        window.onclick = function(event) {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>

<?php
$con->close();
?>
