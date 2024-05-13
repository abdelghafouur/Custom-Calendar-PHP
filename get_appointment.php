<?php
// Include database connection
include 'db_connection.php';

// Retrieve appointment details based on the ID
if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];

        $sql = "SELECT a.*, c.* ,a.id FROM appointments a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment) {

            echo json_encode($appointment);
        } else {

            echo json_encode(array('error' => 'Appointment not found'));
        }
    } catch (PDOException $e) {

        echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
    }
} else {
    
    echo json_encode(array('error' => 'ID parameter not passed'));
}
?>
