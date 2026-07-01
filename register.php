<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register_form.html");
    exit;
}

$firstname = isset($_POST['firstname']) ? trim(htmlspecialchars($_POST['firstname'])) : '';
$surname   = isset($_POST['surname'])   ? trim(htmlspecialchars($_POST['surname']))   : '';
$email     = isset($_POST['email'])     ? trim($_POST['email'])                       : '';
$terms     = isset($_POST['terms'])     ? $_POST['terms']                             : 'no';

$success = false;
$error   = '';

if (empty($firstname) || empty($surname) || empty($email)) {
    $error = 'Please fill in all required fields.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
} elseif ($terms !== 'yes') {
    $error = 'You must accept the Terms & Conditions to register.';
} else {
    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=cycling;charset=utf8mb4',
            'root', '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Check for an existing registration with the same email first.
        $check = $pdo->prepare("SELECT id FROM interest WHERE email = :email LIMIT 1");
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            $error = 'That email address has already been registered. Please use a different email or contact us if this is a mistake.';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO interest (firstname, surname, email, terms)
                 VALUES (:firstname, :surname, :email, :terms)"
            );
            $stmt->execute([
                ':firstname' => $firstname,
                ':surname'   => $surname,
                ':email'     => $email,
                ':terms'     => $terms,
            ]);
            $success = true;
        }
    } catch (PDOException $e) {
        // 1062 = MySQL duplicate-entry error (in case you add a UNIQUE
        // constraint on email at the DB level too, which is recommended
        // as a second line of defence against race conditions).
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
            $error = 'That email address has already been registered.';
        } else {
            $error = 'Something went wrong while saving your registration. Please try again.';
            // For local debugging only — remove/comment out in production:
            // $error .= ' (' . $e->getMessage() . ')';
        }
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $success ? 'You\'re In! — Cit-E Cycling' : 'Registration Failed — Cit-E Cycling' ?></title>
<link rel="icon" href="./Resource/Logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ─── tokens ─── */
:root{
  --g:#16a34a; --gd:#15803d; --gl:#22c55e;
  --gfaint:rgba(22,163,74,.08); --gglow:rgba(22,163,74,.18);
  --am:#f59e0b; --amd:#d97706; --amfaint:rgba(245,158,11,.10);
  --red:#ef4444; --redfaint:rgba(239,68,68,.09);
  --ink:#0f172a; --ink2:#1e293b;
  --muted:#64748b; --hairline:#e2e8e6;
  --bg:#f1f5f0; --card:#ffffff;
  --font:'Plus Jakarta Sans',system-ui,sans-serif;
  --mono:'JetBrains Mono',monospace;
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{font-size:15px;scroll-behavior:smooth;}
body{
  font-family:var(--font);color:var(--ink);
  background:var(--bg);min-height:100vh;
  display:flex;flex-direction:column;
  -webkit-font-smoothing:antialiased;
}
a{color:var(--g);text-decoration:none;}
a:hover{color:var(--gd);}

/* ─── navbar ─── */
.nav{
  height:60px;
  background:rgba(255,255,255,.9);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--hairline);
  display:flex;align-items:center;
  padding:0 28px;
  position:sticky;top:0;z-index:100;
}
.nav-in{
  width:100%;max-width:1100px;margin:0 auto;
  display:flex;align-items:center;justify-content:space-between;
}
.nav-logo{
  display:flex;align-items:center;gap:9px;
  text-decoration:none;color:var(--ink);
}
.nav-logo img{width:32px;height:32px;border-radius:50%;border:1.5px solid var(--g);object-fit:cover;}
.nav-logo span{font-weight:800;font-size:.94rem;letter-spacing:-.01em;}
.nav-links{display:flex;gap:20px;font-size:.84rem;font-weight:600;}
.nav-links a{color:var(--muted);transition:color .15s;}
.nav-links a:hover{color:var(--ink);text-decoration:none;}

/* ─── main body ─── */
.body{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:60px 20px;position:relative;overflow:hidden;
}

