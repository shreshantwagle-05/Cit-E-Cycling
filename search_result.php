<?php
include 'dbconnect.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit;
}

$search_type = '';
$search_term = '';
$results = [];
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['participant']) && $_POST['participant'] == '1') {
        $search_type = 'participant';
        $search_term = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        if ($search_term !== '') {
            try {
                $results = db_search_participants($search_term);
            } catch (Exception $e) {
                $error_msg = 'Search error: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'Please enter a name to search.';
        }
    } else {
        $search_type = 'club';
        $search_term = isset($_POST['club']) ? trim($_POST['club']) : '';
        if ($search_term !== '') {
            try {
                $results = db_search_clubs($search_term);
            } catch (Exception $e) {
                $error_msg = 'Search error: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'Please enter a club name to search.';
        }
    }
} else {
    $error_msg = 'Invalid request. Please use the search directory form.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Search Results — Cit-E Cycling</title>
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

    <main class="container" style="max-width: 960px;">
        <div class="portal-card portal-card-large glass">
            <a href="search_form.php" class="back-link">&larr; Back to Search</a>
            
            <div class="portal-header" style="text-align: left; margin-bottom: 24px;">
                <h1>Search Results</h1>
                <p>Showing matching database entries for: <strong style="color: var(--route-green-deep);">"<?php echo htmlspecialchars($search_term); ?>"</strong></p>
            </div>

            <?php if ($error_msg !== ''): ?>
                <div class="alert-box alert-danger">
                    <p><?php echo htmlspecialchars($error_msg); ?></p>
                </div>
            <?php elseif (empty($results)): ?>
                <div class="alert-box alert-info">
                    <p>No matching <?php echo $search_type === 'participant' ? 'riders' : 'clubs'; ?> found in the directory. Try adjusting your search term.</p>
                </div>
            <?php else: ?>
                <!-- Results Table -->
                <div class="table-responsive">
                    <table class="portal-table">
                        <?php if ($search_type === 'participant'): ?>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rider Name</th>
                                    <th>Email</th>
                                    <th>Power (W)</th>
                                    <th>Distance (KM)</th>
                                    <th>Club</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $rider): ?>
                                    <tr>
                                        <td style="font-family: var(--font-mono); font-weight: 500; font-size: 0.84rem;"><?php echo htmlspecialchars($rider['id']); ?></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($rider['firstname'] . ' ' . $rider['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($rider['email']); ?></td>
                                        <td style="font-family: var(--font-mono);"><?php echo $rider['power_output'] !== null ? htmlspecialchars($rider['power_output']) . ' W' : '<span style="color:var(--asphalt); font-style:italic;">--</span>'; ?></td>
                                        <td style="font-family: var(--font-mono);"><?php echo $rider['distance'] !== null ? htmlspecialchars($rider['distance']) . ' km' : '<span style="color:var(--asphalt); font-style:italic;">--</span>'; ?></td>
                                        <td>
                                            <span class="badge-status <?php echo $rider['club_id'] ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo htmlspecialchars($rider['club_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_participant.php?id=<?php echo $rider['id']; ?>" class="btn-action btn-action-edit">Edit</a>
                                                <a href="delete.php?id=<?php echo $rider['id']; ?>" class="btn-action btn-action-delete" onclick="return confirm('Are you sure you want to delete this rider?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Club Name</th>
                                    <th>Base Location</th>
                                    <th>Riders Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $club): ?>
                                    <tr>
                                        <td style="font-family: var(--font-mono); font-weight: 500; font-size: 0.84rem;"><?php echo htmlspecialchars($club['id']); ?></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($club['name']); ?></td>
                                        <td><?php echo htmlspecialchars($club['location']); ?></td>
                                        <td>
                                            <?php if (empty($club['riders'])): ?>
                                                <span style="color: var(--asphalt); font-style: italic;">No riders registered.</span>
                                            <?php else: ?>
                                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                    <?php foreach ($club['riders'] as $rider_name): ?>
                                                        <span class="badge-status" style="background: rgba(16, 27, 44, 0.05); border: 1px solid var(--hairline); text-transform: none; font-family: var(--font-body); font-size: 0.78rem; font-weight: 500;"><?php echo htmlspecialchars($rider_name); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            <?php endif; ?>
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