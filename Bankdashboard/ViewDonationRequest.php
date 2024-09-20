<?php
require('../connection.php');
session_start();

// Check if blood bank is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}
// After successful login, set session variables
// $bankid = $_SESSION['bankid'];
$bankEmail = $_SESSION['bankemail'];

// Get blood bank ID
$sql = "SELECT id FROM users WHERE email = ? AND user_type = 'BloodBank'";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $bankEmail);
$stmt->execute();
$result = $stmt->get_result();
$bank = $result->fetch_assoc();
$bankId = $bank['id'];

// Define blood type priority (lower number means higher priority)
$priority = [
    'AB-' => 1,
    'AB+' => 2,
    'A-' => 3,
    'A+' => 4,
    'B-' => 5,
    'B+' => 6,
    'O-' => 7,
    'O+' => 8,
];

// Convert priority array to SQL CASE statement
$prioritySql = '';
foreach ($priority as $type => $prio) {
    $prioritySql .= "WHEN '{$type}' THEN {$prio} ";
}

// Prepare the SQL query
$sql = "SELECT dr.id, dr.donor_email, dr.quantity, dr.message, dr.request_date, dr.status, dr.appointment_time,
               u.fullname AS donor_name, u.email AS donor_email, u.phone AS donor_phone, u.address AS donor_address, 
               d.donor_blood_type
        FROM donation_requests dr
        JOIN users u ON dr.donor_email = u.email
        JOIN donor d ON u.id = d.id
        WHERE dr.blood_bank_id = ?
        ORDER BY CASE d.donor_blood_type
            $prioritySql
            ELSE 9999
        END";

// Prepare and execute the statement
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
    <?php include("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8">
        <h1 class="text-3xl font-bold mb-8">View Donation Requests</h1>
        <?php if (isset($_GET['error'])): ?>
            <?php
            $errorMessage = $_GET['error'];
            $errorClass = ($errorMessage === 'Status updated successfully!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            ?>
            <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2 border border-gray-300">Blood Type</th>
                    <th class="px-4 py-2 border border-gray-300">Quantity</th>
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
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_name']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_email']); ?></td>
                        <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($row['donor_phone']); ?></td>
                        <td class="py-2 px-4 border-b">
                            <?php 
                                $address = htmlspecialchars($row['donor_address']);
                                $words = explode(' ', $address); // Split address into words
                                $firstThreeWords = implode(' ', array_slice($words, 0, 3)); // Get the first three words
                                echo $firstThreeWords;
                            ?>
                        </td>
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
                    <td colspan="10" class="px-4 py-2 border border-gray-300 text-center">No requests available</td>
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

        // Show modal if status is 'Approved'
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
        if (currentRequestId && appointmentTime) {
            $.post('updatebloodreq.php', {
                id: currentRequestId,
                status: 'Approved',
                appointment_time: appointmentTime
            }, function(response) {
                $('#appointmentModal').addClass('hidden');
                alert(response);
                location.reload(); // Reload page on success
            }).fail(function() {
                alert('Failed to save appointment. Please try again.');
            });
        } else {
            alert('Please select a valid appointment time.');
        }
    });

    // Close modal
    $('#closeModal').click(function() {
        $('#appointmentModal').addClass('hidden');
    });

    // Function to update status via AJAX
    function updateStatus(id, status) {
        $.post('updatebloodreq.php', { id: id, status: status }, function(response) {
            alert(response);
            location.reload(); // Reload the page after status update
        }).fail(function() {
            alert('Failed to communicate with the server. Please try again.');
        });
    }
});

    </script>
</body>
</html>

