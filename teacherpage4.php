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
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

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

// Fetch student name
$stmt = $conn->prepare("SELECT name FROM student WHERE student_ID = ?");
$stmt->bind_param("i", $student_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $student_name = $student['name'];
} else {
    echo "Student not found.";
    exit();
}

// Get filters first (from POST)
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Prepare base level query with joins
$level_query = "
    SELECT cw.level 
    FROM classwork cw
    LEFT JOIN subject s ON cw.subject_ID = s.subject_ID
    WHERE cw.student_ID = ?
";

$level_params = [$student_ID];
$level_types = "i";

// Initialize $student_level empty
$student_level = '';

// Only fetch level if subject filter is selected
if (!empty($subject)) {
    // Build query only filtering by student_ID and subject
    $level_query = "
        SELECT cw.level 
        FROM classwork cw
        LEFT JOIN subject s ON cw.subject_ID = s.subject_ID
        WHERE cw.student_ID = ?
        AND s.subject = ?
        ORDER BY cw.date DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($level_query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind only student_ID and subject, no month/year filtering here
    $stmt->bind_param("is", $student_ID, $subject);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_level = $row['level'];
    } else {
        $student_level = ''; // no level found for subject
    }
} else {
    // No subject selected - show empty level
    $student_level = '';
}


// Build query joining classwork with subject to get subject name and other details
$query_classwork = "
    SELECT cw.*, s.subject 
    FROM classwork cw
    LEFT JOIN subject s ON cw.subject_ID = s.subject_ID
    WHERE cw.student_ID = ?
";

$params = [$student_ID];
$types = "i";

if ($subject) {
    $query_classwork .= " AND s.subject = ?";
    $params[] = $subject;
    $types .= "s";
}

if ($month) {
    $query_classwork .= " AND MONTH(cw.date) = ?";
    $params[] = $month;
    $types .= "i";
}

if ($year) {
    $query_classwork .= " AND YEAR(cw.date) = ?";
    $params[] = $year;
    $types .= "i";
}

// Add ordering by date ascending
$query_classwork .= " ORDER BY cw.date ASC";

$stmt_classwork = $conn->prepare($query_classwork);
$stmt_classwork->bind_param($types, ...$params);
$stmt_classwork->execute();
$result_classwork = $stmt_classwork->get_result();

$classwork_data = [];
while ($row = $result_classwork->fetch_assoc()) {
    $classwork_data[] = $row;
}
$stmt_classwork->close();


// FIXED PART BELOW:
$sql = "SELECT DISTINCT subject FROM subject";
$result_subjects = $conn->query($sql);

if (!$result_subjects) {
    die("Database error: " . $conn->error);
}

