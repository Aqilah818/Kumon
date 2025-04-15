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

// Fetch rank data for the logged-in student from the students table
$query_rank = "SELECT rank FROM student WHERE student_ID = ?";
$stmt_rank = $conn->prepare($query_rank);
$stmt_rank->bind_param("i", $student_id);
$stmt_rank->execute();
$result_rank = $stmt_rank->get_result();

// Initialize rank variable
$current_rank = null;
if ($result_rank->num_rows > 0) {
    $row = $result_rank->fetch_assoc();
    $current_rank = $row['rank'];
} else {
    $current_rank = "Not Assigned";
}
$stmt_rank->close();

// Get the values from the dropdowns (if selected)
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Prepare the SQL query to fetch classwork data with filtering
$query_classwork = "SELECT * FROM classwork WHERE student_ID = ?";

// Add conditions to the query based on selected filters
$params = [$student_id];
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
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="student/style2.css">
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
            <li class="non-navigable"><?php echo $_SESSION['student_name']; ?></li> <!-- Non-navigable item -->
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

    <div class="first-container">
        <h1>Current Rank: 
        <?php echo htmlspecialchars($current_rank); ?>
        </h1>
    </div>  

   <!-- Dropdown Filters -->
   <form method="POST" action="studentpage2.php">
    <div class="dropdown-container">
        <!-- Subject Dropdown -->
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

        <!-- Month Dropdown -->
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

        <!-- Year Dropdown -->
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
</body>
</html>

