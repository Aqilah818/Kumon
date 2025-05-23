<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['parent_id'])) {
    header('Location: parentlogin.php');
    exit();
}

include('db.php');

$parent_id = $_SESSION['parent_id'];
$stmt = $conn->prepare("SELECT * FROM parents WHERE parents_ID = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$parent = $result->fetch_assoc();
$parent_ic_no = $parent['ic_no'];

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = ucwords(strtolower(trim($_POST['name'])));
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $days = $_POST['days'];
    $time = $_POST['time'];
    $mykid = trim($_POST['mykid']);

    $selectedSubjects = isset($_POST['class_enrollment']) ? $_POST['class_enrollment'] : [];

    // Map selected subjects to ENUM-friendly string
    sort($selectedSubjects); // Ensure consistent order
    if ($selectedSubjects == ['English']) {
        $class_enrollment = 'English';
    } elseif ($selectedSubjects == ['Mathematics']) {
        $class_enrollment = 'Mathematics';
    } elseif ($selectedSubjects == ['English', 'Mathematics']) {
        $class_enrollment = 'Mathematics & English';
    } else {
        $errorMessage = "Invalid subject selection.";
    }

    // Validate MYKID
    if (empty($mykid)) {
        $errorMessage = "MYKID is required.";
    } elseif (!preg_match('/^\d{12}$/', $mykid)) {
        $errorMessage = "MYKID must be exactly 12 digits.";
    } else {
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE mykid = ?");
        $checkStmt->bind_param("s", $mykid);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            $errorMessage = "MYKID number already exists.";
        }
    }

    if (empty($selectedSubjects)) {
        $errorMessage = "Please select at least one subject.";
    }

    if (empty($errorMessage)) {
        $sql = "INSERT INTO student (
                name, age, gender, class_enrollment, date_of_birth,
                days, time, parent_ic_no, mykid, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sisssssss",
            $name,
            $age,
            $gender,
            $class_enrollment,
            $date_of_birth,
            $days,
            $time,
            $parent_ic_no,
            $mykid
        );

        if ($stmt->execute()) {
            $student_ID = $stmt->insert_id; // Get the new student ID

            // Insert into classwork (junction table) for each selected subject
            $insertSubjectStmt = $conn->prepare("INSERT INTO classwork (student_ID, subject_ID) VALUES (?, ?)");

            foreach ($selectedSubjects as $subject) {
                if ($subject == 'Mathematics') {
                    $subject_ID = 1;
                } elseif ($subject == 'English') {
                    $subject_ID = 2;
                } else {
                    continue; // Skip if unknown subject
                }
                $insertSubjectStmt->bind_param("ii", $student_ID, $subject_ID);
                $insertSubjectStmt->execute();
            }

            $insertSubjectStmt->close();
            $successMessage = "Student registered successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="student/style1.css">
    <link rel="stylesheet" href="default/style5.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .error {
            border-color: red;
        }
    </style>
    <script>
        function validateFormAndSubmit(event) {
            const form = event.target.form || event.target;
            let isValid = true;

            form.querySelectorAll(".error").forEach(e => e.classList.remove("error"));
            form.querySelectorAll("[required]").forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add("error");
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert("Please fill out all required fields!");
            }
        }
    </script>
</head>

<body>
    <!-- Blue Header -->
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
            <li class="non-navigable"><?php echo htmlspecialchars($parent['name']); ?></li>
            <li><a href="studentpage1.php"><i class='bx bxs-user'></i><span class="links_name">Student Info</span></a></li>
            <li><a href="studentpage2.php"><i class='bx bx-book-open'></i><span class="links_name">Classwork</span></a></li>
            <li><a href="studentpage3.php"><i class='bx bx-bar-chart-alt'></i><span class="links_name">Academic Performance</span></a></li>
            <li><a href="studentpage4.php"><i class='bx bx-user-plus'></i><span class="links_name">Add Student</span></a></li>
            <li><a href="#reset-password"><i class='bx bxs-lock'></i><span class="links_name">Reset Password</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="form-container">
        <h2 style="margin-top: 180px;">Student Registration Form</h2>
        <p>*Kindly fill out the Registration Form COMPLETELY</p>

        <?php if ($successMessage): ?>
            <p style="color: green;"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p style="color: red;"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="validateFormAndSubmit(event)">
            <label for="name">Full Name: <span>*</span></label>
            <input type="text" id="name" name="name" style="text-transform: capitalize;"
                pattern="[A-Za-z\s]+" title="Full Name must contain letters and spaces only" required><br>

            <label for="age">Age: <span>*</span></label>
            <input type="number" id="age" name="age" min="0" required><br>

            <label for="mykid">MYKID Number: <span>*</span></label>
            <input type="text" id="mykid" name="mykid" maxlength="12" minlength="12"
                pattern="\d{12}" title="MYKID must be exactly 12 digits" required>

            <br>


            <label for="gender">Gender: <span>*</span></label>
            <select id="gender" name="gender" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
            </select><br>
            <div class="class-enrollment-form">
                <label>Class Enrollment: <span class="required">*</span></label><br><br>

                <div class="subject-wrapper">
                    <label>
                        <span>Mathematics</span>
                        <input type="checkbox" name="class_enrollment[]" value="Mathematics">
                    </label>
                </div>

                <div class="subject-wrapper">
                    <label>
                        <span>English</span>
                        <input type="checkbox" name="class_enrollment[]" value="English">
                    </label>
                </div>
            </div>

            <br>

            <label for="date_of_birth">Date of Birth: <span>*</span></label>
            <input type="date" id="date_of_birth" name="date_of_birth" required><br>

            <label for="days">Days: <span>*</span></label>
            <select id="days" name="days" required>
                <option value="">Select</option>
                <option>Monday & Tuesday</option>
                <option>Monday & Thursday</option>
                <option>Monday & Friday</option>
                <option>Tuesday & Thursday</option>
                <option>Tuesday & Friday</option>
                <option>Thursday & Friday</option>
            </select><br>

            <label for="time">Time: <span>*</span></label>
            <select id="time" name="time" required>
                <option value="">Select</option>
                <option>2:00 - 3:00 p.m.</option>
                <option>3:00 - 4:00 p.m.</option>
                <option>4:00 - 5:00 p.m.</option>
                <option>5:00 - 6:00 p.m.</option>
                <option>6:00 - 7:00 p.m.</option>
                <option>8:00 - 9:00 p.m.</option>
                <option>9:00 - 10:00 p.m.</option>
            </select><br>

            <button type="submit" style="margin-top: 5px;">Register</button>
        </form>
    </main>
    <script>
        // Input restriction: Allow only letters and spaces for name
        document.getElementById('name').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });



        // Input restriction: Allow only numbers for MYKID, limit to 12 characters
        document.getElementById('mykid').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });
    </script>
</body>

</html>