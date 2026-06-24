<?php
include 'dbconnect.php';

$success = false;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    if (empty($firstname) || empty($surname) || empty($email)) {
        $error_msg = 'All form fields are required.';
    } else {
        try {
            $registered = db_register_interest($firstname, $surname, $email, $terms);
            if ($registered) {
                $success = true;
            } else {
                $error_msg = 'Could not register your interest at this time. Please try again.';
            }
        } catch (Exception $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $error_msg = 'Invalid request method. Please use the registration form.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Status — Cit-E Cycling</title>
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
            
            <?php if ($success): ?>
                <div class="portal-header">
                    <div style="width: 64px; height: 64px; background: var(--route-green-tint); border: 2px solid var(--route-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <svg viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: none; stroke: var(--route-green); stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h1>Registration Successful!</h1>
                    <p>Thank you for your interest, <?php echo htmlspecialchars($firstname); ?>.</p>
                </div>

                <div class="alert-box alert-success">
                    <p>We've successfully added <strong><?php echo htmlspecialchars($email); ?></strong> to our launch newsletter list. We'll send updates as we rollout Cit-E Cycling to more locations!</p>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <p style="color: var(--asphalt); margin-bottom: 20px; font-size: 0.95rem;">Want to see which cities are next?</p>
                    <a href="index.html#cities" class="btn btn-primary">Check City Rollout</a>
                </div>
            <?php else: ?>
                <div class="portal-header">
                    <div style="width: 64px; height: 64px; background: rgba(235, 87, 87, 0.1); border: 2px solid #eb5757; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <svg viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: none; stroke: #eb5757; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                    <h1>Registration Failed</h1>
                    <p>We encountered an issue processing your request.</p>
                </div>

                <div class="alert-box alert-danger">
                    <p><?php echo htmlspecialchars($error_msg); ?></p>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="register_form.html" class="btn btn-primary">&larr; Back to Form</a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer" style="margin-top: 80px;">
        <div class="container footer-bottom">
            <p>&copy; 2026 Cit-E Cycling. All rights reserved.</p>
            <p class="footer-tag">Built for riders, by riders.</p>
        </div>
    </footer>
</body>

</html>