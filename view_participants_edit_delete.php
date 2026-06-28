<?php include_once 'dbconnect.php'; ?>
<style>
.pm-topbar{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:0}
.pm-filter-wrap{position:relative;min-width:260px}
.pm-filter-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--ash)}
.pm-filter{width:100%;border:1.5px solid var(--line);border-radius:50px;padding:10px 14px 10px 38px;font-family:var(--font);font-size:.88rem;color:var(--ink);outline:none;background:#fafcfa;transition:border-color .2s,box-shadow .2s}
.pm-filter:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(30,122,70,.1)}

.pm-meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.pm-meta h3{font-size:.92rem;font-weight:800;margin:0}
.pm-count{font-family:var(--mono);font-size:.72rem;font-weight:700;background:var(--gt);color:var(--gd);padding:5px 12px;border-radius:50px}

.pm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.pm-card{
  background:var(--surf);border:1px solid var(--line);border-radius:var(--rl);padding:22px;
  opacity:0;transform:translateY(10px);animation:fadeUp .4s ease forwards;
  transition:transform .2s,box-shadow .2s;
}
.pm-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(13,22,32,.1)}
.pm-card-top{display:flex;align-items:center;gap:13px;margin-bottom:14px}
.pm-av{width:46px;height:46px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--g),var(--gl));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.92rem}
.pm-name{font-weight:800;font-size:1rem;margin:0 0 2px}
.pm-id{font-family:var(--mono);font-size:.68rem;color:var(--ash)}
.pm-email{display:flex;align-items:center;gap:7px;color:var(--ash);font-size:.82rem;margin-bottom:10px;word-break:break-all}
.pm-club-tag{display:inline-flex;align-items:center;gap:6px;font-size:.76rem;font-weight:700;padding:5px 12px;border-radius:50px;margin-bottom:14px}
.pm-club-tag.has{background:rgba(242,169,59,.14);color:var(--ad)}
.pm-club-tag.none{background:var(--paper);color:var(--ash)}
.pm-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}
.pm-stat{background:var(--paper);border-radius:10px;padding:10px 12px;text-align:center}
.pm-stat-val{font-weight:800;font-size:1rem;display:block}
.pm-stat-lbl{font-size:.68rem;text-transform:uppercase;letter-spacing:.04em;color:var(--ash)}
.pm-actions{display:flex;gap:8px}
.pm-btn{flex:1;padding:10px;border:none;border-radius:10px;font-family:var(--font);font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:.18s}
.pm-btn-edit{background:var(--gt);color:var(--gd)}.pm-btn-edit:hover{background:var(--g);color:#fff;text-decoration:none}
.pm-btn-del{background:rgba(220,53,69,.1);color:#c0392b}.pm-btn-del:hover{background:#dc3545;color:#fff}

.pm-empty{text-align:center;padding:56px 20px;color:var(--ash);border:1px dashed var(--line);border-radius:var(--rl)}
.pm-empty i{font-size:2.8rem;color:var(--line);display:block;margin-bottom:14px}

/* SweetAlert overrides */
.swal2-popup{border-radius:20px!important;font-family:var(--font)!important}
.swal2-title{font-family:var(--font)!important;font-weight:800!important;color:var(--ink)!important}
.swal2-html-container{color:var(--ash)!important}
.swal2-confirm{border-radius:50px!important;font-weight:700!important;background:#dc3545!important;box-shadow:none!important;padding:10px 26px!important}
.swal2-cancel{border-radius:50px!important;font-weight:700!important;background:var(--paper)!important;color:var(--ink)!important;box-shadow:none!important;padding:10px 26px!important}
</style>

<div class="pm-topbar topbar">
  <div>
    <span class="topbar-eyebrow">Participant records</span>
    <h1>Manage Participants</h1>
    <p>View, edit and delete participant records.</p>
  </div>
  <div class="pm-filter-wrap">
    <i class="bi bi-search"></i>
    <input type="text" id="pmFilter" class="pm-filter" placeholder="Filter by name, email or club…">
  </div>
</div>

<div class="pm-meta">
  <h3>All Participants</h3>
  <span class="pm-count" id="pmCount"></span>
</div>

<?php
try {
  $pdo=new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
    $username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $st=$pdo->query("SELECT p.*,c.name AS club_name FROM participant p
     LEFT JOIN club c ON c.id=p.club_id ORDER BY p.firstname");
  $rows=$st->fetchAll(PDO::FETCH_ASSOC);

  if($rows){
    echo '<div class="pm-grid" id="pmGrid">';
    foreach($rows as $i=>$r){
      $ini=strtoupper(substr($r['firstname'],0,1).substr($r['surname'],0,1));
      $clubClass=$r['club_name']?'has':'none';
      $clubName=$r['club_name']?htmlspecialchars($r['club_name']):'No club';
      $search=strtolower($r['firstname'].' '.$r['surname'].' '.$r['email'].' '.($r['club_name']??''));
      echo '<div class="pm-card" data-search="'.$search.'" style="animation-delay:'.($i*.035).'s">
              <div class="pm-card-top">
                <div class="pm-av">'.$ini.'</div>
                <div>
                  <p class="pm-name">'.htmlspecialchars($r['firstname'].' '.$r['surname']).'</p>
                  <span class="pm-id">ID #'.$r['id'].'</span>
                </div>
              </div>
              <div class="pm-email"><i class="bi bi-envelope-fill"></i>'.htmlspecialchars($r['email']).'</div>
              <span class="pm-club-tag '.$clubClass.'"><i class="bi bi-flag-fill"></i>'.$clubName.'</span>
              <div class="pm-stats">
                <div class="pm-stat"><span class="pm-stat-val">'.$r['power_output'].'</span><span class="pm-stat-lbl">Power (W)</span></div>
                <div class="pm-stat"><span class="pm-stat-val">'.$r['distance'].'</span><span class="pm-stat-lbl">Distance (km)</span></div>
              </div>
              <div class="pm-actions">
                <a href="edit_participant.php?id='.$r['id'].'" class="pm-btn pm-btn-edit"><i class="bi bi-pencil-fill"></i> Edit</a>
                <button class="pm-btn pm-btn-del" onclick="doDelete('.$r['id'].')"><i class="bi bi-trash3-fill"></i> Delete</button>
              </div>
            </div>';
    }
    echo '</div>';
  } else {
    echo '<div class="pm-empty"><i class="bi bi-inbox"></i><p>No participants found.</p></div>';
  }
} catch(PDOException $e){
  echo '<div style="background:#fdecec;border:1px solid #f5c6cb;color:#8a1f28;border-radius:14px;padding:16px 20px;font-size:.9rem">'.htmlspecialchars($e->getMessage()).'</div>';
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  var grid=document.getElementById('pmGrid');
  var countEl=document.getElementById('pmCount');
  var cards=grid?Array.from(grid.querySelectorAll('.pm-card')):[];
  function upd(n){if(countEl)countEl.textContent=n+(n===1?' record':' records');}
  upd(cards.length);
  var fi=document.getElementById('pmFilter');
  if(fi&&grid){fi.addEventListener('input',function(){
    var q=fi.value.trim().toLowerCase();var n=0;
    cards.forEach(function(c){var m=c.getAttribute('data-search').includes(q);c.style.display=m?'':'none';if(m)n++;});
    upd(n);
  });}
})();

function doDelete(id){
  Swal.fire({
    title:'Delete this participant?',
    html:'This will permanently remove the record.<br><strong>This cannot be undone.</strong>',
    icon:'warning',showCancelButton:true,reverseButtons:true,
    confirmButtonText:'<i class="bi bi-trash3-fill"></i> Delete',
    cancelButtonText:'Cancel',buttonsStyling:false,
    customClass:{confirmButton:'swal2-confirm',cancelButton:'swal2-cancel'}
  }).then(function(r){if(r.isConfirmed)window.location.href='delete.php?id='+id;});
}
</script>