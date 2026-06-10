<?php
// db_connect.php
// Setup error reporting to intentionally expose raw errors for vulnerability demonstration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Docker service name is 'db'. Change to 'localhost' if testing outside Docker container.
$host = 'db'; 
$user = 'root';
$pass = '';
$db   = 'myeduconnect';

// Establish connection using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Check connection and expose raw error details if it fails
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
