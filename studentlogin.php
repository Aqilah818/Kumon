<?php
session_start(); // Start the session

// If the user is already logged in, redirect to the student info page
if (isset($_SESSION['student_id'])) {
    header('Location: studentpage1.php');
    exit();
}

// Include database configuration
include('db.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted credentials
    $student_name = $_POST['student_name'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']); // Check if "Remember Me" is checked

    // Validate reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response']; // reCAPTCHA response from the form
    $recaptcha_secret = '6LfGabUqAAAAAFqqLsq7qE6DoR5WYnn7SMT-LizU'; // Your reCAPTCHA secret key
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

    // Send a POST request to Google's reCAPTCHA API
    $response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_keys = json_decode($response, true);

    if (!$response_keys['success']) {
        // If reCAPTCHA validation fails
        $error = "Please complete the reCAPTCHA verification.";
    } else {
        // Proceed with login only if reCAPTCHA is valid
    $stmt = $conn->prepare("SELECT * FROM student WHERE name = ? AND password = ?");
    $stmt->bind_param("ss", $student_name, $password); // 's' for strings
    $stmt->execute();
    $result = $stmt->get_result();

    // If valid student, store session and redirect
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $_SESSION['student_id'] = $student['student_ID']; // Store student ID in session
        $_SESSION['student_name'] = $student['name'];     // Optional: Store student name

        // If "Remember Me" is checked, set cookies securely
        if ($remember_me) {
            setcookie('student_name', $student_name, time() + (86400 * 30), "/", "", true, true); // Secure and HTTPOnly
            setcookie('remember_me', true, time() + (86400 * 30), "/", "", true, true);
        } else {
            // Clear cookies if "Remember Me" is not checked
            setcookie('student_name', '', time() - 3600, "/");
            setcookie('remember_me', '', time() - 3600, "/");
        }

        // Redirect to the student info page
        header('Location: studentpage1.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
} 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="default/loginstyle.css">
    <script src="toggle.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Student Login</h1>
        <form action="studentlogin.php" method="POST" class="login-form">
            <label for="student_name">Fullname:</label>
            <input type="text" name="student_name" required placeholder="CAPITAL LETTER" 
                   value="<?php echo isset($_COOKIE['student_name']) ? htmlspecialchars($_COOKIE['student_name'], ENT_QUOTES, 'UTF-8') : ''; ?>"><br>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required placeholder="Enter your password">
                <i class='bx bx-hide' id="toggle-password"></i>
            </div>
            <div class="g-recaptcha" data-sitekey="6LfGabUqAAAAAG6sGhf7p21oAxQhRv85lRzoSOhd"></div>
            <div class="extra-options">
                <label>
                    <input type="checkbox" name="remember_me" 
                           <?php echo isset($_COOKIE['remember_me']) ? 'checked' : ''; ?>> Remember Me
                </label>
                <a href="forgotpassword.php" class="forgot-password">Forgot Password?</a>
            </div>

            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

