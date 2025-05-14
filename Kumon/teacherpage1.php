<?php
session_start(); // Start the session

// Redirect to login if the user is not logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacherlogin.php');
    exit();
}

// Include database configuration
include('db.php');

// Get the logged-in teacher's ID from the session
$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher data from the database
$stmt = $conn->prepare("SELECT * FROM teacher WHERE teacher_ID = ?");
$stmt->bind_param("i", $teacher_id); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

// Check if the teacher exists
if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    echo "Teacher not found.";
    exit();
}

// Fetch the classwork subject based on the classwork_ID from the teacher's record
$classwork_stmt = $conn->prepare("SELECT subject FROM classwork WHERE classwork_ID = ?");
$classwork_stmt->bind_param("i", $teacher['classwork_ID']); // Using classwork_ID from the teacher table
$classwork_stmt->execute();
$classwork_result = $classwork_stmt->get_result();

// Check if the classwork record exists
if ($classwork_result->num_rows > 0) {
    $classwork = $classwork_result->fetch_assoc();
    $subject_assigned = $classwork['subject'];
} else {
    $subject_assigned = 'N/A'; // Default value if no classwork found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition - Teacher Info</title>
    <link rel="stylesheet" href="teacher/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <!-- Blue Header -->
    <header class="header">
        <div class="icon">
            <a href="teacherlogout.php" data-tooltip="Logout">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <!-- Display teacher name dynamically -->
            <li class="non-navigable"><?php echo $teacher['name']; ?></li> <!-- Non-navigable item -->
            <li>                    
                <a href="teacherpage1.php" data-tooltip="Teacher info">
                    <i class='bx bxs-user'></i>
                    <span class="links_name">Teacher Info</span>
                </a>
            </li>
            <li>
                <a href="teacherpage2.php" data-tooltip="Student record">
                    <i class='bx bx-book-open'></i>
                    <span class="links_name">Student Record</span>
                </a>
            </li>
            <li>
                <a href="teacherpage3.php" data-tooltip="Academic Performance">
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
        <!-- Teacher Information Section -->
        <div class="teacher-info">
            <h2>Teacher Information</h2>
            <div class="teacher-info-container">
                <p><strong>Name:</strong> <?php echo $teacher['name']; ?></p>
                <p><strong>Age:</strong> <?php echo $teacher['age']; ?></p>
                <p><strong>Gender:</strong> <?php echo $teacher['gender']; ?></p>
                <p><strong>IC Number:</strong> <?php echo $teacher['ic_no']; ?></p>
                <p><strong>Email:</strong> <?php echo $teacher['email']; ?></p>
                <p><strong>Assigned Subject:</strong> <?php echo $subject_assigned; ?></p>
                <p><strong>Date of Birth:</strong> <?php echo $teacher['date_of_birth']; ?></p>
                <p><strong>Contact Number:</strong> <?php echo $teacher['contact_no']; ?></p>
            </div>
        </div>
    </main>

</body>
</html>

