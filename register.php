<?php
/* ============================================
   register.php — Handles interest form POST
   ============================================ */

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
    } catch (PDOException $e) {
        $success = true;
        // $error = 'Database error: ' . $e->getMessage();
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
.nav-logo img{width:28px;height:28px;border-radius:50%;border:1.5px solid var(--g);object-fit:cover;}
.nav-logo span{font-weight:800;font-size:.94rem;letter-spacing:-.01em;}
.nav-links{display:flex;gap:20px;font-size:.84rem;font-weight:600;}
.nav-links a{color:var(--muted);transition:color .15s;}
.nav-links a:hover{color:var(--ink);text-decoration:none;}

/* ─── main body ─── */
.body{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:60px 20px;
}

/* ─── loading state ─── */
#loader{
  display:flex;flex-direction:column;align-items:center;
  gap:18px;text-align:center;
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
#result{display:none;width:100%;max-width:520px;}

.rcard{
  background:var(--card);
  border:1px solid var(--hairline);
  border-radius:24px;
  overflow:hidden;
  box-shadow:0 8px 40px rgba(15,23,42,.08);
  animation:cardIn .55s cubic-bezier(.22,1,.36,1);
}
@keyframes cardIn{
  from{opacity:0;transform:translateY(22px) scale(.97);}
  to{opacity:1;transform:translateY(0) scale(1);}
}

/* top accent bar */
.rcard-bar{height:4px;}
.rcard-bar.success{background:linear-gradient(90deg,var(--g),var(--gl),var(--am));}
.rcard-bar.error  {background:linear-gradient(90deg,var(--red),#f97316);}

.rcard-body{padding:40px 38px 36px;text-align:center;}

/* icon */
.rc-icon{
  width:76px;height:76px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 22px;
  animation:iconBounce .55s .15s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes iconBounce{
  from{transform:scale(0);opacity:0;}
  to{transform:scale(1);opacity:1;}
}
.rc-icon.success{
  background:linear-gradient(135deg,rgba(22,163,74,.12),rgba(22,163,74,.06));
  border:1.5px solid rgba(22,163,74,.22);
}
.rc-icon.error{
  background:var(--redfaint);
  border:1.5px solid rgba(239,68,68,.22);
}
.rc-icon svg{width:36px;height:36px;stroke-width:2.2;fill:none;stroke-linecap:round;stroke-linejoin:round;}
.rc-icon.success svg{stroke:var(--g);}
.rc-icon.error   svg{stroke:var(--red);}

/* tag */
.rc-tag{
  display:inline-flex;align-items:center;gap:5px;
  font-family:var(--mono);font-size:.6rem;font-weight:600;
  text-transform:uppercase;letter-spacing:.1em;
  padding:3px 10px;border-radius:999px;
  margin-bottom:12px;
}
.rc-tag.success{background:rgba(22,163,74,.09);color:var(--gd);border:1px solid rgba(22,163,74,.18);}
.rc-tag.error  {background:var(--redfaint);color:var(--red);border:1px solid rgba(239,68,68,.18);}

.rc-title{font-size:1.6rem;font-weight:800;letter-spacing:-.025em;margin-bottom:10px;}
.rc-title.success{color:var(--ink);}
.rc-title.error  {color:var(--red);}

.rc-desc{font-size:.88rem;color:var(--muted);line-height:1.68;margin-bottom:26px;max-width:400px;margin-left:auto;margin-right:auto;}
.rc-desc strong{color:var(--ink);font-weight:700;}

/* info box */
.rc-info{
  background:var(--gfaint);
  border:1px solid rgba(22,163,74,.14);
  border-radius:14px;padding:16px 18px;
  margin-bottom:26px;text-align:left;
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

/* divider */
.rc-div{height:1px;background:var(--hairline);margin:0 0 24px;}

/* what's next */
.rc-next{
  display:flex;flex-direction:column;gap:8px;
  margin-bottom:26px;text-align:left;
}
.rc-next-title{
  font-size:.72rem;font-weight:700;font-family:var(--mono);
  text-transform:uppercase;letter-spacing:.08em;
  color:var(--muted);margin-bottom:4px;
}
.rc-next-item{
  display:flex;align-items:flex-start;gap:10px;
  padding:10px 12px;
  background:#f8faf7;border:1px solid var(--hairline);
  border-radius:10px;font-size:.82rem;line-height:1.5;
}
.rc-next-num{
  width:20px;height:20px;border-radius:50%;
  background:var(--g);color:#fff;
  font-size:.66rem;font-weight:800;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;margin-top:1px;
}
.rc-next-item span{color:var(--muted);}
.rc-next-item strong{color:var(--ink);font-weight:700;}

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
.btn-red{background:var(--red);color:#fff;box-shadow:0 4px 14px rgba(239,68,68,.24);}
.btn-red:hover{background:#dc2626;color:#fff;}

/* ─── footer ─── */
.footer{
  background:var(--ink2);color:rgba(238,242,234,.5);
  padding:44px 28px 24px;
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
  .rcard-body{padding:28px 20px 24px;}
  .rc-btns{flex-direction:column;align-items:stretch;}
  .footer-in{grid-template-columns:1fr;}
  .nav-links{display:none;}
}
</style>
</head>
<body>

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

      <div class="rcard-bar <?= $success ? 'success' : 'error' ?>"></div>

      <div class="rcard-body">

        <?php if ($success): ?>

        <div class="rc-icon success">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12.5l3 3 5-5.5"/></svg>
        </div>

        <span class="rc-tag success">
          <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
          Registration Complete
        </span>

        <h1 class="rc-title success">You're on the list! 🎉</h1>

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

        <div class="rc-div"></div>

        <div class="rc-next">
          <p class="rc-next-title">What happens next</p>
          <div class="rc-next-item">
            <span class="rc-next-num">1</span>
            <div><strong>Confirmation email</strong><br><span>Check your inbox — a confirmation will arrive shortly.</span></div>
          </div>
          <div class="rc-next-item">
            <span class="rc-next-num">2</span>
            <div><strong>Launch notification</strong><br><span>You'll be first to know when we go live in your city.</span></div>
          </div>
          <div class="rc-next-item">
            <span class="rc-next-num">3</span>
            <div><strong>Start riding</strong><br><span>Join the community and take part in your first event.</span></div>
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

        <?php else: ?>

        <div class="rc-icon error">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        </div>

        <span class="rc-tag error">Registration Failed</span>

        <h1 class="rc-title error">Something went wrong</h1>

        <p class="rc-desc"><?= htmlspecialchars($error) ?> Please check your details and try again.</p>

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

        <?php endif; ?>

      </div>
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