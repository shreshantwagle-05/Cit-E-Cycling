<?php
include 'dbconnect.php';

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $error_msg = 'Username and password are required.';
    } else {
        try {
            $authenticated = db_authenticate($username, $password);
            if ($authenticated) {
                $_SESSION['admin_logged_in'] = true;
                header("Location: admin_menu.php");
                exit;
            } else {
                $error_msg = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error_msg = 'Error connecting to database: ' . $e->getMessage();
        }
    }
} else {
    $error_msg = 'You reached this page by mistake. Please login through the login page.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Authentication Status — Cit-E Cycling</title>
    <link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <!-- Ambient background blobs -->
    <div class="bg-decor" aria-hidden="true">
        <span class="blob blob-green"></span>
        <span class="blob blob-amber"></span>
        <span class="blob blob-ink"></span>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar" aria-label="Primary">
        <div class="container nav-inner">
            <a class="logo" href="index.html" aria-label="Cit-E Cycling home">
                <img src="./Resource/Logo.png" alt="Cit-E Cycling logo">
                <span class="logo-text">Cit-E Cycling</span>
            </a>
            <div class="nav-actions">
                <a href="index.html" class="btn btn-ghost btn-sm">Back to Home</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="portal-card glass">
            
            <div class="portal-header">
                <div style="width: 64px; height: 64px; background: rgba(235, 87, 87, 0.1); border: 2px solid #eb5757; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: none; stroke: #eb5757; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1>Authentication Failed</h1>
                <p>We could not sign you in to the admin area.</p>
            </div>

            <div class="alert-box alert-danger">
                <p><?php echo htmlspecialchars($error_msg); ?></p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="admin_login.html" class="btn btn-primary">Try Again</a>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer" style="margin-top: 100px;">
        <div class="container footer-bottom">
            <p>&copy; 2026 Cit-E Cycling. All rights reserved.</p>
            <p class="footer-tag">Built for riders, by riders.</p>
        </div>
    </footer>
</body>

</html>