<?php
require_once __DIR__ . '/classes/Auth.php';

// Logout the user
Auth::logout();

// Redirect to login page with a success message
header("Location: login.php?logged_out=1");
exit();