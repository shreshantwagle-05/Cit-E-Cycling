<?php
include 'dbconnect.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Search Directory — Cit-E Cycling</title>
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

    <main class="container" style="max-width: 900px;">
        <div class="portal-card portal-card-large glass">
            <a href="admin_menu.php" class="back-link">&larr; Back to Dashboard</a>
            
            <div class="portal-header">
                <h1>Directory Search</h1>
                <p>Search for specific riders or cycling clubs registered in the Cit-E Cycling database.</p>
            </div>

            <!-- Two forms columns -->
            <div style="display: grid; gap: 32px; grid-template-columns: 1fr; margin-top: 20px;">
                <style>
                    .search-container-grid {
                        display: grid;
                        gap: 32px;
                        grid-template-columns: 1fr;
                    }
                    @media (min-width: 768px) {
                        .search-container-grid {
                            grid-template-columns: 1fr 1fr;
                        }
                    }
                </style>
                <div class="search-container-grid">
                    <!-- Participant search -->
                    <div class="glass" style="border-radius: var(--radius-sm); padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                                <div style="width: 36px; height: 36px; background: var(--route-green-tint); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: none; stroke: var(--route-green); stroke-width: 2;">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <h3 style="font-size: 1.05rem; font-family: var(--font-display); font-weight: 600;">Search Rider</h3>
                            </div>
                            <p style="font-size: 0.88rem; color: var(--asphalt); margin-bottom: 20px; min-height: 40px;">Look up an individual participant by their first name or surname.</p>
                        </div>
                        
                        <form action="search_result.php" method="POST">
                            <input type="hidden" name="participant" value="1">
                            <div class="form-group">
                                <label for="firstname">Rider's Name</label>
                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="e.g. Alex" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">Search Riders</button>
                        </form>
                    </div>

                    <!-- Club search -->
                    <div class="glass" style="border-radius: var(--radius-sm); padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                                <div style="width: 36px; height: 36px; background: rgba(242, 169, 59, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: none; stroke: #95650f; stroke-width: 2;">
                                        <path d="M17 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        <path d="M21 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M21 3.13a4 4 0 0 1 0 7.75"></path>
                                        <path d="M9 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M8 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h3 style="font-size: 1.05rem; font-family: var(--font-display); font-weight: 600;">Search Club</h3>
                            </div>
                            <p style="font-size: 0.88rem; color: var(--asphalt); margin-bottom: 20px; min-height: 40px;">Look up a cycling club by its name and see its current riders.</p>
                        </div>
                        
                        <form action="search_result.php" method="POST">
                            <div class="form-group">
                                <label for="club">Club Name</label>
                                <input type="text" id="club" name="club" class="form-control" placeholder="e.g. Wheelers" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px; background-color: var(--signal-amber); color: var(--ink); border: none; box-shadow: 0 8px 18px rgba(242, 169, 59, 0.25);">Search Clubs</button>
                        </form>
                    </div>
                </div>
            </div>
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