<?php
include 'dbconnect.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}

$rider = null;
$error_msg = '';
$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');

try {
    if ($is_post) {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $power_output = isset($_POST['power_output']) ? trim($_POST['power_output']) : '';
        $distance_travelled = isset($_POST['distance_travelled']) ? trim($_POST['distance_travelled']) : '';

        if ($id > 0) {
            $updated = db_update_participant($id, $power_output, $distance_travelled);
            if ($updated) {
                header("Location: view_participants_edit_delete.php?status=updated");
                exit;
            } else {
                $error_msg = 'Could not update rider stats. Please verify if the rider exists.';
            }
        } else {
            $error_msg = 'Invalid rider ID.';
        }
    } else {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            $rider = db_get_participant($id);
            if (!$rider) {
                header("Location: view_participants_edit_delete.php?status=notfound");
                exit;
            }
        } else {
            $error_msg = 'No rider specified for editing.';
        }
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
    <title>Edit Rider Stats — Cit-E Cycling</title>
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
                <h1>Edit Rider Statistics</h1>
                <p>Update performance stats for <?php echo $rider ? htmlspecialchars($rider['firstname'] . ' ' . $rider['surname']) : 'rider'; ?>.</p>
            </div>

            <!-- Error banner -->
            <?php if ($error_msg !== ''): ?>
                <div class="alert-box alert-danger">
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p><?php echo htmlspecialchars($error_msg); ?></p>
                </div>
            <?php endif; ?>

            <!-- Render the form if rider exists -->
            <?php 
            if (!$is_post && $rider) {
                include 'edit_participant_form.php';
            } elseif ($is_post && $error_msg !== '') {
                // Re-fetch rider details for form rendering in case POST failed
                $rider = db_get_participant($id);
                if ($rider) {
                    include 'edit_participant_form.php';
                }
            }
            ?>
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