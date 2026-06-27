<?php
include 'dbconnect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    session_unset();
    session_destroy();
    header("Location: admin_login.html");
    exit;
}

try {
    $participants     = db_get_participants();
    $participant_count = count($participants);
    $mock_mode        = db_is_mock();
} catch (Exception $e) {
    $participant_count = 0;
    $mock_mode        = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Cit-E Cycling</title>
    <link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="index.css">
</head>
<body class="admin-body">

    <!-- ── SIDEBAR ───────────────────────────────── -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-logo">
            <img src="./Resource/Logo.png" alt="Cit-E Cycling logo">
            <span class="admin-sidebar-logo-text">Cit-E <em>Cycling</em></span>
        </div>

        <div class="admin-sidebar-section">
            <p class="admin-sidebar-label">Navigation</p>

            <a href="admin_menu.php" class="admin-nav-item active">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#9ba8a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>

            <a href="search_form.php" class="admin-nav-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#9ba8a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Search Directory
            </a>

            <a href="view_participants_edit_delete.php" class="admin-nav-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#9ba8a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Manage Participants
            </a>

            <a href="edit_participant.php" class="admin-nav-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#9ba8a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Participant
            </a>
        </div>

        <div class="admin-sidebar-bottom">
            <div class="admin-sidebar-user">
                <div class="admin-avatar">AD</div>
                <div>
                    <div class="admin-user-name">Administrator</div>
                    <div class="admin-user-role">Super Admin</div>
                </div>
            </div>
            <a href="admin_menu.php?action=logout" class="btn-signout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- ── MAIN ──────────────────────────────────── -->
    <div class="admin-main">

        <header class="admin-topbar">
            <span class="admin-topbar-title">Admin Dashboard</span>
            <div>
                <?php if ($mock_mode): ?>
                    <span class="db-badge db-demo"><span class="dot"></span>Demo Mode</span>
                <?php else: ?>
                    <span class="db-badge db-live"><span class="dot"></span>Live DB</span>
                <?php endif; ?>
            </div>
        </header>

        <div class="admin-page-body">

            <div class="admin-page-header">
                <p class="admin-eyebrow">Control Panel</p>
                <h1>Cit-E Cycling Dashboard</h1>
                <p>Manage riders, search the directory, and monitor registration stats.</p>
            </div>

            <!-- Stats -->
            <div class="admin-stat-row">
                <div class="admin-stat-card">
                    <p class="admin-stat-label">Riders Registered</p>
                    <p class="admin-stat-val volt"><?php echo $participant_count; ?></p>
                    <p class="admin-stat-sub">Total in system</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-label">Database</p>
                    <p class="admin-stat-val" style="font-size:1.1rem; padding-top:6px;">
                        <?php echo $mock_mode ? 'Demo / Session' : 'MySQL Live'; ?>
                    </p>
                    <p class="admin-stat-sub"><?php echo $mock_mode ? 'In-memory storage' : 'Connected & healthy'; ?></p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-label">Admin Session</p>
                    <p class="admin-stat-val" style="font-size:1.1rem; padding-top:6px;">Active</p>
                    <p class="admin-stat-sub">Authenticated</p>
                </div>
            </div>

            <!-- Quick search -->
            <div class="admin-search-card">
                <h2>Quick Search</h2>
                <p>Find a participant or club instantly — results open in the search directory.</p>
                <div class="admin-search-row">
                    <input
                        class="admin-search-input"
                        type="text"
                        id="quickSearch"
                        placeholder="Name, email, or club…"
                        onkeydown="if(event.key==='Enter') doSearch()"
                    >
                    <button class="btn-volt" onclick="doSearch()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Search
                    </button>
                </div>
            </div>

            <!-- Action cards -->
            <div class="admin-action-grid">
                <a href="search_form.php" class="admin-action-card">
                    <div class="admin-action-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c8f53a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h3>Search Directory</h3>
                    <p>Search for specific clubs, team rollouts, or individual participants across the network.</p>
                    <span class="admin-action-link">Go to Search →</span>
                </a>

                <a href="view_participants_edit_delete.php" class="admin-action-card">
                    <div class="admin-action-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c8f53a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3>Manage Participants</h3>
                    <p>View all registered riders, update power output and distance scores, or remove records.</p>
                    <span class="admin-action-link">View Directory →</span>
                </a>

                <a href="edit_participant.php" class="admin-action-card">
                    <div class="admin-action-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c8f53a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </div>
                    <h3>Edit Participant</h3>
                    <p>Directly update a rider's profile, performance scores, or contact information.</p>
                    <span class="admin-action-link">Open Editor →</span>
                </a>

                <a href="search_form.php?filter=clubs" class="admin-action-card">
                    <div class="admin-action-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c8f53a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <h3>Browse Clubs</h3>
                    <p>View registered cycling clubs, their team rollouts, and affiliated rider counts.</p>
                    <span class="admin-action-link">Browse Clubs →</span>
                </a>
            </div>

        </div><!-- /admin-page-body -->
    </div><!-- /admin-main -->

    <footer class="admin-footer">
        <p>&copy; 2026 Cit-E Cycling. All rights reserved.</p>
        <span class="volt-mark">Built for riders, by riders.</span>
    </footer>

    <script>
        function doSearch() {
            const q = document.getElementById('quickSearch').value.trim();
            if (q) window.location.href = 'search_form.php?q=' + encodeURIComponent(q);
        }
    </script>

</body>
</html>