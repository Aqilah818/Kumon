<?php
// Include the database connection file
include 'db.php';

// Initialize variables for error/success messages
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get parents_ID from POST
    $parents_ID = isset($_POST['parents_ID']) ? $_POST['parents_ID'] : null;


    // Get other form data
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $school = $_POST['school'];
    $class_enrollment = $_POST['class_enrollment'];
    $date_of_birth = $_POST['date_of_birth'];
    $language = $_POST['language'];
    $days = $_POST['days'];
    $time = $_POST['time'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match!";
    } else {
        // Allow parents_ID to be 0 (valid case)
        if ($parents_ID !== null && $parents_ID !== '') {
            // Insert the student data into the database
            $sql = "INSERT INTO student (name, age, gender, address, school, class_enrollment, date_of_birth, language, days, time, password, parents_ID) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sissssssssss", $name, $age, $gender, $address, $school, $class_enrollment, $date_of_birth, $language, $days, $time, $password, $parents_ID);

                if ($stmt->execute()) {
                    $successMessage = "Student registered successfully!";
                } else {
                    $errorMessage = "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $errorMessage = "SQL error: " . $conn->error;
            }
        } else {
            $errorMessage = "Parent ID is missing in the form submission.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="default/style5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <script>


// Function to check password strength and show warning if necessary
function checkPasswordStrength() {
    const password = document.getElementById("password").value;
    const strengthIndicator = document.getElementById("password-strength");
    const passwordWarning = document.getElementById("password-warning");
    const regex = {
        weak: /^(?=.*[a-zA-Z0-9@$!%*?&]).{1,}$/,
        medium: /^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&]).{1,}$/,
        strong: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{1,}$/,
        validPassword: /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/
    };

    // Show the warning if the password does not meet the required criteria
    if (!regex.validPassword.test(password)) {
        passwordWarning.style.display = "block"; // Show the warning
    } else {
        passwordWarning.style.display = "none"; // Hide the warning if criteria are met
    }

    // Check password strength and update strength indicator
    if (regex.strong.test(password)) {
        strengthIndicator.style.width = "100%";
        strengthIndicator.style.backgroundColor = "green";
        strengthIndicator.innerText = "Strong";
    } else if (regex.medium.test(password)) {
        strengthIndicator.style.width = "70%";
        strengthIndicator.style.backgroundColor = "orange";
        strengthIndicator.innerText = "Medium";
    } else if (regex.weak.test(password)) {
        strengthIndicator.style.width = "40%";
        strengthIndicator.style.backgroundColor = "red";
        strengthIndicator.innerText = "Weak";
    } 
}

    // Function to validate password match
    function validatePasswords() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;
        const confirmMessage = document.getElementById("confirm-password-message");

        if (password !== confirmPassword) {
            confirmMessage.style.color = "red";
            confirmMessage.innerText = "Passwords do not match!";
        } else {
            confirmMessage.style.color = "green";
            confirmMessage.innerText = "Passwords match.";
        }
    }
// Function to toggle password visibility
function togglePasswordVisibility(toggleButtonId, inputFieldId) {
    const toggleButton = document.getElementById(toggleButtonId);
    const inputField = document.getElementById(inputFieldId);

    // Toggle between 'password' and 'text' input type
    if (inputField.type === "password") {
        inputField.type = "text"; // Show the password
        toggleButton.classList.replace("bx-hide", "bx-show"); // Change the icon to 'show'
    } else {
        inputField.type = "password"; // Hide the password
        toggleButton.classList.replace("bx-show", "bx-hide"); // Change the icon to 'hide'
    }
}

// Add event listeners to toggle password visibility
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("toggle-password1").addEventListener("click", function() {
        togglePasswordVisibility("toggle-password1", "password");
    });

    document.getElementById("toggle-password2").addEventListener("click", function() {
        togglePasswordVisibility("toggle-password2", "confirm_password");
    });
});

