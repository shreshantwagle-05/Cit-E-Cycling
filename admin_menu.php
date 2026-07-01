<?php
session_start();

// ── AJAX intercept for search drawer ──
if (!empty($_POST['sf_ajax'])) {
    include 'search_form.php';
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.html");
    exit();
}

$page = $_GET['page'] ?? 'dashboard';

$pdo = null; $dbOk = false;
$totalParticipants = 0; $totalClubs = 0; $avgPower = 0; $recentRows = [];

try {
    include_once 'dbconnect.php';
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4",
        $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $dbOk = true;
    if ($page === 'dashboard') {
        $totalParticipants = (int)$pdo->query("SELECT COUNT(*) FROM participant")->fetchColumn();
        $totalClubs        = (int)$pdo->query("SELECT COUNT(*) FROM club")->fetchColumn();
        $avgPower          = (int)$pdo->query("SELECT ROUND(AVG(power_output)) FROM participant")->fetchColumn();
        $recentRows        = $pdo->query(
            "SELECT p.id, p.firstname, p.surname, p.email, p.power_output, p.distance, c.name AS club
             FROM participant p LEFT JOIN club c ON c.id = p.club_id
             ORDER BY p.id DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($page === 'manage') {
        $totalParticipants = (int)$pdo->query("SELECT COUNT(*) FROM participant")->fetchColumn();
    }
} catch (PDOException $e) { $dbOk = false; }

$user    = htmlspecialchars($_SESSION['username']);
$userCap = strtoupper(substr($_SESSION['username'], 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Cit-E Cycling</title>
<link rel="icon" href="./Resource/Logo.png" type="image/png" sizes="32x32">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Orbitron:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
:root {
  --primary:#16a34a; --primary-dark:#14532d; --primary-mid:#15803d;
  --primary-glow:rgba(22,163,74,.22);
  --accent:#f59e0b; --accent-dark:#d97706; --danger:#ef4444;
  --bg:#f0f4f0; --card:rgba(255,255,255,0.78); --card-solid:#ffffff;
  --card-border:rgba(255,255,255,0.55);
  --sidebar-bg:#080f1a; --sidebar-card:rgba(255,255,255,0.05);
  --sidebar-border:rgba(255,255,255,0.07);
  --text:#0f172a; --muted:#64748b; --muted-light:#94a3b8;
  --on-dark:rgba(255,255,255,0.88); --on-dark-muted:rgba(255,255,255,0.42);
  --border:rgba(15,23,42,0.09);
  --shadow-sm:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04);
  --shadow-md:0 4px 16px rgba(0,0,0,0.08),0 2px 6px rgba(0,0,0,0.04);
  --shadow-lg:0 20px 40px rgba(0,0,0,0.12),0 8px 16px rgba(0,0,0,0.06);
  --shadow-glow:0 0 32px rgba(22,163,74,0.18);
  --font-display:'Unbounded',system-ui,sans-serif;
  --font-body:'Inter',system-ui,sans-serif;
  --font-mono:'JetBrains Mono',monospace;
  --font-clock:'Orbitron',var(--font-mono);
  --sidebar-w:272px; --radius-sm:10px; --radius-md:16px;
  --radius-lg:22px; --radius-xl:28px;
  --ease:cubic-bezier(0.4,0,0.2,1); --dur:0.22s;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);color:var(--text);background:var(--bg);min-height:100vh;display:flex;-webkit-font-smoothing:antialiased;}
a{color:inherit;text-decoration:none;}
button{font-family:inherit;cursor:pointer;}
body::before{content:'';position:fixed;inset:0;z-index:-1;background:radial-gradient(ellipse 70% 60% at 12% 8%,rgba(22,163,74,.07) 0%,transparent 55%),radial-gradient(ellipse 55% 50% at 88% 85%,rgba(22,163,74,.05) 0%,transparent 55%),var(--bg);}

/* SIDEBAR */
.sidebar{width:var(--sidebar-w);min-height:100vh;position:sticky;top:0;height:100vh;flex-shrink:0;display:flex;flex-direction:column;background:var(--sidebar-bg);border-right:1px solid rgba(255,255,255,0.05);z-index:50;overflow:hidden;}
.sidebar::before{content:'';position:absolute;top:-120px;left:-80px;width:360px;height:360px;background:radial-gradient(circle,rgba(22,163,74,.18) 0%,transparent 65%);pointer-events:none;z-index:0;}
.sidebar::after{content:'';position:absolute;bottom:-80px;right:-80px;width:280px;height:280px;background:radial-gradient(circle,rgba(245,158,11,.08) 0%,transparent 65%);pointer-events:none;z-index:0;}
.sidebar__inner{position:relative;z-index:1;display:flex;flex-direction:column;height:100%;padding:0 14px;}
.sidebar__logo{padding:22px 6px 18px;display:flex;align-items:center;gap:11px;border-bottom:1px solid var(--sidebar-border);margin-bottom:20px;}
.logo__mark{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--primary),#22c55e);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;box-shadow:0 4px 16px rgba(22,163,74,.4),0 0 0 1px rgba(255,255,255,.1);}
.logo__name{font-family:var(--font-display);font-weight:700;font-size:.92rem;color:#fff;letter-spacing:-.01em;}
.logo__sub{font-family:var(--font-mono);font-size:.58rem;color:var(--on-dark-muted);letter-spacing:.09em;text-transform:uppercase;}
.sidebar__label{font-family:var(--font-mono);font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;color:var(--on-dark-muted);padding:0 8px;margin-bottom:6px;}
.sidebar__nav{display:flex;flex-direction:column;gap:2px;margin-bottom:24px;}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:var(--radius-sm);color:rgba(255,255,255,.48);font-weight:600;font-size:.875rem;transition:background var(--dur) var(--ease),color var(--dur) var(--ease);position:relative;overflow:hidden;}
.nav-item i{font-size:1rem;width:18px;text-align:center;flex-shrink:0;}
.nav-item .nav-item__badge{margin-left:auto;font-family:var(--font-mono);font-size:.62rem;background:rgba(255,255,255,.1);color:var(--on-dark-muted);padding:2px 7px;border-radius:50px;}
.nav-item:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.88);}
.nav-item.active{background:linear-gradient(135deg,rgba(22,163,74,.28),rgba(22,163,74,.12));color:#fff;box-shadow:inset 0 0 0 1px rgba(22,163,74,.35),0 4px 14px rgba(22,163,74,.15);}
.nav-item.active i{color:#4ade80;}
.nav-item.active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;background:linear-gradient(180deg,#4ade80,var(--primary));border-radius:0 3px 3px 0;box-shadow:0 0 8px rgba(74,222,128,.6);}
.nav-item.soon{opacity:.38;pointer-events:none;}
.sidebar__divider{height:1px;background:var(--sidebar-border);margin:6px 0 16px;}
.sidebar__bottom{margin-top:auto;padding:14px 0 18px;}
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border-radius:var(--radius-sm);background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.22);color:#fca5a5;font-weight:700;font-size:.85rem;transition:background var(--dur),color var(--dur);}
.logout-btn:hover{background:var(--danger);color:#fff;}

/* MAIN */
.main{flex:1;min-width:0;display:flex;flex-direction:column;}

/* HEADER */
.header{position:sticky;top:0;z-index:40;background:rgba(240,244,240,0.88);backdrop-filter:blur(18px) saturate(160%);-webkit-backdrop-filter:blur(18px) saturate(160%);border-bottom:1px solid var(--border);padding:0 36px;height:68px;display:flex;align-items:center;justify-content:flex-end;gap:14px;}
.clock-widget{display:flex;align-items:center;gap:10px;background:var(--card-solid);border:1.5px solid var(--border);border-radius:50px;padding:7px 16px;}
.clock-time{font-family:var(--font-clock);font-size:1.2rem;font-weight:600;color:var(--text);letter-spacing:.02em;line-height:1;display:flex;align-items:baseline;gap:1px;}
.clock-time .colon{animation:colonBlink 1s step-end infinite;color:var(--primary);margin:0 1px;}
@keyframes colonBlink{0%,100%{opacity:1;}50%{opacity:.15;}}
.clock-ampm{font-family:var(--font-clock);font-size:.62rem;font-weight:700;color:var(--accent-dark);margin-left:4px;letter-spacing:.04em;}
.clock-date{font-family:var(--font-mono);font-size:.66rem;color:var(--muted);letter-spacing:.03em;margin-top:1px;}
.clock-badge-dot{width:6px;height:6px;border-radius:50%;background:var(--primary);animation:livePulse 1.8s ease-in-out infinite;flex-shrink:0;}
@keyframes livePulse{0%{box-shadow:0 0 0 0 rgba(22,163,74,.5);}70%{box-shadow:0 0 0 6px rgba(22,163,74,0);}100%{box-shadow:0 0 0 0 rgba(22,163,74,0);}}
.header__right{display:flex;align-items:center;gap:10px;}
.header__icon-btn{width:38px;height:38px;border-radius:50%;background:var(--card-solid);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:1rem;transition:background var(--dur),color var(--dur),border-color var(--dur);position:relative;}
.header__icon-btn:hover{background:var(--primary);color:#fff;border-color:var(--primary);}
.header__notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:var(--accent);border:1.5px solid var(--bg);}
.db-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border-radius:50px;font-family:var(--font-mono);font-size:.68rem;font-weight:500;letter-spacing:.04em;white-space:nowrap;}
.db-chip.live{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.22);color:var(--primary-dark);}
.db-chip.demo{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#92400e;}
.db-chip__dot{width:7px;height:7px;border-radius:50%;}
.live .db-chip__dot{background:var(--primary);animation:livePulse 1.8s ease-in-out infinite;}
.demo .db-chip__dot{background:var(--accent);}
.header__cta{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--primary);color:#fff;border:none;border-radius:50px;font-family:var(--font-display);font-weight:600;font-size:.78rem;box-shadow:0 4px 14px rgba(22,163,74,.3);transition:background var(--dur),transform var(--dur),box-shadow var(--dur);white-space:nowrap;}
.header__cta:hover{background:var(--primary-mid);transform:translateY(-1px);box-shadow:0 6px 20px rgba(22,163,74,.36);color:#fff;}
.header__av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-dark));color:#1a0a00;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.78rem;flex-shrink:0;box-shadow:0 2px 8px rgba(245,158,11,.3);}

/* PAGE BODY */
.page-body{flex:1;padding:36px 40px 60px;display:flex;flex-direction:column;gap:28px;animation:pageFadeIn .45s var(--ease);}
@keyframes pageFadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.hero-header{display:flex;flex-direction:column;gap:6px;}
.hero-header h1{font-family:var(--font-display);font-size:clamp(1.8rem,3.2vw,2.5rem);font-weight:700;letter-spacing:-.02em;line-height:1.15;color:var(--text);}
.hero-header h1 .name-highlight{background:linear-gradient(135deg,var(--primary),#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-header p{color:var(--muted);font-size:.95rem;font-weight:500;}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
.kpi-card{background:var(--card);backdrop-filter:blur(20px) saturate(160%);border:1px solid var(--card-border);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-md);position:relative;overflow:hidden;transition:transform var(--dur) var(--ease),box-shadow var(--dur) var(--ease);opacity:0;transform:translateY(14px);animation:kpiIn .5s var(--ease) forwards;}
.kpi-card:nth-child(1){animation-delay:.06s;}.kpi-card:nth-child(2){animation-delay:.12s;}.kpi-card:nth-child(3){animation-delay:.18s;}.kpi-card:nth-child(4){animation-delay:.24s;}
@keyframes kpiIn{to{opacity:1;transform:translateY(0);}}
.kpi-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);}
.kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius-lg) var(--radius-lg) 0 0;}
.kpi-card--green::before{background:linear-gradient(90deg,var(--primary),#4ade80);}
.kpi-card--amber::before{background:linear-gradient(90deg,var(--accent),#fcd34d);}
.kpi-card--blue::before{background:linear-gradient(90deg,#3b82f6,#93c5fd);}
.kpi-card--violet::before{background:linear-gradient(90deg,#8b5cf6,#c4b5fd);}
.kpi-card__top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;}
.kpi-card__icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;position:relative;z-index:1;}
.icon--green{background:rgba(22,163,74,.12);color:var(--primary);}
.icon--amber{background:rgba(245,158,11,.14);color:var(--accent-dark);}
.icon--blue{background:rgba(59,130,246,.12);color:#2563eb;}
.icon--violet{background:rgba(139,92,246,.12);color:#7c3aed;}
.kpi-card__trend{font-family:var(--font-mono);font-size:.68rem;font-weight:700;padding:4px 9px;border-radius:50px;letter-spacing:.04em;position:relative;z-index:1;}
.trend--up{background:rgba(22,163,74,.12);color:var(--primary);}
.trend--warn{background:rgba(245,158,11,.14);color:var(--accent-dark);}
.trend--info{background:rgba(59,130,246,.12);color:#2563eb;}
.trend--ok{background:rgba(139,92,246,.12);color:#7c3aed;}
.kpi-card__num{font-family:var(--font-mono);font-size:2.25rem;font-weight:700;letter-spacing:-.03em;line-height:1;color:var(--text);margin-bottom:6px;position:relative;z-index:1;}
.kpi-card__label{font-size:.83rem;color:var(--muted);font-weight:500;position:relative;z-index:1;}
.hero-banner{border-radius:var(--radius-xl);padding:40px 44px;position:relative;overflow:hidden;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:32px;animation:kpiIn .5s var(--ease) .3s both;background:linear-gradient(130deg,#0a1a10 0%,#0f3320 35%,#14532d 60%,#166534 100%);box-shadow:var(--shadow-lg),var(--shadow-glow);}
.hero-banner::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 65% 80% at 80% 50%,rgba(74,222,128,.12) 0%,transparent 60%),radial-gradient(ellipse 40% 60% at 15% 30%,rgba(245,158,11,.1) 0%,transparent 55%),repeating-linear-gradient(45deg,rgba(255,255,255,.012) 0 2px,transparent 2px 22px);pointer-events:none;}
.hero-banner__rings{position:absolute;right:200px;top:50%;transform:translateY(-50%);width:220px;height:220px;pointer-events:none;opacity:.12;}
.hero-banner__rings circle{fill:none;stroke:#4ade80;}
.hero-banner__content{position:relative;z-index:1;max-width:520px;}
.hero-banner__eyebrow{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:#4ade80;margin-bottom:10px;display:flex;align-items:center;gap:7px;}
.hero-banner__eyebrow::before{content:'';width:16px;height:2px;background:#4ade80;border-radius:2px;}
.hero-banner__title{font-family:var(--font-display);font-size:1.4rem;font-weight:700;letter-spacing:-.01em;color:#fff;margin-bottom:10px;line-height:1.25;}
.hero-banner__sub{color:rgba(255,255,255,.65);font-size:.9rem;line-height:1.6;margin-bottom:26px;}
.hero-banner__pills{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;}
.hero-pill{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:50px;color:rgba(255,255,255,.88);font-size:.8rem;font-weight:600;backdrop-filter:blur(8px);}
.hero-pill i{color:#4ade80;font-size:.85rem;}
.hero-banner__visual{position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-end;gap:10px;flex-shrink:0;}
.hero-stat-card{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:16px;padding:14px 20px;min-width:160px;backdrop-filter:blur(12px);}
.hsc__label{font-family:var(--font-mono);font-size:.62rem;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:6px;}
.hsc__val{font-family:var(--font-mono);font-size:1.55rem;font-weight:700;color:#fff;letter-spacing:-.02em;line-height:1;}
.hsc__sub{font-size:.72rem;color:#4ade80;margin-top:3px;font-weight:600;}
.page-fade{animation:pageFadeIn .4s var(--ease);}
.section-wrap{padding:36px 40px 60px;display:flex;flex-direction:column;gap:26px;}

/* ── STUNNING DELETE POPUP ── */
.del-backdrop{position:fixed;inset:0;z-index:9999;background:rgba(8,14,24,0.75);backdrop-filter:blur(12px) saturate(150%);-webkit-backdrop-filter:blur(12px) saturate(150%);display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity 0.25s ease;pointer-events:none;}
.del-backdrop.open{opacity:1;pointer-events:all;}
.del-card{background:#fff;border-radius:32px;max-width:410px;width:100%;overflow:hidden;box-shadow:0 0 0 1px rgba(0,0,0,.06),0 48px 120px rgba(0,0,0,.32),0 16px 40px rgba(0,0,0,.18);transform:scale(0.84) translateY(28px);opacity:0;transition:transform 0.36s cubic-bezier(0.34,1.56,0.64,1),opacity 0.26s ease;}
.del-backdrop.open .del-card{transform:scale(1) translateY(0);opacity:1;}
.del-card.shake{animation:delShake 0.4s ease;}
@keyframes delShake{0%,100%{transform:scale(1) translateY(0);}20%{transform:scale(1) translateY(-4px) rotate(-1.2deg);}40%{transform:scale(1) translateY(3px) rotate(1deg);}60%{transform:scale(1) translateY(-2px);}80%{transform:scale(1) translateY(1px);}}

/* Header */
.del-hdr{position:relative;padding:42px 36px 34px;text-align:center;overflow:hidden;background:linear-gradient(150deg,#07090f 0%,#1c0404 45%,#6b0e0e 100%);}
.del-hdr::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(239,68,68,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(239,68,68,.06) 1px,transparent 1px);background-size:26px 26px;pointer-events:none;}
.del-hdr::after{content:'';position:absolute;bottom:-70px;left:50%;transform:translateX(-50%);width:280px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(239,68,68,.28) 0%,transparent 65%);pointer-events:none;}

/* Icon rings */
.del-rings{position:relative;width:86px;height:86px;margin:0 auto 20px;}
.del-ring{position:absolute;border-radius:50%;border:1px solid rgba(239,68,68,.22);inset:0;animation:delRingPulse 2.8s ease-in-out infinite;}
.del-ring:nth-child(2){inset:-11px;border-color:rgba(239,68,68,.13);animation-delay:.55s;}
.del-ring:nth-child(3){inset:-22px;border-color:rgba(239,68,68,.07);animation-delay:1.1s;}
@keyframes delRingPulse{0%,100%{transform:scale(.94);opacity:.9;}50%{transform:scale(1.06);opacity:.35;}}
.del-core{position:absolute;inset:0;border-radius:50%;background:linear-gradient(135deg,rgba(239,68,68,.22),rgba(185,28,28,.28));border:1.5px solid rgba(239,68,68,.42);display:flex;align-items:center;justify-content:center;box-shadow:0 0 28px rgba(239,68,68,.22);}
.del-core i{font-size:1.85rem;color:#fca5a5;filter:drop-shadow(0 0 12px rgba(239,68,68,.65));}

.del-hdr-label{font-family:var(--font-mono);font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.32);margin-bottom:7px;position:relative;z-index:1;}
.del-hdr-title{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:#fff;letter-spacing:-.01em;position:relative;z-index:1;}

/* Body */
.del-body{padding:26px 30px 28px;background:#fff;}

/* Name chip */
.del-chip{display:flex;align-items:center;gap:12px;background:#fafafa;border:1px solid #f0f0f0;border-radius:16px;padding:12px 15px;margin-bottom:14px;}
.del-chip-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0;box-shadow:0 3px 12px rgba(239,68,68,.28);}
.del-chip-name{font-weight:700;font-size:.9rem;color:#0f172a;}
.del-chip-id{font-family:var(--font-mono);font-size:.66rem;color:#94a3b8;margin-top:2px;}

/* Warning pill */
.del-pill{display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.16);border-radius:50px;padding:5px 13px;font-family:var(--font-mono);font-size:.68rem;font-weight:700;color:#dc2626;letter-spacing:.03em;margin-bottom:12px;}
.del-pill i{font-size:.65rem;}

/* Warning text */
.del-warn{font-size:.82rem;color:#64748b;line-height:1.6;margin-bottom:20px;text-align:center;}
.del-warn strong{color:#dc2626;font-weight:700;}

/* Buttons */
.del-btns{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
.del-cancel{padding:12px 16px;border-radius:50px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-family:var(--font-body);font-weight:700;font-size:.85rem;cursor:pointer;transition:all .16s;display:flex;align-items:center;justify-content:center;gap:6px;}
.del-cancel:hover{background:#e2e8f0;color:#0f172a;border-color:#cbd5e1;}
.del-confirm{padding:12px 16px;border-radius:50px;border:none;background:linear-gradient(135deg,#b91c1c 0%,#ef4444 100%);color:#fff;font-family:var(--font-body);font-weight:800;font-size:.85rem;cursor:pointer;box-shadow:0 4px 18px rgba(220,38,38,.36);transition:all .16s;display:flex;align-items:center;justify-content:center;gap:6px;animation:delBtnGlow 2.6s ease-in-out infinite;}
@keyframes delBtnGlow{0%,100%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 0 rgba(220,38,38,.2);}50%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 7px rgba(220,38,38,0);}}
.del-confirm:hover{background:linear-gradient(135deg,#991b1b 0%,#dc2626 100%);box-shadow:0 6px 26px rgba(220,38,38,.52);transform:translateY(-1px);animation:none;}
.del-confirm:active{transform:scale(.97);}

@media(max-width:1200px){.kpi-grid{grid-template-columns:repeat(2,1fr);}.hero-banner__visual{display:none;}}
@media(max-width:900px){:root{--sidebar-w:64px;}.logo__text,.nav-item span,.sidebar__label,.logout-btn span{display:none;}.nav-item{justify-content:center;padding:12px;}.nav-item i{width:auto;}.sidebar__logo{justify-content:center;padding:18px 0;}.logo__mark{margin:0 auto;}.page-body,.section-wrap{padding:22px 16px 40px;}.header{padding:0 16px;}.clock-time{font-size:1rem;}.clock-date{display:none;}.hero-banner{padding:28px 24px;}}
@media(max-width:600px){.kpi-grid{grid-template-columns:1fr;}.hero-banner{flex-direction:column;align-items:flex-start;}}
.counter{display:inline-block;}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar__inner">
    <div class="sidebar__logo">
      <div class="logo__mark"><i class="bi bi-bicycle" style="color:#fff;font-size:1.1rem"></i></div>
      <div class="logo__text">
        <div class="logo__name">Cit-E Cycling</div>
        <div class="logo__sub">Admin Console</div>
      </div>
    </div>
    <div class="sidebar__label">Main Menu</div>
    <nav class="sidebar__nav">
      <a class="nav-item <?=($page==='dashboard')?'active':''?>" href="admin_menu.php?page=dashboard"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
      <a class="nav-item <?=($page==='search')?'active':''?>" href="admin_menu.php?page=search"><i class="bi bi-search"></i><span>Search Participants</span></a>
      <a class="nav-item <?=($page==='manage')?'active':''?>" href="admin_menu.php?page=manage"><i class="bi bi-people-fill"></i><span>Manage Participants</span><span class="nav-item__badge"><?=(int)$totalParticipants?></span></a>
    </nav>
    <div class="sidebar__divider"></div>
    <div class="sidebar__label">More</div>
    <nav class="sidebar__nav">
      <a class="nav-item soon" href="#"><i class="bi bi-bar-chart-fill"></i><span>Analytics</span><span class="nav-item__badge">Soon</span></a>
      <a class="nav-item soon" href="#"><i class="bi bi-flag-fill"></i><span>Clubs</span><span class="nav-item__badge">Soon</span></a>
    </nav>
    <div class="sidebar__bottom">
      <a href="?logout=1" class="logout-btn"><i class="bi bi-box-arrow-right"></i><span>Log Out</span></a>
    </div>
  </div>
</aside>

<div class="main">
  <header class="header">
    <div class="header__right">
      <?php if($dbOk): ?><span class="db-chip live"><span class="db-chip__dot"></span>Live Database</span><?php else: ?><span class="db-chip demo"><span class="db-chip__dot"></span>Demo Mode</span><?php endif; ?>
      <button class="header__icon-btn" title="Notifications"><i class="bi bi-bell-fill"></i><span class="header__notif-dot"></span></button>
      <button class="header__icon-btn" title="Help"><i class="bi bi-question-circle-fill"></i></button>
      <a href="admin_menu.php?page=manage" class="header__cta"><i class="bi bi-person-plus-fill"></i> Manage Riders</a>
      <div class="header__av" title="<?=$user?>"><?=$userCap?></div>
      <div class="clock-widget">
        <span class="clock-badge-dot"></span>
        <div>
          <div class="clock-time"><span id="clockHH">00</span><span class="colon">:</span><span id="clockMM">00</span><span class="colon">:</span><span id="clockSS">00</span><span class="clock-ampm" id="clockAMPM">AM</span></div>
          <div class="clock-date" id="clockDate">Loading&hellip;</div>
        </div>
      </div>
    </div>
  </header>

  <?php switch($page):
    case 'search': echo '<div class="section-wrap page-fade">'; include 'search_form.php'; echo '</div>'; break;
    case 'manage': echo '<div class="section-wrap page-fade">'; include 'view_participants_edit_delete.php'; echo '</div>'; break;
    default: ?>

  <div class="page-body">
    <div class="hero-header">
      <h1>Welcome back, <span class="name-highlight"><?=$user?></span>.</h1>
      <p>Here's your Cit-E Cycling system overview — everything looks good.</p>
    </div>
    <div class="kpi-grid">
      <div class="kpi-card kpi-card--green"><div class="kpi-card__top"><div class="kpi-card__icon icon--green"><i class="bi bi-people-fill"></i></div><span class="kpi-card__trend trend--up">Active</span></div><div class="kpi-card__num counter" data-target="<?=(int)$totalParticipants?>"><?=(int)$totalParticipants?></div><div class="kpi-card__label">Total Participants</div></div>
      <div class="kpi-card kpi-card--amber"><div class="kpi-card__top"><div class="kpi-card__icon icon--amber"><i class="bi bi-flag-fill"></i></div><span class="kpi-card__trend trend--warn">Registered</span></div><div class="kpi-card__num counter" data-target="<?=(int)$totalClubs?>"><?=(int)$totalClubs?></div><div class="kpi-card__label">Registered Clubs</div></div>
      <div class="kpi-card kpi-card--blue"><div class="kpi-card__top"><div class="kpi-card__icon icon--blue"><i class="bi bi-lightning-fill"></i></div><span class="kpi-card__trend trend--info">Avg</span></div><div class="kpi-card__num"><?=(int)$avgPower?><small style="font-size:1rem;opacity:.6"> W</small></div><div class="kpi-card__label">Avg Power Output</div></div>
      <div class="kpi-card kpi-card--violet"><div class="kpi-card__top"><div class="kpi-card__icon icon--violet"><i class="bi bi-shield-check-fill"></i></div><span class="kpi-card__trend trend--ok">Secure</span></div><div class="kpi-card__num" style="font-size:1.5rem;padding-top:6px">Protected</div><div class="kpi-card__label">Session Status</div></div>
    </div>
    <div class="hero-banner">
      <svg class="hero-banner__rings" viewBox="0 0 220 220"><circle cx="110" cy="110" r="100" stroke-width="1.5" stroke-dasharray="8 6"/><circle cx="110" cy="110" r="78" stroke-width="1" stroke-dasharray="4 8" opacity=".6"/><circle cx="110" cy="110" r="54" stroke-width="0.8" stroke-dasharray="3 10" opacity=".4"/><circle cx="110" cy="110" r="30" stroke-width="2" opacity=".3"/></svg>
      <div class="hero-banner__content">
        <div class="hero-banner__eyebrow">Command centre</div>
        <h2 class="hero-banner__title">Manage the ride, end&nbsp;to&nbsp;end.</h2>
        <p class="hero-banner__sub">Search participant records, update scores, manage clubs and monitor the full participant list — all in one place.</p>
        <div class="hero-banner__pills">
          <span class="hero-pill"><i class="bi bi-people-fill"></i><?=(int)$totalParticipants?> riders registered</span>
          <span class="hero-pill"><i class="bi bi-flag-fill"></i><?=(int)$totalClubs?> clubs active</span>
          <?php if($dbOk): ?><span class="hero-pill"><i class="bi bi-database-fill-check"></i>DB connected</span><?php endif; ?>
        </div>
      </div>
      <div class="hero-banner__visual">
        <div class="hero-stat-card"><div class="hsc__label">Total Participants</div><div class="hsc__val"><?=(int)$totalParticipants?></div><div class="hsc__sub">↑ System total</div></div>
        <div class="hero-stat-card"><div class="hsc__label">Avg Power</div><div class="hsc__val"><?=(int)$avgPower?> W</div><div class="hsc__sub">Across all riders</div></div>
      </div>
    </div>
  </div>

  <?php break; endswitch; ?>
</div>

<!-- ══ STUNNING DELETE POPUP (no SweetAlert needed) ══ -->
<div class="del-backdrop" id="delBackdrop">
  <div class="del-card" id="delCard">
    <div class="del-hdr">
      <div class="del-rings">
        <div class="del-ring"></div>
        <div class="del-ring"></div>
        <div class="del-ring"></div>
        <div class="del-core"><i class="bi bi-trash3-fill"></i></div>
      </div>
      <div class="del-hdr-label">Irreversible action</div>
      <div class="del-hdr-title">Delete Participant?</div>
    </div>
    <div class="del-body">
      <div class="del-chip">
        <div class="del-chip-av" id="delInitials">?</div>
        <div>
          <div class="del-chip-name" id="delName">—</div>
          <div class="del-chip-id" id="delIdLabel">Participant</div>
        </div>
      </div>
      <div style="text-align:center;">
        <span class="del-pill"><i class="bi bi-exclamation-triangle-fill"></i> Cannot be undone</span>
      </div>
      <p class="del-warn">This will <strong>permanently delete</strong> this participant and all their performance data from the database.</p>
      <div class="del-btns">
        <button class="del-cancel" id="delCancelBtn"><i class="bi bi-x-lg"></i> Cancel</button>
        <button class="del-confirm" id="delConfirmBtn"><i class="bi bi-trash3-fill"></i> Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
/* ── Live Clock ── */
(function(){
  var days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  var months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  function pad(n){return n<10?'0'+n:''+n;}
  function tick(){
    var now=new Date(),h24=now.getHours(),ampm=h24>=12?'PM':'AM',h12=h24%12||12;
    var elHH=document.getElementById('clockHH'),elMM=document.getElementById('clockMM'),elSS=document.getElementById('clockSS'),elAP=document.getElementById('clockAMPM'),elDT=document.getElementById('clockDate');
    if(elHH)elHH.textContent=pad(h12);
    if(elMM)elMM.textContent=pad(now.getMinutes());
    if(elSS)elSS.textContent=pad(now.getSeconds());
    if(elAP)elAP.textContent=ampm;
    if(elDT)elDT.textContent=days[now.getDay()]+', '+now.getDate()+' '+months[now.getMonth()]+' '+now.getFullYear();
  }
  tick(); setInterval(tick,1000);
})();

/* ── Counters ── */
(function(){
  document.querySelectorAll('.counter[data-target]').forEach(function(el){
    var t=parseInt(el.getAttribute('data-target'),10);
    if(isNaN(t)||t===0)return;
    var dur=900,s=null;
    function step(ts){if(!s)s=ts;var p=Math.min((ts-s)/dur,1),e=1-Math.pow(1-p,3);el.textContent=Math.round(e*t);if(p<1)requestAnimationFrame(step);else el.textContent=t;}
    requestAnimationFrame(step);
  });
})();

/* ── Delete popup ── */
(function(){
  var backdrop=document.getElementById('delBackdrop');
  var card=document.getElementById('delCard');
  var cancelBtn=document.getElementById('delCancelBtn');
  var confirmBtn=document.getElementById('delConfirmBtn');
  var currentId=null;

  window.confirmDeleteParticipant=function(id,name){
    currentId=id;
    var parts=name.trim().split(' ');
    var initials=parts.map(function(p){return p[0]||'';}).join('').substring(0,2).toUpperCase();
    document.getElementById('delInitials').textContent=initials;
    document.getElementById('delName').textContent=name;
    document.getElementById('delIdLabel').textContent='Participant ID #'+id;
    backdrop.classList.add('open');
    document.addEventListener('keydown',onKey);
  };

  function close(){
    backdrop.classList.remove('open');
    document.removeEventListener('keydown',onKey);
  }

  function shake(){
    card.classList.remove('shake');
    void card.offsetWidth;
    card.classList.add('shake');
  }

  cancelBtn.addEventListener('click',close);

  backdrop.addEventListener('click',function(e){
    if(e.target===backdrop)shake();
  });

  confirmBtn.addEventListener('click',function(){
    if(currentId)window.location.href='delete.php?id='+currentId;
  });

  function onKey(e){if(e.key==='Escape')close();}
})();
</script>

</body>
</html>