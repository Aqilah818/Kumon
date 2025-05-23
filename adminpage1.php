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
// Fetch the number of student accounts registered
$stmt_students = $conn->prepare("SELECT COUNT(*) AS total_students FROM student");
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$total_students = $result_students->fetch_assoc()['total_students'];

// Fetch the number of teacher accounts registered
$stmt_teachers = $conn->prepare("SELECT COUNT(*) AS total_teachers FROM teacher");
$stmt_teachers->execute();
$result_teachers = $stmt_teachers->get_result();
$total_teachers = $result_teachers->fetch_assoc()['total_teachers'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="admin/style1.css">
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
    <div class="dashboard-first-container">
    <div class="dashboard-box">
        <h3><?php echo $total_students; ?></h3>
        <p>Students</p>
    </div>
    <div class="dashboard-box">
        <h3><?php echo $total_teachers; ?></h3>
        <p>Teachers</p>
    </div>
    </div>
    <div class="dashboard-second-container">
    <div class="dashboard-box">
        <h3>5</h3>
        <p>Active Users</p>
    </div>
    <div class="dashboard-box">
        <h3>3</h3>
        <p>Pending Approval</p>
    </div>
    </div>
</body>
</html>