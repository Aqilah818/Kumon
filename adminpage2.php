<?php
session_start(); // Start the session

// Redirect to login if the user is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

// Include database configuration
include('db.php');

// Get the logged-in admin's ID from the session
$admin_id = $_SESSION['admin_id'];

// Fetch admin data from the database
$stmt = $conn->prepare("SELECT * FROM admin WHERE admin_ID = ?");
$stmt->bind_param("i", $admin_id); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

// Check if the admin exists
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin not found.";
    exit();
}

// Handle the search query
$search_query = isset($_POST['search']) ? trim($_POST['search']) : ''; // Trim input for safety
$search_param = "%" . strtolower($search_query) . "%"; // Convert to lowercase for case-insensitive search

// Query for Subjects, Ages, Levels, and Days
$query_subjects = "SELECT DISTINCT class_enrollment FROM student";
$result_subjects = $conn->query($query_subjects);

$query_age = "SELECT DISTINCT age FROM student ORDER BY age ASC";
$result_age = $conn->query($query_age);

$query_levels = "SELECT DISTINCT rank FROM student WHERE rank IS NOT NULL ORDER BY rank";
$result_levels = $conn->query($query_levels);

$query_days = "SELECT DISTINCT days FROM student";
$result_days = $conn->query($query_days);

// Query to fetch distinct time values from the database
$query_time = "SELECT DISTINCT time FROM student WHERE time IS NOT NULL ORDER BY time";
$result_time = $conn->query($query_time);

// Handle the filter values
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$age = isset($_POST['age']) ? $_POST['age'] : '';
$level = isset($_POST['level']) ? $_POST['level'] : '';
$days = isset($_POST['days']) ? $_POST['days'] : '';
$time = isset($_POST['time']) ? $_POST['time'] : ''; // Default to empty if not set


// Building the query
$query = "SELECT student_ID, name, class_enrollment, age, time, days FROM student WHERE 1";

// Start building the query with search
$params = [];
$types = "";

if ($search_query) {
    $query .= " AND LOWER(name) LIKE ?";
    $params[] = $search_param;
    $types .= "s"; // 's' for string
}

// Add additional filters if applicable (separate from search)
if ($subject) {
    $query .= " AND class_enrollment = ?";
    $params[] = $subject;
    $types .= "s";
}

if ($age) {
    $query .= " AND age = ?";
    $params[] = $age;
    $types .= "i"; // 'i' for integer
}

if ($level) {
    $query .= " AND rank = ?"; // You may not need this anymore, as you're replacing rank with time
    $params[] = $level;
    $types .= "s";
}

if ($days) {
    $query .= " AND days = ?";
    $params[] = $days;
    $types .= "s";
}

// Prepare and execute the query
$stmt_students = $conn->prepare($query);
if (!empty($params)) {
    $stmt_students->bind_param($types, ...$params);
}
$stmt_students->execute();
$result_students = $stmt_students->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="admin/style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <!-- Blue Header -->
    <header class="header">
        <div class="icon">
        <a href="adminlogout.php" data-tooltip="Logout">
            <i class='bx bx-log-out' id="log_out"></i>
        </a>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <!-- Display admin name dynamically -->
            <li class="non-navigable"><?php echo $admin['name']; ?></li> <!-- Non-navigable item -->
            <li>                    
                <a href="adminpage1.php" data-tooltip="Dashboard">
                <i class='bx bxs-dashboard'></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="adminpage2.php" data-tooltip="Student Management">
                    <i class='bx bx-book-open'></i>
                    <span class="links_name">Student Management</span>
                </a>
            </li>
            <li>
                <a href="adminpage3.php" data-tooltip="Teacher Management">
                    <i class='bx bx-pen'></i>
                    <span class="links_name">Teacher Management</span>
                </a>
            </li>
            <li>
                <a href="adminpage4.php" data-tooltip="Performance Analytics">
                    <i class='bx bx-bar-chart-alt'></i>
                    <span class="links_name">Performance Analytics</span>
                </a>
            </li>
            <li>                    
                <a href="adminpage5.php" data-tooltip="Admin info">
                    <i class='bx bxs-user'></i>
                    <span class="links_name">Admin Info</span>
                </a>
            </li>
            <li>                    
                <a href="adminpage8.php" data-tooltip="Pending Approval">
                <i class='bx bx-time-five'></i>
                    <span class="links_name">Pending Approval</span>
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
    <h1>Student Management</h1>
    <div class="search-container">
        <form method="POST" action="adminpage2.php" class="search-bar">
            <input type="text" name="search" placeholder="Search by student name..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit"><i class='bx bx-search'></i></button>
        </form>
    </div>
    <!-- Dropdown Filters -->
<form method="POST" action="">
    <!-- Add hidden field for student_ID -->
    <input type="hidden" name="student_ID" value="<?php echo htmlspecialchars($student_ID); ?>">

   <!-- Dropdown Filters -->
<div class="dropdown-container">
    <!-- Subject Dropdown -->
    <div class="dropdown">
        <select name="subject" id="subject">
            <option value="">Subject</option>
            <?php while ($row = $result_subjects->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['class_enrollment']); ?>" <?php if ($subject == $row['class_enrollment']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['class_enrollment']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <!-- Age Dropdown -->
    <div class="dropdown">
        <select name="age" id="age">
            <option value="">Age</option>
            <?php while ($row = $result_age->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['age']); ?>" <?php if ($age == $row['age']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['age']); ?>
                </option>
            <?php } ?>
        </select>
    </div>


    <!-- Days Dropdown -->
    <div class="dropdown">
        <select name="days" id="days">
            <option value="">Days</option>
            <?php while ($row = $result_days->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['days']); ?>" <?php if ($days == $row['days']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['days']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
<!-- Time Dropdown -->
<div class="dropdown">
    <select name="time" id="time">
        <option value="">Time</option>
        <?php while ($row = $result_time->fetch_assoc()) { ?>
            <option value="<?php echo htmlspecialchars($row['time']); ?>" <?php echo ($time == $row['time']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['time']); ?>
            </option>
        <?php } ?>
    </select>
</div>
    <button type="submit" style="width: 40px;"><i class='bx bx-filter' style="font-size: 30px;"></i></button>
    <div class="button-container">
    <a href="targetedpage.php">
    <i class='bx bx-user-plus' style="font-size: 25px;"></i>
    </a>
    </div>
</div>
<div class="container">
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No.</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Age</th>
                <th>Days</th>
                <th>Time</th>
                <th>Edit / Delete</th>
            </tr>
        </thead>
        <tbody>
                <?php if ($result_students->num_rows > 0) { ?>
                    <?php $number = 1; ?>
                    <?php while ($row = $result_students->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $number++; ?></td>
                            <td><a href="adminpage2.php?student_ID=<?php echo $row['student_ID']; ?>" style="color: black; text-decoration: none;"><?php echo htmlspecialchars($row['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['class_enrollment']); ?></td>
                            <td><?php echo htmlspecialchars($row['age']); ?></td>
                            <td><?php echo htmlspecialchars($row['days']); ?></td>
                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                            <td>
                                <div class="button-wrapper">
                                    <div class="button-container">
                                    <a href="adminpage6.php?student_ID=<?php echo $row['student_ID']; ?>">
                                    <i class='bx bx-pencil' style="font-size: 25px;"></i>
                                    </a>
                                    </div>
                                    <div class="button-second-container">
                                        <a href="targetpage2.php">
                                            <i class='bx bx-trash' style="font-size: 25px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7">No student records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
    </table>
</div>

</body>
</html>