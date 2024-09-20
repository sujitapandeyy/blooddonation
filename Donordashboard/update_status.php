<?php
require('../connection.php');

// Check if the user is an admin or has permission to update the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the parameters from the POST request
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $delivery_time = isset($_POST['delivery_time']) ? $_POST['delivery_time'] : null;

    // Prepare the SQL query to update the request
    $query = "UPDATE donorblood_request 
              SET status = ?, responsetime = NOW()";
    
    // Add delivery_time to query if the status is 'Approved'
    if ($status === 'Approved' && $delivery_time) {
        $query .= ", delivery_time = ?";
    }

    $query .= " WHERE id = ?";

    // Prepare the statement
    $stmt = $con->prepare($query);

    if ($status === 'Approved' && $delivery_time) {
        // Bind parameters including delivery_time
        $stmt->bind_param("ssi", $status, $delivery_time, $id);
    } else {
        // Bind parameters without delivery_time
        $stmt->bind_param("si", $status, $id);
    }

    // Execute the query
    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement and database connection
    $stmt->close();
    $con->close();
}
?>
