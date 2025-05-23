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

// Handle student_ID from the query parameter
if (isset($_GET['student_ID'])) {
    $student_ID = $_GET['student_ID'];
} elseif (isset($_POST['student_ID'])) {
    $student_ID = $_POST['student_ID'];
} else {
    echo "No student selected.";
    exit();
}

// Fetch student data based on student_ID
$stmt = $conn->prepare("SELECT name, rank FROM student WHERE student_ID = ?");
$stmt->bind_param("i", $student_ID); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $student_name = $student['name'];
    $student_rank = $student['rank'];
} else {
    echo "Student not found.";
    exit();
}

// Fetch test data based on the selected student_ID and filters
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Prepare the SQL query to fetch test data with filtering
$query_tests = "
    SELECT st.level, st.grade, st.time, st.status, st.month, st.year, t.subject
    FROM student_test st
    JOIN test t ON st.test_ID = t.test_ID
    WHERE st.student_ID = ?
";

// Add conditions to the query based on selected filters
$params = [$student_ID];
$types = "i";  // 'i' for student_ID

if ($subject) {
    $query_tests .= " AND t.subject = ?";
    $params[] = $subject;
    $types .= "s";  // subject is a string
}

if ($month) {
    $query_tests .= " AND st.month = ?";
    $params[] = $month;
    $types .= "s";  // month is a string
}

if ($year) {
    $query_tests .= " AND st.year = ?";
    $params[] = $year;
    $types .= "s";  // year is a string
}

// Prepare and execute the query with the dynamic filters
$stmt_tests = $conn->prepare($query_tests);
$stmt_tests->bind_param($types, ...$params);
$stmt_tests->execute();
$result_tests = $stmt_tests->get_result();

// Fetch all test records into an array
$test_data = [];
while ($row = $result_tests->fetch_assoc()) {
    $test_data[] = $row;
}
$stmt_tests->close();

$query_subjects = "SELECT DISTINCT subject FROM test";
$query_months = "SELECT DISTINCT month FROM student_test";
$query_years = "SELECT DISTINCT year FROM student_test";

$result_subjects = $conn->query($query_subjects);
$result_months = $conn->query($query_months);
$result_years = $conn->query($query_years);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition - Teacher Info</title>
    <link rel="stylesheet" href="teacher/style3.css">
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

    <div class="container">
        <h1><?php echo htmlspecialchars($student_name); ?></h1>
        <div class="first-container">
            <h2>Rank: <?php echo htmlspecialchars($student_rank); ?></h2>
        </div>
    </div>

    <!-- Dropdown Filters -->
    <form method="POST" action="">
        <!-- Add hidden field for student_ID -->
        <input type="hidden" name="student_ID" value="<?php echo htmlspecialchars($student_ID); ?>">

        <!-- Dropdown Filters -->
        <div class="dropdown-container">
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

            <!-- Edit and Save Buttons -->
            <!-- Edit and Save Button with inline styles -->
            <button type="button" id="edit-button" onclick="enableEditing()" style="width: 40px;">
                <i class='bx bx-pencil' style="font-size: 20px;"></i>
            </button>


            <button id="save-button" style="display: none; width: 40px;" onclick="saveEdits()"><i class='bx bx-save' style="font-size: 20px;"></i></button>
            <button type="button" id="add-button" onclick="addRow()" style="width: 40px;">
                <i class='bx bx-plus' style="font-size: 20px;"></i>
            </button>
        </div>
    </form>

    <div class="second-container">
        <table>
            <thead>
                <tr>
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
                            <td><?php echo htmlspecialchars($test['level']); ?></td>
                            <td><?php echo htmlspecialchars($test['grade']); ?></td>
                            <td><?php echo htmlspecialchars($test['time']); ?> minutes</td>
                            <td><?php echo htmlspecialchars($test['status']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">No test records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
    <script>
    function enableEditing() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell) => {
                const originalText = cell.innerText.trim();
                cell.innerHTML = `<input type="text" value="${originalText}">`;
            });
        });

        // Toggle buttons
        document.getElementById('edit-button').style.display = 'none';
        document.getElementById('save-button').style.display = 'inline-block';
    }

    function addRow() {
        const tableBody = document.querySelector('tbody');
        const newRow = document.createElement('tr');

        const newLevel = document.createElement('td');
        newLevel.innerHTML = `<input type="text" name="level[]" placeholder="Level" required>`;
        newRow.appendChild(newLevel);

        const newGrade = document.createElement('td');
        newGrade.innerHTML = `<input type="text" name="grade[]" placeholder="Grade" required>`;
        newRow.appendChild(newGrade);

        const newTime = document.createElement('td');
        newTime.innerHTML = `<input type="text" name="time[]" placeholder="Time" required>`;
        newRow.appendChild(newTime);

        const newStatus = document.createElement('td');
        newStatus.innerHTML = `<input type="text" name="status[]" placeholder="Status" required>`;
        newRow.appendChild(newStatus);

        tableBody.appendChild(newRow);

        // When adding a row, switch to editing mode
        document.getElementById('edit-button').style.display = 'none';
        document.getElementById('save-button').style.display = 'inline-block';
    }

    function saveEdits() {
        const inputs = document.querySelectorAll('tbody input');
        inputs.forEach(input => {
            const td = input.parentElement;
            td.textContent = input.value.trim();
        });

        // After saving, toggle buttons
        document.getElementById('edit-button').style.display = 'inline-block';
        document.getElementById('save-button').style.display = 'none';
    }
</script>

</body>
</html>