<?php
require('../connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $deliveryTime = $_POST['delivery_time'] ?? null; 

    // Fetch the blood bank ID from the session
    if (!isset($_SESSION['bankemail'])) {
        echo "Error: Blood bank not logged in.";
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

    // Fetch request details
    $sql = "SELECT quantity, bloodgroup FROM blood_requests WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $requestedQty = $request['quantity'];
    $requestedBloodGroup = $request['bloodgroup'];

    if ($status === 'Approved') {
        $sql = "SELECT id, bloodqty, expire FROM blood_details WHERE bloodgroup = ? AND expire > CURDATE() AND bloodbank_id = ? ORDER BY expire ASC";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('si', $requestedBloodGroup, $bankId); 
        $stmt->execute();
        $result = $stmt->get_result();

        $totalAvailableQty = 0;
        $bloodDetailsRecords = [];

        // Collect all valid bloodqty and calculate total available quantity
        while ($row = $result->fetch_assoc()) {
            // Check if the expire date is in the future
            if (strtotime($row['expire']) > strtotime(date('Y-m-d'))) {
                $bloodDetailsRecords[] = $row;  // Save each valid record
                $totalAvailableQty += $row['bloodqty'];  // Sum all available quantities
            }
        }

        // Check if there's enough blood to fulfill the request
        if ($requestedQty <= $totalAvailableQty) {
            $remainingQtyToReduce = $requestedQty;

            foreach ($bloodDetailsRecords as $bloodDetails) {
                $availableQty = $bloodDetails['bloodqty'];
                $bloodDetailsId = $bloodDetails['id'];

                // Determine how much to reduce from this record
                if ($remainingQtyToReduce <= $availableQty) {
                    $newQty = $availableQty - $remainingQtyToReduce;
                    $remainingQtyToReduce = 0;  // All quantity has been reduced
                } else {
                    $newQty = 0;  // Reduce all available quantity in this record
                    $remainingQtyToReduce -= $availableQty;  // Reduce the remaining amount
                }

                // Update the quantity in the blood_details table for this record
                $sql = "UPDATE blood_details SET bloodqty = ? WHERE id = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('ii', $newQty, $bloodDetailsId);
                $stmt->execute();

                // Stop looping if we have reduced the entire requested quantity
                if ($remainingQtyToReduce == 0) {
                    break;
                }
            }

            // Update the request status and delivery time
            $sql = "UPDATE blood_requests SET status = ?, delivery_time = ? WHERE id = ?";
            $stmt->prepare($sql);
            $stmt->bind_param('ssi', $status, $deliveryTime, $requestId);
            if ($stmt->execute()) {
                echo "Status updated and quantity reduced successfully!";
            } else {
                echo "Failed to update status.";
            }
        } else {
            echo "Requested quantity exceeds total available stock.";
        }
    } else {
        // Only update the status if it's not 'Approved'
        $sql = "UPDATE blood_requests SET status = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('si', $status, $requestId);
        if ($stmt->execute()) {
            echo "Status updated successfully!";
        } else {
            echo "Failed to update status.";
        }
    }
}
?>
