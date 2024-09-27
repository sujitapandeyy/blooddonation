<?php
require('../connection.php');
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

// Fetch blood requests for the logged-in blood bank
$sql = "SELECT br.*, u.fullname AS requester_name, u.email AS requester_email, u.phone AS requester_phone
        FROM blood_requests br
        JOIN users u ON br.requester_email = u.email
        WHERE br.bloodbank_id = ?";
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
    <title>View Blood Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-white">
    <?php @include("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8">
        <h1 class="text-3xl font-bold mb-8">View Blood Requests</h1>
        
        <?php if (isset($_GET['error'])) : ?>
            <?php
            $errorMessage = $_GET['error'];
            $errorClass = ($errorMessage === 'Status updated successfully!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            ?>
            <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                <p><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>

        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2 border border-gray-300">Blood Group</th>
                    <th class="px-4 py-2 border border-gray-300">Quantity (ml)</th>
                    <th class="px-4 py-2 border border-gray-300">Requester Name</th>
                    <th class="px-4 py-2 border border-gray-300">Requester Email</th>
                    <th class="px-4 py-2 border border-gray-300">Requester Phone</th>
                    <th class="px-4 py-2 border border-gray-300">Message</th>
                    <th class="px-4 py-2 border border-gray-300">Request Date</th>
                    <th class="px-4 py-2 border border-gray-300">Status</th>
                    <th class="px-4 py-2 border border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($requests->num_rows > 0): ?>
                <?php while ($row = $requests->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['bloodgroup']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['requester_name']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['requester_email']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['requester_phone']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['message']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['request_date']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="px-4 py-2 border border-gray-300">
                        <select class="status-dropdown" data-request-id="<?php echo $row['id']; ?>">
                            <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Approved" <?php if ($row['status'] == 'Approved') echo 'selected'; ?>>Approved</option>
                            <option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                            <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                        </select>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="px-4 py-2 border border-gray-300 text-center">No requests available</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Will be there within</h2>
            <div class="mb-4">
                <label for="deliveryHours" class="block text-sm font-medium text-gray-700">Hours</label>
                <select id="deliveryHours" class="w-full p-2 border border-gray-300 rounded">
                    <option value="">Select hours (optional)</option>
                    <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="deliveryMinutes" class="block text-sm font-medium text-gray-700">Minutes</label>
                <select id="deliveryMinutes" class="w-full p-2 border border-gray-300 rounded">
                    <option value="">Select minutes (optional)</option>
                    <?php for ($i = 0; $i < 60; $i++): ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button id="closeModal" class="bg-red-500 text-white font-semibold px-4 py-2 rounded-lg mr-2 hover:bg-red-600">Cancel</button>
                <button id="saveDelivery" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600">Save</button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let currentRequestId = null;

        // Handle status change
        $('.status-dropdown').change(function() {
            const requestId = $(this).data('request-id');
            const newStatus = $(this).val();

            if (newStatus === 'Approved') {
                currentRequestId = requestId;
                $('#appointmentModal').removeClass('hidden');
            } else {
                updateStatus(requestId, newStatus);
            }
        });

        // Save delivery time when the modal is submitted
        $('#saveDelivery').click(function() {
            const hours = $('#deliveryHours').val();
            const minutes = $('#deliveryMinutes').val();

            // Validate that both hours and minutes are selected
            if (hours !== '' && minutes !== '') {
                // Combine hours and minutes into HH:MM:SS format
                const deliveryTime = `${hours}:${minutes}:00`;

                // Call the function to update status
                updateStatus(currentRequestId, 'Approved', deliveryTime);

                // Hide the modal after saving
                $('#appointmentModal').addClass('hidden');
            } else {
                alert('Please select both hours and minutes.');
            }
        });

        // Close the modal without saving
        $('#closeModal').click(function() {
            $('#appointmentModal').addClass('hidden');
        });

        // Function to update the status via AJAX
        function updateStatus(requestId, status, deliveryTime = null) {
            const requestData = {
                request_id: requestId,
                status: status,
                delivery_time: deliveryTime
            };

            console.log("Sending request data:", requestData); // Debugging line

            $.ajax({
                url: 'updateRequestStatus.php',
                method: 'POST',
                data: requestData,
                success: function(response) {
                    console.log(response);
                    alert(response);
                    location.reload(); // Reload page after updating status
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
        }
    });
    </script>
</body>
</html>
