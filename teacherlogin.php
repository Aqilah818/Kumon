<?php
session_start(); // Start the session

// If the user is already logged in, redirect to the teacher info page
if (isset($_SESSION['teacher_id'])) {
    header('Location: teacherpage1.php');
    exit();
}

// Include database configuration
include('db.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted credentials
    $teacher_email = $_POST['teacher_email'];
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
        // Prepare and execute the query to validate credentials
        $stmt = $conn->prepare("SELECT * FROM teacher WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $teacher_email, $password); // 's' for strings
        $stmt->execute();
        $result = $stmt->get_result();

        // If valid teacher, store session and redirect
        if ($result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
            $_SESSION['teacher_id'] = $teacher['teacher_ID']; // Store teacher ID in session
            $_SESSION['teacher_name'] = $teacher['name'];   // Optional: Store teacher name

            // If "Remember Me" is checked, set cookies
            if ($remember_me) {
                setcookie('teacher_email', $teacher_email, time() + (86400 * 30), "/"); // 30 days
                setcookie('password', $password, time() + (86400 * 30), "/"); // For demonstration purposes
            } else {
                // Clear cookies if "Remember Me" is not checked
                setcookie('teacher_email', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
            }

            // Redirect to the teacher page
            alert('success login');
            redirect('teacherpage1.php');
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="default/loginstyle.css">
    <script src="toggle.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="login-page">
    <div class="login-container">
        <h1>Teacher Login</h1>
        <form action="teacherlogin.php" method="POST" class="login-form">
            <label for="teacher_email">Email:</label>
            <input type="email" name="teacher_email" required placeholder="Enter your email"
                value="<?php echo isset($_COOKIE['teacher_email']) ? $_COOKIE['teacher_email'] : ''; ?>"><br>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required placeholder="Enter your password"
                    value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>">
                <i class='bx bx-hide' id="toggle-password"></i>
            </div>
            <div class="g-recaptcha" data-sitekey="6LfGabUqAAAAAG6sGhf7p21oAxQhRv85lRzoSOhd"></div>
            <div class="extra-options">
                <label>
                    <input type="checkbox" name="remember_me"
                        <?php echo isset($_COOKIE['teacher_email']) ? 'checked' : ''; ?>> Remember Me
                </label>
                <a href="forgotpassword.php" class="forgot-password">Forgot Password?</a>
            </div>

            <?php if (isset($error)) {
                echo "<p style='color:red;'>$error</p>";
            } ?>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>