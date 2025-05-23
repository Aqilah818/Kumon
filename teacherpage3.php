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

// Handle the search query
$search_query = isset($_POST['search']) ? $_POST['search'] : '';
$query_students = "SELECT student_ID, name, days FROM student WHERE name LIKE ?";
$stmt_students = $conn->prepare($query_students);
$search_param = "%" . $search_query . "%";
$stmt_students->bind_param("s", $search_param);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition - Teacher Info</title>
    <link rel="stylesheet" href="teacher/style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <!-- Blue Header -->
    <header class="header">
                <div class="logo">
            <img src="kumon.png" alt="Logo">
        </div>
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

    <h1 style="margin-left: 290px; white-space: nowrap;">Student Academic Performance</h1>

    <div class="search-container">
        <form method="POST" action="teacherpage3.php" class="search-bar">
            <input type="text" name="search" placeholder="Search by student name..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit"><i class='bx bx-search'></i></button>
        </form>
    </div>

    <div class="container">
        <table>
            <thead>
                <tr>
                <th style="width: 50px;">No.</th>
                <th>Student Name</th>
                <th>Days</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_students->num_rows > 0) { ?>
                    <?php $number = 1; // Start numbering from 1 ?>
                    <?php while ($row = $result_students->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $number++; ?></td>
                            <td><a href="teacherpage5.php?student_ID=<?php echo $row['student_ID']; ?>" style="color: black; text-decoration: none;"><?php echo htmlspecialchars($row['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['days']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3">No student records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>
</html>