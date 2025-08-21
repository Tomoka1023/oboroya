<?php require_once __DIR__ . '/_config.php'; ?>
<?php
// どのページから include されても動くように
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php'; // ここが重要！__DIR__で確実に読む

$BASE = '/oboroya'; // ルート相対のベース。環境に合わせて必要なら変更

$cartCount = 0;
foreach (($_SESSION['cart'] ?? []) as $it) $cartCount += (int)$it['quantity'];

$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin = 0;
if ($isLoggedIn) {
  $st = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
  $st->execute([$_SESSION['user_id']]);
  $isAdmin = (int)($st->fetchColumn() ?: 0);
}
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<link rel="stylesheet" href="<?= $BASE ?>/assets/styles.css">

<div class="site-header">
  <div class="inner">
  <div class="brand">
    <a href="<?= $BASE ?>/index.php" class="brand-link">
      <img src="<?= $BASE ?>/assets/oboroya-mark.svg" alt="朧屋ロゴ" class="brand-mark">
      <span class="brand-text">朧屋</span>
    </a>
  </div>

  <button class="hamburger" id="navToggle"
          aria-label="メニュー" aria-controls="siteNav" aria-expanded="false">
      <span></span><span></span><span></span>
  </button>

    <div class="nav" id="siteNav">
      <a href="<?= $BASE ?>/index.php" class="<?= $current==='index.php'?'active':'' ?>">商品一覧</a>
      <a href="<?= $BASE ?>/cart.php" class="<?= $current==='cart.php'?'active':'' ?>">
        🛒 カート<?= $cartCount ? ' <span class="badge">'.$cartCount.'</span>' : '' ?>
      </a>
      <?php if ($isLoggedIn): ?>
        <a href="<?= $BASE ?>/my_orders.php" class="<?= $current==='my_orders.php'?'active':'' ?>">注文履歴</a>
        <?php if ($isAdmin): ?>
          <a href="<?= $BASE ?>/admin/products.php">管理：商品</a>
          <a href="<?= $BASE ?>/admin/orders.php">管理：注文</a>
        <?php endif; ?>
        <span>ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?> さん</span>
        <a href="<?= $BASE ?>/logout.php">ログアウト</a>
      <?php else: ?>
        <a href="<?= $BASE ?>/login.php" class="<?= $current==='login.php'?'active':'' ?>">ログイン</a>
        <a href="<?= $BASE ?>/register.php" class="<?= $current==='register.php'?'active':'' ?>">新規登録</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="nav-backdrop" id="navBackdrop"></div>  
</div>
<script>
(() => {
  const btn = document.getElementById('navToggle');
  const backdrop = document.getElementById('navBackdrop');
  const close = () => { document.body.classList.remove('nav-open'); btn.setAttribute('aria-expanded','false'); };
  const toggle = () => {
    const on = document.body.classList.toggle('nav-open');
    btn.setAttribute('aria-expanded', on ? 'true' : 'false');
  };
  btn?.addEventListener('click', toggle);
  backdrop?.addEventListener('click', close);
  window.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  // メニュー内のリンクをタップしたら閉じる
  document.getElementById('siteNav')?.addEventListener('click', e => {
    if (e.target.closest('a')) close();
  });
})();
</script>