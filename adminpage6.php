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
// Get the student_ID from the URL
if (isset($_GET['student_ID'])) {
    $student_ID = $_GET['student_ID'];

// Fetch student data from the database
$stmt = $conn->prepare("SELECT * FROM student WHERE student_ID = ?");
$stmt->bind_param("i", $student_ID); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

// Check if the student exists
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student not found.";
    exit();
}

// Fetch parents' information using parent_ID in the students table
if (isset($student['parents_ID'])) { // Check if the student has a parent_ID
    $parent_stmt = $conn->prepare("SELECT * FROM parents WHERE parents_ID = ?");
    $parent_stmt->bind_param("i", $student['parents_ID']);
    $parent_stmt->execute();
    $parent_result = $parent_stmt->get_result();

    // Store parents' information in an array
    $parents = [];
    if ($parent_result->num_rows > 0) {
        while ($parent = $parent_result->fetch_assoc()) {
            $parents[] = $parent;
        }
    }
} else {
    $parents = []; // No parent_ID associated with the student
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link rel="stylesheet" href="student/style1.css">
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
    <main class="content">
    <!-- Student Information Section -->
    <div class="student-info">
    <h2>Student Information</h2>
    <div class="student-info-container">
    <p><strong>Name:</strong> <span id="name" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['name']); ?></span></p>
    <p><strong>Age:</strong> <span id="age" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['age']); ?></span></p>
    <p><strong>Gender:</strong> <span id="gender" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['gender']); ?></span></p>
    <p><strong>Address:</strong> <span id="address" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['address']); ?></span></p>
    <p><strong>School:</strong> <span id="school" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['school']); ?></span></p>
    <p><strong>Class Enrollment:</strong> <span id="class_enrollment" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['class_enrollment']); ?></span></p>
    <p><strong>Date of Birth:</strong> <span id="date_of_birth" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['date_of_birth']); ?></span></p>
    <p><strong>Language:</strong> <span id="language" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['language']); ?></span></p>
    <p><strong>Days:</strong> <span id="days" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['days']); ?></span></p>
    <p><strong>Time:</strong> <span id="time" class="editable" contenteditable="true"><?php echo htmlspecialchars($student['time']); ?></span></p>

    </div>
    </div>

    <!-- Parents Information Section -->
    <div class="parents-info">
        <h2>Parents/Guardian Information</h2>
        <div class="parents-info-container">
        <?php if (!empty($parents)) { ?>
            <?php foreach ($parents as $parent) { ?>
                <p><strong>Name:</strong> <span id="name" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['name']); ?></span></p>
                <p><strong>Relationship:</strong> <span id="relationship" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['relationship_with_student']); ?></span></p>
                <p><strong>IC Number:</strong> <span id="ic_no" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['ic_no']); ?></span></p>
                <p><strong>Email:</strong> <span id="email" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['email']); ?></span></p>
                <p><strong>Address:</strong> <span id="address" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['address']); ?></span></p>
                <p><strong>Contact Number:</strong> <span id="contact_no" class="editable" contenteditable="true"><?php echo htmlspecialchars($parent['contact_no']); ?></span></p>

            <?php } ?>
        <?php } else { ?>
            <p>No parents' information available for this student.</p>
        <?php } ?>
        </div>
    </div>
    <div class="container">
            <button type="button" id="edit-button" onclick="enableEditing()">
            <i class='bx bx-pencil'></i>
            </button>
            <button id="save-button" style="display: none;" onclick="saveEdits()"><i class='bx bx-save'></i></button>
            </button>
            </div>
</main>
<script>
// Enable editing mode by replacing the text with input fields
function enableEditing() {
    const fields = document.querySelectorAll('.editable');
    fields.forEach(field => {
        const value = field.innerText.trim();
        field.innerHTML = `<input type="text" value="${value}" />`;
    });

    // Show the Save button and hide the Edit button
    document.getElementById('edit-button').style.display = 'none';
    document.getElementById('save-button').style.display = 'inline-block';
}

// Save the edits and send the updated data to the server
function saveEdits() {
    const fields = document.querySelectorAll('.editable');
    const data = {};

    // Collect all the updated data
    fields.forEach(field => {
        const input = field.querySelector('input');
        if (input) {
            const key = field.id; // Using the field's ID directly (e.g., "name", "age")
            const value = input.value.trim();
            data[key] = value; // Store the updated value in the data object
            field.innerText = value; // Replace input with the updated text
        }
    });

    // Log the data to be sent to the server for debugging
    console.log("Sending updated data:", data);

    // Send the updated data to the server via AJAX (fetch)
    fetch('update_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data) // Send the data as JSON
    })
    .then(response => response.json()) // Parse the JSON response
    .then(result => {
        // Check if the server returned success or error
        if (result.success) {
            alert('Changes saved successfully!');
        } else {
            alert('Failed to save changes: ' + result.error);
        }
    })
    .catch(error => {
        // Log and alert on any error
        console.error('Error:', error);
        alert('An error occurred while saving changes.');
    });

    // Hide the Save button and show the Edit button
    document.getElementById('edit-button').style.display = 'inline-block';
    document.getElementById('save-button').style.display = 'none';
}

</script>
</body>
</html>
