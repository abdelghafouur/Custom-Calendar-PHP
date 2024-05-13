<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "Appointment";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log errors for debugging purposes
    error_log("Connection failed: " . $e->getMessage());

    // Display a user-friendly message
    die("Oops! Something went wrong. Please try again later..");
}
?>
