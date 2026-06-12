<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

require_once 'db_connect.php';

$student_id = $_GET['student_id'] ?? null;

if ($student_id === null) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required query parameter: student_id"
    ]);
    exit;
}

$student_id_clean = intval($student_id);

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
        "debug_error" => $conn->error 
    ]);
}
?>
