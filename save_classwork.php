<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include('db.php');

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['student_ID']) || !isset($data['classwork'][0])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing student_ID or classwork data']);
    exit;
}

$student_ID = (int)$data['student_ID'];
$classwork = $data['classwork'][0];

// Validate required fields
$required_fields = ['subject', 'date', 'level', 'number', 'time', 'attendance', 'submission'];
foreach ($required_fields as $field) {
    if (!isset($classwork[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Assign and sanitize input
$subject = $classwork['subject'];
$date = $classwork['date'];
$original_date = isset($classwork['original_date']) ? $classwork['original_date'] : $date; // fallback to same date if not provided
$level = $classwork['level'];
$number = (int)$classwork['number'];
$time = (int)$classwork['time'];

// Validate date format (YYYY-MM-DD)
$date_obj = DateTime::createFromFormat('Y-m-d', $date);
if (!$date_obj || $date_obj->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Expected YYYY-MM-DD']);
    exit;
}

// Sanitize attendance and submission
$valid_attendance = ['Attend', 'Absent', 'No Class'];
$attendance = in_array($classwork['attendance'], $valid_attendance) ? $classwork['attendance'] : 'No Class';

$valid_submission = ['Submitted', 'Not Submitted'];
$submission = in_array($classwork['submission'], $valid_submission) ? $classwork['submission'] : 'Not Submitted';

// Get subject_ID from subject name
$stmt = $conn->prepare("SELECT subject_ID FROM subject WHERE subject = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $subject);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => "Subject not found: $subject"]);
    exit;
}
$subject_ID = $result->fetch_assoc()['subject_ID'];
$stmt->close();

// Check if classwork already exists (use original_date to find it)
$check = $conn->prepare("SELECT classwork_ID FROM classwork WHERE student_ID = ? AND subject_ID = ? AND date = ?");
if (!$check) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare check failed: " . $conn->error]);
    exit;
}
$check->bind_param("iis", $student_ID, $subject_ID, $original_date);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    // Record exists, perform update (including new date)
    $update = $conn->prepare("UPDATE classwork 
        SET level = ?, number = ?, time = ?, attendance = ?, submission = ?, date = ?
        WHERE student_ID = ? AND subject_ID = ? AND date = ?");
    if (!$update) {
        http_response_code(500);
        echo json_encode(['error' => "Prepare update failed: " . $conn->error]);
        exit;
    }
    $update->bind_param("siisssiis", $level, $number, $time, $attendance, $submission, $date, $student_ID, $subject_ID, $original_date);
    if (!$update->execute()) {
        http_response_code(500);
        echo json_encode(['error' => "Update failed: " . $update->error]);
        exit;
    }
    $update->close();
} else {
    // No record found, insert a new one
    $insert = $conn->prepare("INSERT INTO classwork (student_ID, subject_ID, date, level, number, time, attendance, submission) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$insert) {
        http_response_code(500);
        echo json_encode(['error' => "Prepare insert failed: " . $conn->error]);
        exit;
    }
    $insert->bind_param("iissiiss", $student_ID, $subject_ID, $date, $level, $number, $time, $attendance, $submission);
    if (!$insert->execute()) {
        http_response_code(500);
        echo json_encode(['error' => "Insert failed: " . $insert->error]);
        exit;
    }
    $insert->close();
}

$check->close();
$conn->close();

echo json_encode(['message' => 'Classwork record saved successfully.']);
?>
