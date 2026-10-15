<?php
require_once __DIR__ . '/icons.php';
$pageLede = $pageLede ?? '';
$activeNav = $activeNav ?? '';
$showPageHead = $showPageHead ?? true;
$currentUser = $_SESSION['admin_user'] ?? $textbotlang['panel']['layoutDefaultAdminName'];
$initials = mb_strtoupper(mb_substr($currentUser, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
  <meta name="theme-color" content="#07090E" id="mtc">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title><?= htmlspecialchars($pageTitle ? $pageTitle . ' — NOVA-X-PANEL' : 'NOVA-X-PANEL · تلگرام شاپ') ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/cyberpunk_theme.css">
  <script>
    (function () {
      var t = 'navy';
      var bg = '#07090E';
      var root = document.documentElement;
      root.style.backgroundColor = bg;
      root.setAttribute('data-theme', 'navy');
      root.style.colorScheme = 'dark';
      var mtc = document.getElementById('mtc');
      if (mtc) mtc.content = bg;
      if (localStorage.getItem('panel-sb-collapsed') === '1' && window.innerWidth > 768)
        root.classList.add('sb-pre-collapsed');
    }());
  </script>
  <script>
    window.PANEL_I18N = <?= json_encode(
      array_filter(
        $textbotlang['panel'] ?? [],
        fn($k) => strncmp($k, 'js_', 3) === 0,
        ARRAY_FILTER_USE_KEY
      ),
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;
    window.t = function (key, vars) {
      var s = (window.PANEL_I18N && window.PANEL_I18N[key]) || key;
      if (vars) {
        for (var k in vars) {
          if (Object.prototype.hasOwnProperty.call(vars, k)) {
            s = s.replace('{' + k + '}', vars[k]);
          }
        }
      }
      return s;
    };
  </script>
</head>

<body>

  <div id="load-bar"></div>
  <div id="toast-area"></div>

  <div class="confirm-veil" id="confirm-veil">
    <div class="confirm-box">
      <div class="confirm-icon"><?= icon('block', 26) ?></div>
      <h4 id="confirm-title"><?= $textbotlang['panel']['layoutNavDashboard'] ?></h4>
      <p id="confirm-msg"><?= $textbotlang['panel']['layoutNavUsers'] ?></p>
      <div class="confirm-btns">
        <button class="btn btn-no" id="confirm-ok"><?= $textbotlang['panel']['layoutNavOrders'] ?></button>
        <button class="btn btn-ghost"
          onclick="closeConfirm()"><?= $textbotlang['panel']['layoutNavServices'] ?></button>
      </div>
    </div>
  </div>

  <div class="app">
    <div class="sidebar-backdrop" id="backdrop" onclick="closeSidebar()"></div>

    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark">NX</div>
        <div class="brand-name">NOVA · SHOP<span>مدیریت تلگرام شاپ اختصاصی NOVA</span></div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-heading">داشبورد و آمار</div>
          <a href="index.php" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleDashboard'] ?>">
            <span class="nav-icon"><?= icon('dashboard') ?></span><span
              class="nav-label">داشبورد</span>
          </a>
        </div>
        <div class="nav-section">
          <div class="nav-heading">مدیریت کاربران و سفارشات</div>
          <a href="users.php" class="nav-item <?= $activeNav === 'users' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleUsers'] ?>">
            <span class="nav-icon"><?= icon('users') ?></span><span
              class="nav-label">کاربران ربات</span>
          </a>
          <a href="invoice.php" class="nav-item <?= $activeNav === 'invoice' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleInvoice'] ?>">
            <span class="nav-icon"><?= icon('invoice') ?></span><span
              class="nav-label">فاکتورها و تراکنش‌ها</span>
          </a>
          <a href="service.php" class="nav-item <?= $activeNav === 'service' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleService'] ?>">
            <span class="nav-icon"><?= icon('server') ?></span><span
              class="nav-label">سرورها و اینباندها</span>
          </a>
          <a href="product.php" class="nav-item <?= $activeNav === 'product' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleProduct'] ?>">
            <span class="nav-icon"><?= icon('package') ?></span><span
              class="nav-label">محصولات و پلن‌ها</span>
          </a>
          <a href="category.php" class="nav-item <?= $activeNav === 'category' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['categoryPageTitle'] ?>">
            <span class="nav-icon"><?= icon('folder') ?></span><span
              class="nav-label">دسته‌بندی پلن‌ها</span>
          </a>
          <a href="payment.php" class="nav-item <?= $activeNav === 'payment' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitlePayment'] ?>">
            <span class="nav-icon"><?= icon('card') ?></span><span
              class="nav-label">درگاه‌های پرداخت</span>
          </a>
          <a href="keyboard.php" class="nav-item <?= $activeNav === 'keyboard' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleKeyboard'] ?>">
            <span class="nav-icon">
              <?= icon('settings') ?>
            </span><span class="nav-label">دکمه‌های کیبورد ربات</span>
          </a>
          <a href="bottext.php" class="nav-item <?= $activeNav === 'bottext' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['bottextPageTitle'] ?>">
            <span class="nav-icon"><?= icon('edit') ?></span><span
              class="nav-label">متن‌های ربات</span>
          </a>
        </div>
        <div class="nav-section">
          <div class="nav-heading">سیستم و پیکربندی</div>
          <a href="settings.php" class="nav-item <?= $activeNav === 'settings' ? 'active' : '' ?>"
            title="<?= $textbotlang['panel']['layoutPageTitleSettings'] ?>">
            <span class="nav-icon"><?= icon('settings') ?></span><span
              class="nav-label">تنظیمات عمومی</span>
          </a>
          <a href="logout.php" class="nav-item" title="<?= $textbotlang['panel']['layoutPageTitleLogout'] ?>">
            <span class="nav-icon"><?= icon('logout') ?></span><span
              class="nav-label">خروج از حساب</span>
          </a>
        </div>
      </nav>
      <div class="sidebar-foot">
        <div class="user-pill">
          <div class="user-mono"><?= htmlspecialchars($initials) ?></div>
          <div class="user-info">
            <div class="uname"><?= htmlspecialchars($currentUser) ?></div>
            <div class="urole" style="color:#22d3ee;font-weight:700;">مدیر ارشد نوا</div>
          </div>
        </div>
      </div>
    </aside>

    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button class="icon-btn menu-toggle" onclick="openSidebar()"><?= icon('menu', 18) ?></button>
          <button class="icon-btn sb-toggle" onclick="toggleSidebar()"><?= icon('menu', 17) ?></button>
          <div>
            <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
            <div class="crumb"><span>NOVA-X-PANEL</span><span
                style="opacity:.4;margin:0 4px">/</span><span><?= htmlspecialchars($pageTitle) ?></span></div>
          </div>
        </div>
        <div class="topbar-tools">
          <div style="display:flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);font-size:0.75rem;color:#34d399;font-weight:700;margin-left:8px;">
            <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981;"></span>
            <span>هسته NOVA-X فعال</span>
          </div>
          <a href="settings.php" class="icon-btn"
            title="<?= $textbotlang['panel']['layoutPageTitleSettings'] ?>"><?= icon('settings', 16) ?></a>
          <a href="logout.php" class="icon-btn"
            title="<?= $textbotlang['panel']['layoutPageTitleLogout'] ?>"><?= icon('logout', 16) ?></a>
        </div>
      </header>
      <main class="content">
        <?php
        $s = get_flash('success');
        $e = get_flash('error');
        $w = get_flash('warning');
        if ($s): ?>
          <div class="notice notice-ok"><?= htmlspecialchars($s) ?></div><?php endif;
        if ($e): ?>
          <div class="notice notice-no"><?= htmlspecialchars($e) ?></div><?php endif;
        if ($w): ?>
          <div class="notice notice-warn"><?= htmlspecialchars($w) ?></div><?php endif;
        if ($showPageHead): ?>
          <div class="page-head fade-up">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if ($pageLede): ?>
              <p><?= htmlspecialchars($pageLede) ?></p><?php endif; ?>
          </div>
        <?php endif; ?>
