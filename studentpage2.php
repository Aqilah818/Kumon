<?php
session_start();

if (!isset($_SESSION['parent_id'])) {
    header('Location: parentlogin.php');
    exit();
}

include('db.php');

$parent_id = $_SESSION['parent_id'];

// Fetch parent's ic_no
$parent_stmt = $conn->prepare("SELECT ic_no FROM parents WHERE parents_ID = ?");
$parent_stmt->bind_param("i", $parent_id);
$parent_stmt->execute();
$parent_result = $parent_stmt->get_result();
$parent_data = $parent_result->fetch_assoc();
$parent_ic_no = $parent_data['ic_no'];
$parent_stmt->close();

// Fetch all students for this parent
$student_stmt = $conn->prepare("SELECT student_ID, name FROM student WHERE parent_ic_no = ?");
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

// Fetch latest level per student from classwork table
$latest_levels = [];
if (!empty($student_ids)) {
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $types = str_repeat('i', count($student_ids));

    $sql_latest = "
        SELECT cw.student_ID, cw.level
        FROM classwork cw
        INNER JOIN (
            SELECT student_ID, MAX(date) AS latest_date
            FROM classwork
            WHERE student_ID IN ($placeholders)
            GROUP BY student_ID
        ) latest ON cw.student_ID = latest.student_ID AND cw.date = latest.latest_date
    ";

    $stmt_latest = $conn->prepare($sql_latest);
    $stmt_latest->bind_param($types, ...$student_ids);
    $stmt_latest->execute();
    $result_latest = $stmt_latest->get_result();

    while ($row = $result_latest->fetch_assoc()) {
        $latest_levels[$row['student_ID']] = $row['level'];
    }
    $stmt_latest->close();
}

// Form filters
$selected_student = $_POST['student'] ?? '';
$subject = $_POST['subject'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

$latest_level_for_student_subject = 'Not Assigned';

if ($selected_student && $subject) {
    $stmt_level = $conn->prepare("
        SELECT level 
        FROM classwork 
        WHERE student_ID = ? AND subject_ID = (
            SELECT subject_ID FROM subject WHERE subject = ?
        )
        ORDER BY date DESC 
        LIMIT 1
    ");
    $stmt_level->bind_param("is", $selected_student, $subject);
    $stmt_level->execute();
    $result_level = $stmt_level->get_result();

    if ($row = $result_level->fetch_assoc()) {
        $latest_level_for_student_subject = $row['level'];
    }
    $stmt_level->close();
}


// Fetch classwork data based on filters
$classwork_data = [];
if (!empty($student_ids) && $selected_student && $subject && $month && $year) {
    $query = "
        SELECT cw.*, s.subject 
        FROM classwork cw
        LEFT JOIN subject s ON cw.subject_ID = s.subject_ID
        WHERE cw.student_ID = ? 
          AND s.subject = ? 
          AND DATE_FORMAT(cw.date, '%M') = ? 
          AND YEAR(cw.date) = ?
        ORDER BY cw.date ASC
    ";

    $stmt_classwork = $conn->prepare($query);
    $stmt_classwork->bind_param("isss", $selected_student, $subject, $month, $year);
    $stmt_classwork->execute();
    $result_classwork = $stmt_classwork->get_result();

    while ($row = $result_classwork->fetch_assoc()) {
        $classwork_data[] = $row;
    }
    $stmt_classwork->close();
}

// Fetch subjects
$query_subjects = "SELECT DISTINCT subject FROM subject WHERE subject IN ('English', 'Mathematics')";
$result_subjects = $conn->query($query_subjects);

// Fetch months and years
$query_months = "SELECT DISTINCT DATE_FORMAT(date, '%M') AS month FROM classwork ORDER BY date";
$query_years = "SELECT DISTINCT YEAR(date) AS year FROM classwork ORDER BY date";
$result_months = $conn->query($query_months);
$result_years = $conn->query($query_years);
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
            <li class="non-navigable"><?php echo $_SESSION['parent_name'] ?? 'Parent'; ?></li>
            <li><a href="studentpage1.php"><i class='bx bxs-user'></i><span class="links_name">Student Info</span></a></li>
            <li><a href="studentpage2.php"><i class='bx bx-book-open'></i><span class="links_name">Classwork</span></a></li>
            <li><a href="studentpage3.php"><i class='bx bx-bar-chart-alt'></i><span class="links_name">Academic Performance</span></a></li>
            <li><a href="studentpage4.php"><i class='bx bx-user-plus'></i><span class="links_name">Add Student</span></a></li>
            <li><a href="#reset-password"><i class='bx bxs-lock'></i><span class="links_name">Reset Password</span></a></li>
        </ul>
    </aside>

    <div class="first-container">
        <h1>
            Level: <?php echo htmlspecialchars($latest_level_for_student_subject); ?>
        </h1>


    </div>

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
                <?php if ($selected_student && $subject && $month && $year) { ?>
                    <?php if (!empty($classwork_data)) { ?>
                        <?php foreach ($classwork_data as $cw) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cw['subject']); ?></td>
                                <td><?php echo date('d', strtotime($cw['date'])); ?></td>
                                <td><?php echo htmlspecialchars($cw['level']); ?></td>
                                <td><?php echo htmlspecialchars($cw['number']); ?></td>
                                <td><?php echo htmlspecialchars($cw['time']); ?> minutes</td>
                                <?php for ($i = 1; $i <= 10; $i++) { ?>
                                    <td><?php echo htmlspecialchars($cw["score_$i"] ?? '-'); ?></td>
                                <?php } ?>
                                <td><?php echo $cw['attendance']; ?></td>
                                <td><?php echo htmlspecialchars($cw['submission']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="17">No classwork records found.</td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="17">Please select student, subject, month, and year to view classwork records.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>