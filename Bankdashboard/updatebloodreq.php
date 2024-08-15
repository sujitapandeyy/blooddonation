<?php
require('../connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestId = intval($_POST['request_id']);
    $status = $_POST['status'];
    $appointmentTime = $_POST['appointment_time'] ?? null;

    // Debugging lines
    error_log("Request ID: $requestId");
    error_log("Status: $status");
    error_log("Appointment Time: $appointmentTime");

    if ($appointmentTime) {
        // Assuming `appointmentTime` is in HH:MM format, append the current date
        $appointmentDateTime = date('Y-m-d') . ' ' . $appointmentTime . ':00'; // Format as YYYY-MM-DD HH:MM:SS
    } else {
        $appointmentDateTime = null;
    }

    // Update the request status and appointment time
    $sql = "UPDATE blood_requests SET status = ?, delivery_time = ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $con->error);
    }

    $stmt->bind_param('ssi', $status, $appointmentDateTime, $requestId);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}
