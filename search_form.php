<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }

// ── AJAX handler — runs when called via fetch ──
if (!empty($_POST['sf_ajax'])) {
    header('Content-Type: application/json');
    $type = trim($_POST['type'] ?? '');
    $term = trim($_POST['term'] ?? '');
    if (!$type || !$term) { echo json_encode(['rows'=>[],'error'=>'Missing params']); exit(); }
    try {
        include_once 'dbconnect.php';
        $db = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
            $username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        if ($type==='participant') {
            $s=$db->prepare("SELECT p.id,p.firstname,p.surname,p.email,p.power_output,p.distance,c.name AS club_name FROM participant p LEFT JOIN club c ON c.id=p.club_id WHERE p.firstname LIKE :t OR p.surname LIKE :t ORDER BY p.firstname");
            $s->execute([':t'=>'%'.$term.'%']);
            echo json_encode(['rows'=>$s->fetchAll(PDO::FETCH_ASSOC),'error'=>'']);
        } else {
            $s=$db->prepare("SELECT * FROM club WHERE name LIKE :t ORDER BY name");
            $s->execute([':t'=>'%'.$term.'%']);
            $clubs=$s->fetchAll(PDO::FETCH_ASSOC);
            foreach($clubs as &$c){ $cs=$db->prepare("SELECT COUNT(*) FROM participant WHERE club_id=:id"); $cs->execute([':id'=>$c['id']]); $c['member_count']=(int)$cs->fetchColumn(); } unset($c);
            echo json_encode(['rows'=>$clubs,'error'=>'']);
        }
    } catch(PDOException $e){ echo json_encode(['rows'=>[],'error'=>$e->getMessage()]); }
    exit();
}

