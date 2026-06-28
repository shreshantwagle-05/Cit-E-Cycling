<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }
include_once 'dbconnect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deleted = false;
$name = '';
$error = '';

if ($id) {
    try {
        $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
            $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        /* Get name before deleting */
        $st = $pdo->prepare("SELECT firstname, surname FROM participant WHERE id=:id");
        $st->execute([':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $name = htmlspecialchars($row['firstname'].' '.$row['surname']);
            $pdo->prepare("DELETE FROM participant WHERE id=:id")->execute([':id'=>$id]);
            $deleted = true;
        } else {
            $error = 'Participant not found.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
} else {
    $error = 'No participant ID provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Participant Deleted — Cit-E Cycling</title>
<link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
:root{--g:#1e7a46;--gd:#145c34;--gl:#2f9e5c;--a:#f2a93b;--ink:#0d1620;--ash:#677a70;--paper:#eef2ed;--surf:#fff;--line:#e1e8df;--font:'Plus Jakarta Sans',system-ui,sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font);color:var(--ink);background:var(--paper);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased}
.card{background:var(--surf);border:1px solid var(--line);border-radius:24px;padding:52px 44px;text-align:center;max-width:500px;width:100%;box-shadow:0 8px 32px rgba(13,22,32,.07);animation:popIn .5s cubic-bezier(.34,1.56,.64,1)}
@keyframes popIn{from{opacity:0;transform:scale(.94) translateY(16px)}to{opacity:1;transform:scale(1) translateY(0)}}
.icon-wrap{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 22px;animation:iconIn .5s cubic-bezier(.34,1.56,.64,1) .15s both}
@keyframes iconIn{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}
.icon-del{background:rgba(220,53,69,.1);border:2px solid rgba(220,53,69,.25)}
.icon-del i{font-size:2.2rem;color:#dc3545}
.icon-err{background:rgba(242,169,59,.12);border:2px solid rgba(242,169,59,.3)}
.icon-err i{font-size:2.2rem;color:var(--a)}
h2{font-size:1.5rem;font-weight:800;letter-spacing:-.02em;margin-bottom:10px}
p{color:var(--ash);font-size:.92rem;line-height:1.6;margin-bottom:26px;max-width:360px;margin-left:auto;margin-right:auto}
strong{color:var(--ink)}
.btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:10px;font-family:var(--font);font-size:.88rem;font-weight:700;cursor:pointer;border:none;transition:.18s;text-decoration:none}
.btn-primary{background:var(--g);color:#fff;box-shadow:0 4px 14px rgba(30,122,70,.26)}.btn-primary:hover{background:var(--gd);color:#fff}
.btn-outline{background:transparent;border:1.5px solid var(--line);color:var(--ash)}.btn-outline:hover{border-color:var(--g);color:var(--g)}
</style>
</head>
<body>
<div class="card">
  <?php if($deleted): ?>
    <div class="icon-wrap icon-del"><i class="bi bi-trash3-fill"></i></div>
    <h2>Participant Deleted</h2>
    <p><strong><?=$name?></strong> has been permanently removed from the system.</p>
  <?php else: ?>
    <div class="icon-wrap icon-err"><i class="bi bi-exclamation-triangle-fill"></i></div>
    <h2>Deletion Failed</h2>
    <p><?=htmlspecialchars($error)?></p>
  <?php endif; ?>
  <div class="btns">
    <a href="admin_menu.php?page=manage" class="btn btn-primary"><i class="bi bi-people-fill"></i> View Participants</a>
    <a href="admin_menu.php?page=dashboard" class="btn btn-outline"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
  </div>
</div>
</body>
</html>