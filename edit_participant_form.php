<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Saved! | Cit-E Cycling</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
:root{
  --g:#14a558;--gd:#0e7a40;--gl:#1ccc6e;--gf:rgba(20,165,88,.10);
  --am:#f5a623;--amd:#c8851a;--amf:rgba(245,166,35,.13);
  --ink:#080e18;--ink2:#111c2e;--tx:#1a2332;--mu:#6b7a8d;
  --bg:#f2f5f1;--ln:#e2e8e0;--wh:#ffffff;
  --r12:12px;--r16:16px;--r20:20px;--r24:24px;--rp:999px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'Inter',system-ui,sans-serif;color:var(--tx);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:var(--bg);padding:24px;-webkit-font-smoothing:antialiased;
}
h1,h2,h3{font-family:'Sora','Inter',sans-serif;letter-spacing:-.025em;}
a{color:var(--g);text-decoration:none;}

/* Page background shapes */
body::before{
  content:'';position:fixed;inset:0;z-index:0;
  background:
    radial-gradient(circle at 20% 20%, rgba(20,165,88,.08) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(245,166,35,.07) 0%, transparent 50%);
  pointer-events:none;
}

.card{
  position:relative;z-index:1;
  background:var(--wh);
  border:1px solid var(--ln);
  border-radius:var(--r24);
  padding:48px 44px;
  max-width:460px;width:100%;
  text-align:center;
  box-shadow:0 16px 56px rgba(8,14,24,.09);
  animation:pop .5s cubic-bezier(.22,1,.36,1);
}
@keyframes pop{from{opacity:0;transform:scale(.92) translateY(18px);}to{opacity:1;transform:scale(1) translateY(0);}}

/* tick */
.tick-ring{
  width:80px;height:80px;border-radius:50%;
  background:var(--gf);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 22px;
  animation:tickPop .55s .25s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes tickPop{from{transform:scale(0);opacity:0;}to{transform:scale(1);opacity:1;}}
.tick-ring i{font-size:2rem;color:var(--g);}

.card h1{font-size:1.6rem;font-weight:800;margin-bottom:8px;}
.card .sub{color:var(--mu);font-size:.9rem;line-height:1.65;margin-bottom:24px;}

/* Participant chip */
.p-chip{
  background:var(--bg);border:1px solid var(--ln);
  border-radius:var(--r16);padding:14px 18px;
  display:flex;align-items:center;gap:13px;
  text-align:left;margin-bottom:24px;
}
.chip-av{
  width:44px;height:44px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--g),var(--gl));
  color:#fff;font-weight:700;font-size:.9rem;
  display:flex;align-items:center;justify-content:center;
  font-family:'Sora',sans-serif;
}
.chip-name{font-weight:700;font-size:.92rem;margin-bottom:3px;}
.chip-meta{font-size:.78rem;color:var(--mu);}
.chip-right{margin-left:auto;text-align:right;flex-shrink:0;}
.chip-val{font-family:'JetBrains Mono',monospace;font-size:.8rem;font-weight:700;color:var(--g);display:block;}
.chip-lbl{font-size:.68rem;color:var(--mu);}

/* score pill row */
.score-pills{display:flex;justify-content:center;gap:10px;margin-bottom:24px;}
.score-pill{
  display:flex;flex-direction:column;align-items:center;
  background:var(--bg);border:1px solid var(--ln);
  border-radius:var(--r12);padding:11px 20px;min-width:100px;
}
.score-pill .v{font-family:'JetBrains Mono',monospace;font-size:1.25rem;font-weight:700;color:var(--g);}
.score-pill .l{font-size:.7rem;color:var(--mu);text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}

.div{height:1px;background:var(--ln);margin:0 0 22px;}

/* actions */
.acts{display:flex;flex-direction:column;gap:9px;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:12px 20px;border-radius:var(--rp);font-family:'Inter',sans-serif;font-weight:600;font-size:.86rem;border:none;cursor:pointer;transition:background .15s,transform .1s,color .15s;text-decoration:none;line-height:1;}
.btn:active{transform:scale(.97);}
.btn-green{background:var(--g);color:#fff;box-shadow:0 3px 12px rgba(20,165,88,.28);}
.btn-green:hover{background:var(--gd);color:#fff;}
.btn-outline{background:transparent;color:var(--g);border:1.5px solid var(--g);}
.btn-outline:hover{background:var(--g);color:#fff;}
.btn-muted{background:var(--bg);color:var(--mu);border:1px solid var(--ln);}
.btn-muted:hover{background:var(--ln);color:var(--tx);}
</style>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: admin_login.php"); exit(); }

include 'dbconnect.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p  = null;

if ($id) {
    try {
        $conn = new PDO("mysql:host=$servername;port=$port;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT p.*,c.name AS club_name FROM participant p LEFT JOIN club c ON c.id=p.club_id WHERE p.id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

$ini = $p ? strtoupper(substr($p['firstname'],0,1)).strtoupper(substr($p['surname'],0,1)) : '?';
?>

<div class="card">

  <div class="tick-ring"><i class="bi bi-check-lg"></i></div>

  <h1>Changes Saved!</h1>
  <p class="sub">The participant record has been updated successfully. All changes are live in the database.</p>

  <?php if ($p): ?>
  <div class="p-chip">
    <div class="chip-av"><?= $ini ?></div>
    <div style="flex:1;min-width:0;">
      <div class="chip-name"><?= htmlspecialchars($p['firstname'].' '.$p['surname']) ?></div>
      <div class="chip-meta">
        <?= htmlspecialchars($p['email']) ?>
        <?= $p['club_name'] ? ' · '.htmlspecialchars($p['club_name']) : '' ?>
      </div>
    </div>
  </div>

  <div class="score-pills">
    <div class="score-pill">
      <span class="v"><?= (int)$p['power_output'] ?> W</span>
      <span class="l">Power Output</span>
    </div>
    <div class="score-pill">
      <span class="v"><?= (float)$p['distance'] ?> km</span>
      <span class="l">Distance</span>
    </div>
  </div>
  <?php endif; ?>

  <div class="div"></div>

  <div class="acts">
    <a href="admin_menu.php?page=manage" class="btn btn-green">
      <i class="bi bi-people-fill"></i> View All Participants
    </a>
    <?php if ($p): ?>
    <a href="edit_participant.php?id=<?= $id ?>" class="btn btn-outline">
      <i class="bi bi-pencil-square"></i> Edit Again
    </a>
    <?php endif; ?>
    <a href="admin_menu.php?page=dashboard" class="btn btn-muted">
      <i class="bi bi-grid-1x2-fill"></i> Back to Dashboard
    </a>
  </div>

</div>

</body>
</html>