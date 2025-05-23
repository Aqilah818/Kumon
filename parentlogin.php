<?php
session_start();

if (isset($_SESSION['parent_id'])) {
    header('Location: studentpage1.php');
    exit();
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $parent_email = $_POST['parent_email'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    // reCAPTCHA verification
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_secret = '6LfGabUqAAAAAFqqLsq7qE6DoR5WYnn7SMT-LizU';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

    $response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_keys = json_decode($response, true);

    if (!$response_keys['success']) {
        $error = "Please complete the reCAPTCHA verification.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM parents WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $parent_email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $parent = $result->fetch_assoc();

            $_SESSION['parent_id'] = $parent['parents_ID'];
            $_SESSION['parent_name'] = $parent['name'];
            $_SESSION['parent_email'] = $parent['email'];

            if ($remember_me) {
                setcookie('parent_email', $parent_email, time() + (86400 * 30), "/", "", true, true);
                setcookie('remember_me', true, time() + (86400 * 30), "/", "", true, true);
            } else {
                setcookie('parent_email', '', time() - 3600, "/");
                setcookie('remember_me', '', time() - 3600, "/");
            }

            header('Location: studentpage1.php');
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="default/loginstyle.css">
    <script src="toggle.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Login</h1>
        <form action="parentlogin.php" method="POST" class="login-form">
    <label for="parent_email">Email:</label>
    <input type="email" name="parent_email" required placeholder="Enter your email"
           value="<?php echo isset($_COOKIE['parent_email']) ? htmlspecialchars($_COOKIE['parent_email'], ENT_QUOTES, 'UTF-8') : ''; ?>">

    <label for="password">Password:</label>
    <div class="password-container">
        <input type="password" name="password" id="password" required placeholder="Enter your password">
        <i class='bx bx-hide' id="toggle-password"></i>
    </div>

    <div class="g-recaptcha" data-sitekey="6LfGabUqAAAAAG6sGhf7p21oAxQhRv85lRzoSOhd"></div>

    <div class="extra-options">
        <label>
            <input type="checkbox" name="remember_me" <?php echo isset($_COOKIE['remember_me']) ? 'checked' : ''; ?>> Remember Me
        </label>
        <a href="forgotpassword.php" class="forgot-password">Forgot Password?</a>
    </div>

    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <button type="submit">Login</button>
</form>

    </div>
</body>
</html>

