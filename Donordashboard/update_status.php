<?php
require('../connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $delivery_time = isset($_POST['delivery_time']) ? $_POST['delivery_time'] : null;
    $donor_id = isset($_POST['donor_id']) ? intval($_POST['donor_id']) : 0; 

    $query = "UPDATE donorblood_request 
              SET status = ?, responsetime = NOW()";
    
    if ($status === 'Approved' && $delivery_time) {
        $query .= ", delivery_time = ?";
    }

    $query .= " WHERE id = ?";

    $stmt = $con->prepare($query);

    if ($status === 'Approved' && $delivery_time) {
        $stmt->bind_param("ssi", $status, $delivery_time, $id);
    } else {
        $stmt->bind_param("si", $status, $id);
    }

    // Execute the query to update the blood request status
    if ($stmt->execute()) {
        // If the status is 'Completed', update the donor's last_donation_date
        if ($status === 'Completed') {
            $donor_update_query = "UPDATE donor SET last_donation_date = NOW() WHERE id = ?";
            $donor_stmt = $con->prepare($donor_update_query);
            $donor_stmt->bind_param("i", $donor_id);

            if ($donor_stmt->execute()) {
                echo "Record updated successfully, and donor's last donation date updated.";
            } else {
                echo "Error updating donor's last donation date: " . $donor_stmt->error;
            }

            $donor_stmt->close();
        } else {
            echo "Record updated successfully.";
        }
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement and database connection
    $stmt->close();
    $con->close();
}
?>
