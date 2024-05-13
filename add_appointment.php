<?php
// Establish connection to the database
include 'db_connection.php';

// Check if form data has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $is_full_day = $_POST['is_full_day'];
    $start_time = ($is_full_day == 0) ? $_POST['start_time'] : null;
    $end_time = ($is_full_day == 0) ? $_POST['end_time'] : null;
    $category_id = $_POST['category'];

    // Check if there are events for the entire day
    $event_spans_entire_day = false;
    $sql_check_day = "SELECT * FROM appointments WHERE date = :date AND is_full_day = 1";
    $stmt_check_day = $pdo->prepare($sql_check_day);
    $stmt_check_day->bindParam(':date', $date);
    $stmt_check_day->execute();
    if ($stmt_check_day->rowCount() > 0) {
        $event_spans_entire_day = true;
    }

    // Check if there are events for the entire duration of the time range.
    $event_spans_time_range = false;
    if (!$event_spans_entire_day && $is_full_day == 0) {
        $sql_check_time = "SELECT * FROM appointments WHERE date = :date AND (
            (start_time < :start_time AND end_time > :start_time) OR  
            (start_time < :end_time AND end_time > :end_time) OR     
            (start_time >= :start_time AND end_time <= :end_time) OR  
            (start_time <= :start_time AND end_time >= :end_time)    
        )";        
        $stmt_check_time = $pdo->prepare($sql_check_time);
        $stmt_check_time->bindParam(':date', $date);
        $stmt_check_time->bindParam(':start_time', $start_time);
        $stmt_check_time->bindParam(':end_time', $end_time);
        $stmt_check_time->execute();
        if ($stmt_check_time->rowCount() > 0) {
            $event_spans_time_range = true;
        }
    }

    // If no event covers the entire day or the entire time range, add the appointment.
    if (!$event_spans_entire_day && !$event_spans_time_range) {
        // Prepare and execute SQL statement to add the appointment.
        $sql = "INSERT INTO appointments (title, description, date, end_date, start_time, end_time, is_full_day, category_id)
                VALUES (:title, :description, :date, :end_date, :start_time, :end_time, :is_full_day, :category_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':is_full_day', $is_full_day);
        $stmt->bindParam(':category_id', $category_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        if ($event_spans_entire_day) {
            echo 'error_day';
        } else {
            echo 'error_time';
        }
    }
} else {
    echo 'error_no_data';
}

// Close database connection
$pdo = null;
