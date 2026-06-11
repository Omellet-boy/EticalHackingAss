<?php
// db_connect.php
// Use a dedicated least-privilege DB user for the web app.
// Keep errors controlled so the demo does not expose raw stack traces.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'db_server'; 
$user = 'myedu_app';
$pass = 'Str0ngAppP@ssw0rd!2026#';
$db   = 'myeduconnect';

// Establish connection using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Check connection and expose a controlled message if it fails.
if ($conn->connect_error) {
    error_log('MyEduConnect DB connection failed: ' . $conn->connect_error);
    die('Connection failed. Please contact the administrator.');
}

$conn->set_charset('utf8mb4');
?>
