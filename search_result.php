<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['username'])) { header("Location: admin_login.html"); exit(); }

$quickClubs = [];
if (!empty($pdo)) {
    try {
        $quickClubs = $pdo->query("SELECT name FROM club ORDER BY name LIMIT 8")
                         ->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}
?>
<!--
  FIX: the icons (bi-person-fill, bi-search, bi-flag-fill, etc.) and the
  'Unbounded' / 'Inter' fonts referenced in the CSS below were never being
  loaded anywhere — that's why they showed up as blank squares. This file
  is included into your admin layout, so if your admin header/layout file
  already loads Bootstrap Icons + these fonts, you can delete this block.
  Otherwise leave it here; it's safe even injected mid-body.
-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700;800;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
.sf-head { margin-bottom: 28px; }
.sf-head__ey {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'JetBrains Mono', monospace; font-size: 0.68rem;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: #16a34a; margin-bottom: 8px;
}
.sf-head__ey::before {
    content: ''; width: 16px; height: 2px;
    background: #16a34a; border-radius: 2px;
}
.sf-head h1 {
    font-family: 'Unbounded', system-ui, sans-serif; font-size: 2rem;
    font-weight: 900; letter-spacing: -0.04em;
    color: #0f172a; margin-bottom: 5px;
}
.sf-head p { color: #64748b; font-size: 0.9rem; font-weight: 500; }

.sf-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}

.sf-card {
    background: #ffffff;
    border: 1.5px solid rgba(15,23,42,0.09);
    border-radius: 22px;
    padding: 28px 26px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, border-color 0.2s;
}
.sf-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    border-color: rgba(22,163,74,.22);
}

.sf-card__icon {
    width: 50px; height: 50px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; margin-bottom: 16px;
}
.sf-card__icon--green { background: rgba(22,163,74,.10); color: #16a34a; }
.sf-card__icon--amber { background: rgba(245,158,11,.12); color: #d97706; }

.sf-card h2 {
    font-family: 'Unbounded', system-ui, sans-serif; font-size: 1.05rem;
    font-weight: 800; letter-spacing: -0.02em;
    color: #0f172a; margin-bottom: 3px;
}
.sf-card__sub {
    font-size: 0.82rem; color: #64748b;
    margin-bottom: 18px; font-weight: 500;
}

.sf-field { position: relative; margin-bottom: 14px; }
.sf-field i {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8; font-size: 0.9rem; pointer-events: none;
}
.sf-field input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    background: #f0f4f0;
    border: 1.5px solid rgba(15,23,42,0.09);
    border-radius: 10px;
    font-family: 'Inter', system-ui, sans-serif; font-size: 0.9rem;
    color: #0f172a; outline: none;
    transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
}
.sf-field input::placeholder { color: #94a3b8; }
.sf-field input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,.22);
    background: #fff;
}

.sf-btn {
    width: 100%; display: flex; align-items: center;
    justify-content: center; gap: 8px;
    padding: 12px 20px; border: none;
    border-radius: 10px;
    font-family: 'Unbounded', system-ui, sans-serif; font-weight: 700;
    font-size: 0.88rem; cursor: pointer;
    transition: transform 0.12s, box-shadow 0.18s, background 0.18s;
    letter-spacing: -0.01em;
}
.sf-btn:active { transform: scale(0.97); }
.sf-btn--green {
    background: #16a34a; color: #fff;
    box-shadow: 0 4px 14px rgba(22,163,74,.28);
}
.sf-btn--green:hover {
    background: #15803d; color: #fff;
    box-shadow: 0 6px 20px rgba(22,163,74,.38);
    transform: translateY(-1px);
}
.sf-btn--amber {
    background: #f59e0b; color: #1a0a00;
    box-shadow: 0 4px 14px rgba(245,158,11,.28);
}
.sf-btn--amber:hover {
    background: #d97706; color: #1a0a00;
    box-shadow: 0 6px 20px rgba(245,158,11,.38);
    transform: translateY(-1px);
}

.sf-quick {
    background: #ffffff;
    border: 1.5px solid rgba(15,23,42,0.09);
    border-radius: 22px;
    padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}
.sf-quick__title {
    font-family: 'Unbounded', system-ui, sans-serif; font-size: 0.88rem;
    font-weight: 800; color: #0f172a;
    display: flex; align-items: center; gap: 7px;
    margin-bottom: 13px;
}
.sf-quick__title i { color: #d97706; }

.sf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.sf-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 15px; border-radius: 50px;
    background: #f0f4f0; border: 1.5px solid rgba(15,23,42,0.09);
    color: #0f172a; font-family: 'Inter', system-ui, sans-serif;
    font-size: 0.8rem; font-weight: 600; cursor: pointer;
    transition: background 0.14s, border-color 0.14s, color 0.14s, transform 0.12s, box-shadow 0.14s;
}
.sf-chip i { font-size: 0.62rem; color: #d97706; transition: color 0.14s; }
.sf-chip:hover {
    background: #16a34a; border-color: #16a34a; color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22,163,74,.22);
}
.sf-chip:hover i { color: #fff; }

@media(max-width: 700px) { .sf-grid { grid-template-columns: 1fr; } }
</style>

<div class="sf-head">
    <span class="sf-head__ey">Find records</span>
    <h1>Search Participants &amp; Clubs</h1>
    <p>Look up an individual rider or an entire club in seconds.</p>
</div>

<div class="sf-grid">

    <div class="sf-card">
        <div class="sf-card__icon sf-card__icon--green">
            <i class="bi bi-person-fill"></i>
        </div>
        <h2>Search a Participant</h2>
        <p class="sf-card__sub">Find by first name or surname.</p>
        <form action="search_result.php" method="POST">
            <div class="sf-field">
                <i class="bi bi-search"></i>
                <input type="text" name="firstname" placeholder="e.g. Lorette or Stavers" required>
            </div>
            <input type="hidden" name="participant" value="1">
            <button type="submit" class="sf-btn sf-btn--green">
                <i class="bi bi-search"></i> Search Participant
            </button>
        </form>
    </div>

    <div class="sf-card">
        <div class="sf-card__icon sf-card__icon--amber">
            <i class="bi bi-flag-fill"></i>
        </div>
        <h2>Search a Club</h2>
        <p class="sf-card__sub">Find by club name.</p>
        <form action="search_result.php" method="POST">
            <div class="sf-field">
                <i class="bi bi-flag"></i>
                <input type="text" name="club" placeholder="e.g. Roker Rollers" required>
            </div>
            <button type="submit" class="sf-btn sf-btn--amber">
                <i class="bi bi-flag-fill"></i> Search Club
            </button>
        </form>
    </div>

</div>

<?php if (!empty($quickClubs)): ?>
<div class="sf-quick">
    <div class="sf-quick__title">
        <i class="bi bi-lightning-fill"></i> Quick Club Picks
    </div>
    <div class="sf-chips">
        <?php foreach ($quickClubs as $cn): ?>
        <form action="search_result.php" method="POST" style="margin:0;">
            <input type="hidden" name="club" value="<?= htmlspecialchars($cn) ?>">
            <button type="submit" class="sf-chip">
                <i class="bi bi-flag-fill"></i>
                <?= htmlspecialchars($cn) ?>
            </button>
        </form>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="sf-quick">
    <div class="sf-quick__title"><i class="bi bi-lightning-fill"></i> Quick Club Picks</div>
    <span style="font-size:.84rem;color:#64748b;">No clubs loaded.</span>
</div>
<?php endif; ?>