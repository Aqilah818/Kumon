<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['parent_id'])) {
    header('Location: parentlogin.php');
    exit();
}

include('db.php');

// Fetch parent info from session
$parent_id = $_SESSION['parent_id'];

$stmt = $conn->prepare("SELECT * FROM parents WHERE parents_ID = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $parent = $result->fetch_assoc();
} else {
    echo "Parent not found.";
    exit();
}

// Fetch student(s) linked to this parent by IC
$students = [];
if (!empty($parent['ic_no'])) {
    $student_stmt = $conn->prepare("SELECT * FROM student WHERE parent_ic_no = ?");
    $student_stmt->bind_param("s", $parent['ic_no']);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();

    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
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
        <div class="logo">
            <img src="kumon.png" alt="Logo">
        </div>
        <div class="icon">
            <a href="parentlogout.php" data-tooltip="Logout">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <li class="non-navigable"><?php echo htmlspecialchars($parent['name']); ?></li>
            <li><a href="studentpage1.php"><i class='bx bxs-user'></i><span class="links_name">Student Info</span></a></li>
            <li><a href="studentpage2.php"><i class='bx bx-book-open'></i><span class="links_name">Classwork</span></a></li>
            <li><a href="studentpage3.php"><i class='bx bx-bar-chart-alt'></i><span class="links_name">Academic Performance</span></a></li>
            <li><a href="studentpage4.php"><i class='bx bx-user-plus'></i><span class="links_name">Add Student</span></a></li>
            <li><a href="#reset-password"><i class='bx bxs-lock'></i><span class="links_name">Reset Password</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="content">
        <!-- Student(s) Info -->
        <div class="student-info">
            <h2>Associated Student(s)</h2>
            <?php if (!empty($students)) { ?>
                <?php foreach ($students as $student) { ?>
                    <div class="student-info-container">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($student['age']); ?> Years Old</p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
                        <p><strong>Class Enrollment:</strong> <?php echo htmlspecialchars($student['class_enrollment']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo date('d/m/Y', strtotime($student['date_of_birth'])); ?></p>
                        <p><strong>Days:</strong> <?php echo htmlspecialchars($student['days']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($student['time']); ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No student records found for this parent.</p>
            <?php } ?>
        </div>
        <!-- Parent Info -->
        <div class="parents-info">
            <h2>Parent/Guardian Information</h2>
            <div class="parents-info-container">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($parent['name']); ?></p>
                <p><strong>Relationship:</strong> <?php echo htmlspecialchars($parent['relationship_with_student']); ?></p>
                <p><strong>IC Number:</strong> <?php echo htmlspecialchars($parent['ic_no']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($parent['email']); ?></p>
                <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($parent['contact_no']); ?></p>
            </div>
        </div>
    </main>
</body>

</html>