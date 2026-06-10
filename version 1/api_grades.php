<?php
// api_grades.php
// Set response type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow API requests from other origins

require_once 'db_connect.php';

// Retrieve student_id from GET query string
$student_id = $_GET['student_id'] ?? null;

if ($student_id === null) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required query parameter: student_id"
    ]);
    exit;
}

// Convert student_id to integer to keep the vulnerability focused on BOLA/IDOR
$student_id_clean = intval($student_id);

// VULNERABLE BOLA (Broken Object Level Authorization) IMPLEMENTATION:
// No token validation, cookies verification, session_start() checking, or access privileges check is performed.
// Any unauthenticated external request can query any student_id.
$sql = "SELECT id, course_name, grade FROM grades WHERE student_id = $student_id_clean";
$result = $conn->query($sql);

if ($result) {
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "student_id" => $student_id_clean,
        "grades" => $grades
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database query failed.",
        "debug_error" => $conn->error // Exposing system error details
    ]);
}
?>
