<?php
session_start();

// Clear all admin session data
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to login page
header('Location: index.php');
exit;
?>