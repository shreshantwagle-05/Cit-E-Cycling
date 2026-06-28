<?php /* Included inside admin_menu.php — shares its styles & $pdo */ ?>
<style>
.sf-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.sf-card{background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);padding:28px}
.sf-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:18px}
.sf-icon.gr{background:var(--gt);color:var(--g)}.sf-icon.am{background:rgba(242,169,59,.15);color:var(--ad)}
.sf-card h3{font-size:1rem;font-weight:800;margin-bottom:4px}.sf-card p{font-size:.84rem;color:var(--ash);margin-bottom:18px}
.sf-input-wrap{position:relative;margin-bottom:14px}
.sf-input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--ash);font-size:.95rem}
.sf-input{width:100%;border:1.5px solid var(--line);border-radius:10px;padding:11px 14px 11px 38px;font-family:var(--font);font-size:.9rem;color:var(--ink);outline:none;transition:border-color .2s,box-shadow .2s;background:#fafcfa}
.sf-input:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(30,122,70,.1)}
.sf-btn{width:100%;padding:12px;border:none;border-radius:10px;font-family:var(--font);font-weight:700;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.18s}
.sf-btn-g{background:var(--g);color:#fff}.sf-btn-g:hover{background:var(--gd)}
.sf-btn-a{background:var(--a);color:var(--ink)}.sf-btn-a:hover{background:var(--ad);color:var(--ink)}

.sf-quick{background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);padding:22px 28px}
.sf-quick h4{font-size:.88rem;font-weight:800;margin-bottom:14px}
.sf-quick-tags{display:flex;gap:8px;flex-wrap:wrap}
.sf-tag{background:var(--paper);border:1px solid var(--line);border-radius:50px;padding:7px 16px;font-size:.8rem;font-weight:600;color:var(--ink);cursor:pointer;transition:.18s;font-family:var(--font)}
.sf-tag:hover{background:var(--gt);border-color:rgba(30,122,70,.3);color:var(--gd)}

@media(max-width:700px){.sf-grid{grid-template-columns:1fr}}
</style>

<div class="topbar" style="margin-bottom:0">
  <div>
    <span class="topbar-eyebrow">Find records</span>
    <h1>Search Participants &amp; Clubs</h1>
    <p>Look up an individual rider or an entire club in seconds.</p>
  </div>
</div>

<div class="sf-grid">

  <div class="sf-card">
    <div class="sf-icon gr"><i class="bi bi-person-fill"></i></div>
    <h3>Search a Participant</h3>
    <p>Find by first name or surname</p>
    <form action="search_result.php" method="POST">
      <div class="sf-input-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="firstname" class="sf-input" placeholder="e.g. Lorette or Stavers" required>
      </div>
      <input type="hidden" name="participant" value="1">
      <button type="submit" class="sf-btn sf-btn-g"><i class="bi bi-search"></i> Search Participant</button>
    </form>
  </div>

  <div class="sf-card">
    <div class="sf-icon am"><i class="bi bi-flag-fill"></i></div>
    <h3>Search a Club</h3>
    <p>Find by club name</p>
    <form action="search_result.php" method="POST">
      <div class="sf-input-wrap">
        <i class="bi bi-flag"></i>
        <input type="text" name="club" class="sf-input" placeholder="e.g. Roker Rollers" required>
      </div>
      <button type="submit" class="sf-btn sf-btn-a"><i class="bi bi-flag-fill"></i> Search Club</button>
    </form>
  </div>

</div>

<div class="sf-quick">
  <h4><i class="bi bi-lightning-fill" style="color:var(--a)"></i> Quick Club Picks</h4>
  <div class="sf-quick-tags">
    <?php
    try {
        include_once 'dbconnect.php';
        $c2 = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
            $username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $clubs=$c2->query("SELECT name FROM club ORDER BY name LIMIT 8")->fetchAll(PDO::FETCH_COLUMN);
        foreach($clubs as $cn){
            echo '<form action="search_result.php" method="POST" style="margin:0">
                    <input type="hidden" name="club" value="'.htmlspecialchars($cn).'">
                    <button type="submit" class="sf-tag">'.htmlspecialchars($cn).'</button>
                  </form>';
        }
    } catch(PDOException $e){ echo '<span style="color:var(--ash);font-size:.84rem">No clubs loaded.</span>'; }
    ?>
  </div>
</div>