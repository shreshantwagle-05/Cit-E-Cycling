<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.html");
    exit();
}
?>
<?php include_once 'dbconnect.php'; ?>
<style>
/* ── self-contained tokens so this works inside any parent ── */
.vp-wrap{
  --g:#16a34a;--gd:#14532d;--gl:#22c55e;--gt:rgba(22,163,74,.1);
  --a:#f59e0b;--ad:#d97706;
  --ink:#0f172a;--ash:#64748b;--paper:#f8fafc;--surf:#ffffff;
  --line:#e2e8f0;--rl:18px;
  --font:'Plus Jakarta Sans','Inter',system-ui,sans-serif;
  --mono:'JetBrains Mono',monospace;
  display:flex;flex-direction:column;gap:22px;width:100%;
}

/* topbar row */
.vp-topbar{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:14px}
.vp-eyebrow{font-family:var(--mono);font-size:.65rem;letter-spacing:.1em;text-transform:uppercase;color:var(--g);display:block;margin-bottom:4px}
.vp-topbar h1{font-family:var(--font);font-size:1.65rem;font-weight:800;letter-spacing:-.025em;margin:0;color:var(--ink)}
.vp-topbar p{font-size:.88rem;color:var(--ash);margin-top:3px}
.vp-filter-wrap{position:relative;min-width:260px}
.vp-filter-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--ash);font-size:.9rem;pointer-events:none}
.vp-filter{
  width:100%;border:1.5px solid var(--line);border-radius:50px;
  padding:10px 16px 10px 38px;font-family:var(--font);font-size:.86rem;
  color:var(--ink);outline:none;background:#fff;
  transition:border-color .2s,box-shadow .2s;
}
.vp-filter:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(22,163,74,.1)}

/* meta row */
.vp-meta{display:flex;justify-content:space-between;align-items:center}
.vp-meta h3{font-family:var(--font);font-size:.88rem;font-weight:800;color:var(--ink);margin:0}
.vp-count{
  font-family:var(--mono);font-size:.7rem;font-weight:700;
  background:var(--gt);color:var(--gd);padding:5px 12px;border-radius:50px;
  border:1px solid rgba(22,163,74,.18);
}

/* card grid */
.vp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px}

.vp-card{
  background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);
  padding:20px;cursor:default;
  opacity:0;transform:translateY(12px);
  animation:vpIn .4s ease forwards;
  transition:transform .22s ease,box-shadow .22s ease;
  display:flex;flex-direction:column;
}
.vp-card:hover{transform:translateY(-5px);box-shadow:0 14px 32px rgba(15,23,42,.1)}
@keyframes vpIn{to{opacity:1;transform:translateY(0)}}

/* card top */
.vp-card-top{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.vp-av{
  width:44px;height:44px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--g),var(--gl));
  color:#fff;display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:.88rem;
  box-shadow:0 3px 10px rgba(22,163,74,.28);
}
.vp-name{font-family:var(--font);font-weight:800;font-size:.95rem;color:var(--ink);margin:0 0 2px}
.vp-id{font-family:var(--mono);font-size:.65rem;color:var(--ash)}

/* email */
.vp-email{
  display:flex;align-items:center;gap:7px;
  color:var(--ash);font-size:.8rem;margin-bottom:10px;word-break:break-all;
}
.vp-email i{flex-shrink:0;font-size:.82rem}

/* club tag */
.vp-club{
  display:inline-flex;align-items:center;gap:6px;
  font-size:.73rem;font-weight:700;padding:4px 11px;border-radius:50px;margin-bottom:14px;
  align-self:flex-start;
}
.vp-club.has{background:rgba(245,158,11,.13);color:var(--ad)}
.vp-club.none{background:rgba(100,116,139,.1);color:var(--ash)}

/* stats */
.vp-stats{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:16px}
.vp-stat{
  background:var(--paper);border:1px solid var(--line);
  border-radius:11px;padding:10px 12px;text-align:center;
}
.vp-stat-val{
  font-family:var(--mono);font-weight:700;font-size:.95rem;
  display:block;color:var(--ink);margin-bottom:2px;
}
.vp-stat-lbl{font-size:.64rem;text-transform:uppercase;letter-spacing:.05em;color:var(--ash)}

