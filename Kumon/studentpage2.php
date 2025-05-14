<?php
session_start(); // Start the session

// Redirect to login if the parent is not logged in
if (!isset($_SESSION['parent_id'])) {
    header('Location: parentlogin.php');
    exit();
}

include('db.php');

// Get the logged-in parent's ID from session
$parent_id = $_SESSION['parent_id'];

// Fetch parent's ic_no
$parent_stmt = $conn->prepare("SELECT ic_no FROM parents WHERE parents_ID = ?");
$parent_stmt->bind_param("i", $parent_id);
$parent_stmt->execute();
$parent_result = $parent_stmt->get_result();
$parent_data = $parent_result->fetch_assoc();
$parent_ic_no = $parent_data['ic_no'];
$parent_stmt->close();

// Fetch all students associated with this parent, including their rank
$student_stmt = $conn->prepare("SELECT student_ID, name, rank FROM student WHERE parent_ic_no = ?");
$student_stmt->bind_param("s", $parent_ic_no);
$student_stmt->execute();
$students_result = $student_stmt->get_result();

$students = [];
$student_ids = [];
$student_ranks = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
    $student_ids[] = $row['student_ID'];
    $student_ranks[$row['student_ID']] = $row['rank']; // Store rank by student_ID
}
$student_stmt->close();

// Get filter inputs
$selected_student = $_POST['student'] ?? '';
$subject = $_POST['subject'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

// Prepare query for classwork data (for all children)
$classwork_data = [];
if (!empty($student_ids)) {
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $query_classwork = "
        SELECT sc.*, cw.subject 
        FROM student_classwork sc
        LEFT JOIN classwork cw ON sc.classwork_ID = cw.classwork_ID
        WHERE sc.student_ID IN ($placeholders)
    ";

    $types = str_repeat("i", count($student_ids));
    $params = $student_ids;

    // Add filters
    if ($selected_student) {
        $query_classwork .= " AND sc.student_ID = ?";
        $types .= "i";
        $params[] = $selected_student;
    }    
    if ($subject) {
        $query_classwork .= " AND cw.subject = ?";
        $types .= "s";
        $params[] = $subject;
    }
    if ($month) {
        $query_classwork .= " AND sc.month = ?";
        $types .= "s";
        $params[] = $month;
    }
    if ($year) {
        $query_classwork .= " AND sc.year = ?";
        $types .= "s";
        $params[] = $year;
    }

    $stmt_classwork = $conn->prepare($query_classwork);
    $stmt_classwork->bind_param($types, ...$params);
    $stmt_classwork->execute();
    $result_classwork = $stmt_classwork->get_result();

    while ($row = $result_classwork->fetch_assoc()) {
        $classwork_data[] = $row;
    }
    $stmt_classwork->close();
}

// Fetch distinct subjects (English and Mathematics) from the classwork table
$query_subjects = "SELECT DISTINCT subject FROM classwork WHERE subject IN ('English', 'Mathematics')";
$result_subjects = $conn->query($query_subjects);

// Fetch distinct months and years for filters
$query_months = "SELECT DISTINCT month FROM student_classwork";
$query_years = "SELECT DISTINCT year FROM student_classwork";
$result_months = $conn->query($query_months);
$result_years = $conn->query($query_years);

// Get distinct ranks from student data
$ranks = array_unique(array_column($students, 'rank'));
$display_rank = !empty($ranks) ? implode('', $ranks) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="student/style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Blue Header -->
    <header class="header">
        <div class="icon">
            <a href="parentlogout.php" data-tooltip="Logout">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <li class="non-navigable"><?php echo $_SESSION['parent_name'] ?? 'Parent'; ?></li>
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
            <li><a href="studentpage4.php"><i class='bx bx-user-plus'></i><span class="links_name">Add Student</span></a></li>
            <li>
                <a href="#reset-password" data-tooltip="Reset Password">
                    <i class='bx bxs-lock'></i>
                    <span class="links_name">Reset Password</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <div class="first-container">
        <h1>Current Level: <?php echo htmlspecialchars($display_rank); ?></h1>
    </div>

    <!-- Dropdown Filters -->
    <form method="POST" action="studentpage2.php">
        <div class="dropdown-container">
        <div class="dropdown">
    <select name="student" id="student">
        <option value="">Student</option>
        <?php foreach ($students as $stu) { ?>
            <option value="<?php echo $stu['student_ID']; ?>" <?php if ($selected_student == $stu['student_ID']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($stu['name']); ?>
            </option>
        <?php } ?>
    </select>
</div>

            <div class="dropdown">
                <select name="subject" id="subject">
                    <option value="">Subject</option>
                    <?php while ($row = $result_subjects->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['subject']); ?>" <?php if ($subject == $row['subject']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['subject']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="dropdown">
                <select name="month" id="month">
                    <option value="">Month</option>
                    <?php while ($row = $result_months->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['month']); ?>" <?php if ($month == $row['month']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['month']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="dropdown">
                <select name="year" id="year">
                    <option value="">Year</option>
                    <?php while ($row = $result_years->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['year']); ?>" <?php if ($year == $row['year']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['year']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" style="width: 40px;"><i class='bx bx-filter' style="font-size: 30px;"></i></button>
        </div>  
    </form>

    <div class="second-container">   
        <table>
            <thead>
                <tr>
                    <th rowspan="2">CW</th>
                    <th rowspan="2">Date</th>
                    <th rowspan="2">Level</th>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Time</th>
                    <th colspan="10">Score</th>
                    <th rowspan="2">Attendance</th>
                    <th rowspan="2">Submission</th>
                </tr>
                <tr>
                    <?php for ($i = 1; $i <= 10; $i++) echo "<th>$i</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($classwork_data)) { ?>
                    <?php foreach ($classwork_data as $classwork) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($classwork['subject']); ?></td>
                            <td><?php echo htmlspecialchars($classwork['date']); ?></td>
                            <td><?php echo htmlspecialchars($classwork['level']); ?></td>
                            <td><?php echo htmlspecialchars($classwork['number']); ?></td>
                            <td><?php echo htmlspecialchars($classwork['time']); ?> minutes</td>
                            <?php for ($i = 1; $i <= 10; $i++) { ?>
                                <td><?php echo htmlspecialchars($classwork["score_$i"] ?? '-'); ?></td>
                            <?php } ?>
                            <td><?php echo $classwork['attendance'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo $classwork['submission'] ? 'Submitted' : 'Not Submitted'; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="16">No classwork records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

