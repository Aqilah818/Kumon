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
    $student_ID = $_GET['student_ID']; // Get student_ID from the query parameter
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

// Get the values from the dropdowns (if selected)
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Prepare the SQL query to fetch classwork data with filtering
$query_classwork = "SELECT * FROM classwork WHERE student_ID = ?";

// Add conditions to the query based on selected filters
$params = [$student_ID];
$types = "i";  // Initial type for student_ID (integer)

if ($subject) {
    $query_classwork .= " AND subject = ?";
    $params[] = $subject;
    $types .= "s";  // Add type for subject (string)
}

if ($month) {
    $query_classwork .= " AND month = ?";
    $params[] = $month;
    $types .= "s";  // Add type for month (string)
}

if ($year) {
    $query_classwork .= " AND year = ?";
    $params[] = $year;
    $types .= "s";  // Add type for year (string)
}

// Prepare and execute the query with the dynamic filters
$stmt_classwork = $conn->prepare($query_classwork);
$stmt_classwork->bind_param($types, ...$params);
$stmt_classwork->execute();
$result_classwork = $stmt_classwork->get_result();

// Fetch all classwork records into an array
$classwork_data = [];
while ($row = $result_classwork->fetch_assoc()) {
    $classwork_data[] = $row;
}
$stmt_classwork->close();

// Fetch distinct values for dropdowns from the database
$query_subjects = "SELECT DISTINCT subject FROM classwork";
$query_months = "SELECT DISTINCT month FROM classwork";
$query_years = "SELECT DISTINCT year FROM classwork";

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
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>9</th>
            <th>10</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($classwork_data)) { ?>
            <?php foreach ($classwork_data as $classwork) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($classwork['classwork']); ?></td>
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
            <tr>
                <td colspan="16">No classwork records found.</td>
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
                // Make all cells editable, including CW column (index 0)
                const originalText = cell.innerText.trim();
                cell.innerHTML = `<input type="text" value="${originalText}">`;
            });
        });

        // Toggle buttons
        document.getElementById('edit-button').style.display = 'none';
        document.getElementById('save-button').style.display = 'inline-block';
    }
    function addRow() {
    const table = document.querySelector('table tbody');  // Select the table body
    const newRow = document.createElement('tr');  // Create a new row element

    // Add empty cells to the new row (You can customize the number of columns here)
    for (let i = 0; i < 16; i++) {
        const cell = document.createElement('td');
        if (i === 0) {
            cell.innerHTML = `<input type="text" placeholder="CW">`;  // First column: CW
        } else if (i === 1) {
            cell.innerHTML = `<input type="date">`;  // Second column: Date
        } else if (i === 2) {
            cell.innerHTML = `<input type="text" placeholder="Level">`;  // Third column: Level
        } else if (i === 3) {
            cell.innerHTML = `<input type="text" placeholder="No.">`;  // Fourth column: No.
        } else if (i === 4) {
            cell.innerHTML = `<input type="number" placeholder="Time">`;  // Fifth column: Time
        } else if (i >= 5 && i <= 14) {
            cell.innerHTML = `<input type="number" placeholder="Score ${i - 4}">`;  // Score columns (1-10)
        } else if (i === 15) {
            cell.innerHTML = `<select>
                                <option value="1">Submitted</option>
                                <option value="0">Not Submitted</option>
                              </select>`;  // Last column: Submission status
        }
        newRow.appendChild(cell);  // Append the cell to the row
    }

    table.appendChild(newRow);  // Append the new row to the table body
}

</script>

</body>
</html>
