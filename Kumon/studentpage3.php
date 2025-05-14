<?php
session_start();

if (!isset($_SESSION['parent_id'])) {
    header('Location: parentlogin.php');
    exit();
}

include('db.php');

$parent_id = $_SESSION['parent_id'];

$parent_stmt = $conn->prepare("SELECT ic_no FROM parents WHERE parents_ID = ?");
$parent_stmt->bind_param("i", $parent_id);
$parent_stmt->execute();
$parent_result = $parent_stmt->get_result();
$parent_data = $parent_result->fetch_assoc();
$parent_ic_no = $parent_data['ic_no'];
$parent_stmt->close();

// Get students
$student_stmt = $conn->prepare("SELECT * FROM student WHERE parent_ic_no = ?");
$student_stmt->bind_param("s", $parent_ic_no);
$student_stmt->execute();
$students_result = $student_stmt->get_result();

$students = [];
$student_ids = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
    $student_ids[] = $row['student_ID'];
}
$student_stmt->close();

$student_id = $students[0]['student_ID'] ?? 0;

// Get rank
$query_rank = "SELECT rank FROM student WHERE student_ID = ?";
$stmt_rank = $conn->prepare($query_rank);
$stmt_rank->bind_param("i", $student_id);
$stmt_rank->execute();
$result_rank = $stmt_rank->get_result();
$current_rank = "Not Assigned";

if ($result_rank->num_rows > 0) {
    $row = $result_rank->fetch_assoc();
    $current_rank = $row['rank'] ?: "Not Assigned";
}
$stmt_rank->close();

// Filters
$selected_student = $_POST['student'] ?? '';
$subject = $_POST['subject'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

$query = "
    SELECT st.*, t.subject
    FROM student_test st
    JOIN test t ON st.test_ID = t.test_ID
    WHERE st.student_ID = ?
";

$params = [$student_id];
$types = "i";

if ($selected_student) {
    $query_classwork .= " AND sc.student_ID = ?";
    $types .= "i";
    $params[] = $selected_student;
}

if ($subject) {
    $query .= " AND t.subject = ?";
    $params[] = $subject;
    $types .= "s";
}
if ($month) {
    $query .= " AND st.month = ?";
    $params[] = $month;
    $types .= "s";
}
if ($year) {
    $query .= " AND st.year = ?";
    $params[] = $year;
    $types .= "s";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$test_data = [];
while ($row = $result->fetch_assoc()) {
    $test_data[] = $row;
}
$stmt->close();

// Dropdown options
$result_subjects = $conn->query("SELECT DISTINCT subject FROM test");
$result_months = $conn->query("SELECT DISTINCT month FROM student_test");
$result_years = $conn->query("SELECT DISTINCT year FROM student_test");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="student/style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<header class="header">
    <div class="icon">
        <a href="studentlogout.php" data-tooltip="Logout">
            <i class='bx bx-log-out' id="log_out"></i>
        </a>
    </div>
</header>

<aside class="sidebar">
    <ul>
        <li class="non-navigable"><?php echo $_SESSION['parent_name'] ?? 'Parent'; ?></li>
        <li><a href="studentpage1.php"><i class='bx bxs-user'></i><span>Student Info</span></a></li>
        <li><a href="studentpage2.php"><i class='bx bx-book-open'></i><span>Classwork</span></a></li>
        <li><a href="studentpage3.php"><i class='bx bx-bar-chart-alt'></i><span>Academic Performance</span></a></li>
        <li><a href="studentpage4.php"><i class='bx bx-user-plus'></i><span class="links_name">Add Student</span></a></li>
        <li><a href="#reset-password"><i class='bx bxs-lock'></i><span>Reset Password</span></a></li>
    </ul>
</aside>

<div class="first-container">
    <h1>Current Level: <?php echo htmlspecialchars($current_rank); ?></h1>
</div>

<form method="POST" action="studentpage3.php">
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
        <th>Subject</th>
        <th>Level</th>
        <th>Grade</th>
        <th>Time</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
<?php if (!empty($test_data)) { ?>
    <?php foreach ($test_data as $test) { ?>
        <tr>
            <td><?php echo htmlspecialchars($test['subject']); ?></td>
            <td><?php echo htmlspecialchars($test['level']); ?></td>
            <td><?php echo htmlspecialchars($test['grade']); ?></td>
            <td><?php echo htmlspecialchars($test['time']); ?> minutes</td>
            <td><?php echo htmlspecialchars($test['status']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="5">No test records found.</td>
    </tr>
<?php } ?>
</tbody>
</table>
</div>
</body>
</html>
