<?php
include 'dbconnect.php';

// Authentication check
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.html");
//     exit;
// }

$participants = [];
$error_msg = '';
$info_msg = '';

// Handle redirect messages
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

<?php

function db_get_participants($conn) {
    $stmt = $conn->prepare("SELECT * FROM participant");
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

// Call function
$result = db_get_participants($conn);

// Display result
echo count($result);

if (count($result)) {
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Sur Name</th>
            <th>Email</th>
            <th>Power Output</th>
            <th>Distance</th>
          </tr>";

    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['firstname'] . "</td>";
        echo "<td>" . $row['surname'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['power_output'] . "</td>";
        echo "<td>" . $row['distance'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No participants found.";
}

?>


try {
    $participants = db_get_participants();
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
    <title>Participant Management — Cit-E Cycling</title>
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
            <a href="admin_menu.php" class="back-link">&larr; Back to Dashboard</a>
            
            <div class="portal-header" style="text-align: left; margin-bottom: 24px;">
                <h1>Participant Directory</h1>
                <p>View, update scores (power and distance), or delete participant records in the system.</p>
            </div>

            <!-- Banners -->
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

            <?php if ($info_msg !== ''): ?>
                <div class="alert-box alert-success">
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <p><?php echo htmlspecialchars($info_msg); ?></p>
                </div>
            <?php endif; ?>

            <!-- Table of participants -->
            <?php if (empty($participants)): ?>
                <div class="alert-box alert-info">
                    <p>There are no riders registered in the directory yet. Interest submissions will appear here after approval.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="portal-table">
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
                            <?php foreach ($participants as $rider): ?>
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
                                            <a href="delete.php?id=<?php echo $rider['id']; ?>" class="btn-action btn-action-delete" onclick="return confirm('Are you sure you want to delete rider: <?php echo htmlspecialchars($rider['firstname'] . ' ' . $rider['surname']); ?>?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
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