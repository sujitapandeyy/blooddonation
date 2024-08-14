<?php
require('../connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestId = intval($_POST['request_id']);
    $status = $_POST['status'];
    $appointmentTime = $_POST['appointment_time'] ?? null;

    // Update the request status and appointment time
    $sql = "UPDATE donation_requests SET status = ?, appointment_time = ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ssi', $status, $appointmentTime, $requestId);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status.";
    }

    $stmt->close();
}
?>
