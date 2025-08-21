<?php require_once __DIR__ . '/_config.php'; ?>
<?php
// _footer.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

$BASE = '/oboroya';
$year = date('Y');

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

<footer class="site-footer">
  <div class="inner">
    <div class="f-brand">
      <img src="<?= $BASE ?>/assets/oboroya-mark.svg" alt="朧屋" class="f-mark">
      <div class="f-text">
        <strong class="f-name">朧屋</strong>
        <p class="tagline">技を支える、もうひとつの手。</p>
      </div>
    </div>

    <nav class="f-nav">
      <a href="<?= $BASE ?>/index.php">商品一覧</a>
      <a href="<?= $BASE ?>/cart.php">カート</a>
      <a href="<?= $BASE ?>/my_orders.php">注文履歴</a>
    </nav>
  </div>

  <div class="legal">© <?= $year ?> 朧屋 — All rights reserved.</div>
</footer>
