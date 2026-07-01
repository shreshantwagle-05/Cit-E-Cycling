<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }

include_once 'dbconnect.php';

$errors  = [];
$success = false;
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row     = null;
$clubs   = [];

if (!$id) {
    header("Location: admin_menu.php?page=manage");
    exit();
}

try {
    $db = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
        $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $clubs = $db->query("SELECT id, name FROM club ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT p.*, c.name AS club_name FROM participant p LEFT JOIN club c ON c.id=p.club_id WHERE p.id=:id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) { header("Location: admin_menu.php?page=manage"); exit(); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_participant'])) {
        $firstname    = trim($_POST['firstname']    ?? '');
        $surname      = trim($_POST['surname']      ?? '');
        $email        = trim($_POST['email']        ?? '');
        $club_id      = $_POST['club_id'] !== '' ? (int)$_POST['club_id'] : null;
        $power_output = trim($_POST['power_output'] ?? '');
        $distance     = trim($_POST['distance']     ?? '');

        if (empty($firstname))    $errors['firstname']    = 'First name is required.';
        if (empty($surname))      $errors['surname']      = 'Surname is required.';
        if (empty($email))        $errors['email']        = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
        if ($power_output === '') $errors['power_output'] = 'Power output is required.';
        elseif (!is_numeric($power_output) || (float)$power_output < 0) $errors['power_output'] = 'Must be a positive number.';
        if ($distance === '')     $errors['distance']     = 'Distance is required.';
        elseif (!is_numeric($distance) || (float)$distance < 0) $errors['distance'] = 'Must be a positive number.';

        if (empty($errors['email'])) {
            $chk = $db->prepare("SELECT id FROM participant WHERE email=:e AND id!=:id");
            $chk->execute([':e'=>$email,':id'=>$id]);
            if ($chk->fetch()) $errors['email'] = 'This email is already used by another participant.';
        }

        if (empty($errors)) {
            $upd = $db->prepare("UPDATE participant SET firstname=:fn,surname=:sn,email=:em,club_id=:cl,power_output=:po,distance=:di WHERE id=:id");
            $upd->execute([':fn'=>$firstname,':sn'=>$surname,':em'=>$email,':cl'=>$club_id,':po'=>(float)$power_output,':di'=>(float)$distance,':id'=>$id]);
            // Reload fresh data for success display
            $stmt2 = $db->prepare("SELECT p.*, c.name AS club_name FROM participant p LEFT JOIN club c ON c.id=p.club_id WHERE p.id=:id");
            $stmt2->execute([':id'=>$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            $success = true;
        } else {
            // Keep posted values
            $row['firstname']    = $firstname;
            $row['surname']      = $surname;
            $row['email']        = $email;
            $row['club_id']      = $club_id;
            $row['power_output'] = $power_output;
            $row['distance']     = $distance;
        }
    }
} catch (PDOException $e) {
    die('<p style="color:red;padding:20px;">DB error: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

$ini = strtoupper(substr($row['firstname'],0,1) . substr($row['surname'],0,1));
?>
<style>
/* ── Edit form — self-contained ── */
.ep-wrap { max-width: 680px; }
.ep-head { margin-bottom: 26px; }
.ep-ey { display:inline-flex;align-items:center;gap:6px;font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:#16a34a;margin-bottom:7px; }
.ep-ey::before { content:'';width:16px;height:2px;background:#16a34a;border-radius:2px; }
.ep-head h1 { font-family:'Unbounded',system-ui,sans-serif;font-size:1.6rem;font-weight:900;letter-spacing:-.03em;color:#0f172a;margin-bottom:4px; }
.ep-head p  { color:#64748b;font-size:.9rem; }

/* ── SUCCESS STATE ── */
.ep-success {
    background:#fff;border:1.5px solid rgba(15,23,42,.08);border-radius:24px;
    padding:44px 36px;text-align:center;
    animation:epIn .45s cubic-bezier(.22,1,.36,1);
}
@keyframes epIn { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

.ep-suc-ring {
    width:76px;height:76px;border-radius:50%;
    background:rgba(22,163,74,.08);
    border:2px solid rgba(22,163,74,.25);
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 20px;
    animation:epPop .5s .1s cubic-bezier(.34,1.56,.64,1) both;
    box-shadow:0 0 0 0 rgba(22,163,74,.3);
    animation:epPop .5s .1s cubic-bezier(.34,1.56,.64,1) both, epGlow 2.4s 0.6s ease-in-out infinite;
}
@keyframes epPop { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }
@keyframes epGlow {
    0%,100% { box-shadow:0 0 0 0 rgba(22,163,74,.25); }
    50%      { box-shadow:0 0 0 12px rgba(22,163,74,0); }
}
.ep-suc-ring i { font-size:2rem;color:#16a34a;filter:drop-shadow(0 0 8px rgba(22,163,74,.4)); }

.ep-suc-title { font-family:'Unbounded',system-ui,sans-serif;font-size:1.5rem;font-weight:900;color:#16a34a;letter-spacing:-.025em;margin-bottom:8px; }
.ep-suc-sub   { font-size:.9rem;color:#64748b;margin-bottom:26px;line-height:1.6; }
.ep-suc-sub strong { color:#0f172a; }

/* Info table */
.ep-suc-info {
    background:#f8faf7;border:1px solid rgba(22,163,74,.14);
    border-radius:16px;padding:6px 0;margin-bottom:26px;text-align:left;
}
.ep-suc-row {
    display:flex;align-items:center;justify-content:space-between;
    padding:11px 20px;border-bottom:1px solid rgba(15,23,42,.05);
    font-size:.87rem;
}
.ep-suc-row:last-child { border-bottom:none; }
.ep-suc-lbl { display:flex;align-items:center;gap:8px;color:#64748b;font-weight:500; }
.ep-suc-lbl i { color:#16a34a;font-size:.9rem;width:16px;text-align:center; }
.ep-suc-val { font-weight:700;color:#0f172a; }

/* Action buttons */
.ep-suc-btns { display:flex;gap:10px;justify-content:center;flex-wrap:wrap; }
.ep-btn {
    display:inline-flex;align-items:center;gap:7px;
    padding:11px 22px;border-radius:50px;border:none;cursor:pointer;
    font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-weight:700;font-size:.87rem;
    transition:all .16s;text-decoration:none;
}
.ep-btn:active { transform:scale(.97); }
.ep-btn-primary { background:#16a34a;color:#fff;box-shadow:0 4px 14px rgba(22,163,74,.28); }
.ep-btn-primary:hover { background:#15803d;color:#fff;box-shadow:0 6px 20px rgba(22,163,74,.36);transform:translateY(-1px); }
.ep-btn-outline { background:transparent;color:#16a34a;border:1.5px solid #16a34a; }
.ep-btn-outline:hover { background:#16a34a;color:#fff; }
.ep-btn-muted { background:#f0f4f0;color:#64748b;border:1px solid rgba(15,23,42,.09); }
.ep-btn-muted:hover { background:#e2e8e0;color:#0f172a; }

/* ── FORM STATE ── */
.ep-card {
    background:#fff;border:1.5px solid rgba(15,23,42,.08);
    border-radius:24px;overflow:hidden;
    box-shadow:0 2px 8px rgba(15,23,42,.06);
}
.ep-card-head {
    background:linear-gradient(130deg,#080f1a 0%,#0a1a10 55%,#0f3320 100%);
    padding:24px 28px;display:flex;align-items:center;gap:15px;position:relative;overflow:hidden;
}
.ep-card-head::before { content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:28px 28px;pointer-events:none; }
.ep-cav { width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.22);color:#fff;font-family:'Unbounded',sans-serif;font-weight:800;font-size:1.1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;z-index:1; }
.ep-cname { font-family:'Unbounded',system-ui,sans-serif;font-weight:800;font-size:1.1rem;color:#fff;margin-bottom:3px;position:relative;z-index:1; }
.ep-cid   { font-family:'JetBrains Mono',monospace;font-size:.66rem;color:rgba(255,255,255,.42);position:relative;z-index:1; }

.ep-body { padding:28px; }

/* Section label */
.ep-sect { display:flex;align-items:center;gap:10px;font-size:.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin:22px 0 16px; }
.ep-sect::before,.ep-sect::after { content:'';flex:1;height:1px;background:#e2e8e0; }

/* Score highlight */
.ep-score-box { background:linear-gradient(135deg,rgba(22,163,74,.07),rgba(22,163,74,.03));border:1.5px solid rgba(22,163,74,.15);border-radius:14px;padding:18px;margin-bottom:18px; }
.ep-score-box-title { display:flex;align-items:center;gap:7px;font-weight:700;font-size:.82rem;color:#15803d;margin-bottom:14px; }

.ep-row2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.ep-fg   { margin-bottom:17px; }
.ep-fg:last-child { margin-bottom:0; }
.ep-label { display:block;font-weight:700;font-size:.8rem;color:#0f172a;margin-bottom:6px; }
.ep-req   { color:#ef4444;margin-left:2px; }
.ep-input, .ep-select {
    width:100%;padding:11px 13px;border:1.5px solid #e2e8e0;border-radius:11px;
    font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-size:.88rem;color:#0f172a;
    background:#fff;outline:none;transition:border-color .18s,box-shadow .18s;
}
.ep-input:focus,.ep-select:focus { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12); }
.ep-input.err { border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.10); }
.ep-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:34px;cursor:pointer; }
.ep-ferr  { font-size:.74rem;color:#ef4444;margin-top:4px;display:none; }
.ep-ferr.show { display:block; }
.ep-hint  { font-size:.74rem;color:#94a3b8;margin-top:4px; }

/* Error banner */
.ep-err-banner { display:flex;align-items:flex-start;gap:9px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:13px 16px;font-size:.86rem;margin-bottom:18px; }
.ep-err-banner i { flex-shrink:0;margin-top:1px; }
.ep-err-banner ul { margin:6px 0 0 14px;padding:0; }
.ep-err-banner li { margin-bottom:2px; }

.ep-actions { display:flex;gap:10px;padding-top:20px;border-top:1px solid #e2e8e0;margin-top:22px; }
.ep-actions .ep-btn { border-radius:12px;padding:12px 20px; }
.ep-btn-submit { background:#16a34a;color:#fff;box-shadow:0 4px 14px rgba(22,163,74,.26);flex:1;justify-content:center; }
.ep-btn-submit:hover { background:#15803d;color:#fff; }
.ep-btn-cancel { background:#f0f4f0;color:#64748b;border:1px solid #e2e8e0;padding:12px 18px;border-radius:12px; }
.ep-btn-cancel:hover { background:#e2e8e0;color:#0f172a; }

@media(max-width:600px){.ep-row2{grid-template-columns:1fr}.ep-suc-btns{flex-direction:column}}
</style>

<div class="ep-wrap">

    <div class="ep-head">
        <span class="ep-ey">Participant Management</span>
        <h1><?= $success ? 'Scores Updated!' : 'Update Rider Scores' ?></h1>
        <p><?= $success ? 'Changes saved successfully to the database.' : 'Edit power output and distance for the selected participant.' ?></p>
    </div>

    <?php if ($success): ?>
    <!-- ══ SUCCESS STATE ══ -->
    <div class="ep-success">

        <div class="ep-suc-ring">
            <i class="bi bi-check-lg"></i>
        </div>

        <h2 class="ep-suc-title">Participant Updated!</h2>
        <p class="ep-suc-sub">The scores for <strong><?= htmlspecialchars($row['firstname'].' '.$row['surname']) ?></strong> have been saved successfully.</p>

        <div class="ep-suc-info">
            <div class="ep-suc-row">
                <span class="ep-suc-lbl"><i class="bi bi-person-fill"></i> Participant</span>
                <span class="ep-suc-val"><?= htmlspecialchars($row['firstname'].' '.$row['surname']) ?></span>
            </div>
            <div class="ep-suc-row">
                <span class="ep-suc-lbl"><i class="bi bi-lightning-fill"></i> Power Output</span>
                <span class="ep-suc-val"><?= (int)$row['power_output'] ?> W</span>
            </div>
            <div class="ep-suc-row">
                <span class="ep-suc-lbl"><i class="bi bi-signpost-fill"></i> Distance</span>
                <span class="ep-suc-val"><?= (float)$row['distance'] ?> km</span>
            </div>
            <div class="ep-suc-row">
                <span class="ep-suc-lbl"><i class="bi bi-flag-fill"></i> Club</span>
                <span class="ep-suc-val"><?= htmlspecialchars($row['club_name'] ?? 'No club') ?></span>
            </div>
        </div>

        <div class="ep-suc-btns">
            <a href="admin_menu.php?page=manage" class="ep-btn ep-btn-primary">
                <i class="bi bi-people-fill"></i> View All Participants
            </a>
            <a href="edit_participant_form.php?id=<?= $id ?>" class="ep-btn ep-btn-outline">
                <i class="bi bi-pencil-square"></i> Edit Again
            </a>
            <a href="admin_menu.php?page=dashboard" class="ep-btn ep-btn-muted">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
        </div>

    </div>

    <?php else: ?>
    <!-- ══ EDIT FORM ══ -->

    <?php if (!empty($errors)): ?>
    <div class="ep-err-banner">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Fix the following before saving:</strong>
            <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="ep-card">
        <div class="ep-card-head">
            <div class="ep-cav"><?= $ini ?></div>
            <div>
                <div class="ep-cname"><?= htmlspecialchars($row['firstname'].' '.$row['surname']) ?></div>
                <div class="ep-cid">Participant ID #<?= $id ?> · Edit Record</div>
            </div>
        </div>

        <div class="ep-body">
            <form method="POST" action="edit_participant_form.php?id=<?= $id ?>" novalidate>

                <div class="ep-sect">Personal Details</div>

                <div class="ep-row2">
                    <div class="ep-fg">
                        <label class="ep-label" for="epFN">First Name <span class="ep-req">*</span></label>
                        <input class="ep-input <?= isset($errors['firstname'])?'err':'' ?>" type="text" id="epFN" name="firstname" value="<?= htmlspecialchars($row['firstname']) ?>" placeholder="First name" required>
                        <?php if(isset($errors['firstname'])): ?><span class="ep-ferr show"><?= htmlspecialchars($errors['firstname']) ?></span><?php endif; ?>
                    </div>
                    <div class="ep-fg">
                        <label class="ep-label" for="epSN">Surname <span class="ep-req">*</span></label>
                        <input class="ep-input <?= isset($errors['surname'])?'err':'' ?>" type="text" id="epSN" name="surname" value="<?= htmlspecialchars($row['surname']) ?>" placeholder="Surname" required>
                        <?php if(isset($errors['surname'])): ?><span class="ep-ferr show"><?= htmlspecialchars($errors['surname']) ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="ep-fg">
                    <label class="ep-label" for="epEM">Email <span class="ep-req">*</span></label>
                    <input class="ep-input <?= isset($errors['email'])?'err':'' ?>" type="email" id="epEM" name="email" value="<?= htmlspecialchars($row['email']) ?>" placeholder="email@example.com" required>
                    <?php if(isset($errors['email'])): ?><span class="ep-ferr show"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
                </div>

                <div class="ep-fg">
                    <label class="ep-label" for="epCL">Club</label>
                    <select class="ep-select" id="epCL" name="club_id">
                        <option value="">— No club —</option>
                        <?php foreach($clubs as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)$row['club_id']===(int)$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="ep-hint">Leave blank if participant has no club.</span>
                </div>

                <div class="ep-sect">Performance Scores</div>

                <div class="ep-score-box">
                    <div class="ep-score-box-title"><i class="bi bi-lightning-fill"></i> Update Scores — both fields required</div>
                    <div class="ep-row2">
                        <div class="ep-fg">
                            <label class="ep-label" for="epPO">Power Output (W) <span class="ep-req">*</span></label>
                            <input class="ep-input <?= isset($errors['power_output'])?'err':'' ?>" type="number" id="epPO" name="power_output" value="<?= htmlspecialchars($row['power_output']??'') ?>" placeholder="e.g. 250" min="0" step="0.1" required>
                            <?php if(isset($errors['power_output'])): ?><span class="ep-ferr show"><?= htmlspecialchars($errors['power_output']) ?></span><?php endif; ?>
                        </div>
                        <div class="ep-fg">
                            <label class="ep-label" for="epDI">Distance (km) <span class="ep-req">*</span></label>
                            <input class="ep-input <?= isset($errors['distance'])?'err':'' ?>" type="number" id="epDI" name="distance" value="<?= htmlspecialchars($row['distance']??'') ?>" placeholder="e.g. 42.5" min="0" step="0.01" required>
                            <?php if(isset($errors['distance'])): ?><span class="ep-ferr show"><?= htmlspecialchars($errors['distance']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="ep-actions">
                    <button type="submit" name="save_participant" class="ep-btn ep-btn-submit">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="admin_menu.php?page=manage" class="ep-btn ep-btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    <?php endif; ?>

</div>