// ── Quick clubs for chips ──
$quickClubs=[];
if(!empty($pdo)){ try{ $quickClubs=$pdo->query("SELECT name FROM club ORDER BY name LIMIT 8")->fetchAll(PDO::FETCH_COLUMN); }catch(PDOException $e){} }
?>
<style>
.sf-head{margin-bottom:28px}
.sf-ey{display:inline-flex;align-items:center;gap:6px;font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:#16a34a;margin-bottom:8px}
.sf-ey::before{content:'';width:16px;height:2px;background:#16a34a;border-radius:2px}
.sf-head h1{font-family:'Unbounded',system-ui,sans-serif;font-size:2rem;font-weight:900;letter-spacing:-.04em;color:#0f172a;margin-bottom:5px}
.sf-head p{color:#64748b;font-size:.9rem;font-weight:500}
.sf-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
.sf-card{background:#fff;border:1.5px solid rgba(15,23,42,.09);border-radius:22px;padding:28px 26px 24px;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:box-shadow .2s,border-color .2s}
.sf-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);border-color:rgba(22,163,74,.22)}
.sf-ico{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:16px}
.sf-ico-g{background:rgba(22,163,74,.1);color:#16a34a}
.sf-ico-a{background:rgba(245,158,11,.12);color:#d97706}
.sf-card h2{font-family:'Unbounded',system-ui,sans-serif;font-size:1.05rem;font-weight:800;letter-spacing:-.02em;color:#0f172a;margin-bottom:3px}
.sf-sub{font-size:.82rem;color:#64748b;margin-bottom:18px;font-weight:500}
.sf-fi{position:relative;margin-bottom:14px}
.sf-fi i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.9rem;pointer-events:none}
.sf-fi input{width:100%;padding:12px 14px 12px 40px;background:#f0f4f0;border:1.5px solid rgba(15,23,42,.09);border-radius:10px;font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-size:.9rem;color:#0f172a;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
.sf-fi input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15);background:#fff}
.sf-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border:none;border-radius:10px;font-family:'Unbounded',system-ui,sans-serif;font-weight:700;font-size:.88rem;cursor:pointer;transition:transform .12s,box-shadow .18s,background .18s}
.sf-btn:active{transform:scale(.97)}
.sf-bg{background:#16a34a;color:#fff;box-shadow:0 4px 14px rgba(22,163,74,.28)}
.sf-bg:hover{background:#15803d;color:#fff;transform:translateY(-1px)}
.sf-ba{background:#f59e0b;color:#1a0a00;box-shadow:0 4px 14px rgba(245,158,11,.28)}
.sf-ba:hover{background:#d97706;color:#1a0a00;transform:translateY(-1px)}
.sf-qw{background:#fff;border:1.5px solid rgba(15,23,42,.09);border-radius:22px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.sf-qt{font-family:'Unbounded',system-ui,sans-serif;font-size:.88rem;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px;margin-bottom:13px}
.sf-qt i{color:#d97706}
.sf-chips{display:flex;flex-wrap:wrap;gap:8px}
.sf-chip{display:inline-flex;align-items:center;gap:5px;padding:7px 15px;border-radius:50px;background:#f0f4f0;border:1.5px solid rgba(15,23,42,.09);color:#0f172a;font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .14s,border-color .14s,color .14s,transform .12s}
.sf-chip i{font-size:.62rem;color:#d97706;transition:color .14s}
.sf-chip:hover{background:#16a34a;border-color:#16a34a;color:#fff;transform:translateY(-1px)}
.sf-chip:hover i{color:#fff}
/* Drawer */
.sfd-back{position:fixed;inset:0;z-index:8000;background:rgba(8,14,24,.5);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);opacity:0;pointer-events:none;transition:opacity .28s ease}
.sfd-back.open{opacity:1;pointer-events:all}
.sfd{position:fixed;top:0;right:0;bottom:0;z-index:8001;width:500px;max-width:96vw;background:#f8faf7;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .34s cubic-bezier(.22,1,.36,1);box-shadow:-20px 0 60px rgba(8,14,24,.2);border-left:1px solid rgba(15,23,42,.07)}
.sfd.open{transform:translateX(0)}
.sfd-head{display:flex;align-items:center;justify-content:space-between;padding:20px 22px 16px;background:#fff;border-bottom:1px solid rgba(15,23,42,.08);flex-shrink:0}
.sfd-title{font-family:'Unbounded',system-ui,sans-serif;font-size:.92rem;font-weight:800;color:#0f172a;letter-spacing:-.02em}
.sfd-meta{font-family:'JetBrains Mono',monospace;font-size:.66rem;color:#64748b;margin-top:3px}
.sfd-close{width:34px;height:34px;border-radius:50%;border:1.5px solid rgba(15,23,42,.1);background:#f0f4f0;color:#64748b;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.9rem;transition:background .15s,color .15s}
.sfd-close:hover{background:#ef4444;color:#fff;border-color:#ef4444}
.sfd-q{padding:10px 22px;background:#fff;border-bottom:1px solid rgba(15,23,42,.06);font-size:.8rem;color:#64748b;flex-shrink:0}
.sfd-q span{font-weight:700;color:#0f172a;font-family:'JetBrains Mono',monospace}
.sfd-body{flex:1;overflow-y:auto;padding:16px 18px 32px}
.sfd-body::-webkit-scrollbar{width:5px}
.sfd-body::-webkit-scrollbar-thumb{background:rgba(15,23,42,.12);border-radius:10px}
.sfd-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;gap:16px;color:#64748b;font-size:.86rem}
.sfd-spin{width:38px;height:38px;border-radius:50%;border:3px solid rgba(22,163,74,.15);border-top-color:#16a34a;animation:sfSpin .8s linear infinite}
@keyframes sfSpin{to{transform:rotate(360deg)}}
.sfd-card{background:#fff;border:1.5px solid rgba(15,23,42,.08);border-radius:16px;padding:13px 15px;margin-bottom:9px;display:flex;align-items:center;gap:12px;transition:border-color .18s,box-shadow .18s,transform .18s;animation:sfIn .32s ease both}
.sfd-card:hover{border-color:#16a34a;box-shadow:0 4px 14px rgba(22,163,74,.09);transform:translateX(-3px)}
@keyframes sfIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
.sfd-av{width:40px;height:40px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-weight:800;font-size:.84rem;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(22,163,74,.22)}
.sfd-av.club{background:linear-gradient(135deg,#d97706,#f59e0b);color:#1a0a00;border-radius:11px}
.sfd-b{flex:1;min-width:0}
.sfd-name{font-weight:700;font-size:.88rem;color:#0f172a;margin-bottom:2px}
.sfd-meta2{display:flex;flex-wrap:wrap;gap:9px;font-size:.74rem;color:#64748b}
.sfd-meta2 span{display:flex;align-items:center;gap:3px}
.sfd-badges{display:flex;gap:5px;flex-shrink:0}
.sfd-badge{background:#f0f4f0;border:1px solid rgba(15,23,42,.08);border-radius:50px;padding:4px 9px;font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;color:#15803d}
.sfd-edit{display:inline-flex;align-items:center;gap:5px;flex-shrink:0;padding:6px 12px;border-radius:50px;border:1.5px solid #16a34a;background:transparent;color:#16a34a;font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.75rem;cursor:pointer;text-decoration:none;transition:background .15s,color .15s}
.sfd-edit:hover{background:#16a34a;color:#fff}
.sfd-empty{text-align:center;padding:50px 20px;color:#64748b}
.sfd-empty i{font-size:2.2rem;color:rgba(15,23,42,.1);display:block;margin-bottom:11px}
.sfd-empty h3{font-size:.93rem;color:#0f172a;margin-bottom:4px}
.sfd-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:13px 16px;font-size:.83rem}
.sfd-type-p{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:50px;font-size:.65rem;font-weight:700;font-family:'JetBrains Mono',monospace;background:rgba(22,163,74,.1);color:#15803d}
.sfd-type-c{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:50px;font-size:.65rem;font-weight:700;font-family:'JetBrains Mono',monospace;background:rgba(245,158,11,.12);color:#b45309}
@media(max-width:700px){.sf-grid{grid-template-columns:1fr}.sfd{width:100%;max-width:100%}}
</style>

<div class="sf-head">
    <span class="sf-ey">Find records</span>
    <h1>Search Participants &amp; Clubs</h1>
    <p>Look up an individual rider or an entire club in seconds.</p>
</div>

<div class="sf-grid">
    <div class="sf-card">
        <div class="sf-ico sf-ico-g"><i class="bi bi-person-fill"></i></div>
        <h2>Search a Participant</h2>
        <p class="sf-sub">Find by first name or surname.</p>
        <div class="sf-fi"><i class="bi bi-search"></i><input type="text" id="sfPI" placeholder="e.g. Lorette or Stavers" autocomplete="off"></div>
        <button class="sf-btn sf-bg" onclick="sfGo('participant')"><i class="bi bi-search"></i> Search Participant</button>
    </div>
    <div class="sf-card">
        <div class="sf-ico sf-ico-a"><i class="bi bi-flag-fill"></i></div>
        <h2>Search a Club</h2>
        <p class="sf-sub">Find by club name.</p>
        <div class="sf-fi"><i class="bi bi-flag"></i><input type="text" id="sfCI" placeholder="e.g. Roker Rollers" autocomplete="off"></div>
        <button class="sf-btn sf-ba" onclick="sfGo('club')"><i class="bi bi-flag-fill"></i> Search Club</button>
    </div>
</div>

<div class="sf-qw">
    <div class="sf-qt"><i class="bi bi-lightning-fill"></i> Quick Club Picks</div>
    <div class="sf-chips">
        <?php if($quickClubs): foreach($quickClubs as $cn): ?>
        <button class="sf-chip" onclick="sfQuick(<?=json_encode($cn)?>)"><i class="bi bi-flag-fill"></i><?=htmlspecialchars($cn)?></button>
        <?php endforeach; else: ?>
        <span style="font-size:.84rem;color:#64748b;">No clubs loaded yet.</span>
        <?php endif; ?>
    </div>
</div>

<!-- Drawer -->
<div class="sfd-back" id="sfdBack" onclick="sfClose()"></div>
<div class="sfd" id="sfd">
    <div class="sfd-head">
        <div>
            <div class="sfd-title" id="sfdTitle">Results</div>
            <div class="sfd-meta" id="sfdMeta"></div>
        </div>
        <button class="sfd-close" onclick="sfClose()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sfd-q" id="sfdQ" style="display:none;">Showing results for <span id="sfdQS"></span></div>
    <div class="sfd-body" id="sfdBody"></div>
</div>

<script>
(function(){
    var AJAX_URL = 'admin_menu.php?page=search'; // posts to self (same file included)

    function open(){document.getElementById('sfdBack').classList.add('open');document.getElementById('sfd').classList.add('open');document.addEventListener('keydown',onKey);}
    function sfClose(){document.getElementById('sfdBack').classList.remove('open');document.getElementById('sfd').classList.remove('open');document.removeEventListener('keydown',onKey);}
    window.sfClose=sfClose;
    function onKey(e){if(e.key==='Escape')sfClose();}

    function sfGo(type){
        var val=type==='participant'?document.getElementById('sfPI').value.trim():document.getElementById('sfCI').value.trim();
        if(!val){
            var el=document.getElementById(type==='participant'?'sfPI':'sfCI');
            el.style.borderColor='#ef4444';el.style.boxShadow='0 0 0 3px rgba(239,68,68,.15)';el.focus();
            setTimeout(function(){el.style.borderColor='';el.style.boxShadow='';},1200);return;
        }
        document.getElementById('sfdTitle').textContent=type==='participant'?'Participant Results':'Club Results';
        document.getElementById('sfdMeta').innerHTML=type==='participant'?'<span class="sfd-type-p"><i class="bi bi-person-fill"></i> Participant</span>':'<span class="sfd-type-c"><i class="bi bi-flag-fill"></i> Club</span>';
        document.getElementById('sfdQS').textContent='"'+val+'"';
        document.getElementById('sfdQ').style.display='block';
        document.getElementById('sfdBody').innerHTML='<div class="sfd-loading"><div class="sfd-spin"></div><span>Searching…</span></div>';
        open();
        var fd=new FormData();fd.append('sf_ajax','1');fd.append('type',type);fd.append('term',val);
        fetch('admin_menu.php?page=search',{method:'POST',body:fd})
            .then(function(r){return r.json();})
            .then(function(d){render(d,type);})
            .catch(function(){document.getElementById('sfdBody').innerHTML='<div class="sfd-err"><i class="bi bi-exclamation-triangle-fill"></i> Search failed. Please try again.</div>';});
    }
    window.sfGo=sfGo;

    function sfQuick(n){document.getElementById('sfCI').value=n;sfGo('club');}
    window.sfQuick=sfQuick;

    document.getElementById('sfPI').addEventListener('keydown',function(e){if(e.key==='Enter')sfGo('participant');});
    document.getElementById('sfCI').addEventListener('keydown',function(e){if(e.key==='Enter')sfGo('club');});

    function render(data,type){
        var body=document.getElementById('sfdBody');
        var rows=data.rows||[];
        if(data.error){body.innerHTML='<div class="sfd-err"><i class="bi bi-exclamation-triangle-fill"></i> '+esc(data.error)+'</div>';return;}
        document.getElementById('sfdMeta').innerHTML=(type==='participant'?'<span class="sfd-type-p"><i class="bi bi-person-fill"></i> Participant</span>':'<span class="sfd-type-c"><i class="bi bi-flag-fill"></i> Club</span>')+' &nbsp;<span style="font-family:\'JetBrains Mono\',monospace;font-size:.66rem;color:#64748b;">'+rows.length+' result'+(rows.length!==1?'s':'')+'</span>';
        if(!rows.length){body.innerHTML='<div class="sfd-empty"><i class="bi bi-'+(type==='participant'?'person-x':'flag')+'"></i><h3>No '+(type==='participant'?'participant':'club')+' found</h3><p>Try a different name — partial matches work.</p></div>';return;}
        var html='';
        rows.forEach(function(row,i){
            var d=(i*.05).toFixed(2);
            if(type==='participant'){
                var ini=((row.firstname||'').charAt(0)+(row.surname||'').charAt(0)).toUpperCase();
                html+='<div class="sfd-card" style="animation-delay:'+d+'s;"><div class="sfd-av">'+esc(ini)+'</div><div class="sfd-b"><div class="sfd-name">'+esc(row.firstname+' '+row.surname)+'</div><div class="sfd-meta2"><span><i class="bi bi-envelope-fill"></i>'+esc(row.email)+'</span>'+(row.club_name?'<span><i class="bi bi-flag-fill"></i>'+esc(row.club_name)+'</span>':'<span><i class="bi bi-dash-circle"></i>No club</span>')+'</div></div><div class="sfd-badges"><span class="sfd-badge">'+esc(row.power_output)+' W</span><span class="sfd-badge">'+esc(row.distance)+' km</span></div><a href="edit_participant_form.php?id='+parseInt(row.id)+'" class="sfd-edit"><i class="bi bi-pencil-square"></i> Edit</a></div>';
            } else {
                html+='<div class="sfd-card" style="animation-delay:'+d+'s;"><div class="sfd-av club"><i class="bi bi-flag-fill"></i></div><div class="sfd-b"><div class="sfd-name">'+esc(row.name)+'</div><div class="sfd-meta2"><span><i class="bi bi-geo-alt-fill"></i>'+esc(row.location||'')+'</span></div></div><div class="sfd-badges"><span class="sfd-badge">'+parseInt(row.member_count||0)+' riders</span></div></div>';
            }
        });
        body.innerHTML=html;
    }

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
})();
</script>