function validateFormAndSubmit(event) {
    const form = event.target.form;
    let isValid = true;

    // Clear existing error highlights
    document.querySelectorAll(".error").forEach((field) => {
        field.classList.remove("error");
    });

    // Validate each required field
    form.querySelectorAll("[required]").forEach((input) => {
        if (!input.value.trim()) {
            input.classList.add("error");
            isValid = false;
        }
    });

    // Get the password value and strength indicator
    const password = document.getElementById("password").value;
    const strengthIndicator = document.getElementById("password-strength");

    // Define the regex patterns
    const regex = {
        weak: /^(?=.*[a-zA-Z0-9@$!%*?&]).{1,}$/, // Weak password regex
        medium: /^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&]).{1,}$/, // Medium password regex
        strong: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/ // Strong password regex
    };

    // If password is not strong, prevent form submission
    if (regex.strong.test(password) === false) {
        alert("Password is too weak or medium. Please choose a strong password with at least 8 characters, one capital letter, one number, and one special character.");
        isValid = false;
    }

    if (!isValid) {
        event.preventDefault(); // Prevent form submission if validation fails
        alert("Please fill out all required fields!");
    } else {
        // Submit the form and redirect to the next page after successful insertion
        form.submit();
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
        <nav class="navbar">
            <a href="home.html">Home</a>
            <a href="about.html">About Us</a>
            <a href="programmes.html">Our Programmes</a>
            <a href="register_parent.php">Register</a>
            <div class="profile-dropdown">
                <a href="#profile" class="profile-icon">
                    <i class="fas fa-user"></i> <!-- User icon -->
                </a>
                <div class="dropdown-menu">
                    <a href="studentlogin.php">Log In</a> <!-- Log out link -->
                </div>
            </div>
        </nav>
    </header>

    <h2 style="margin-top: 600px;">Student Registration Form</h2>
    <p>*Kindly fill out the Registration Form COMPLETELY</p>

    <!-- Success and Error Messages -->
    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Student Registration Form -->
    <form action="" method="POST" onsubmit="validateFormAndSubmit(event)">
        <label for="name">Full Name: <span>*</span></label>
        <input type="text" id="name" name="name" placeholder="CAPITAL LETTERS" style="text-transform: uppercase;" required><br>

        <label for="age">Age: <span>*</span></label>
        <input type="number" id="age" name="age" min="0" required><br>


        <label for="gender">Gender: <span>*</span></label>
        <select id="gender" name="gender" required>
            <option value="" disabled selected>Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br>

        <label for="address">Address: <span>*</span></label>
        <textarea id="address" name="address" rows="4" required></textarea><br>

        <label for="school">School: <span>*</span></label>
        <textarea id="school" name="school" rows="4" required></textarea><br>

        <label for="class_enrollment">Class Enrollment: <span>*</span></label>
        <select id="class_enrollment" name="class_enrollment" required>
            <option value="" disabled selected>Select</option>
            <option value="Mathematics">Mathematics</option>
            <option value="English">English</option>
            <option value="Mathematics & English">Mathematics & English</option>
        </select><br>

        <label for="date_of_birth">Date of Birth: <span>*</span></label>
        <input type="date" id="date_of_birth" name="date_of_birth" required><br>

        <label for="language">Language: <span>*</span></label>
        <select id="language" name="language" required>
            <option value="" disabled selected>Select</option>
            <option value="English">English</option>
            <option value="Malay">Malay</option>
        </select><br>

        <label for="days">Days: <span>*</span></label>
        <select id="days" name="days" required>
            <option value="" disabled selected>Select</option>
            <option value="Monday & Tuesday">Monday & Tuesday</option>
            <option value="Monday & Thursday">Monday & Thursday</option>
            <option value="Monday & Friday">Monday & Friday</option>
            <option value="Tuesday & Thursday">Tuesday & Thursday</option>
            <option value="Tuesday & Friday">Tuesday & Friday</option>
            <option value="Thursday & Friday">Thursday & Friday</option>
        </select><br>

        <label for="time">Time: <span>*</span></label>
        <select id="time" name="time" required>
            <option value="" disabled selected>Select</option>
            <option value="2:00 - 3:00 p.m.">2:00 - 3:00 p.m.</option>
            <option value="3:00 - 4:00 p.m.">3:00 - 4:00 p.m.</option>
            <option value="4:00 - 5:00 p.m.">4:00 - 5:00 p.m.</option>
            <option value="5:00 - 6:00 p.m.">5:00 - 6:00 p.m.</option>
            <option value="6:00 - 7:00 p.m.">6:00 - 7:00 p.m.</option>
            <option value="8:00 - 9:00 p.m.">8:00 - 9:00 p.m.</option>
            <option value="9:00 - 10:00 p.m.">9:00 - 10:00 p.m.</option>
        </select><br>

<!-- Password Label and Input Field -->
<label for="password">Password: <span>*</span></label>
<div class="password-container">
    <input type="password" id="password" name="password" required oninput="checkPasswordStrength()">
    <i class="bx bx-hide" id="toggle-password1"></i>
</div>

<!-- Password Strength Indicator -->
<div id="password-strength" style="height: 20px; width: 0%; margin-bottom: 10px; color: white;"></div>

<!-- Password Requirements Warning -->
<p id="password-warning" style="color: red; display: none;">Password must be at least 8 characters long, <br>
contain a capital letter, a number,<br> and a special character.</p>

<!-- Confirm Password Label and Input Field -->
<label for="confirm_password">Confirm Password: <span>*</span></label>
<div class="password-container">
    <input type="password" id="confirm_password" name="confirm_password" required oninput="validatePasswords()">
    <i class="bx bx-hide" id="toggle-password2"></i>
</div>


<!-- Password match feedback -->
<p id="confirm-password-message"></p>

<!-- Hidden field to pass parents_ID -->
<input type="hidden" name="parents_ID" value="<?php echo isset($_GET['parents_ID']) && $_GET['parents_ID'] !== '' ? $_GET['parents_ID'] : '0'; ?>">

<button type="submit" onclick="validateFormAndSubmit(event)">Register</button>

    </form>
</body>
</html>