/* Floating orbs (error state only via body class) */
.body::before,.body::after{
  content:'';position:fixed;border-radius:50%;pointer-events:none;z-index:0;filter:blur(70px);
}
body.is-error .body::before{
  width:380px;height:380px;background:rgba(239,68,68,.07);top:-100px;left:-100px;
  animation:orbA 12s ease-in-out infinite;
}
body.is-error .body::after{
  width:300px;height:300px;background:rgba(239,68,68,.05);bottom:-80px;right:-60px;
  animation:orbB 15s ease-in-out infinite;
}
@keyframes orbA{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(40px,30px) scale(1.1);}}
@keyframes orbB{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(-30px,-25px) scale(1.08);}}

/* ─── loading state ─── */
#loader{
  display:flex;flex-direction:column;align-items:center;
  gap:18px;text-align:center;position:relative;z-index:1;
}

.spin-ring{
  width:72px;height:72px;position:relative;
}
.spin-ring::before{
  content:'';position:absolute;inset:0;
  border-radius:50%;
  border:3px solid var(--gfaint);
}
.spin-ring::after{
  content:'';position:absolute;inset:0;
  border-radius:50%;
  border:3px solid transparent;
  border-top-color:var(--g);
  animation:spin 0.9s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}

.spin-inner{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;
}

.ld-title{
  font-size:1.28rem;font-weight:800;letter-spacing:-.02em;color:var(--ink);
}
.ld-sub{font-size:.86rem;color:var(--muted);line-height:1.5;}

.prog-track{
  width:260px;height:5px;
  background:rgba(22,163,74,.12);
  border-radius:999px;overflow:hidden;
}
.prog-bar{
  height:100%;border-radius:999px;
  background:linear-gradient(90deg,var(--g),var(--gl));
  animation:prog 2.8s cubic-bezier(.4,0,.2,1) forwards;
  width:0;
}
@keyframes prog{0%{width:0}100%{width:100%}}

.ld-steps{
  display:flex;flex-direction:column;gap:8px;
  font-size:.8rem;color:var(--muted);
  text-align:left;
}
.ld-step{
  display:flex;align-items:center;gap:8px;
  opacity:0;animation:fadeIn .4s ease forwards;
}
.ld-step:nth-child(1){animation-delay:.3s;}
.ld-step:nth-child(2){animation-delay:1.0s;}
.ld-step:nth-child(3){animation-delay:1.8s;}
@keyframes fadeIn{to{opacity:1;}}
.ld-dot{
  width:6px;height:6px;border-radius:50%;
  background:var(--g);flex-shrink:0;
}

/* ─── result card ─── */
#result{display:none;width:100%;max-width:520px;position:relative;z-index:1;}

.rcard{
  background:var(--card);
  border:1px solid var(--hairline);
  border-radius:28px;
  overflow:hidden;
  box-shadow:0 0 0 1px rgba(0,0,0,.04), 0 16px 56px rgba(15,23,42,.12), 0 6px 18px rgba(15,23,42,.06);
  animation:cardIn .55s cubic-bezier(.22,1,.36,1);
}
@keyframes cardIn{
  from{opacity:0;transform:translateY(22px) scale(.97);}
  to{opacity:1;transform:translateY(0) scale(1);}
}

/* ── SUCCESS header ── */
.rcard-hdr.success{
  background:linear-gradient(135deg, var(--g) 0%, var(--gl) 60%, var(--am) 130%);
  padding:38px 38px 32px;text-align:center;position:relative;overflow:hidden;
}
.rcard-hdr.success::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(45deg, rgba(255,255,255,.05) 0 2px, transparent 2px 20px);
  pointer-events:none;
}

