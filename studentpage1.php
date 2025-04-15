<?php
session_start(); // Start the session

// Redirect to login if the user is not logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: studentlogin.php');
    exit();
}

// Include database configuration
include('db.php');

// Get the logged-in student's ID from the session
$student_id = $_SESSION['student_id'];

// Fetch student data from the database
$stmt = $conn->prepare("SELECT * FROM student WHERE student_ID = ?");
$stmt->bind_param("i", $student_id); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

// Check if the student exists
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student not found.";
    exit();
}

// Fetch parents' information using parent_ID in the students table
if (isset($student['parents_ID'])) { // Check if the student has a parent_ID
    $parent_stmt = $conn->prepare("SELECT * FROM parents WHERE parents_ID = ?");
    $parent_stmt->bind_param("i", $student['parents_ID']);
    $parent_stmt->execute();
    $parent_result = $parent_stmt->get_result();

    // Store parents' information in an array
    $parents = [];
    if ($parent_result->num_rows > 0) {
        while ($parent = $parent_result->fetch_assoc()) {
            $parents[] = $parent;
        }
    }
} else {
    $parents = []; // No parent_ID associated with the student
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="student/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Blue Header -->
    <header class="header">
        <div class="icon">
        <a href="studentlogout.php" data-tooltip="Logout">
            <i class='bx bx-log-out' id="log_out"></i>
        </a>
        </div>
    </header>
    <aside class="sidebar">
    <ul>
        <!-- Display student name dynamically -->
        <li class="non-navigable"><?php echo $student['name']; ?></li> <!-- Non-navigable item -->
        <li>                    
            <a href="studentpage1.php" data-tooltip="Student info">
            <i class='bx bxs-user'></i>
            <span class="links_name">Student Info</span>
            </a>
        </li>
        <li>
            <a href="studentpage2.php" data-tooltip="Classwork">
            <i class='bx bx-book-open'></i>
            <span class="links_name">Classwork</span>
            </a>
        </li>
        <li>
            <a href="studentpage3.php" data-tooltip="Academic Performance">
            <i class='bx bx-bar-chart-alt'></i>
            <span class="links_name">Academic Performance</span>
            </a>
        </li>
        <li>
            <a href="#reset-password" data-tooltip="Reset Password">
            <i class='bx bxs-lock'></i>
            <span class="links_name">Reset Password</span>
            </a>
        </li>
    </ul>
</aside>

<main class="content">
    <!-- Student Information Section -->
    <div class="student-info">
    <h2>Student Information</h2>
    <div class="student-info-container">
    <p><strong>Name:</strong> <?php echo $student['name']; ?></p>
    <p><strong>Age:</strong> <?php echo $student['age']; ?></p>
    <p><strong>Gender:</strong> <?php echo $student['gender']; ?></p>
    <p><strong>Address:</strong> <?php echo $student['address']; ?></p>
    <p><strong>School:</strong> <?php echo $student['school']; ?></p>
    <p><strong>Class Enrollment:</strong> <?php echo $student['class_enrollment']; ?></p>
    <p><strong>Date of Birth:</strong> <?php echo $student['date_of_birth']; ?></p>
    <p><strong>Language:</strong> <?php echo $student['language']; ?></p>
    <p><strong>Days:</strong> <?php echo $student['days']; ?></p>
    <p><strong>Time:</strong> <?php echo $student['time']; ?></p>
    </div>
    </div>
    <!-- Parents Information Section -->
    <div class="parents-info">
        <h2>Parents/Guardian Information</h2>
        <div class="parents-info-container">
        <?php if (!empty($parents)) { ?>
            <?php foreach ($parents as $parent) { ?>
                <p><strong>Name:</strong> <?php echo $parent['name']; ?></p>
                <p><strong>Relationship:</strong> <?php echo $parent['relationship_with_student']; ?></p>
                <p><strong>IC Number:</strong> <?php echo $parent['ic_no']; ?></p>
                <p><strong>Email:</strong> <?php echo $parent['email']; ?></p>
                <p><strong>Address:</strong> <?php echo $parent['address']; ?></p>
                <p><strong>Contact Number:</strong> <?php echo $parent['contact_no']; ?></p>
            <?php } ?>
        <?php } else { ?>
            <p>No parents' information available for this student.</p>
        <?php } ?>
        </div>
    </div>
</main>
</body>
</html>
