<?php
session_start();
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
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $class_enrollment = $_POST['class_enrollment'];
    $date_of_birth = $_POST['date_of_birth'];
    $days = $_POST['days'];
    $time = $_POST['time'];

    $sql = "INSERT INTO student (name, age, gender, class_enrollment, date_of_birth, days, time, parent_ic_no, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissssss", $name, $age, $gender, $class_enrollment, $date_of_birth, $days, $time, $parent_ic_no);

    if ($stmt->execute()) {
        $successMessage = "Student registered successfully!";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration Form</title>
    <link rel="stylesheet" href="default/style5.css">
    <style>
        .error { border-color: red; }
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

<h2>Student Registration Form</h2>
<p>*Kindly fill out the Registration Form COMPLETELY</p>

<?php if ($successMessage): ?>
    <p style="color: green;"><?php echo $successMessage; ?></p>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <p style="color: red;"><?php echo $errorMessage; ?></p>
<?php endif; ?>

<form method="POST" onsubmit="validateFormAndSubmit(event)">
    <label for="name">Full Name: *</label>
    <input type="text" id="name" name="name" pattern="[A-Za-z\s]+" required><br>

    <label for="age">Age: *</label>
    <input type="number" id="age" name="age" min="0" required><br>

    <label for="gender">Gender: *</label>
    <select id="gender" name="gender" required>
        <option value="">Select</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select><br>

    <label for="class_enrollment">Class Enrollment: *</label>
    <select id="class_enrollment" name="class_enrollment" required>
        <option value="">Select</option>
        <option>Mathematics</option>
        <option>English</option>
        <option>Mathematics & English</option>
    </select><br>

    <label for="date_of_birth">Date of Birth: *</label>
    <input type="date" id="date_of_birth" name="date_of_birth" required><br>

    <label for="days">Days: *</label>
    <select id="days" name="days" required>
        <option value="">Select</option>
        <option>Monday & Tuesday</option>
        <option>Monday & Thursday</option>
        <option>Monday & Friday</option>
        <option>Tuesday & Thursday</option>
        <option>Tuesday & Friday</option>
        <option>Thursday & Friday</option>
    </select><br>

    <label for="time">Time: *</label>
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

    <button type="submit">Register</button>
</form>

</body>
</html>
