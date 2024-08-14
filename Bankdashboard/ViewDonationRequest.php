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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-white">
    <?php @include("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8">
        <h1 class="text-3xl font-bold mb-8">View Donation Requests</h1>
         <!-- <?php if (isset($_GET['error'])) { ?>
                <p class="bg-red-500 mb-4 text-center rounded">*<?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php } ?> -->
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
                    <th class="px-4 py-2 border border-gray-300">Blood Type</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Name</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Email</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Phone</th>
                    <th class="px-4 py-2 border border-gray-300">Donor Address</th>
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
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_blood_type']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_name']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_email']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_phone']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_address']); ?></td>
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
            <h2 class="text-xl font-bold mb-4">Set Appointment Time</h2>
            <input type="datetime-local" id="appointmentTime" class="w-full p-2 border border-gray-300 rounded mb-4">
            <div class="flex justify-end">
                <button id="closeModal" class="bg-red-500 text-white font-semibold px-4 py-2 rounded-lg mr-2 hover:bg-red-600">Cancel</button>
                <button id="saveAppointment" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600">Save</button>
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

        // Save appointment time
        $('#saveAppointment').click(function() {
            const appointmentTime = $('#appointmentTime').val();
            if (appointmentTime) {
                updateStatus(currentRequestId, 'Approved', appointmentTime);
                $('#appointmentModal').addClass('hidden');
            } else {
                alert('Please select an appointment time.');
            }
        });

        // Close modal
        $('#closeModal').click(function() {
            $('#appointmentModal').addClass('hidden');
        });

        function updateStatus(requestId, status, appointmentTime = null) {
            $.ajax({
                url: 'updateRequestStatus.php',
                method: 'POST',
                data: {
                    request_id: requestId,
                    status: status,
                    appointment_time: appointmentTime
                },
                success: function(response) {
                    alert(response);
                    // Optionally reload or update the UI
                    location.reload();
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
