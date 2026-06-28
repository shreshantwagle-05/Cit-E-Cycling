<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.html");
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.html");
    exit();
}

$page = $_GET['page'] ?? 'dashboard';

/* ── DB connect ── */
$pdo = null; $dbOk = false;
$totalParticipants = 0; $totalClubs = 0; $avgPower = 0; $recentRows = [];

try {
    include_once 'dbconnect.php';
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
        $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $dbOk = true;
    if ($page === 'dashboard') {
        $totalParticipants = $pdo->query("SELECT COUNT(*) FROM participant")->fetchColumn();
        $totalClubs        = $pdo->query("SELECT COUNT(*) FROM club")->fetchColumn();
        $avgPower          = (int)$pdo->query("SELECT ROUND(AVG(power_output)) FROM participant")->fetchColumn();
        $recentRows        = $pdo->query(
            "SELECT p.firstname, p.surname, p.email, c.name AS club
             FROM participant p LEFT JOIN club c ON c.id=p.club_id
             ORDER BY p.id DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $dbOk = false; }

$user = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Cit-E Cycling</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
/* =========================================================================
   CIT-E CYCLING — PREMIUM ADMIN DASHBOARD
   Design tokens, modular BEM-ish sections, glassmorphism, premium effects
   ========================================================================= */

:root{
  --primary:#16a34a;
  --primary-dark:#14532d;
  --primary-light:#22c55e;
  --accent:#f59e0b;
  --accent-dark:#d97706;
  --bg:#f8fafc;
  --card:rgba(255,255,255,.75);
  --card-solid:#fff;
  --text:#0f172a;
  --muted:#64748b;
  --border:rgba(255,255,255,.25);
  --line:#e6ebe4;

  /* legacy aliases kept so search_form.php / view_participants_edit_delete.php
     (which reference these names) keep rendering correctly, unchanged */
  --forest:var(--primary);
  --forest-dark:var(--primary-dark);
  --forest-light:var(--primary-light);
  --amber:var(--accent);
  --amber-dark:var(--accent-dark);
  --ink:var(--text);
  --paper:var(--bg);

  --font-head:'Plus Jakarta Sans','Inter',sans-serif;
  --font-body:'Inter',system-ui,sans-serif;
  --font-mono:'JetBrains Mono',monospace;
}

*,*::before,*::after{ box-sizing:border-box; margin:0; padding:0; }
html{ scroll-behavior:smooth; }

body{
  font-family:var(--font-body);
  color:var(--text);
  background:var(--bg);
  min-height:100vh;
  -webkit-font-smoothing:antialiased;
  opacity:0;
  animation:cdFadeIn .4s ease forwards;
}
@keyframes cdFadeIn{ to{ opacity:1; } }

a{ color:var(--primary); text-decoration:none; }
a:hover{ color:var(--primary-dark); }
img,svg{ display:block; }

/* ---- Ambient mesh background (gives the blur cards something to blur) ---- */
.cd-mesh{ position:fixed; inset:0; z-index:-1; overflow:hidden; pointer-events:none; }
.cd-mesh span{ position:absolute; border-radius:50%; filter:blur(70px); opacity:.18; }
.cd-mesh .m1{ width:480px; height:480px; background:var(--primary-light); top:-160px; left:260px; }
.cd-mesh .m2{ width:380px; height:380px; background:var(--accent); top:40%; right:-140px; }
.cd-mesh .m3{ width:340px; height:340px; background:#60a5fa; bottom:-160px; left:10%; opacity:.12; }

/* =========================================================================
   LAYOUT
   ========================================================================= */
.cd-app{ display:flex; min-height:100vh; }

/* =========================================================================
   SIDEBAR — premium dark glass
   ========================================================================= */
.cd-sidebar{
  width:280px; flex-shrink:0; min-height:100vh;
  position:sticky; top:0; height:100vh;
  background:linear-gradient(200deg, #0a1020 0%, #102032 55%, #0d2a22 100%);
  backdrop-filter:blur(18px);
  border-right:1px solid rgba(255,255,255,.06);
  display:flex; flex-direction:column;
  padding:20px 16px;
  z-index:40;
}

.cd-sidebar__logo{
  display:flex; align-items:center; gap:12px;
  padding:14px; margin-bottom:18px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
  border-radius:16px;
  box-shadow:0 8px 24px rgba(0,0,0,.25);
}
.cd-sidebar__logo-mark{
  width:40px; height:40px; border-radius:12px; flex-shrink:0;
  background:linear-gradient(135deg, var(--primary), var(--primary-light));
  display:flex; align-items:center; justify-content:center;
  font-size:1.1rem; color:#fff;
  box-shadow:0 4px 14px rgba(22,163,74,.45);
}
.cd-sidebar__logo-name{ font-family:var(--font-head); font-weight:800; font-size:.98rem; color:#fff; line-height:1.15; letter-spacing:-.01em; }
.cd-sidebar__logo-sub{ font-family:var(--font-mono); font-size:.62rem; color:rgba(255,255,255,.4); letter-spacing:.08em; text-transform:uppercase; }

.cd-sidebar__home{
  display:flex; align-items:center; gap:10px;
  padding:10px 14px; margin-bottom:18px; border-radius:12px;
  color:rgba(255,255,255,.65); font-weight:500; font-size:.85rem;
  border:1px solid rgba(255,255,255,.08);
  transition:.2s ease;
}
.cd-sidebar__home:hover{ background:rgba(255,255,255,.07); color:#fff; }

.cd-sidebar__label{
  font-family:var(--font-mono); font-size:.62rem; letter-spacing:.09em; text-transform:uppercase;
  color:rgba(255,255,255,.28); padding:0 10px; margin-bottom:10px;
}

.cd-nav{ position:relative; display:flex; flex-direction:column; gap:3px; }
.cd-nav__link{
  position:relative;
  display:flex; align-items:center; gap:12px;
  padding:11px 14px; border-radius:12px;
  color:rgba(255,255,255,.58); font-weight:500; font-size:.87rem;
  transition:color .18s ease, background .18s ease;
}
.cd-nav__link i{ font-size:1rem; width:18px; text-align:center; flex-shrink:0; }
.cd-nav__link:hover{ background:rgba(255,255,255,.06); color:#fff; }
.cd-nav__link.is-active{
  color:#fff; font-weight:600;
  background:linear-gradient(135deg, rgba(22,163,74,.22), rgba(34,197,94,.1));
}
.cd-nav__link.is-active::before{
  content:''; position:absolute; left:-16px; top:6px; bottom:6px; width:3px; border-radius:0 4px 4px 0;
  background:var(--primary-light);
  box-shadow:0 0 10px 1px rgba(34,197,94,.9);
}
.cd-nav__link.is-active .bi{ color:var(--primary-light); }

.cd-nav__soon{
  margin-left:auto; font-family:var(--font-mono); font-size:.6rem; letter-spacing:.04em;
  padding:2px 7px; border-radius:50px; background:rgba(255,255,255,.08); color:rgba(255,255,255,.45);
}

.cd-sidebar__spacer{ flex:1; }

.cd-sidebar__profile{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
  border-radius:16px; padding:13px; margin-bottom:10px;
  display:flex; align-items:center; gap:11px;
}
.cd-sidebar__avatar{
  width:38px; height:38px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg, var(--accent), var(--accent-dark));
  color:#0a1020; display:flex; align-items:center; justify-content:center;
  font-weight:800; font-size:.85rem;
}
.cd-sidebar__uname{ font-size:.86rem; font-weight:700; color:#fff; line-height:1.2; }
.cd-sidebar__urole{ font-size:.7rem; color:rgba(255,255,255,.4); font-family:var(--font-mono); }

.cd-sidebar__logout{
  display:flex; align-items:center; justify-content:center; gap:8px; width:100%;
  padding:11px; border-radius:12px; cursor:pointer;
  background:rgba(220,53,69,.14); border:1px solid rgba(220,53,69,.3);
  color:#ff9aa2; font-weight:700; font-size:.85rem; transition:.2s ease;
}
.cd-sidebar__logout:hover{ background:#dc3545; color:#fff; }

/* =========================================================================
   MAIN
   ========================================================================= */
.cd-main{ flex:1; min-width:0; padding:34px 40px 64px; display:flex; flex-direction:column; gap:26px; position:relative; }

/* ---- Header ---- */
.cd-header{ display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap; }
.cd-header__eyebrow{ font-family:var(--font-mono); font-size:.72rem; letter-spacing:.1em; text-transform:uppercase; color:var(--primary); display:block; margin-bottom:8px; }
.cd-header__title{ font-family:var(--font-head); font-size:44px; font-weight:800; letter-spacing:-2px; line-height:1.05; color:var(--text); }
.cd-header__subtitle{ color:var(--muted); font-size:.95rem; margin-top:8px; font-weight:500; }

.cd-header__tools{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }

.cd-search{
  display:flex; align-items:center; gap:8px;
  background:var(--card); backdrop-filter:blur(16px);
  border:1px solid var(--border); border-radius:50px;
  padding:10px 16px; min-width:220px;
  box-shadow:0 4px 16px rgba(15,23,42,.05);
}
.cd-search i{ color:var(--muted); font-size:.9rem; }
.cd-search input{ border:none; background:transparent; outline:none; font-size:.86rem; width:100%; font-family:var(--font-body); }

.cd-icon-btn{
  width:42px; height:42px; border-radius:50%; flex-shrink:0;
  background:var(--card); backdrop-filter:blur(16px);
  border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  color:var(--text); font-size:1rem; position:relative;
  box-shadow:0 4px 16px rgba(15,23,42,.05);
  transition:transform .15s ease;
}
.cd-icon-btn:hover{ transform:translateY(-2px); }
.cd-icon-btn .cd-dot{
  position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%;
  background:var(--accent); box-shadow:0 0 0 2px #fff;
}

.cd-header__avatar{
  width:42px; height:42px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg, var(--primary), var(--primary-light));
  color:#fff; display:flex; align-items:center; justify-content:center;
  font-weight:700; font-size:.88rem;
  box-shadow:0 4px 16px rgba(22,163,74,.3);
}

.cd-quick-btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:11px 20px; border-radius:50px;
  background:var(--primary); color:#fff; font-weight:700; font-size:.86rem;
  box-shadow:0 6px 18px rgba(22,163,74,.32);
  transition:.18s ease, transform .12s ease;
}
.cd-quick-btn:hover{ background:var(--primary-dark); color:#fff; transform:translateY(-2px); }
.cd-quick-btn:active{ transform:scale(.97); }

.cd-status-row{ display:flex; justify-content:flex-end; }
.cd-status{
  display:inline-flex; align-items:center; gap:8px;
  font-family:var(--font-mono); font-size:.72rem; padding:7px 14px; border-radius:50px;
  border:1px solid var(--border); backdrop-filter:blur(10px);
}
.cd-status.is-live{ background:rgba(22,163,74,.08); border-color:rgba(22,163,74,.22); color:var(--primary-dark); }
.cd-status.is-demo{ background:rgba(245,158,11,.1); border-color:rgba(245,158,11,.25); color:#92660c; }
.cd-status__dot{ width:7px; height:7px; border-radius:50%; }
.cd-status.is-live .cd-status__dot{ background:var(--primary); animation:cdPulse 1.8s ease-in-out infinite; }
.cd-status.is-demo .cd-status__dot{ background:var(--accent); }
@keyframes cdPulse{ 0%{ box-shadow:0 0 0 0 rgba(22,163,74,.5); } 70%{ box-shadow:0 0 0 8px rgba(22,163,74,0); } 100%{ box-shadow:0 0 0 0 rgba(22,163,74,0); } }

/* ---- KPI cards ---- */
.cd-kpi-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
.cd-kpi{
  position:relative;
  background:var(--card); backdrop-filter:blur(20px);
  border:1px solid var(--border); border-radius:20px; padding:22px;
  opacity:0; transform:translateY(14px);
  animation:cdCardIn .5s ease forwards;
  transition:transform .25s ease, box-shadow .25s ease;
  overflow:hidden;
}
.cd-kpi::before{
  content:''; position:absolute; inset:0; border-radius:20px; padding:1px;
  background:linear-gradient(135deg, rgba(22,163,74,.35), rgba(245,158,11,.25), transparent 70%);
  -webkit-mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor; mask-composite:exclude;
  pointer-events:none;
}
.cd-kpi:hover{ transform:translateY(-8px) scale(1.015); box-shadow:0 1.5rem 2.5rem rgba(15,23,42,.12); }
@keyframes cdCardIn{ to{ opacity:1; transform:translateY(0); } }
.cd-kpi:nth-child(1){ animation-delay:.05s; } .cd-kpi:nth-child(2){ animation-delay:.12s; }
.cd-kpi:nth-child(3){ animation-delay:.19s; } .cd-kpi:nth-child(4){ animation-delay:.26s; }

.cd-kpi__top{ display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; }
.cd-kpi__icon{ width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
.cd-kpi.is-green .cd-kpi__icon{ background:rgba(22,163,74,.12); color:var(--primary); }
.cd-kpi.is-amber .cd-kpi__icon{ background:rgba(245,158,11,.15); color:var(--accent-dark); }
.cd-kpi.is-dark .cd-kpi__icon{ background:rgba(15,23,42,.07); color:var(--text); }

.cd-kpi__trend{
  display:flex; align-items:center; gap:4px;
  font-family:var(--font-mono); font-size:.7rem; font-weight:700;
  color:var(--primary-dark); background:rgba(22,163,74,.1);
  padding:4px 9px; border-radius:50px;
}

.cd-kpi__value{
  font-family:var(--font-mono); font-size:36px; font-weight:700; letter-spacing:-1px;
  line-height:1; margin-bottom:6px;
  background:linear-gradient(90deg, var(--text), #334155 90%);
  background-clip:text; -webkit-background-clip:text; color:transparent;
}
.cd-kpi__label{ font-size:14px; color:var(--muted); font-weight:500; }

/* shimmer while counting up */
.cd-kpi__value.is-counting{ position:relative; }
.cd-kpi__value.is-counting::after{
  content:''; position:absolute; inset:0;
  background:linear-gradient(100deg, transparent 30%, rgba(255,255,255,.6) 50%, transparent 70%);
  background-size:200% 100%; animation:cdShimmer 1.1s linear infinite;
}
@keyframes cdShimmer{ from{ background-position:200% 0; } to{ background-position:-200% 0; } }

/* ---- Hero banner ---- */
.cd-banner{
  position:relative; overflow:hidden; border-radius:24px; padding:42px 44px; color:#fff;
  background:
    radial-gradient(circle at 15% 20%, rgba(34,197,94,.35), transparent 45%),
    radial-gradient(circle at 85% 80%, rgba(245,158,11,.28), transparent 50%),
    linear-gradient(135deg, #0a1020 0%, #14532d 65%, #16a34a 100%);
  opacity:0; animation:cdCardIn .5s ease forwards; animation-delay:.32s;
}
.cd-banner__noise{ position:absolute; inset:0; background-image:repeating-linear-gradient(45deg, rgba(255,255,255,.035) 0 2px, transparent 2px 22px); pointer-events:none; }
.cd-banner__route{ position:absolute; right:-10px; bottom:-10px; opacity:.55; z-index:0; }
.cd-banner__content{ position:relative; z-index:1; max-width:560px; }
.cd-banner__title{ font-family:var(--font-head); font-size:30px; font-weight:800; letter-spacing:-.5px; margin-bottom:10px; }
.cd-banner__desc{ color:rgba(255,255,255,.74); font-size:.95rem; margin-bottom:24px; }

.cd-pill-row{ display:flex; gap:10px; margin-bottom:26px; flex-wrap:wrap; position:relative; z-index:1; }
.cd-pill{
  display:flex; align-items:center; gap:7px;
  background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18);
  backdrop-filter:blur(10px); border-radius:50px; padding:8px 14px;
  font-family:var(--font-mono); font-size:.74rem; color:#fff;
}
.cd-pill i{ color:var(--accent); }

.cd-banner__actions{ display:flex; gap:14px; flex-wrap:wrap; position:relative; z-index:1; }
.cd-btn{
  display:inline-flex; align-items:center; gap:8px; padding:13px 24px; border-radius:50px;
  font-weight:700; font-size:.88rem; transition:.18s ease, transform .12s ease;
}
.cd-btn:active{ transform:scale(.96); }
.cd-btn--primary{ background:var(--primary); color:#fff; box-shadow:0 8px 22px rgba(22,163,74,.4); }
.cd-btn--primary:hover{ background:var(--primary-dark); color:#fff; transform:translateY(-2px); }
.cd-btn--ghost{ background:rgba(255,255,255,.1); color:#fff; border:1px solid rgba(255,255,255,.25); backdrop-filter:blur(10px); }
.cd-btn--ghost:hover{ background:rgba(255,255,255,.18); color:#fff; transform:translateY(-2px); }

/* ---- Recent participants — table/card hybrid ---- */
.cd-panel{
  background:var(--card); backdrop-filter:blur(20px);
  border:1px solid var(--border); border-radius:22px; overflow:hidden;
  opacity:0; animation:cdCardIn .5s ease forwards; animation-delay:.4s;
}
.cd-panel__head{ display:flex; justify-content:space-between; align-items:center; padding:20px 26px; border-bottom:1px solid var(--line); }
.cd-panel__head h3{ font-family:var(--font-head); font-size:18px; font-weight:700; }
.cd-panel__head a{ font-size:.85rem; font-weight:700; }

.cd-prow{
  display:grid; grid-template-columns:auto 1.3fr 1fr auto auto; align-items:center; gap:16px;
  padding:14px 26px; border-bottom:1px solid var(--line);
  transition:background .18s ease, backdrop-filter .18s ease;
}
.cd-prow:last-child{ border-bottom:none; }
.cd-prow:hover{ background:rgba(22,163,74,.05); backdrop-filter:blur(6px); }

.cd-prow__avatar{
  width:38px; height:38px; border-radius:50%;
  background:linear-gradient(135deg, var(--primary), var(--primary-light));
  color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.78rem;
}
.cd-prow__name{ font-weight:700; font-size:.9rem; }
.cd-prow__email{ color:var(--muted); font-size:.8rem; }
.cd-prow__club{
  display:inline-flex; align-items:center; gap:6px;
  background:rgba(245,158,11,.13); color:var(--accent-dark);
  font-size:.74rem; font-weight:700; padding:5px 11px; border-radius:50px; justify-self:start;
}
.cd-prow__club.none{ background:rgba(15,23,42,.05); color:var(--muted); }
.cd-status-pill{
  display:inline-flex; align-items:center; gap:5px;
  font-size:.72rem; font-weight:700; color:var(--primary-dark);
  background:rgba(22,163,74,.1); padding:5px 11px; border-radius:50px; justify-self:start;
}
.cd-status-pill .dot{ width:6px; height:6px; border-radius:50%; background:var(--primary); }
.cd-prow__go{
  width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center;
  color:var(--muted); border:1px solid var(--line); transition:.15s ease; justify-self:end;
}
.cd-prow__go:hover{ background:var(--primary); color:#fff; border-color:var(--primary); }

/* ---- Shared section wrapper for search / manage pages ---- */
.cd-section{ background:var(--card-solid); border:1px solid var(--line); border-radius:20px; padding:28px 32px; }
.page-fade{ animation:cdCardIn .4s ease both; }

/* legacy class aliases so included fragments keep their look */
.section-card{ background:var(--card-solid); border:1px solid var(--line); border-radius:20px; padding:30px; }
.page-greeting .eyebrow{ font-family:var(--font-mono); font-size:.74rem; letter-spacing:.07em; text-transform:uppercase; color:var(--primary); display:block; margin-bottom:6px; }
.page-greeting h1{ font-family:var(--font-head); font-size:1.7rem; font-weight:700; margin:0; }
.page-greeting p{ color:var(--muted); margin-top:6px; margin-bottom:0; font-size:.92rem; }
.badge-id{ padding:6px 11px; border-radius:8px; font-family:var(--font-mono); font-size:.76rem; }

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width:1200px){ .cd-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
@media (max-width:780px){
  .cd-app{ flex-direction:column; }
  .cd-sidebar{ position:relative; width:100%; height:auto; min-height:auto; }
  .cd-main{ padding:24px 18px 48px; }
  .cd-header__title{ font-size:32px; letter-spacing:-1px; }
  .cd-kpi-grid{ grid-template-columns:1fr; }
  .cd-prow{ grid-template-columns:auto 1fr; row-gap:6px; }
  .cd-prow__club, .cd-status-pill, .cd-prow__go{ display:none; }
}
</style>
</head>
<body>

<div class="cd-mesh" aria-hidden="true"><span class="m1"></span><span class="m2"></span><span class="m3"></span></div>

<div class="cd-app">

  <!-- ── SIDEBAR ── -->
  <aside class="cd-sidebar">

    <div class="cd-sidebar__logo">
      <div class="cd-sidebar__logo-mark"><i class="bi bi-bicycle"></i></div>
      <div>
        <div class="cd-sidebar__logo-name">Cit-E Cycling</div>
        <div class="cd-sidebar__logo-sub">Admin Console</div>
      </div>
    </div>

    <a href="index.html" class="cd-sidebar__home"><i class="bi bi-house-door"></i> Back to Home</a>

    <div class="cd-sidebar__label">Workspace</div>
    <nav class="cd-nav">
      <a class="cd-nav__link <?=($page==='dashboard')?'is-active':''?>" href="admin_menu.php?page=dashboard">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
      </a>
      <a class="cd-nav__link <?=($page==='search')?'is-active':''?>" href="admin_menu.php?page=search">
        <i class="bi bi-search"></i> Search Participants
      </a>
      <a class="cd-nav__link <?=($page==='manage')?'is-active':''?>" href="admin_menu.php?page=manage">
        <i class="bi bi-people-fill"></i> Manage Participants
      </a>
      <a class="cd-nav__link" href="admin_menu.php?page=dashboard">
        <i class="bi bi-flag-fill"></i> Clubs <span class="cd-nav__soon">Soon</span>
      </a>
      <a class="cd-nav__link" href="admin_menu.php?page=dashboard">
        <i class="bi bi-bar-chart-fill"></i> Analytics <span class="cd-nav__soon">Soon</span>
      </a>
      <a class="cd-nav__link" href="admin_menu.php?page=dashboard">
        <i class="bi bi-file-earmark-text-fill"></i> Reports <span class="cd-nav__soon">Soon</span>
      </a>
      <a class="cd-nav__link" href="admin_menu.php?page=dashboard">
        <i class="bi bi-gear-fill"></i> Settings <span class="cd-nav__soon">Soon</span>
      </a>
    </nav>

    <div class="cd-sidebar__spacer"></div>

    <div class="cd-sidebar__profile">
      <div class="cd-sidebar__avatar"><?=strtoupper(substr($user,0,2))?></div>
      <div>
        <div class="cd-sidebar__uname"><?=$user?></div>
        <div class="cd-sidebar__urole">Administrator</div>
      </div>
    </div>
    <a href="?logout=1" class="cd-sidebar__logout"><i class="bi bi-box-arrow-right"></i> Sign Out</a>

  </aside>

  <!-- ── MAIN ── -->
  <div class="cd-main">

<?php switch($page):
  case 'search': echo '<div class="page-fade">'; include 'search_form.php'; echo '</div>'; break;
  case 'manage': echo '<div class="page-fade">'; include 'view_participants_edit_delete.php'; echo '</div>'; break;
  default: ?>

    <!-- Header -->
    <div class="cd-header">
      <div>
        <span class="cd-header__eyebrow">Admin Console</span>
        <h1 class="cd-header__title">Welcome back, <?=$user?></h1>
        <p class="cd-header__subtitle">Here's your Cit-E Cycling overview for today.</p>
      </div>

      <div class="cd-header__tools">
        <form class="cd-search" onsubmit="return false;">
          <i class="bi bi-search"></i>
          <input type="text" placeholder="Quick search&hellip;" aria-label="Quick search">
        </form>
        <button type="button" class="cd-icon-btn" title="Notifications">
          <i class="bi bi-bell"></i><span class="cd-dot"></span>
        </button>
        <div class="cd-header__avatar"><?=strtoupper(substr($user,0,2))?></div>
        <a href="admin_menu.php?page=manage" class="cd-quick-btn"><i class="bi bi-plus-lg"></i> Quick Action</a>
      </div>
    </div>

    <div class="cd-status-row">
      <?php if($dbOk): ?>
        <span class="cd-status is-live"><span class="cd-status__dot"></span> Live Database</span>
      <?php else: ?>
        <span class="cd-status is-demo"><span class="cd-status__dot"></span> Demo Mode</span>
      <?php endif; ?>
    </div>

    <!-- KPI grid -->
    <div class="cd-kpi-grid">

      <div class="cd-kpi is-green">
        <div class="cd-kpi__top">
          <div class="cd-kpi__icon"><i class="bi bi-people-fill"></i></div>
          <span class="cd-kpi__trend"><i class="bi bi-arrow-up-short"></i> Active</span>
        </div>
        <div class="cd-kpi__value" data-countup="<?=(int)$totalParticipants?>">0</div>
        <div class="cd-kpi__label">Total Participants</div>
      </div>

      <div class="cd-kpi is-amber">
        <div class="cd-kpi__top">
          <div class="cd-kpi__icon"><i class="bi bi-flag-fill"></i></div>
          <span class="cd-kpi__trend"><i class="bi bi-arrow-up-short"></i> Growing</span>
        </div>
        <div class="cd-kpi__value" data-countup="<?=(int)$totalClubs?>">0</div>
        <div class="cd-kpi__label">Registered Clubs</div>
      </div>

      <div class="cd-kpi is-dark">
        <div class="cd-kpi__top">
          <div class="cd-kpi__icon"><i class="bi bi-lightning-fill"></i></div>
          <span class="cd-kpi__trend"><i class="bi bi-graph-up"></i> Avg</span>
        </div>
        <div class="cd-kpi__value"><span data-countup="<?=(int)$avgPower?>">0</span>W</div>
        <div class="cd-kpi__label">Avg Power Output</div>
      </div>

      <div class="cd-kpi is-green">
        <div class="cd-kpi__top">
          <div class="cd-kpi__icon"><i class="bi bi-shield-check-fill"></i></div>
          <span class="cd-kpi__trend"><i class="bi bi-check2"></i> OK</span>
        </div>
        <div class="cd-kpi__value" style="font-size:24px;">Active</div>
        <div class="cd-kpi__label">Session Status</div>
      </div>

    </div>

    <!-- Hero banner -->
    <div class="cd-banner">
      <div class="cd-banner__noise"></div>
      <svg class="cd-banner__route" width="320" height="180" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg">
        <path d="M10,160 C70,140 90,90 150,80 C210,70 220,30 300,15" fill="none" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" opacity="0.6"/>
        <circle cx="10" cy="160" r="6" fill="#f59e0b" opacity="0.7"/>
        <circle cx="300" cy="15" r="6" fill="#f59e0b" opacity="0.7"/>
      </svg>
      <div class="cd-banner__content">
        <h2 class="cd-banner__title">Manage the ride, end to end.</h2>
        <p class="cd-banner__desc">Search participant records or manage the full list — everything is one click away.</p>

        <div class="cd-pill-row">
          <span class="cd-pill"><i class="bi bi-people-fill"></i> <?=(int)$totalParticipants?> riders</span>
          <span class="cd-pill"><i class="bi bi-flag-fill"></i> <?=(int)$totalClubs?> clubs</span>
        </div>

        <div class="cd-banner__actions">
          <a href="admin_menu.php?page=search" class="cd-btn cd-btn--primary"><i class="bi bi-search"></i> Search Participants</a>
          <a href="admin_menu.php?page=manage" class="cd-btn cd-btn--ghost"><i class="bi bi-people-fill"></i> Manage Participants</a>
        </div>
      </div>
    </div>

    <!-- Recent participants -->
    <div class="cd-panel">
      <div class="cd-panel__head">
        <h3>Recently Added Participants</h3>
        <a href="admin_menu.php?page=manage">View all &rarr;</a>
      </div>

      <?php if($recentRows): foreach($recentRows as $r):
        $ini = strtoupper(substr($r['firstname'],0,1).substr($r['surname'],0,1));
        $hasClub = !empty($r['club']);
      ?>
        <div class="cd-prow">
          <div class="cd-prow__avatar"><?=$ini?></div>
          <div>
            <div class="cd-prow__name"><?=htmlspecialchars($r['firstname'].' '.$r['surname'])?></div>
            <div class="cd-prow__email"><?=htmlspecialchars($r['email'])?></div>
          </div>
          <span class="cd-prow__club <?=$hasClub?'':'none'?>"><i class="bi bi-flag-fill"></i> <?=$hasClub?htmlspecialchars($r['club']):'No club'?></span>
          <span class="cd-status-pill"><span class="dot"></span> Active</span>
          <a href="admin_menu.php?page=manage" class="cd-prow__go"><i class="bi bi-arrow-right"></i></a>
        </div>
      <?php endforeach; else: ?>
        <div class="cd-prow" style="grid-template-columns:1fr;"><span class="cd-prow__email">No participants yet.</span></div>
      <?php endif; ?>
    </div>

<?php endswitch; ?>

  </div><!-- /.cd-main -->
</div><!-- /.cd-app -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Count-up animation with shimmer-to-solid transition
document.querySelectorAll('[data-countup]').forEach(el => {
    const target = parseInt(el.getAttribute('data-countup'), 10) || 0;
    const valueEl = el.classList.contains('cd-kpi__value') ? el : el.closest('.cd-kpi__value');
    if (valueEl) valueEl.classList.add('is-counting');
    const duration = 850;
    const start = performance.now();
    function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(eased * target).toLocaleString();
        if (progress < 1) {
            requestAnimationFrame(tick);
        } else if (valueEl) {
            valueEl.classList.remove('is-counting');
        }
    }
    requestAnimationFrame(tick);
});

// Subtle tilt effect on KPI cards
document.querySelectorAll('.cd-kpi').forEach(card => {
    card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left, y = e.clientY - rect.top;
        const rx = ((y / rect.height) - 0.5) * -5;
        const ry = ((x / rect.width) - 0.5) * 5;
        card.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-6px)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
});
</script>
</body>
</html>