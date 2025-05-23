<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumon Tuition</title>
    <link rel="stylesheet" href="default/style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <style>
        .success-container {
            text-align: center;
            margin-top: 150px;
        }

        .success-container img {
            width: 300px;
            margin-top: -150px;
        }

        .success-message {
            color: green;
            font-size: 40px;
            margin-top: -50px;
            margin-bottom: 20px;
        }
        .success-description {
        font-size: 18px;
        color: #333;
        margin-bottom: 20px;
        }
        .login-button {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
        }

        .login-button:hover {
            background-color: #0056b3;
        }
    </style>
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
                    <a href="studentlogin.php">Log In</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Success Message Section -->
    <div class="success-container">
        <img src="correct.jpg" alt="Success Icon">
        <p class="success-message">Success!</p>
        <p class="success-description">Congratulations, your account has been successfully created. You can now login to your account.</p>

        <a href="parentlogin.php" class="login-button">Log In</a>
    </div>
</body>
</html>
