<?php
include 'dbconnect.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    header("Location: admin_login.html");
    exit;
}

// Get statistics
try {
    $participants = db_get_participants();
    $participant_count = count($participants);
    $mock_mode = db_is_mock();
} catch (Exception $e) {
    $participant_count = 0;
    $mock_mode = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Menu — Cit-E Cycling</title>
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
                <a href="admin_menu.php?action=logout" class="btn btn-ghost btn-sm" style="border-color: rgba(235, 87, 87, 0.4); color: #c53030;">Sign Out</a>
            </div>
        </div>
    </nav>

    <main class="container" style="max-width: 900px; margin-top: 40px;">
        <div style="margin-bottom: 30px;">
            <p class="eyebrow eyebrow-on-light" style="text-align: left; margin-bottom: 8px;">Control Panel</p>
            <h1 style="font-family: var(--font-display); font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 600; margin-bottom: 8px;">Cit-E Cycling Dashboard</h1>
            <p style="color: var(--asphalt); font-size: 0.95rem;">Manage participants, search clubs, and check registration interest statistics.</p>
        </div>

        <!-- Connection / Stats Banner -->
        <div class="glass" style="border-radius: var(--radius-sm); padding: 18px 22px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 30px;">
            <div>
                <span class="badge-status <?php echo $mock_mode ? 'badge-warning' : 'badge-success'; ?>" style="margin-bottom: 6px;">
                    <?php echo $mock_mode ? 'Demo Mode (Session DB)' : 'Connected (Live DB)'; ?>
                </span>
                <p style="font-size: 0.84rem; color: var(--asphalt); margin-top: 4px;">
                    <?php echo $mock_mode ? 'Using high-fidelity in-memory storage for demonstration.' : 'Connected to the MySQL database server.'; ?>
                </p>
            </div>
            
            <div style="display: flex; gap: 24px; align-items: center;">
                <div style="text-align: right;">
                    <p style="font-family: var(--font-mono); font-size: 1.4rem; font-weight: 600; line-height: 1; color: var(--route-green-deep);"><?php echo $participant_count; ?></p>
                    <p style="font-size: 0.72rem; font-family: var(--font-mono); text-transform: uppercase; color: var(--asphalt); margin-top: 2px;">Riders Registered</p>
                </div>
            </div>
        </div>

        <!-- Menu Action Grid -->
        <div class="admin-menu-grid">
            <a href="search_form.php" class="admin-menu-card glass">
                <h3>
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: none; stroke: var(--route-green); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    Search Directory
                </h3>
                <p>Search for specific clubs, team rollouts, or individual participants across the network.</p>
                <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 600; color: var(--route-green); margin-top: auto; display: flex; align-items: center; gap: 4px;">
                    Go to Search &rarr;
                </span>
            </a>

            <a href="view_participants_edit_delete.php" class="admin-menu-card glass">
                <h3>
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: none; stroke: var(--route-green); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Manage Participants
                </h3>
                <p>View all registered riders, update their power output and distance scores, or remove records from the system.</p>
                <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 600; color: var(--route-green); margin-top: auto; display: flex; align-items: center; gap: 4px;">
                    View Directory &rarr;
                </span>
            </a>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer" style="margin-top: 120px;">
        <div class="container footer-bottom">
            <p>&copy; 2026 Cit-E Cycling. All rights reserved.</p>
            <p class="footer-tag">Built for riders, by riders.</p>
        </div>
    </footer>
</body>

</html>