// Use these queries to extract month names and years from the `date` column:
$result_months = $conn->query("SELECT DISTINCT MONTH(date) AS month_num, MONTHNAME(date) AS month_name FROM classwork ORDER BY month_num");
$result_years = $conn->query("SELECT DISTINCT YEAR(date) AS year FROM classwork ORDER BY year");

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
            <li class="non-navigable"><?php echo $teacher['name']; ?></li>
            <li><a href="teacherpage1.php" data-tooltip="Teacher info"><i class='bx bxs-user'></i><span class="links_name">Teacher Info</span></a></li>
            <li><a href="teacherpage2.php" data-tooltip="Student record"><i class='bx bx-book-open'></i><span class="links_name">Student Record</span></a></li>
            <li><a href="teacherpage3.php" data-tooltip="Academic Performance"><i class='bx bx-bar-chart-alt'></i><span class="links_name">Academic Performance</span></a></li>
            <li><a href="#reset-password" data-tooltip="Reset Password"><i class='bx bxs-lock'></i><span class="links_name">Reset Password</span></a></li>
        </ul>
    </aside>

    <div class="container">
        <h1><?php echo htmlspecialchars($student_name); ?></h1>
        <div class="first-container">
            <h2>Level: <?php echo htmlspecialchars($student_level); ?></h2>
        </div>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="student_ID" value="<?php echo htmlspecialchars($student_ID); ?>">

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
                        <option value="<?php echo htmlspecialchars($row['month_num']); ?>" <?php if ($month == $row['month_num']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['month_name']); ?>
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
            <button type="button" id="edit-button" onclick="enableEditing()" style="width: 40px;"><i class='bx bx-pencil' style="font-size: 20px;"></i></button>
            <button id="save-button" style="display: none; width: 40px;" type="button" onclick="saveEdits()">
                <i class='bx bx-save' style="font-size: 20px;"></i></button>
            <button type="button" id="add-button" onclick="addRow()" style="width: 40px;"><i class='bx bx-plus' style="font-size: 20px;"></i></button>
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
                <?php
                // Show message if any filter (subject, month, year) is empty
                if (empty($subject) || empty($month) || empty($year)) { ?>
                    <tr>
                        <td colspan="17">Please select student, subject, month, and year to view classwork records.</td>
                    </tr>
                    <?php
                } else {
                    if (!empty($classwork_data)) {
                        foreach ($classwork_data as $cw) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cw['subject']); ?></td>
                                <td data-full-date="<?php echo htmlspecialchars($cw['date']); ?>">
                                    <?php echo date('d', strtotime($cw['date'])); ?>
                                </td>

                                <td><?php echo htmlspecialchars($cw['level']); ?></td>
                                <td><?php echo htmlspecialchars($cw['number']); ?></td>
                                <td><?php echo htmlspecialchars($cw['time']); ?> minutes</td>
                                <?php for ($i = 1; $i <= 10; $i++) { ?>
                                    <td><?php echo htmlspecialchars($cw["score_$i"] ?? '-'); ?></td>
                                <?php } ?>
                                <td><?php echo $cw['attendance']; ?></td>
                                <td><?php echo htmlspecialchars($cw['submission']); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="17">No classwork records found.</td>
                        </tr>
                <?php }
                } ?>

            </tbody>
        </table>
    </div>

    <script>
        function enableEditing() {
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');

                cells.forEach((cell, index) => {
                    const value = cell.innerText.trim();

                    if (index === 1) {
                        let fullDate = cell.getAttribute('data-full-date') || '';

                        // Validate fullDate format yyyy-mm-dd before assigning to input
                        const isValidDate = /^\d{4}-\d{2}-\d{2}$/.test(fullDate);
                        const dateValue = isValidDate ? fullDate : '';

                        cell.innerHTML = `<input type="date" value="${dateValue}">`;
                    } else if (index >= 5 && index <= 14) {
                        // Score inputs
                        cell.innerHTML = `<input type="number" value="${value === '-' ? '' : value}" min="0" max="100">`;
                    } else if (index === 15) {
                        // Attendance dropdown
                        cell.innerHTML = `
                    <select>
                        <option value="Attend" ${value === 'Attend' ? 'selected' : ''}>Attend</option>
                        <option value="Absent" ${value === 'Absent' ? 'selected' : ''}>Absent</option>
                        <option value="No Class" ${value === 'No Class' ? 'selected' : ''}>No Class</option>
                    </select>`;
                    } else if (index === 16) {
                        // Submission dropdown
                        cell.innerHTML = `
                    <select>
                        <option value="1" ${value === '1' || value.toLowerCase() === 'submitted' ? 'selected' : ''}>Submitted</option>
                        <option value="0" ${value === '0' || value.toLowerCase() === 'not submitted' ? 'selected' : ''}>Not Submitted</option>
                    </select>`;
                    } else {
                        // Default to text input
                        cell.innerHTML = `<input type="text" value="${value}">`;
                    }
                });
            });

            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('save-button').style.display = 'inline-block';
        }

        function addRow() {
            const table = document.querySelector('table tbody');
            const newRow = document.createElement('tr');
            const today = new Date().toISOString().split('T')[0]; // yyyy-mm-dd

            for (let i = 0; i < 17; i++) {
                const cell = document.createElement('td');
                if (i === 0) {
                    cell.innerHTML = `<input type="text" placeholder="CW">`;
                } else if (i === 1) {
                    cell.innerHTML = `<input type="date" value="${today}">`;
                } else if (i === 2) {
                    cell.innerHTML = `<input type="text" placeholder="Level">`;
                } else if (i === 3) {
                    cell.innerHTML = `<input type="text" placeholder="No.">`;
                } else if (i === 4) {
                    cell.innerHTML = `<input type="number" placeholder="Time">`;
                } else if (i >= 5 && i <= 14) {
                    cell.innerHTML = `<input type="number" placeholder="Score ${i - 4}">`;
                } else if (i === 15) {
                    cell.innerHTML = `
                <select>
                    <option value="Attend">Attend</option>
                    <option value="Absent">Absent</option>
                    <option value="No Class">No Class</option>
                </select>`;
                } else if (i === 16) {
                    cell.innerHTML = `
                <select>
                    <option value="1">Submitted</option>
                    <option value="0">Not Submitted</option>
                </select>`;
                }
                newRow.appendChild(cell);
            }

            table.appendChild(newRow);
            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('save-button').style.display = 'inline-block';
        }

        function saveEdits() {
            const rows = document.querySelectorAll('tbody tr');
            const data = [];

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');

                let dateValue = '';
                const dateInput = cells[1].querySelector('input[type="date"]');
                if (dateInput) {
                    dateValue = dateInput.value;
                } else {
                    dateValue = cells[1].innerText.trim();
                }

                const entry = {
                    subject: cells[0].querySelector('input')?.value || cells[0].innerText.trim(),
                    date: dateValue,
                    level: cells[2].querySelector('input')?.value || cells[2].innerText.trim(),
                    number: cells[3].querySelector('input')?.value || cells[3].innerText.trim(),
                    time: cells[4].querySelector('input')?.value || cells[4].innerText.trim(),
                    scores: [],
                    attendance: cells[15].querySelector('select')?.value || cells[15].innerText.trim(),
                    submission: cells[16].querySelector('select')?.value || cells[16].innerText.trim()
                };

                for (let i = 5; i <= 14; i++) {
                    const scoreInput = cells[i].querySelector('input');
                    entry.scores.push(scoreInput ? scoreInput.value : cells[i].innerText.trim());
                }

                data.push(entry);
            });

            fetch('save_classwork.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        student_ID: <?php echo json_encode($student_ID); ?>,
                        classwork: data
                    })
                })
                .then(response => response.text())
                .then(result => {
                    alert('Saved successfully!');
                    location.reload();
                })
                .catch(error => {
                    console.error('Save error:', error);
                    alert('Failed to save data.');
                });
        }
        function saveClasswork(student_ID, subject, original_date, new_date, level, number, time, attendance, submission) {
    const payload = {
        student_ID: student_ID,
        classwork: [{
            subject: subject,
            original_date: original_date, // date before edit
            date: new_date,               // possibly updated date
            level: level,
            number: number,
            time: time,
            attendance: attendance,
            submission: submission
        }]
    };

    fetch('save_classwork.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        } else if (data.error) {
            alert("Error: " + data.error);
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        alert('AJAX request failed');
    });
}
    </script>
</body>

</html>