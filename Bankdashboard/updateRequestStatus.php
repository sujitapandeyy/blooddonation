<?php
require('../connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $deliveryTime = $_POST['delivery_time'] ?? null; // Optional delivery time

    // Debugging: Log received values
    error_log("Received Request ID: " . $requestId);
    error_log("Received Status: " . $status);
    error_log("Received Delivery Time: " . $deliveryTime);

    if ($status === 'Approved' && !empty($deliveryTime)) {
        error_log("Attempting to update delivery time to: " . $deliveryTime);
        $sql = "UPDATE blood_requests SET status = ?, delivery_time = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('ssi', $status, $deliveryTime, $requestId);
    } else {
        $sql = "UPDATE blood_requests SET status = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('si', $status, $requestId);
    }

    if ($stmt->execute()) {
        echo "Status updated successfully!!";
    } else {
        error_log("Failed to update: " . $stmt->error); // Log the error
        echo "Failed to update status: " . $stmt->error; // Return error to user
    }
}
?>
