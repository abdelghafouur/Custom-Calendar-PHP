<?php
// Connect to the database
include 'db_connection.php';

// Check if form data has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $title = htmlspecialchars($_POST['editTitle']);
    $description = htmlspecialchars($_POST['editDescription']);
    $date = htmlspecialchars($_POST['editDate']);
    // Ensure proper handling of the end_date
    $end_date = (!empty($_POST['end_date'])) ? htmlspecialchars($_POST['end_date']) : null;
    $is_full_day = htmlspecialchars($_POST['is_full_dayEdite']);
    // Ensure proper handling of start_time and end_time
    if ($is_full_day == 0) {
        $start_time = htmlspecialchars($_POST['editStartTime']);
        $end_time = htmlspecialchars($_POST['editEndTime']);
    } else {
        $start_time = null;
        $end_time = null;
    }
    $category_id = htmlspecialchars($_POST['category_id']);

    $sql = "UPDATE appointments SET title = :title, description = :description, date = :date, end_date = :end_date, start_time = :start_time, is_full_day = :is_full_day, end_time = :end_time, category_id = :category_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':id', $_POST['editId'], PDO::PARAM_INT);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':is_full_day', $is_full_day);
    $stmt->bindParam(':category_id', $category_id);

    if ($stmt->execute()) {

        header("Location: index.php?msgEdite=success");
        exit();
    } else {

        header("Location: index.php?msgEdite=error");
        exit();
    }
} else {

    header("Location: index.php?msgEdite=error");
}
?>
