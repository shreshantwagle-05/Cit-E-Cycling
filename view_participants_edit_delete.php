<?php
include 'dbconnect.php';

$conn = new PDO(
    "mysql:host=$servername;port=$port;dbname=$database",
    $username,
    $password
);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$participants = [];
$error_msg = '';
$info_msg = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        $info_msg = 'Rider details successfully updated.';
    } elseif ($_GET['status'] === 'deleted') {
        $info_msg = 'Rider record successfully removed.';
    } elseif ($_GET['status'] === 'notfound') {
        $error_msg = 'Rider could not be found.';
    } elseif ($_GET['status'] === 'error') {
        $error_msg = 'An error occurred processing the action.';
    }
}

if (!function_exists('db_get_participants')) {
    function db_get_participants($conn) {
        $stmt = $conn->prepare("SELECT * FROM participant");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Single call, wrapped in try/catch
try {
    $participants = db_get_participants($conn);
} catch (Exception $e) {
    $error_msg = 'Failed to retrieve participants: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Participant Directory — Cit-E Cycling</title>
    <link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ─── TOKENS ─────────────────────────────────────── */
        :root {
            --ink:        #0d0f0e;
            --ink-mid:    #2a2e2c;
            --asphalt:    #6b7270;
            --fog:        #c8cecc;
            --surface:    #f2f4f3;
            --white:      #ffffff;
            --volt:       #c8f53a;   /* signature: electric lime */
            --volt-dark:  #9abf1e;
            --danger:     #e05252;
            --danger-bg:  #fdf1f1;
            --success:    #2ea06e;
            --success-bg: #edf8f3;
            --info-bg:    #eef3ff;
            --info-txt:   #3a5bd4;

            --font-display: 'Syne', sans-serif;
            --font-body:    'DM Sans', sans-serif;
            --font-mono:    'DM Mono', monospace;

            --radius-sm:  6px;
            --radius-md:  12px;
            --radius-lg:  20px;
            --shadow-card: 0 2px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
            --shadow-hover: 0 8px 32px rgba(0,0,0,.11);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 15px;
            line-height: 1.6;
        }

        /* ─── AMBIENT ─────────────────────────────────────── */
        .bg-decor { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .28;
        }
        .blob-volt  { width: 520px; height: 520px; background: var(--volt);    top: -140px; right: -120px; }
        .blob-ink   { width: 380px; height: 380px; background: #1a2020;        bottom: -80px; left: -80px; }
        .blob-fog   { width: 300px; height: 300px; background: #a8c4be;        top: 55%; left: 40%; }

        /* ─── NAVBAR ──────────────────────────────────────── */
        .navbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(242, 244, 243, .82);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(0,0,0,.07);
        }
        .nav-inner {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 64px;
            max-width: 1100px; margin: 0 auto;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo img { width: 32px; height: 32px; object-fit: contain; }
        .logo-text {
            font-family: var(--font-display);
            font-weight: 800; font-size: 1.1rem;
            color: var(--ink); letter-spacing: -.02em;
        }
        .logo-text span { color: var(--volt-dark); }
        .nav-actions { display: flex; gap: 8px; align-items: center; }

        .btn { display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); font-family: var(--font-body); font-weight: 600; cursor: pointer; text-decoration: none; transition: background .18s, color .18s, box-shadow .18s; border: 1.5px solid transparent; white-space: nowrap; }
        .btn-sm { padding: 6px 14px; font-size: .82rem; }
        .btn-ghost { background: transparent; border-color: var(--fog); color: var(--asphalt); }
        .btn-ghost:hover { background: var(--white); color: var(--ink); border-color: var(--fog); }
        .btn-danger-ghost { background: transparent; border-color: rgba(224,82,82,.35); color: var(--danger); }
        .btn-danger-ghost:hover { background: var(--danger-bg); }

        /* ─── LAYOUT ──────────────────────────────────────── */
        main { flex: 1; position: relative; z-index: 1; padding: 40px 16px 80px; }
        .container { max-width: 1000px; margin: 0 auto; width: 100%; }

        /* ─── CARD ────────────────────────────────────────── */
        .card {
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,.85);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 40px 40px 32px;
        }

        /* ─── BACK LINK ───────────────────────────────────── */
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .82rem; font-weight: 600; color: var(--asphalt);
            text-decoration: none; letter-spacing: .02em;
            text-transform: uppercase; margin-bottom: 28px;
            transition: color .15s;
        }
        .back-link:hover { color: var(--ink); }
        .back-link::before { content: '←'; font-size: 1rem; }

        /* ─── PAGE HEADER ─────────────────────────────────── */
        .page-header { margin-bottom: 28px; }
        .eyebrow {
            font-family: var(--font-mono);
            font-size: .72rem; font-weight: 500;
            letter-spacing: .12em; text-transform: uppercase;
            color: var(--volt-dark); margin-bottom: 8px;
        }
        .page-header h1 {
            font-family: var(--font-display);
            font-weight: 800; font-size: 2rem;
            letter-spacing: -.03em; line-height: 1.1;
            color: var(--ink); margin-bottom: 8px;
        }
        .page-header p { color: var(--asphalt); font-size: .9rem; max-width: 520px; }

        /* ─── STAT PILLS ──────────────────────────────────── */
        .stat-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 28px; }
        .stat-pill {
            background: var(--surface);
            border: 1px solid var(--fog);
            border-radius: 999px;
            padding: 6px 16px;
            font-size: .8rem; font-weight: 600;
            color: var(--ink-mid);
            display: flex; align-items: center; gap: 6px;
        }
        .stat-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--volt-dark); }

        /* ─── ALERTS ──────────────────────────────────────── */
        .alert-box {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 18px; border-radius: var(--radius-md);
            font-size: .875rem; margin-bottom: 24px;
            border: 1px solid transparent;
        }
        .alert-box svg { flex-shrink: 0; margin-top: 1px; }
        .alert-danger  { background: var(--danger-bg);  color: #b52727; border-color: rgba(224,82,82,.25); }
        .alert-success { background: var(--success-bg); color: #1c7a52; border-color: rgba(46,160,110,.25); }
        .alert-info    { background: var(--info-bg);    color: var(--info-txt); border-color: rgba(58,91,212,.2); }

        /* ─── TABLE ───────────────────────────────────────── */
        .table-wrap { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid rgba(0,0,0,.07); }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead { background: var(--ink); }
        thead th {
            font-family: var(--font-display);
            font-size: .7rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: rgba(255,255,255,.6);
            padding: 13px 18px; text-align: left;
        }
        thead th:first-child { border-radius: var(--radius-md) 0 0 0; }
        thead th:last-child  { border-radius: 0 var(--radius-md) 0 0; }

        tbody tr {
            border-bottom: 1px solid rgba(0,0,0,.055);
            transition: background .15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(200,245,58,.07); }

        tbody td { padding: 14px 18px; vertical-align: middle; color: var(--ink-mid); }

        .td-id {
            font-family: var(--font-mono); font-size: .78rem;
            color: var(--asphalt); font-weight: 500;
        }
        .td-name { font-weight: 600; color: var(--ink); }
        .td-email { color: var(--asphalt); font-size: .84rem; }
        .td-metric {
            font-family: var(--font-mono); font-size: .84rem;
            font-weight: 500;
        }
        .td-metric .unit { font-size: .72rem; color: var(--asphalt); margin-left: 2px; }
        .td-empty { color: var(--fog); font-style: italic; font-size: .82rem; }

        /* ─── ACTION BUTTONS ──────────────────────────────── */
        .action-cell { display: flex; gap: 8px; align-items: center; }
        .btn-edit, .btn-del {
            display: inline-flex; align-items: center;
            padding: 5px 13px; border-radius: var(--radius-sm);
            font-size: .78rem; font-weight: 700;
            letter-spacing: .02em; text-decoration: none;
            transition: background .15s, color .15s, box-shadow .15s;
            border: 1.5px solid transparent;
        }
        .btn-edit {
            background: rgba(200,245,58,.18);
            border-color: rgba(154,191,30,.4);
            color: #5a7800;
        }
        .btn-edit:hover { background: var(--volt); color: var(--ink); border-color: var(--volt); box-shadow: 0 2px 10px rgba(200,245,58,.4); }
        .btn-del {
            background: var(--danger-bg);
            border-color: rgba(224,82,82,.3);
            color: var(--danger);
        }
        .btn-del:hover { background: var(--danger); color: var(--white); border-color: var(--danger); }

        /* ─── EMPTY STATE ─────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 60px 20px;
        }
        .empty-icon {
            font-size: 2.8rem; margin-bottom: 14px;
            filter: grayscale(.4);
        }
        .empty-state h3 {
            font-family: var(--font-display);
            font-size: 1.1rem; font-weight: 700;
            color: var(--ink); margin-bottom: 8px;
        }
        .empty-state p { color: var(--asphalt); font-size: .88rem; }

        /* ─── FOOTER ──────────────────────────────────────── */
        footer {
            position: relative; z-index: 1;
            background: var(--ink); color: rgba(255,255,255,.45);
            padding: 24px 32px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: .8rem;
        }
        footer .volt-tag { color: var(--volt); font-weight: 600; font-family: var(--font-mono); font-size: .72rem; }

        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 640px) {
            .nav-inner { padding: 0 16px; }
            .card { padding: 24px 18px 20px; }
            .page-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="bg-decor" aria-hidden="true">
        <span class="blob blob-volt"></span>
        <span class="blob blob-ink"></span>
        <span class="blob blob-fog"></span>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar" aria-label="Primary">
        <div class="nav-inner">
            <a class="logo" href="index.html" aria-label="Cit-E Cycling home">
                <img src="./Resource/Logo.png" alt="Cit-E Cycling logo">
                <span class="logo-text">Cit-E <span>Cycling</span></span>
            </a>
            <div class="nav-actions">
                <a href="admin_menu.php" class="btn btn-sm btn-ghost">Dashboard</a>
                <a href="admin_menu.php?action=logout" class="btn btn-sm btn-danger-ghost">Sign Out</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="card">

                <a href="admin_menu.php" class="back-link">Back to Dashboard</a>

                <div class="page-header">
                    <p class="eyebrow">Admin · Riders</p>
                    <h1>Participant Directory</h1>
                    <p>View, update scores (power and distance), or remove participant records.</p>
                </div>

                <!-- Stat pills -->
                <?php if (!empty($participants)): ?>
                <div class="stat-row">
                    <span class="stat-pill">
                        <span class="dot"></span>
                        <?php echo count($participants); ?> Rider<?php echo count($participants) !== 1 ? 's' : ''; ?> Registered
                    </span>
                    <?php
                        $with_data = array_filter($participants, fn($r) => $r['power_output'] !== null || $r['distance'] !== null);
                    ?>
                    <span class="stat-pill">
                        <?php echo count($with_data); ?> with Performance Data
                    </span>
                </div>
                <?php endif; ?>

                <!-- Banners -->
                <?php if ($error_msg !== ''): ?>
                    <div class="alert-box alert-danger" role="alert">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p><?php echo htmlspecialchars($error_msg); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($info_msg !== ''): ?>
                    <div class="alert-box alert-success" role="status">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <p><?php echo htmlspecialchars($info_msg); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Table or empty state -->
                <?php if (empty($participants)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🚴</div>
                        <h3>No riders yet</h3>
                        <p>Interest submissions will appear here once approved.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rider Name</th>
                                    <th>Email</th>
                                    <th>Power (W)</th>
                                    <th>Distance (km)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $rider): ?>
                                    <tr>
                                        <td class="td-id"><?php echo htmlspecialchars($rider['id']); ?></td>
                                        <td class="td-name"><?php echo htmlspecialchars($rider['firstname'] . ' ' . $rider['surname']); ?></td>
                                        <td class="td-email"><?php echo htmlspecialchars($rider['email']); ?></td>
                                        <td class="td-metric">
                                            <?php if ($rider['power_output'] !== null): ?>
                                                <?php echo htmlspecialchars($rider['power_output']); ?><span class="unit">W</span>
                                            <?php else: ?>
                                                <span class="td-empty">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td-metric">
                                            <?php if ($rider['distance'] !== null): ?>
                                                <?php echo htmlspecialchars($rider['distance']); ?><span class="unit">km</span>
                                            <?php else: ?>
                                                <span class="td-empty">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="edit_participant.php?id=<?php echo $rider['id']; ?>" class="btn-edit">Edit</a>
                                                <a href="delete.php?id=<?php echo $rider['id']; ?>"
                                                   class="btn-del"
                                                   onclick="return confirm('Delete rider: <?php echo htmlspecialchars(addslashes($rider['firstname'] . ' ' . $rider['surname'])); ?>?');">
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div><!-- /card -->
        </div><!-- /container -->
    </main>

    <footer>
        <p>&copy; 2026 Cit-E Cycling. All rights reserved.</p>
        <span class="volt-tag">Built for riders, by riders.</span>
    </footer>

</body>
</html>