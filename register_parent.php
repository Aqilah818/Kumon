<?php
// Include the database connection file
include 'db.php';

// Initialize variables for error/success messages
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = ucwords(strtolower(trim($_POST['name'])));
    $relationship = $_POST['relationship'];
    $ic_no = $_POST['ic_no'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Fix: First validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (strlen($password) < 12) {
        $errorMessage = "Password must be at least 12 characters long.";
    } elseif ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match!";
    } else {

        // Then check for duplicates
        $checkSql = "SELECT * FROM parents WHERE ic_no = ? OR email = ? OR contact_no = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("sss", $ic_no, $email, $contact_no);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Email, IC Number, or Contact Number already registered.";
        } else {
            $sql = "INSERT INTO parents (name, relationship_with_student, ic_no, email, contact_no, password) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $relationship, $ic_no, $email, $contact_no, $password);

            if ($stmt->execute()) {
                $successMessage = "Parent registered successfully.";
                $parents_ID = $conn->insert_id;
                header("Location: parents_success.php?parents_ID=" . $parents_ID);
                exit();
            } else {
                $errorMessage = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration Form</title>
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
                weak: /^.{1,11}$/,
                medium: /^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&]).{12,}$/,
                strong: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{12,}$/
            };

            if (!regex.strong.test(password)) {
                passwordWarning.style.display = "block";
            } else {
                passwordWarning.style.display = "none";
            }

            if (regex.strong.test(password)) {
                strengthIndicator.style.width = "100%";
                strengthIndicator.style.backgroundColor = "green";
                strengthIndicator.innerText = "Strong";
            } else if (regex.medium.test(password)) {
                strengthIndicator.style.width = "70%";
                strengthIndicator.style.backgroundColor = "orange";
                strengthIndicator.innerText = "Medium";
            } else {
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
                alert("Password is too weak or medium. Please choose a strong password with at least 12 characters, one capital letter, one number, and one special character.");
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission if validation fails
            } else {
                // Allow form to submit naturally
            }

        }

        // Real-time email validation
        function validateEmail(event) {
            const email = event.target.value;
            const emailErrorMessage = document.getElementById("email-error");

            // Check if email format is valid
            const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
            if (!emailRegex.test(email)) {
                emailErrorMessage.style.display = "block";
                emailErrorMessage.innerText = "Invalid email format.";
            } else {
                emailErrorMessage.style.display = "none";
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
                    <i class="fas fa-user"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="parentlogin.php">Log In</a>
                </div>
            </div>
        </nav>
    </header>

    <h2 style="margin-top: 200px;">Parent Registration Form</h2>
    <p>*Kindly fill out the Registration Form COMPLETELY</p>

    <!-- Success and Error Messages -->
    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="" method="POST" onsubmit="validateFormAndSubmit(event)">
        <label for="name">Full Name: <span>*</span></label>
        <input type="text" id="name" name="name" style="text-transform: capitalize;"
            pattern="[A-Za-z\s]+" title="Full Name must contain letters and spaces only" required><br>

        <label for="relationship">Relationship with Student: <span>*</span></label>
        <select id="relationship" name="relationship" required>
            <option value="" disabled selected>Select</option>
            <option value="Father">Father</option>
            <option value="Mother">Mother</option>
            <option value="Guardian">Guardian</option>
        </select><br>

        <label for="ic_no">IC Number: <span>*</span></label>
        <input type="text" id="ic_no" name="ic_no" maxlength="12"
            pattern="\d{12}" title="IC Number must be exactly 12 digits" required><br>

        <label for="email">Email: <span>*</span></label>
        <input type="email" id="email" name="email"
            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
            title="Please enter a valid email address (e.g., example@email.com)" required
            oninput="validateEmail(event)">
        <!-- Email Error Message -->
        <p id="email-error" style="color: red; display: none;"></p><br>

        <label for="contact_no">Contact Number: <span>*</span></label>
        <input type="text" id="contact_no" name="contact_no" maxlength="11"
            pattern="\d{10,11}" title="Contact Number must be 10 or 11 digits" required><br>

        <!-- Password Label and Input Field -->
        <label for="password">Password: <span>*</span></label>
        <div class="password-container">
            <input type="password" id="password" name="password" minlength="12" required oninput="checkPasswordStrength()">
            <i class="bx bx-hide" id="toggle-password1"></i>
        </div>

        <!-- Password Strength Indicator -->
        <div id="password-strength" style="height: 20px; width: 0%; margin-bottom: 10px; color: white;"></div>

        <!-- Password Requirements Warning -->
        <p id="password-warning" style="color: red; display: none;">Password must be at least 12 characters long, <br>
            contain a capital letter, a number,<br> and a special character.</p>

        <!-- Confirm Password Label and Input Field -->
        <label for="confirm_password">Confirm Password: <span>*</span></label>
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" required oninput="validatePasswords()">
            <i class="bx bx-hide" id="toggle-password2"></i>
        </div>

        <!-- Password match feedback -->
        <p id="confirm-password-message"></p>

        <!-- Next Button to go to Student Registration Form -->
        <button type="submit" onclick="validateFormAndSubmit(event)">Register</button>
    </form>

    <script>
        // Input restriction: Allow only letters and spaces for name
        document.getElementById('name').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });

        // Input restriction: Allow only numbers for IC number
        document.getElementById('ic_no').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Input restriction: Allow only numbers for Contact number
        document.getElementById('contact_no').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>

</body>

</html>