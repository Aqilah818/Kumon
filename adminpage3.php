<?php
session_start(); // Start the session

// Redirect to login if the user is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

// Include database configuration
include('db.php');
//hello
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


// Query for Subjects
$query_subjects = "SELECT DISTINCT subject_assigned FROM teacher";
$result_subjects = $conn->query($query_subjects);

// Handle the filter values for teachers
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';

// Building the query for teachers
$query = "SELECT teacher_ID, name, subject_assigned, age, position FROM teacher WHERE 1";

// Start building the query with search
$params = [];
$types = "";

if ($search_query) {
    $query .= " AND LOWER(name) LIKE ?";
    $params[] = $search_param;
    $types .= "s"; // 's' for string
}

// Add subject filter if applicable
if ($subject) {
    $query .= " AND subject_assigned = ?";
    $params[] = $subject;
    $types .= "s";
}

// Prepare and execute the query
$stmt_teachers = $conn->prepare($query);
if (!empty($params)) {
    $stmt_teachers->bind_param($types, ...$params);
}
$stmt_teachers->execute();
$result_teachers = $stmt_teachers->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management</title>
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
    <h1>Teacher Management</h1>
    <div class="search-container">
        <form method="POST" action="adminpage3.php" class="search-bar">
            <input type="text" name="search" placeholder="Search by teacher name..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit"><i class='bx bx-search'></i></button>
        </form>
    </div>
    <!-- Dropdown Filters -->
<form method="POST" action="">
    <!-- Add hidden field for teacher_ID -->
    <input type="hidden" name="teacher_ID" value="<?php echo htmlspecialchars($teacher_ID); ?>">

   <!-- Dropdown Filters -->
<div class="dropdown-container">
    <!-- Subject Dropdown -->
    <div class="dropdown">
        <select name="subject" id="subject">
            <option value="">Subject</option>
            <?php while ($row = $result_subjects->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['subject_assigned']); ?>" <?php if ($subject == $row['subject_assigned']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['subject_assigned']); ?>
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
                <th>Position</th>
                <th>Edit / Delete</th>
            </tr>
        </thead>
        <tbody>
                <?php if ($result_teachers->num_rows > 0) { ?>
                    <?php $number = 1; ?>
                    <?php while ($row = $result_teachers->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $number++; ?></td>
                            <td><a href="adminpage3.php?teacher_ID=<?php echo $row['teacher_ID']; ?>" style="color: black; text-decoration: none;"><?php echo htmlspecialchars($row['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['subject_assigned']); ?></td>
                            <td><?php echo htmlspecialchars($row['age']); ?></td>
                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                            <td>
                                <div class="button-wrapper">
                                    <div class="button-container">
                                        <a href="adminpage7.php?teacher_ID=<?php echo $row['teacher_ID']; ?>">
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
                        <td colspan="7">No teacher records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
    </table>
</div>

</body>
</html>