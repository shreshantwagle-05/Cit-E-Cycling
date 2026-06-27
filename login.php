<?php
include 'dbconnect.php';

// dbconnect.php should already call session_start() — if it doesn't, uncomment:
// session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.html");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// TODO: replace with a real lookup against your users table via dbconnect.php
$VALID_USER = 'admin';
$VALID_PASS = 'password123';

if ($username === '' || $password === '') {
    header("Location: admin_login.html?error=empty");
    exit;
}

if ($username === $VALID_USER && $password === $VALID_PASS) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin_menu.php");
    exit;
} else {
    header("Location: admin_login.html?error=invalid");
    exit;
}