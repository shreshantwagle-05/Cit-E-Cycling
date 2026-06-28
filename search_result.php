<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }
include_once 'dbconnect.php';

$participant = $_POST['participant'] ?? '';
$firstname   = trim($_POST['firstname'] ?? '');
$club        = trim($_POST['club'] ?? '');
$user        = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Results — Cit-E Cycling</title>
<link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
:root{--g:#1e7a46;--gd:#145c34;--gl:#2f9e5c;--gt:rgba(30,122,70,.1);--a:#f2a93b;--ad:#d88f1f;--ink:#0d1620;--ash:#677a70;--paper:#eef2ed;--surf:#fff;--line:#e1e8df;--font:'Plus Jakarta Sans',system-ui,sans-serif;--mono:'JetBrains Mono',monospace;--r:12px;--rl:20px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font);color:var(--ink);background:var(--paper);min-height:100vh;-webkit-font-smoothing:antialiased}
a{color:var(--g);text-decoration:none}a:hover{color:var(--gd)}

.wrap{max-width:820px;margin:0 auto;padding:44px 24px 64px}

.back{display:inline-flex;align-items:center;gap:7px;color:var(--g);font-weight:700;font-size:.88rem;margin-bottom:28px;padding:9px 16px;background:var(--surf);border:1px solid var(--line);border-radius:50px;transition:.18s}
.back:hover{background:var(--gt);border-color:rgba(30,122,70,.25);text-decoration:none}

.eyebrow{font-family:var(--mono);font-size:.68rem;letter-spacing:.09em;text-transform:uppercase;color:var(--g);display:block;margin-bottom:5px}
.result-hdr{margin-bottom:26px}
.result-hdr h1{font-size:1.65rem;font-weight:800;letter-spacing:-.025em;margin-bottom:5px}
.result-hdr p{color:var(--ash);font-size:.88rem}

/* result cards */
.rcard{
  background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);
  padding:20px 24px;margin-bottom:14px;
  display:flex;align-items:center;gap:16px;
  opacity:0;transform:translateY(10px);
  animation:rIn .38s ease forwards;
  transition:box-shadow .18s,transform .18s;
}
.rcard:hover{box-shadow:0 8px 24px rgba(13,22,32,.08);transform:translateY(-2px)}
@keyframes rIn{to{opacity:1;transform:translateY(0)}}
.rav{width:48px;height:48px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--g),var(--gl));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem}
.rav.club{background:linear-gradient(135deg,var(--ad),var(--a));color:var(--ink);border-radius:14px}
.rbody{flex:1;min-width:0}
.rname{font-weight:800;font-size:1rem;margin-bottom:4px}
.rmeta{display:flex;flex-wrap:wrap;gap:12px;font-size:.82rem;color:var(--ash)}
.rmeta span{display:flex;align-items:center;gap:5px}
.rbadges{display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap}
.rbadge{background:var(--paper);border:1px solid var(--line);border-radius:50px;padding:6px 13px;font-size:.76rem;font-weight:700;color:var(--ink);font-family:var(--mono)}

.empty{text-align:center;padding:56px 20px;background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);color:var(--ash)}
.empty i{font-size:2.8rem;color:var(--line);display:block;margin-bottom:14px}
.empty h3{color:var(--ink);margin-bottom:6px;font-size:1.1rem}

.err-box{background:#fdecec;border:1px solid #f5c6cb;color:#8a1f28;border-radius:14px;padding:16px 20px;font-size:.9rem}
</style>
</head>
<body>
<div class="wrap">

  <a href="admin_menu.php?page=search" class="back"><i class="bi bi-arrow-left"></i> Back to Search</a>

  <?php
  try {
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
      $username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    if ($participant == "1") {
      echo '<div class="result-hdr"><span class="eyebrow">Participant search</span>
            <h1>Results for &ldquo;'.htmlspecialchars($firstname).'&rdquo;</h1>
            <p>Matches on first name or surname.</p></div>';

      $st=$pdo->prepare("SELECT p.*,c.name AS club_name FROM participant p
         LEFT JOIN club c ON c.id=p.club_id
         WHERE p.firstname LIKE :t OR p.surname LIKE :t ORDER BY p.firstname");
      $st->execute([':t'=>'%'.$firstname.'%']);
      $rows=$st->fetchAll(PDO::FETCH_ASSOC);

      if($rows){ foreach($rows as $i=>$r){
        $ini=strtoupper(substr($r['firstname'],0,1).substr($r['surname'],0,1));
        echo '<div class="rcard" style="animation-delay:'.($i*.05).'s">
                <div class="rav">'.$ini.'</div>
                <div class="rbody">
                  <div class="rname">'.htmlspecialchars($r['firstname'].' '.$r['surname']).'</div>
                  <div class="rmeta">
                    <span><i class="bi bi-envelope-fill"></i>'.htmlspecialchars($r['email']).'</span>
                    '.($r['club_name']?'<span><i class="bi bi-flag-fill"></i>'.htmlspecialchars($r['club_name']).'</span>':'').'
                  </div>
                </div>
                <div class="rbadges">
                  <span class="rbadge">'.$r['power_output'].' W</span>
                  <span class="rbadge">'.$r['distance'].' km</span>
                </div>
              </div>';
      }} else {
        echo '<div class="empty"><i class="bi bi-person-x"></i><h3>No participant found</h3><p>Try a different name.</p></div>';
      }

    } else {
      echo '<div class="result-hdr"><span class="eyebrow">Club search</span>
            <h1>Results for &ldquo;'.htmlspecialchars($club).'&rdquo;</h1>
            <p>Matches on club name.</p></div>';

      $st=$pdo->prepare("SELECT * FROM club WHERE name LIKE :t ORDER BY name");
      $st->execute([':t'=>'%'.$club.'%']);
      $rows=$st->fetchAll(PDO::FETCH_ASSOC);

      if($rows){ foreach($rows as $i=>$r){
        $cnt=(int)$pdo->prepare("SELECT COUNT(*) FROM participant WHERE club_id=?")->execute([$r['id']]);
        $cs=$pdo->prepare("SELECT COUNT(*) FROM participant WHERE club_id=:id");
        $cs->execute([':id'=>$r['id']]);
        $cnt=(int)$cs->fetchColumn();
        echo '<div class="rcard" style="animation-delay:'.($i*.05).'s">
                <div class="rav club"><i class="bi bi-flag-fill"></i></div>
                <div class="rbody">
                  <div class="rname">'.htmlspecialchars($r['name']).'</div>
                  <div class="rmeta"><span><i class="bi bi-geo-alt-fill"></i>'.htmlspecialchars($r['location']).'</span></div>
                </div>
                <div class="rbadges"><span class="rbadge">'.$cnt.' riders</span></div>
              </div>';
      }} else {
        echo '<div class="empty"><i class="bi bi-flag"></i><h3>No club found</h3><p>Try a different club name.</p></div>';
      }
    }
  } catch(PDOException $e){
    echo '<div class="err-box"><i class="bi bi-exclamation-triangle-fill"></i> '.htmlspecialchars($e->getMessage()).'</div>';
  }
  ?>

</div>
</body>
</html>