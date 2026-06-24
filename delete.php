<?php
include 'dbconnect.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}

$error_msg = '';

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $deleted = db_delete_participant($id);
        if ($deleted) {
            header("Location: view_participants_edit_delete.php?status=deleted");
            exit;
        } else {
            $error_msg = 'Could not delete the rider. Verify if the rider exists.';
        }
    } else {
        $error_msg = 'No rider specified for deletion.';
    }
} catch (Exception $e) {
    $error_msg = 'An error occurred: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Delete Participant — Cit-E Cycling</title>
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
                <a href="admin_menu.php" class="btn btn-ghost btn-sm">Dashboard</a>
                <a href="admin_menu.php?action=logout" class="btn btn-ghost btn-sm" style="border-color: rgba(235, 87, 87, 0.4); color: #c53030;">Sign Out</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="portal-card glass">
            <a href="view_participants_edit_delete.php" class="back-link">&larr; Back to Directory</a>
            
            <div class="portal-header">
                <div style="width: 64px; height: 64px; background: rgba(235, 87, 87, 0.1); border: 2px solid #eb5757; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: none; stroke: #eb5757; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </div>
                <h1>Deletion Failed</h1>
                <p>We could not delete the participant from the directory.</p>
            </div>

            <div class="alert-box alert-danger">
                <p><?php echo htmlspecialchars($error_msg); ?></p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="view_participants_edit_delete.php" class="btn btn-primary">Back to Directory</a>
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