/* ── ERROR header — matches admin delete popup language ── */
.rcard-hdr.error{
  position:relative;padding:42px 36px 34px;text-align:center;overflow:hidden;
  background:linear-gradient(150deg,#07090f 0%,#1c0404 45%,#6b0e0e 100%);
}
.rcard-hdr.error::before{
  content:'';position:absolute;inset:0;
  background-image:linear-gradient(rgba(239,68,68,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(239,68,68,.06) 1px,transparent 1px);
  background-size:26px 26px;pointer-events:none;
}
.rcard-hdr.error::after{
  content:'';position:absolute;bottom:-70px;left:50%;transform:translateX(-50%);
  width:280px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(239,68,68,.28) 0%,transparent 65%);
  pointer-events:none;
}

/* icon rings (error) */
.err-rings{position:relative;width:86px;height:86px;margin:0 auto 20px;}
.err-ring{position:absolute;border-radius:50%;border:1px solid rgba(239,68,68,.22);inset:0;animation:errRingPulse 2.8s ease-in-out infinite;}
.err-ring:nth-child(2){inset:-11px;border-color:rgba(239,68,68,.13);animation-delay:.55s;}
.err-ring:nth-child(3){inset:-22px;border-color:rgba(239,68,68,.07);animation-delay:1.1s;}
@keyframes errRingPulse{0%,100%{transform:scale(.94);opacity:.9;}50%{transform:scale(1.06);opacity:.35;}}
.err-core{position:absolute;inset:0;border-radius:50%;background:linear-gradient(135deg,rgba(239,68,68,.22),rgba(185,28,28,.28));border:1.5px solid rgba(239,68,68,.42);display:flex;align-items:center;justify-content:center;box-shadow:0 0 28px rgba(239,68,68,.22);}
.err-core svg{width:34px;height:34px;stroke:#fca5a5;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round;filter:drop-shadow(0 0 10px rgba(239,68,68,.6));}

/* icon (success — simple circle bounce) */
.suc-icon-wrap{width:78px;height:78px;border-radius:50%;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.35);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;backdrop-filter:blur(6px);animation:iconBounce .55s .15s cubic-bezier(.34,1.56,.64,1) both;box-shadow:0 0 30px rgba(255,255,255,.25);}
@keyframes iconBounce{from{transform:scale(0);opacity:0;}to{transform:scale(1);opacity:1;}}
.suc-icon-wrap svg{width:36px;height:36px;stroke:#fff;stroke-width:2.4;fill:none;stroke-linecap:round;stroke-linejoin:round;}

.hdr-label{font-family:var(--mono);font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;margin-bottom:8px;position:relative;z-index:1;}
.hdr-label.on-dark{color:rgba(255,255,255,.85);}
.hdr-label.on-green{color:rgba(255,255,255,.85);}

.hdr-title{font-size:1.5rem;font-weight:800;letter-spacing:-.025em;position:relative;z-index:1;}
.hdr-title.on-white{color:#fff;}

/* ─── body ─── */
.rcard-body{padding:32px 36px 34px;text-align:center;}

.rc-desc{font-size:.88rem;color:var(--muted);line-height:1.68;margin-bottom:24px;max-width:400px;margin-left:auto;margin-right:auto;}
.rc-desc strong{color:var(--ink);font-weight:700;}

/* info box (success) */
.rc-info{
  background:var(--gfaint);
  border:1px solid rgba(22,163,74,.14);
  border-radius:14px;padding:16px 18px;
  margin-bottom:24px;text-align:left;
  display:flex;flex-direction:column;gap:10px;
}
.rc-info-row{
  display:flex;align-items:center;gap:10px;font-size:.82rem;
}
.rc-info-row .ico{
  width:28px;height:28px;border-radius:8px;
  background:rgba(22,163,74,.12);color:var(--g);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;font-size:.85rem;
}
.rc-info-row .lbl{color:var(--muted);min-width:52px;}
.rc-info-row .val{font-weight:700;color:var(--ink);word-break:break-all;}

/* error detail box */
.err-detail{
  display:flex;align-items:flex-start;gap:11px;
  background:#fef2f2;border:1px solid #fecaca;
  border-radius:14px;padding:14px 16px;margin-bottom:18px;
  text-align:left;
}
.err-detail svg{width:18px;height:18px;stroke:#dc2626;stroke-width:2;fill:none;flex-shrink:0;margin-top:1px;}
.err-detail-text{font-size:.84rem;color:#991b1b;line-height:1.55;}
.err-detail-text b{display:block;font-weight:800;margin-bottom:2px;}

/* error code ref */
.err-code{
  display:flex;align-items:center;justify-content:center;gap:8px;
  background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;
  padding:8px 14px;font-family:var(--mono);font-size:.7rem;color:#64748b;
  margin-bottom:22px;letter-spacing:.04em;
}
.err-code span{color:#dc2626;font-weight:700;}

/* buttons */
.rc-btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
.btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:11px 22px;border-radius:999px;
  font-family:var(--font);font-size:.84rem;font-weight:700;
  border:none;cursor:pointer;
  transition:all .15s;text-decoration:none;
}
.btn:active{transform:scale(.97);}
.btn-primary{
  background:var(--g);color:#fff;
  box-shadow:0 4px 14px rgba(22,163,74,.28);
}
.btn-primary:hover{background:var(--gd);color:#fff;box-shadow:0 6px 20px rgba(22,163,74,.36);transform:translateY(-1px);}
.btn-outline{
  background:transparent;color:var(--muted);
  border:1.5px solid var(--hairline);
}
.btn-outline:hover{border-color:var(--g);color:var(--g);}
.btn-red{
  background:linear-gradient(135deg,#b91c1c 0%,#ef4444 100%);
  color:#fff;
  box-shadow:0 4px 18px rgba(220,38,38,.36);
  animation:btnGlow 2.6s ease-in-out infinite;
}
@keyframes btnGlow{0%,100%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 0 rgba(220,38,38,.2);}50%{box-shadow:0 4px 18px rgba(220,38,38,.36),0 0 0 7px rgba(220,38,38,0);}}
.btn-red:hover{background:linear-gradient(135deg,#991b1b 0%,#dc2626 100%);color:#fff;box-shadow:0 6px 26px rgba(220,38,38,.5);transform:translateY(-1px);animation:none;}

/* ─── footer ─── */
.footer{
  background:var(--ink2);color:rgba(238,242,234,.5);
  padding:44px 28px 24px;position:relative;z-index:1;
}
.footer-in{
  max-width:1100px;margin:0 auto;
  display:grid;grid-template-columns:1.4fr 1fr 1fr;
  gap:28px;padding-bottom:24px;
  border-bottom:1px solid rgba(255,255,255,.07);
}
.footer-in h3{font-size:1rem;font-weight:800;color:#eef2ea;margin-bottom:9px;}
.footer-in h5{
  font-family:var(--mono);font-size:.62rem;
  letter-spacing:.09em;text-transform:uppercase;
  color:var(--am);margin-bottom:10px;
}
.footer-in p,.footer-in li{font-size:.82rem;margin-bottom:5px;list-style:none;}
.footer-in a{color:rgba(238,242,234,.5);transition:color .15s;}
.footer-in a:hover{color:#eef2ea;text-decoration:none;}
.footer-bottom{
  max-width:1100px;margin:18px auto 0;
  text-align:center;font-family:var(--mono);
  font-size:.7rem;letter-spacing:.03em;
}

/* ─── responsive ─── */
@media(max-width:600px){
  .rcard-body{padding:26px 22px 26px;}
  .rcard-hdr.success,.rcard-hdr.error{padding:32px 24px 26px;}
  .rc-btns{flex-direction:column;align-items:stretch;}
  .footer-in{grid-template-columns:1fr;}
  .nav-links{display:none;}
}
</style>
</head>
<body<?= $success ? '' : ' class="is-error"' ?>>

<!-- ══ NAV ══ -->
<nav class="nav">
  <div class="nav-in">
    <a class="nav-logo" href="index.html">
      <img src="./Resource/Logo.png" alt="Cit-E Cycling">
      <span>Cit-E Cycling</span>
    </a>
    <div class="nav-links">
      <a href="index.html">Home</a>
      <a href="register_form.html">Register Interest</a>
      <a href="admin_login.html">Admin Login</a>
    </div>
  </div>
</nav>

<!-- ══ BODY ══ -->
<div class="body">

  <!-- LOADING -->
  <div id="loader">
    <div class="spin-ring">
      <div class="spin-inner">🚴</div>
    </div>

    <div>
      <p class="ld-title">Registering your interest…</p>
      <p class="ld-sub">Hang tight, this only takes a second.</p>
    </div>

    <div class="prog-track"><div class="prog-bar"></div></div>

    <div class="ld-steps">
      <div class="ld-step"><span class="ld-dot"></span> Validating your details</div>
      <div class="ld-step"><span class="ld-dot"></span> Securing your information</div>
      <div class="ld-step"><span class="ld-dot"></span> Confirming registration</div>
    </div>
  </div>

  <!-- RESULT -->
  <div id="result">
    <div class="rcard">

      <?php if ($success): ?>

      <!-- ════════ SUCCESS HEADER ════════ -->
      <div class="rcard-hdr success">
        <div class="suc-icon-wrap">
          <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="hdr-label on-green">Registration Complete</div>
        <h1 class="hdr-title on-white">You're on the list! 🎉</h1>
      </div>

      <div class="rcard-body">

        <p class="rc-desc">
          Welcome aboard, <strong><?= $firstname ?></strong>. Your interest has been recorded —
          we'll reach out as soon as Cit-E Cycling launches in your area.
        </p>

        <div class="rc-info">
          <div class="rc-info-row">
            <div class="ico">👤</div>
            <span class="lbl">Name</span>
            <span class="val"><?= $firstname ?> <?= htmlspecialchars($_POST['surname']) ?></span>
          </div>
          <div class="rc-info-row">
            <div class="ico">✉️</div>
            <span class="lbl">Email</span>
            <span class="val"><?= htmlspecialchars($_POST['email']) ?></span>
          </div>
          <div class="rc-info-row">
            <div class="ico">✅</div>
            <span class="lbl">Terms</span>
            <span class="val">Accepted</span>
          </div>
        </div>

        <div class="rc-btns">
          <a href="index.html" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Back to Home
          </a>
          <a href="register_form.html" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Register Another
          </a>
        </div>

      </div>

      <?php else: ?>

      <!-- ════════ ERROR HEADER (stunning — matches admin delete popup) ════════ -->
      <div class="rcard-hdr error">
        <div class="err-rings">
          <div class="err-ring"></div>
          <div class="err-ring"></div>
          <div class="err-ring"></div>
          <div class="err-core">
            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
          </div>
        </div>
        <div class="hdr-label on-dark">Something went wrong</div>
        <h1 class="hdr-title on-white">Registration Failed</h1>
      </div>

      <div class="rcard-body">

        <div class="err-detail">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
          <div class="err-detail-text">
            <b>What happened?</b>
            <?= htmlspecialchars($error) ?>
          </div>
        </div>

        <div class="err-code">
          ERR · <span>REG_FAILED</span> · <?= date('H:i:s') ?>
        </div>

        <div class="rc-btns">
          <a href="register_form.html" class="btn btn-red">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 .49-3.17"/></svg>
            Try Again
          </a>
          <a href="index.html" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            Home
          </a>
        </div>

      </div>

      <?php endif; ?>

    </div>
  </div>

</div>

<!-- ══ FOOTER ══ -->
<footer class="footer">
  <div class="footer-in">
    <div>
      <h3>Cit-E Cycling</h3>
      <p>A modern platform for managing cycling events, registrations and administration through one integrated web portal.</p>
    </div>
    <div>
      <h5>Quick Links</h5>
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="register_form.html">Register Interest</a></li>
        <li><a href="admin_login.html">Admin Login</a></li>
      </ul>
    </div>
    <div>
      <h5>Contact</h5>
      <p>info@citecycling.com</p>
    </div>
  </div>
  <p class="footer-bottom">&copy; 2026 Cit-E Cycling · All rights reserved.</p>
</footer>

<script>
/* Show loader for 2.8s then reveal result with smooth transition */
(function(){
  const loader = document.getElementById('loader');
  const result = document.getElementById('result');
  setTimeout(function(){
    loader.style.transition = 'opacity .4s ease';
    loader.style.opacity = '0';
    setTimeout(function(){
      loader.style.display = 'none';
      result.style.display = 'block';
    }, 400);
  }, 2800);
})();
</script>

</body>
</html>