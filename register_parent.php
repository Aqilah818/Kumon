<?php
// Include the database connection file
include 'db.php';

// Initialize variables for error/success messages
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $relationship = $_POST['relationship'];
    $ic_no = $_POST['ic_no'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $contact_no = $_POST['contact_no'];

    // Insert the data into the database
    $sql = "INSERT INTO parents (name, relationship_with_student, ic_no, email, address, contact_no) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $relationship, $ic_no, $email, $address, $contact_no);

    if ($stmt->execute()) {
        $successMessage = "Parent registered successfully.";

        // Fetch the last inserted parent's ID
        $parents_ID = $conn->insert_id;

        // Redirect to the student registration page with the parents_ID in the URL
        header("Location: register_student.php?parents_ID=" . $parents_ID);
        exit();
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration Form</title>
    <link rel="stylesheet" href="default/style5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <script>
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
    <h2>Parent Registration Form</h2>
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
        <input type="text" id="name" name="name" placeholder="CAPITAL LETTERS" style="text-transform: uppercase;" required><br>

        <label for="relationship">Relationship with Student: <span>*</span></label>
        <select id="relationship" name="relationship" required>
            <option value="" disabled selected>Select</option>
            <option value="Father">Father</option>
            <option value="Mother">Mother</option>
            <option value="Guardian">Guardian</option>
        </select><br>

        <label for="ic_no">IC Number: <span>*</span></label>
        <input type="text" id="ic_no" name="ic_no" required><br>

        <label for="email">Email: <span>*</span></label>
        <input type="email" id="email" name="email" required><br>

        <label for="address">Address: <span>*</span></label>
        <textarea id="address" name="address" rows="4" required></textarea><br>

        <label for="contact_no">Contact Number: <span>*</span></label>
        <input type="text" id="contact_no" name="contact_no" required><br>

        <!-- Next Button to go to Student Registration Form -->
        <button type="button" onclick="validateFormAndSubmit(event)">Next</button>
    </form>
</body>
</html>