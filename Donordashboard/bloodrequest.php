<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// SQL query to fetch blood requests
$request_query = "SELECT br.id, br.donor_id, br.donor_email, br.requester_name, br.requester_email, br.requester_phone, br.donation_address, br.quantity, br.message, br.request_date, br.status, u.fullname AS donor_name
                  FROM donorblood_request br
                  JOIN users u ON br.donor_id = u.id
                  ORDER BY br.request_date DESC";

// Execute the query and handle potential errors
$result = $con->query($request_query);

// Check if the query was successful
if (!$result) {
    die("Error executing query: " . $con->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="">
            <!-- <?php include("donorMenu.php"); ?> -->

    <div class="flex flex-col items-center ml-32">
        <main class="m-full max-w-5xl p-3 rounded-lg">
            <h1 class="text-2xl font-bold mb-4 text-center">View Blood Requests</h1>

            <?php if ($result->num_rows > 0): ?>
                <table class="min-w-full bg-white border shadow-md p-1 rounded-lg">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Requester Name</th>
                            <th class="py-2 px-4 border-b">Requester Email</th>
                            <th class="py-2 px-4 border-b">Requester Phone</th>
                            <th class="py-2 px-4 border-b">Donation Address</th>
                            <th class="py-2 px-4 border-b">Quantity</th>
                            <th class="py-2 px-4 border-b">Message</th>
                            <th class="py-2 px-4 border-b">Request Date</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_name']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_email']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['requester_phone']) ?></td>
                                <td class="py-2 px-4 border-b"><?php $address = htmlspecialchars($row['donation_address']);
                                    $words = explode(' ', $address); // Split address into words
                                    $firstThreeWords = implode(' ', array_slice($words, 0, 3)); // Get the first three words
                                    echo $firstThreeWords;
                                ?>
                             </td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['quantity']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['message']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['request_date']) ?></td>
                                <td class="py-2 px-4 border-b" id="status-<?= $row['id'] ?>"><?= htmlspecialchars($row['status']) ?></td>
                                <td class="py-2 px-4 border-b">
                                    <select onchange="updateStatus(<?= $row['id'] ?>, this.value)" class="border p-2 rounded">
                                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                        <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-700">No blood requests found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal for delivery time -->
    <div id="timeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-xl font-bold mb-4">Enter Delivery Time</h2>
            <label for="hours" class="block mb-2">Hours:</label>
            <input type="number" id="hours" min="0" max="23" class="border p-2 rounded mb-4">
            <label for="minutes" class="block mb-2">Minutes:</label>
            <input type="number" id="minutes" min="0" max="59" class="border p-2 rounded mb-4">
            <button onclick="confirmTime()" class="bg-blue-500 text-white p-2 rounded">Submit</button>
        </div>
    </div>

    <!-- JavaScript to handle status update -->
    <script>
        let currentRequestId = null;

        function updateStatus(requestId, newStatus) {
            if (newStatus === 'Approved') {
                currentRequestId = requestId;
                document.getElementById('timeModal').style.display = 'block';
            } else {
                sendUpdate(requestId, newStatus);
            }
        }

        function confirmTime() {
            const hours = document.getElementById('hours').value;
            const minutes = document.getElementById('minutes').value;

            if (hours === '' || minutes === '') {
                alert('Please enter both hours and minutes.');
                return;
            }

            const deliveryTime = `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
            sendUpdate(currentRequestId, 'Approved', deliveryTime);
            closeModal();
        }

        function sendUpdate(requestId, newStatus, deliveryTime = null) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            let params = `id=${requestId}&status=${newStatus}`;
            if (deliveryTime) {
                params += `&delivery_time=${deliveryTime}`;
            }

            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById(`status-${requestId}`).textContent = newStatus;
                    alert('Status updated successfully!');
                } else {
                    alert('Error updating status.');
                }
            };

            xhr.send(params);
        }

        function closeModal() {
            document.getElementById('timeModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
$con->close();
?>
