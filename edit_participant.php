<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }
include_once 'dbconnect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$success = false;
$errors  = [];
$row     = null;

try {
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
        $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    /* Handle POST update */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
        $power    = $_POST['power_output'] ?? '';
        $distance = $_POST['distance']     ?? '';

        if ($power === '' || !is_numeric($power))   $errors[] = 'Power output is required and must be a number.';
        elseif ((float)$power < 0)                  $errors[] = 'Power output cannot be negative.';
        if ($distance === '' || !is_numeric($distance)) $errors[] = 'Distance is required and must be a number.';
        elseif ((float)$distance < 0)               $errors[] = 'Distance cannot be negative.';

        if (!$errors) {
            $st = $pdo->prepare("UPDATE participant SET power_output=:p, distance=:d WHERE id=:id");
            $st->execute([':p'=>(float)$power, ':d'=>(float)$distance, ':id'=>$id]);
            $success = true;
        }
    }

    /* Load participant */
    if ($id) {
        $st = $pdo->prepare("SELECT p.*, c.name AS club_name FROM participant p
            LEFT JOIN club c ON c.id=p.club_id WHERE p.id=:id");
        $st->execute([':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}

$user = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Participant — Cit-E Cycling</title>
<link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
/* ── Tokens ── */
:root{--g:#1e7a46;--gd:#145c34;--gl:#2f9e5c;--gt:rgba(30,122,70,.1);--a:#f2a93b;--ad:#d88f1f;--ink:#0d1620;--ash:#677a70;--paper:#eef2ed;--surf:#fff;--line:#e1e8df;--red:#dc2626;--font:'Plus Jakarta Sans',system-ui,sans-serif;--mono:'JetBrains Mono',monospace;--r:12px;--rl:20px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font);color:var(--ink);background:var(--paper);min-height:100vh;display:flex;-webkit-font-smoothing:antialiased}
a{color:var(--g);text-decoration:none}a:hover{color:var(--gd)}
img,svg{display:block}

/* ── Sidebar (same as dashboard) ── */
.sb{width:248px;min-height:100vh;background:linear-gradient(195deg,#0d1620 0%,#16243a 100%);color:#fff;position:sticky;top:0;height:100vh;flex-shrink:0;display:flex;flex-direction:column;z-index:40;border-right:1px solid rgba(255,255,255,.05)}
.sb-top{padding:24px 20px 0;flex:1;overflow-y:auto}
.sb-brand{display:flex;align-items:center;gap:11px;margin-bottom:28px}
.sb-brand-mark{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--g),var(--gl));display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;box-shadow:0 4px 14px rgba(30,122,70,.4)}
.sb-brand-name{font-weight:800;font-size:1rem;line-height:1.15;letter-spacing:-.01em}
.sb-brand-sub{font-family:var(--mono);font-size:.62rem;color:rgba(255,255,255,.35);letter-spacing:.07em;text-transform:uppercase}
.sb-sep{height:1px;background:rgba(255,255,255,.07);margin:4px 0 16px}
.sb-lbl{font-family:var(--mono);font-size:.62rem;letter-spacing:.09em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:8px;padding-left:2px}
.sb-nav{display:flex;flex-direction:column;gap:3px}
.sb-link{display:flex;align-items:center;gap:11px;padding:11px 13px;border-radius:11px;color:rgba(255,255,255,.55);font-weight:600;font-size:.88rem;transition:.18s}
.sb-link i{font-size:1rem;width:19px;text-align:center;flex-shrink:0}
.sb-link:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.9);text-decoration:none}
.sb-link.active{background:linear-gradient(135deg,var(--g),var(--gl));color:#fff;box-shadow:0 4px 14px rgba(30,122,70,.35)}
.sb-bottom{padding:16px 20px 20px;border-top:1px solid rgba(255,255,255,.07)}
.sb-user{display:flex;align-items:center;gap:11px;padding:12px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:13px;margin-bottom:10px}
.sb-av{width:36px;height:36px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--a),var(--ad));color:var(--ink);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem}
.sb-uname{font-size:.86rem;font-weight:700;color:#fff;line-height:1.2}
.sb-urole{font-size:.7rem;color:rgba(255,255,255,.38);font-family:var(--mono)}
.sb-logout{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;border-radius:11px;background:rgba(220,53,69,.13);border:1px solid rgba(220,53,69,.28);color:#ff9aa2;font-weight:700;font-size:.86rem;transition:.18s}
.sb-logout:hover{background:#dc3545;color:#fff;text-decoration:none}

/* ── Main ── */
.main{flex:1;min-width:0;padding:36px 40px 60px;display:flex;flex-direction:column;gap:24px}

/* Back link */
.back{display:inline-flex;align-items:center;gap:7px;color:var(--g);font-weight:700;font-size:.85rem;padding:8px 16px;background:var(--surf);border:1px solid var(--line);border-radius:50px;transition:.18s;align-self:flex-start}
.back:hover{background:var(--gt);border-color:rgba(30,122,70,.25);text-decoration:none}

/* Page header */
.ph{display:flex;flex-direction:column;gap:4px}
.eyebrow{font-family:var(--mono);font-size:.68rem;letter-spacing:.09em;text-transform:uppercase;color:var(--g)}
.ph h1{font-size:1.7rem;font-weight:800;letter-spacing:-.025em}
.ph p{color:var(--ash);font-size:.88rem}

/* ── Success card ── */
.success-wrap{display:flex;justify-content:center;padding:20px 0}
.success-card{
  background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);
  padding:48px 44px;text-align:center;max-width:520px;width:100%;
  box-shadow:0 8px 32px rgba(13,22,32,.07);
  animation:popIn .5s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn{from{opacity:0;transform:scale(.94) translateY(16px)}to{opacity:1;transform:scale(1) translateY(0)}}
.success-icon{width:80px;height:80px;border-radius:50%;background:rgba(30,122,70,.1);border:2px solid rgba(30,122,70,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 22px;animation:iconIn .5s cubic-bezier(.34,1.56,.64,1) .15s both}
@keyframes iconIn{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}
.success-icon i{font-size:2.2rem;color:var(--g)}
.success-card h2{font-size:1.5rem;font-weight:800;letter-spacing:-.02em;margin-bottom:10px;color:var(--gd)}
.success-card p{color:var(--ash);font-size:.92rem;line-height:1.6;margin-bottom:24px;max-width:380px;margin-left:auto;margin-right:auto}

/* Info pills inside success card */
.success-info{display:flex;flex-direction:column;gap:10px;margin-bottom:26px;background:var(--gt);border:1px solid rgba(30,122,70,.18);border-radius:12px;padding:16px 18px;text-align:left}
.sinfo-row{display:flex;align-items:center;gap:10px;font-size:.86rem}
.sinfo-row i{color:var(--g);flex-shrink:0}
.sinfo-label{color:var(--ash)}
.sinfo-val{font-weight:700;color:var(--ink);margin-left:auto}

.success-btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.sbtn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:10px;font-family:var(--font);font-size:.88rem;font-weight:700;cursor:pointer;border:none;transition:.18s;text-decoration:none}
.sbtn-primary{background:var(--g);color:#fff;box-shadow:0 4px 14px rgba(30,122,70,.26)}.sbtn-primary:hover{background:var(--gd);color:#fff}
.sbtn-outline{background:transparent;border:1.5px solid var(--line);color:var(--ash)}.sbtn-outline:hover{border-color:var(--g);color:var(--g)}

/* ── Edit form card ── */
.form-card{background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);padding:32px;max-width:680px}

/* Participant identity strip */
.id-strip{display:flex;align-items:center;gap:16px;padding:20px;background:var(--paper);border:1px solid var(--line);border-radius:14px;margin-bottom:26px}
.id-av{width:52px;height:52px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--g),var(--gl));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem}
.id-name{font-weight:800;font-size:1.1rem;margin-bottom:3px}
.id-meta{display:flex;flex-wrap:wrap;gap:12px;font-size:.8rem;color:var(--ash)}
.id-meta span{display:flex;align-items:center;gap:5px}

/* Alerts */
.alert{display:flex;align-items:flex-start;gap:10px;padding:13px 16px;border-radius:11px;font-size:.87rem;font-weight:500;margin-bottom:22px;animation:fadeUp .25s ease}
.alert i{flex-shrink:0;font-size:1rem;margin-top:1px}
.alert-error{background:rgba(220,38,38,.07);border:1px solid rgba(220,38,38,.22);color:#b91c1c}
.alert-error ul{margin:6px 0 0 16px;display:flex;flex-direction:column;gap:4px;font-size:.85rem}

/* Form fields */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px}
.field{display:flex;flex-direction:column;gap:7px}
.field label{font-size:.82rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:7px}
.field-badge{font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:50px;font-family:var(--mono)}
.fb-amber{background:rgba(242,169,59,.15);color:var(--ad)}
.fb-green{background:var(--gt);color:var(--gd)}
.field-hint{font-size:.76rem;color:var(--ash);font-family:var(--mono)}
.inp{width:100%;border:1.5px solid var(--line);border-radius:10px;padding:12px 14px;font-family:var(--font);font-size:.92rem;color:var(--ink);outline:none;transition:border-color .2s,box-shadow .2s;background:#fafcfa}
.inp:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(30,122,70,.1)}
.inp:disabled{background:rgba(13,22,32,.04);color:var(--ash);cursor:not-allowed;border-color:transparent}
.inp.has-err{border-color:var(--red);box-shadow:0 0 0 3px rgba(220,38,38,.1)}
@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-5px)}40%{transform:translateX(5px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}
.shake{animation:shake .4s ease}

/* Action buttons */
.form-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-save{display:flex;align-items:center;gap:8px;padding:12px 26px;background:var(--g);border:none;border-radius:10px;font-family:var(--font);font-size:.92rem;font-weight:800;color:#fff;cursor:pointer;box-shadow:0 4px 14px rgba(30,122,70,.26);transition:.18s}
.btn-save:hover{background:var(--gd);transform:translateY(-1px)}
.btn-cancel{display:flex;align-items:center;gap:8px;padding:12px 22px;background:transparent;border:1.5px solid var(--line);border-radius:10px;font-family:var(--font);font-size:.92rem;font-weight:700;color:var(--ash);cursor:pointer;transition:.18s}
.btn-cancel:hover{border-color:var(--red);color:var(--red);text-decoration:none}

@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:780px){body{flex-direction:column}.sb{position:relative;width:100%;height:auto;min-height:auto}.main{padding:22px 16px 40px}.form-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sb">
  <div class="sb-top">
    <div class="sb-brand">
      <div class="sb-brand-mark"><i class="bi bi-bicycle" style="color:#fff"></i></div>
      <div>
        <div class="sb-brand-name">Cit-E Cycling</div>
        <div class="sb-brand-sub">Admin Console</div>
      </div>
    </div>
    <div class="sb-sep"></div>
    <div class="sb-lbl">Menu</div>
    <nav class="sb-nav">
      <a class="sb-link" href="admin_menu.php?page=dashboard"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
      <a class="sb-link" href="admin_menu.php?page=search"><i class="bi bi-search"></i> Search Participants</a>
      <a class="sb-link active" href="admin_menu.php?page=manage"><i class="bi bi-people-fill"></i> Manage Participants</a>
    </nav>
  </div>
  <div class="sb-bottom">
    <div class="sb-user">
      <div class="sb-av"><?=strtoupper(substr($user,0,2))?></div>
      <div>
        <div class="sb-uname"><?=$user?></div>
        <div class="sb-urole">Administrator</div>
      </div>
    </div>
    <a href="admin_menu.php?logout=1" class="sb-logout"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
  </div>
</aside>

<!-- Main content -->
<div class="main">

  <a href="admin_menu.php?page=manage" class="back"><i class="bi bi-arrow-left"></i> Back to Participants</a>

  <div class="ph">
    <span class="eyebrow">Participant Management</span>
    <h1>Update Rider Scores</h1>
    <p>Edit power output and distance for the selected participant.</p>
  </div>

  <?php if ($success && $row): ?>
  <!-- ── SUCCESS STATE ── -->
  <div class="success-wrap">
    <div class="success-card">
      <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
      <h2>Participant Updated!</h2>
      <p>The scores for <strong><?=htmlspecialchars($row['firstname'].' '.$row['surname'])?></strong> have been saved successfully.</p>

      <div class="success-info">
        <div class="sinfo-row">
          <i class="bi bi-person-fill"></i>
          <span class="sinfo-label">Participant</span>
          <span class="sinfo-val"><?=htmlspecialchars($row['firstname'].' '.$row['surname'])?></span>
        </div>
        <div class="sinfo-row">
          <i class="bi bi-lightning-fill"></i>
          <span class="sinfo-label">Power Output</span>
          <span class="sinfo-val"><?=htmlspecialchars($row['power_output'])?> W</span>
        </div>
        <div class="sinfo-row">
          <i class="bi bi-signpost-2-fill"></i>
          <span class="sinfo-label">Distance</span>
          <span class="sinfo-val"><?=htmlspecialchars($row['distance'])?> km</span>
        </div>
        <?php if($row['club_name']): ?>
        <div class="sinfo-row">
          <i class="bi bi-flag-fill"></i>
          <span class="sinfo-label">Club</span>
          <span class="sinfo-val"><?=htmlspecialchars($row['club_name'])?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="success-btns">
        <a href="admin_menu.php?page=manage" class="sbtn sbtn-primary"><i class="bi bi-people-fill"></i> View All Participants</a>
        <a href="edit_participant.php?id=<?=$id?>" class="sbtn sbtn-outline"><i class="bi bi-pencil-fill"></i> Edit Again</a>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- ── EDIT FORM ── -->
  <div class="form-card">

    <?php if ($errors): ?>
    <div class="alert alert-error">
      <i class="bi bi-exclamation-circle-fill"></i>
      <div>
        <strong>Please fix the following:</strong>
        <ul><?php foreach($errors as $e) echo '<li>'.$e.'</li>'; ?></ul>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!$row && $id): ?>
    <div class="alert alert-error"><i class="bi bi-exclamation-circle-fill"></i> Participant not found.</div>
    <?php elseif (!$id): ?>
    <div class="alert alert-error"><i class="bi bi-exclamation-circle-fill"></i> No participant ID provided. <a href="admin_menu.php?page=manage">Go back</a>.</div>
    <?php else: ?>

    <!-- Identity strip -->
    <?php
      $ini=strtoupper(substr($row['firstname'],0,1).substr($row['surname'],0,1));
    ?>
    <div class="id-strip">
      <div class="id-av"><?=$ini?></div>
      <div>
        <div class="id-name"><?=htmlspecialchars($row['firstname'].' '.$row['surname'])?></div>
        <div class="id-meta">
          <span><i class="bi bi-envelope-fill"></i><?=htmlspecialchars($row['email'])?></span>
          <?php if($row['club_name']): ?>
          <span><i class="bi bi-flag-fill"></i><?=htmlspecialchars($row['club_name'])?></span>
          <?php endif; ?>
          <span><i class="bi bi-hash"></i>ID <?=$row['id']?></span>
        </div>
      </div>
    </div>

    <form method="POST" action="edit_participant.php" id="editForm" novalidate>
      <input type="hidden" name="id" value="<?=$id?>">

      <div class="form-grid">
        <div class="field">
          <label>
            <i class="bi bi-person-fill" style="color:var(--ash)"></i>
            First Name
          </label>
          <input class="inp" type="text" value="<?=htmlspecialchars($row['firstname'])?>" disabled>
        </div>
        <div class="field">
          <label>
            <i class="bi bi-person-badge-fill" style="color:var(--ash)"></i>
            Surname
          </label>
          <input class="inp" type="text" value="<?=htmlspecialchars($row['surname'])?>" disabled>
        </div>
        <div class="field" id="f-power">
          <label>
            <i class="bi bi-lightning-fill" style="color:var(--ad)"></i>
            Power Output
            <span class="field-badge fb-amber">Required</span>
          </label>
          <input class="inp <?=in_array('Power output is required and must be a number.',$errors)||in_array('Power output cannot be negative.',$errors)?'has-err':''?>"
            type="number" name="power_output" id="power_output" min="0"
            value="<?=htmlspecialchars($row['power_output'])?>" placeholder="e.g. 250" required>
          <span class="field-hint">Watts (W)</span>
        </div>
        <div class="field" id="f-distance">
          <label>
            <i class="bi bi-signpost-2-fill" style="color:var(--g)"></i>
            Distance Travelled
            <span class="field-badge fb-green">Required</span>
          </label>
          <input class="inp <?=in_array('Distance is required and must be a number.',$errors)||in_array('Distance cannot be negative.',$errors)?'has-err':''?>"
            type="number" name="distance" id="distance" step="0.01" min="0"
            value="<?=htmlspecialchars($row['distance'])?>" placeholder="e.g. 42.5" required>
          <span class="field-hint">Kilometres (km)</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-save" id="saveBtn">
          <i class="bi bi-check-circle-fill"></i>
          <span id="saveLbl">Update Rider</span>
        </button>
        <a href="admin_menu.php?page=manage" class="btn-cancel">
          <i class="bi bi-x-circle"></i> Cancel
        </a>
      </div>
    </form>

    <?php endif; ?>
  </div>

  <?php endif; ?>

</div><!-- /main -->

<script>
(function(){
  var form=document.getElementById('editForm');
  if(!form) return;
  var pw=document.getElementById('power_output');
  var di=document.getElementById('distance');
  var btn=document.getElementById('saveBtn');
  var lbl=document.getElementById('saveLbl');

  [pw,di].forEach(function(el){
    if(!el) return;
    el.addEventListener('input',function(){ el.classList.remove('has-err','shake'); });
  });

  form.addEventListener('submit',function(e){
    e.preventDefault();
    var errs=[];
    var ok=true;

    if(pw.value===''||isNaN(pw.value)){
      pw.classList.add('has-err','shake');
      pw.addEventListener('animationend',function(){pw.classList.remove('shake');},{once:true});
      errs.push('Power output is required.');ok=false;
    } else if(parseFloat(pw.value)<0){
      pw.classList.add('has-err','shake');
      pw.addEventListener('animationend',function(){pw.classList.remove('shake');},{once:true});
      errs.push('Power output cannot be negative.');ok=false;
    }

    if(di.value===''||isNaN(di.value)){
      di.classList.add('has-err','shake');
      di.addEventListener('animationend',function(){di.classList.remove('shake');},{once:true});
      errs.push('Distance is required.');ok=false;
    } else if(parseFloat(di.value)<0){
      di.classList.add('has-err','shake');
      di.addEventListener('animationend',function(){di.classList.remove('shake');},{once:true});
      errs.push('Distance cannot be negative.');ok=false;
    }

    if(!ok){
      /* Show banner */
      var existing=document.querySelector('.alert.alert-error');
      if(existing) existing.remove();
      var banner=document.createElement('div');
      banner.className='alert alert-error';
      banner.innerHTML='<i class="bi bi-exclamation-circle-fill"></i><div><strong>Please fix the following:</strong><ul>'
        +errs.map(function(e){return '<li>'+e+'</li>';}).join('')+'</ul></div>';
      form.parentNode.insertBefore(banner,form);
      window.scrollTo({top:0,behavior:'smooth'});
      return;
    }

    lbl.textContent='Saving…';
    btn.disabled=true;
    form.submit();
  });
})();
</script>

</body>
</html>