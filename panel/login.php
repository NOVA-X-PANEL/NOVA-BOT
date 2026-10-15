<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/icons.php';
session_start();

if (!empty($_SESSION['admin_user'])) {
  header('Location: ' . panel_url('index.php'));
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

  if (!csrf_check_value($_POST['_csrf'] ?? '')) {
    $error = $textbotlang['panel']['loginWrongCredentials'];
  } elseif ($username === '' || $password === '') {
    $error = $textbotlang['panel']['loginEnterCredentials'];
  } elseif (!check_login_rate($ip)) {
    $error = $textbotlang['panel']['loginTooManyAttempts'];
    error_log("Login rate limit hit for IP: $ip username: $username");
  } else {
    $admin = select("admin", "*", "username", $username, "select");

    $dummyHash = '$2y$10$dummy.hash.for.timing.attack.prevention.xxxxxxxxxxxxxxxx';
    $storedHash = $admin ? (string) $admin['password'] : $dummyHash;

    $isCorrect = false;
    $storedIsHash = str_starts_with($storedHash, '$2') || str_starts_with($storedHash, '$argon2');
    if ($storedIsHash) {
      $isCorrect = password_verify($password, $storedHash);
    } elseif ($admin) {
      $isCorrect = hash_equals($storedHash, $password);
    }

    if ($isCorrect && $admin && $admin['rule'] === 'administrator') {
      if (!str_starts_with($admin['password'], '$2')) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        update("admin", "password", $hash, "username", $username);
      }
      clear_login_rate($ip);
      session_regenerate_id(true);
      $_SESSION['admin_user'] = $admin['username'];
      $_SESSION['login_time'] = time();
      flash('success', 'خوش آمدید، مدیر گرامی');
      header('Location: ' . panel_url('index.php'));
      exit;
    } else {
      $error = 'اطلاعات ورود اشتباه است یا دسترسی مدیریت ندارید.';
      error_log("Failed login for username: $username from IP: $ip");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <meta name="theme-color" content="#07090E" id="mtc">
  <title>NOVA-X-PANEL · ورود به مدیریت تلگرام شاپ</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/cyberpunk_theme.css">
  <script>(function () { document.documentElement.setAttribute('data-theme', 'navy'); document.documentElement.style.backgroundColor = '#07090e'; })();</script>
</head>

<body>
  <div class="auth">
    <aside class="auth-aside">
      <div class="auth-mark">
        <div class="dot">NX</div>
        <span>NOVA-X-PANEL</span>
      </div>
      <div class="auth-quote">
        <div style="display:inline-block;padding:6px 14px;border-radius:20px;background:rgba(34,211,238,0.12);border:1px solid rgba(34,211,238,0.3);color:#22d3ee;font-size:0.75rem;font-weight:700;margin-bottom:14px;letter-spacing:0.04em;">
          ⚡ TELEGRAM SHOP ENGINE · NOVA CORE
        </div>
        <h2 style="font-size:1.45rem;font-weight:800;line-height:1.7;color:#f8fafc;">
          مدیریت هوشمند فروشگاه تلگرام<br>
          <span style="background:linear-gradient(135deg,#22d3ee,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;">DARK VPN & NOVA PANEL</span>
        </h2>
        <cite style="color:#94a3b8;font-size:0.82rem;margin-top:12px;display:block;line-height:1.6;">
          سیستم خودکار صدور کانفیگ، مدیریت کلاینت‌های Xray، تمدید اشتراک و تراکنش‌های مالی
        </cite>
      </div>
      <div class="auth-foot" style="color:#64748b;font-size:0.78rem;">
        © <?= date('Y') ?> · NOVA-X-PANEL v2.6.15 · زیرساخت ابری اختصاصی
      </div>
    </aside>
    <main class="auth-main">
      <div class="auth-box" style="animation:fadeUp .5s ease-out">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 10px #10b981;"></span>
          <span style="font-size:0.78rem;color:#22d3ee;font-weight:700;letter-spacing:0.06em;">NOVA CYBERPUNK CONSOLE</span>
        </div>
        <h1 style="font-size:1.85rem;font-weight:900;margin-bottom:8px;">ورود به مدیریت</h1>
        <p class="lede" style="color:#94a3b8;margin-bottom:26px;">شناسه و رمز عبور مدیریت ربات را جهت ورود وارد نمایید.</p>
        <?php if ($error): ?>
          <div class="notice notice-no" style="margin-bottom:20px"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form class="auth-form" method="POST" autocomplete="on">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <div class="field">
            <label for="username" style="font-weight:700;color:#cbd5e1;">نام کاربری مدیر</label>
            <input type="text" id="username" name="username" class="input" placeholder="842798945 یا admin"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required autofocus
              maxlength="100">
          </div>
          <div class="field">
            <label for="password" style="font-weight:700;color:#cbd5e1;">رمز عبور مدیریت</label>
            <input type="password" id="password" name="password" class="input" placeholder="••••••••"
              autocomplete="current-password" required maxlength="200">
          </div>
          <button type="submit" class="btn btn-primary" id="loginBtn" style="margin-top:8px;">
            <span id="loginText">ورود به کنترل‌پنل نوا</span>
            <span id="loginSpin"
              style="display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
          </button>
        </form>
        <div class="auth-bottom" style="color:#64748b;font-size:0.76rem;margin-top:24px;">
          🔐 امنیت شبکه احراز هویت شده | رمزنگاری ۲۵۶ بیتی TLS v1.3
        </div>
      </div>
    </main>
  </div>
  <script src="js/login.js"></script>
</body>

</html>
