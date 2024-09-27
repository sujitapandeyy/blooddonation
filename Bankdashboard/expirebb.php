<?php
require('../connection.php');

function deleteExpiredBloodRecords($con) {
    $deleteExpiredSQL = "DELETE FROM blood_details WHERE expire <= CURDATE()";
    if ($con->query($deleteExpiredSQL) === TRUE) {
        // Optional: You can log or echo success message
    } else {
        // Optional: Handle error
        error_log("Error deleting expired blood records: " . $con->error);
    }
}
?>
