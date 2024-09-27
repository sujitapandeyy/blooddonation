<?php 
require('../connection.php');

// Start the session
session_start();

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the parameters from the POST request
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : null;

    // Validate that an ID and status were provided
    if ($id <= 0 || empty($status)) {
        echo "Invalid request data";
        exit();
    }

    // Get bank_email from session
    if (!isset($_SESSION['bankemail'])) {
        echo "Blood bank email not found in session.";
        exit();
    }
    $bankEmail = $_SESSION['bankemail'];

    // Fetch blood bank ID using bank_email
    $bankQuery = "SELECT id FROM users WHERE email = ? AND user_type = 'BloodBank'";
    $bankStmt = $con->prepare($bankQuery);
    $bankStmt->bind_param("s", $bankEmail);
    $bankStmt->execute();
    $bankResult = $bankStmt->get_result();
    $bankData = $bankResult->fetch_assoc();

    if (!$bankData) {
        echo "Blood bank not found.";
        exit();
    }
    $bloodBankId = $bankData['id'];

    // Prepare the SQL query to update the donation request
    $query = "UPDATE donation_requests SET status = ?, responsetime = NOW()";

    // Add appointment_time to the query if the status is 'Approved' and appointment time is provided
    if ($status === 'Approved' && !empty($appointment_time)) {
        $query .= ", appointment_time = ?";
    }

    $query .= " WHERE id = ?";
    $stmt = $con->prepare($query);

    // Bind the parameters
    if ($status === 'Approved' && !empty($appointment_time)) {
        $stmt->bind_param("ssi", $status, $appointment_time, $id);
    } else {
        $stmt->bind_param("si", $status, $id);
    }

    // Execute the query for donation request
    if ($stmt->execute()) {
        // If status is completed, update blood_details table
        if ($status === 'Completed') {
            // Fetch donor details
            $donorQuery = "
                SELECT u.email AS donor_email, dr.quantity, d.donor_blood_type, d.weight, d.gender, d.dob, d.id AS donor_id, u.fullname, u.phone, u.address
                FROM donation_requests dr 
                JOIN users u ON dr.donor_email = u.email
                JOIN donor d ON u.id = d.id 
                WHERE dr.id = ?
            ";
            $donorStmt = $con->prepare($donorQuery);
            $donorStmt->bind_param("i", $id);
            $donorStmt->execute();
            $donorResult = $donorStmt->get_result();
            $donorData = $donorResult->fetch_assoc();
        
            if ($donorData) {
                $donorEmail = $donorData['donor_email'];
                $quantity = $donorData['quantity'];
                $bloodGroup = $donorData['donor_blood_type'];
                $weight = $donorData['weight'];
                $gender = $donorData['gender'];
                $dob = $donorData['dob'];
                $fullname = $donorData['fullname'];
                $phone = $donorData['phone'];
                $address = $donorData['address'];
                $donorId = $donorData['donor_id']; // Fetch donor ID
        
                // Insert into blood_details table
                $bloodDetailsQuery = "
                INSERT INTO blood_details 
                (donor_email, bloodqty, bloodbank_id, collection, bloodgroup, weight, gender, dob, name, contact, address, expire, donor_id) 
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 42 DAY), ?)
                ";

                $bloodDetailsStmt = $con->prepare($bloodDetailsQuery);
                $bloodDetailsStmt->bind_param("sidsisssssi", $donorEmail, $quantity, $bloodBankId, $bloodGroup, $weight, $gender, $dob, $fullname, $phone, $address, $donorId);

                if ($bloodDetailsStmt->execute()) {
                    // Successfully inserted into blood_details, now update last_donation_date in donor table
                    $updateDonorQuery = "UPDATE donor SET last_donation_date = NOW() WHERE id = ?";
                    $updateDonorStmt = $con->prepare($updateDonorQuery);
                    $updateDonorStmt->bind_param("i", $donorId);
                    $updateDonorStmt->execute();
                    
                    // Successfully inserted into blood_details, now delete from donation_requests
                    $deleteQuery = "DELETE FROM donation_requests WHERE id = ?";
                    $deleteStmt = $con->prepare($deleteQuery);
                    $deleteStmt->bind_param("i", $id);
                    
                    if ($deleteStmt->execute()) {
                        echo "Status updated successfully and blood details added! Request removed.";
                    } else {
                        echo "Status updated, blood details added, but failed to remove request.";
                    }

                    $deleteStmt->close();
                } else {
                    echo "Status updated, but error adding blood details: " . $bloodDetailsStmt->error;
                }
                $bloodDetailsStmt->close();
            } else {
                echo "Donor not found for this request.";
            }
            $donorStmt->close();
        } else {
            echo "Status updated successfully!";
        }
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement and database connection
    $stmt->close();
    $con->close();
}
?>