/* actions */
.vp-actions{display:flex;gap:8px;margin-top:auto}
.vp-btn{
  flex:1;padding:9px 10px;border:none;border-radius:10px;
  font-family:var(--font);font-size:.8rem;font-weight:700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:6px;transition:.18s;
  text-decoration:none;
}
.vp-btn-edit{background:var(--gt);color:var(--gd);border:1px solid rgba(22,163,74,.2)}
.vp-btn-edit:hover{background:var(--g);color:#fff;border-color:var(--g);text-decoration:none}
.vp-btn-del{background:rgba(239,68,68,.09);color:#b91c1c;border:1px solid rgba(239,68,68,.18)}
.vp-btn-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* empty */
.vp-empty{
  text-align:center;padding:60px 20px;
  color:var(--ash);border:2px dashed var(--line);border-radius:var(--rl);
}
.vp-empty i{font-size:2.8rem;color:var(--line);display:block;margin-bottom:12px}

/* error */
.vp-error{
  background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;
  border-radius:12px;padding:14px 18px;font-size:.88rem;
}

/* ── STUNNING DELETE POPUP (replaces SweetAlert) ── */
.vp-del-backdrop{position:fixed;inset:0;z-index:9999;background:rgba(8,14,24,0.75);backdrop-filter:blur(12px) saturate(150%);-webkit-backdrop-filter:blur(12px) saturate(150%);display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity 0.25s ease;pointer-events:none;}
.vp-del-backdrop.open{opacity:1;pointer-events:all;}
.vp-del-card{background:#fff;border-radius:32px;max-width:410px;width:100%;overflow:hidden;box-shadow:0 0 0 1px rgba(0,0,0,.06),0 48px 120px rgba(0,0,0,.32),0 16px 40px rgba(0,0,0,.18);transform:scale(0.84) translateY(28px);opacity:0;transition:transform 0.36s cubic-bezier(0.34,1.56,0.64,1),opacity 0.26s ease;}
.vp-del-backdrop.open .vp-del-card{transform:scale(1) translateY(0);opacity:1;}
.vp-del-card.shake{animation:vpDelShake 0.4s ease;}
@keyframes vpDelShake{0%,100%{transform:scale(1) translateY(0);}20%{transform:scale(1) translateY(-4px) rotate(-1.2deg);}40%{transform:scale(1) translateY(3px) rotate(1deg);}60%{transform:scale(1) translateY(-2px);}80%{transform:scale(1) translateY(1px);}}

.vp-del-hdr{position:relative;padding:42px 36px 34px;text-align:center;overflow:hidden;background:linear-gradient(150deg,#07090f 0%,#1c0404 45%,#6b0e0e 100%);}
.vp-del-hdr::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(239,68,68,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(239,68,68,.06) 1px,transparent 1px);background-size:26px 26px;pointer-events:none;}
.vp-del-hdr::after{content:'';position:absolute;bottom:-70px;left:50%;transform:translateX(-50%);width:280px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(239,68,68,.28) 0%,transparent 65%);pointer-events:none;}

.vp-del-rings{position:relative;width:86px;height:86px;margin:0 auto 20px;}
.vp-del-ring{position:absolute;border-radius:50%;border:1px solid rgba(239,68,68,.22);inset:0;animation:vpDelRingPulse 2.8s ease-in-out infinite;}
.vp-del-ring:nth-child(2){inset:-11px;border-color:rgba(239,68,68,.13);animation-delay:.55s;}
.vp-del-ring:nth-child(3){inset:-22px;border-color:rgba(239,68,68,.07);animation-delay:1.1s;}
@keyframes vpDelRingPulse{0%,100%{transform:scale(.94);opacity:.9;}50%{transform:scale(1.06);opacity:.35;}}
.vp-del-core{position:absolute;inset:0;border-radius:50%;background:linear-gradient(135deg,rgba(239,68,68,.22),rgba(185,28,28,.28));border:1.5px solid rgba(239,68,68,.42);display:flex;align-items:center;justify-content:center;box-shadow:0 0 28px rgba(239,68,68,.22);}
.vp-del-core i{font-size:1.85rem;color:#fca5a5;filter:drop-shadow(0 0 12px rgba(239,68,68,.65));}

.vp-del-hdr-label{font-family:var(--mono);font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.32);margin-bottom:7px;position:relative;z-index:1;}
.vp-del-hdr-title{font-family:var(--font);font-size:1.15rem;font-weight:700;color:#fff;letter-spacing:-.01em;position:relative;z-index:1;}

.vp-del-body{padding:26px 30px 28px;background:#fff;}

.vp-del-chip{display:flex;align-items:center;gap:12px;background:#fafafa;border:1px solid #f0f0f0;border-radius:16px;padding:12px 15px;margin-bottom:14px;}
.vp-del-chip-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0;box-shadow:0 3px 12px rgba(239,68,68,.28);}
.vp-del-chip-name{font-weight:700;font-size:.9rem;color:#0f172a;}
.vp-del-chip-id{font-family:var(--mono);font-size:.66rem;color:#94a3b8;margin-top:2px;}

.vp-del-pill{display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.16);border-radius:50px;padding:5px 13px;font-family:var(--mono);font-size:.68rem;font-weight:700;color:#dc2626;letter-spacing:.03em;margin-bottom:12px;}
.vp-del-pill i{font-size:.65rem;}

.vp-del-warn{font-size:.82rem;color:#64748b;line-height:1.6;margin-bottom:20px;text-align:center;}
.vp-del-warn strong{color:#dc2626;font-weight:700;}

.vp-del-btns{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
.vp-del-cancel{padding:12px 16px;border-radius:50px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-family:var(--font);font-weight:700;font-size:.85rem;cursor:pointer;transition:all .16s;display:flex;align-items:center;justify-content:center;gap:6px;}
.vp-del-cancel:hover{background:#e2e8f0;color:#0f172a;border-color:#cbd5e1;}
.vp-del-confirm{padding:12px 16px;border-radius:50px;border:none;background:linear-gradient(135deg,#b91c1c 0%,#ef4444 100%);color:#fff;font-family:var(--font);font-weight:800;font-size:.85rem;cursor:pointer;box-shadow:0 4px 18px rgba(220,38,38,.36);transition:all .16s;display:flex;align-items:center;justify-content:center;gap:6px;animation:vpDelBtnGlow 2.6s ease-in-out infinite;}
@keyframes vpDelBtnGlow{0%,100%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 0 rgba(220,38,38,.2);}50%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 7px rgba(220,38,38,0);}}
.vp-del-confirm:hover{background:linear-gradient(135deg,#991b1b 0%,#dc2626 100%);box-shadow:0 6px 26px rgba(220,38,38,.52);transform:translateY(-1px);animation:none;}
.vp-del-confirm:active{transform:scale(.97);}
</style>

<div class="vp-wrap">

  <!-- Top row -->
  <div class="vp-topbar">
    <div>
      <span class="vp-eyebrow">Participant records</span>
      <h1>Manage Participants</h1>
      <p>View, edit and delete participant records.</p>
    </div>
    <div class="vp-filter-wrap">
      <i class="bi bi-search"></i>
      <input type="text" id="vpFilter" class="vp-filter" placeholder="Filter by name, email or club…">
    </div>
  </div>

  <!-- Meta row -->
  <div class="vp-meta">
    <h3>All Participants</h3>
    <span class="vp-count" id="vpCount"></span>
  </div>

  <?php
  try {
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
      $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query(
      "SELECT p.*, c.name AS club_name
       FROM participant p
       LEFT JOIN club c ON c.id = p.club_id
       ORDER BY p.firstname"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
      echo '<div class="vp-grid" id="vpGrid">';
      foreach ($rows as $i => $r) {
        $ini      = strtoupper(substr($r['firstname'],0,1).substr($r['surname'],0,1));
        $hasClub  = !empty($r['club_name']);
        $clubCls  = $hasClub ? 'has' : 'none';
        $clubName = $hasClub ? htmlspecialchars($r['club_name']) : 'No club';
        $search   = strtolower($r['firstname'].' '.$r['surname'].' '.$r['email'].' '.($r['club_name']??''));
        $delay    = round($i * 0.035, 3);
        $fullName = htmlspecialchars($r['firstname'].' '.$r['surname'], ENT_QUOTES);
        echo '
        <div class="vp-card" data-search="'.$search.'" style="animation-delay:'.$delay.'s">
          <div class="vp-card-top">
            <div class="vp-av">'.$ini.'</div>
            <div>
              <p class="vp-name">'.htmlspecialchars($r['firstname'].' '.$r['surname']).'</p>
              <span class="vp-id">ID #'.(int)$r['id'].'</span>
            </div>
          </div>
          <div class="vp-email">
            <i class="bi bi-envelope-fill"></i>'.htmlspecialchars($r['email']).'
          </div>
          <span class="vp-club '.$clubCls.'">
            <i class="bi bi-'.($hasClub?'flag-fill':'dash').'"></i>'.$clubName.'
          </span>
          <div class="vp-stats">
            <div class="vp-stat">
              <span class="vp-stat-val">'.htmlspecialchars($r['power_output']).'</span>
              <span class="vp-stat-lbl">Power (W)</span>
            </div>
            <div class="vp-stat">
              <span class="vp-stat-val">'.htmlspecialchars($r['distance']).'</span>
              <span class="vp-stat-lbl">Distance (km)</span>
            </div>
          </div>
          <div class="vp-actions">
            <a href="edit_participant.php?id='.(int)$r['id'].'" class="vp-btn vp-btn-edit">
              <i class="bi bi-pencil-fill"></i> Edit
            </a>
            <button class="vp-btn vp-btn-del" onclick="vpDelete('.(int)$r['id'].', \''.$fullName.'\')">
              <i class="bi bi-trash3-fill"></i> Delete
            </button>
          </div>
        </div>';
      }
      echo '</div>'; /* /vp-grid */
    } else {
      echo '<div class="vp-empty"><i class="bi bi-inbox"></i><p>No participants found.</p></div>';
    }
  } catch (PDOException $e) {
    echo '<div class="vp-error"><i class="bi bi-exclamation-triangle-fill"></i> '.htmlspecialchars($e->getMessage()).'</div>';
  }
  ?>

</div><!-- /vp-wrap -->

<!-- ══ STUNNING DELETE POPUP (no SweetAlert needed) ══ -->
<div class="vp-del-backdrop" id="vpDelBackdrop">
  <div class="vp-del-card" id="vpDelCard">
    <div class="vp-del-hdr">
      <div class="vp-del-rings">
        <div class="vp-del-ring"></div>
        <div class="vp-del-ring"></div>
        <div class="vp-del-ring"></div>
        <div class="vp-del-core"><i class="bi bi-trash3-fill"></i></div>
      </div>
      <div class="vp-del-hdr-label">Irreversible action</div>
      <div class="vp-del-hdr-title">Delete Participant?</div>
    </div>
    <div class="vp-del-body">
      <div class="vp-del-chip">
        <div class="vp-del-chip-av" id="vpDelInitials">?</div>
        <div>
          <div class="vp-del-chip-name" id="vpDelName">—</div>
          <div class="vp-del-chip-id" id="vpDelIdLabel">Participant</div>
        </div>
      </div>
      <div style="text-align:center;">
        <span class="vp-del-pill"><i class="bi bi-exclamation-triangle-fill"></i> Cannot be undone</span>
      </div>
      <p class="vp-del-warn">This will <strong>permanently delete</strong> this participant and all their performance data from the database.</p>
      <div class="vp-del-btns">
        <button class="vp-del-cancel" id="vpDelCancelBtn"><i class="bi bi-x-lg"></i> Cancel</button>
        <button class="vp-del-confirm" id="vpDelConfirmBtn"><i class="bi bi-trash3-fill"></i> Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var grid    = document.getElementById('vpGrid');
  var countEl = document.getElementById('vpCount');
  var filter  = document.getElementById('vpFilter');
  var cards   = grid ? Array.from(grid.querySelectorAll('.vp-card')) : [];

  function updateCount(n) {
    if (countEl) countEl.textContent = n + (n === 1 ? ' record' : ' records');
  }
  updateCount(cards.length);

  if (filter && grid) {
    filter.addEventListener('input', function () {
      var q = filter.value.trim().toLowerCase();
      var n = 0;
      cards.forEach(function (c) {
        var match = c.getAttribute('data-search').includes(q);
        c.style.display = match ? '' : 'none';
        if (match) n++;
      });
      updateCount(n);
    });
  }
})();

/* ── Stunning delete popup logic ── */
(function(){
  var backdrop = document.getElementById('vpDelBackdrop');
  var card = document.getElementById('vpDelCard');
  var cancelBtn = document.getElementById('vpDelCancelBtn');
  var confirmBtn = document.getElementById('vpDelConfirmBtn');
  var currentId = null;

  window.vpDelete = function(id, name) {
    currentId = id;
    var parts = name.trim().split(' ');
    var initials = parts.map(function(p){ return p[0]||''; }).join('').substring(0,2).toUpperCase();
    document.getElementById('vpDelInitials').textContent = initials;
    document.getElementById('vpDelName').textContent = name;
    document.getElementById('vpDelIdLabel').textContent = 'Participant ID #' + id;
    backdrop.classList.add('open');
    document.addEventListener('keydown', onKey);
  };

  function close() {
    backdrop.classList.remove('open');
    document.removeEventListener('keydown', onKey);
  }

  function shake() {
    card.classList.remove('shake');
    void card.offsetWidth;
    card.classList.add('shake');
  }

  cancelBtn.addEventListener('click', close);

  backdrop.addEventListener('click', function(e) {
    if (e.target === backdrop) shake();
  });

  confirmBtn.addEventListener('click', function() {
    if (currentId) window.location.href = 'delete.php?id=' + currentId;
  });

  function onKey(e) { if (e.key === 'Escape') close(); }
})();
</script>