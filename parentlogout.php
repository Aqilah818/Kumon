<?php
// Start session
session_start();

// Destroy the session to log out
session_destroy();

// Redirect to login page
header('Location: parentlogin.php');
exit();
?>
