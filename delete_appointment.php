<?php
// Include database connection
include 'db_connection.php';

// Check if appointment ID is provided
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM appointments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);

    // Execute deletion of the appointment
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error_deletion_failed';
    }
} else {
    echo 'error_invalid_id';
}
?>
