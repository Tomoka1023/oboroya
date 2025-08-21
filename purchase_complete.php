<?php
require_once __DIR__ . '/_config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// 自分の注文か簡易チェック（任意）
if ($orderId && !empty($_SESSION['user_id'])) {
  $st = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?'); // ← FROM に修正
  $st->execute([$orderId]);
  $owner = (int)($st->fetchColumn() ?: 0);
  if ($owner !== (int)$_SESSION['user_id']) {
    $orderId = 0;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex">
  <title>朧屋 | 購入完了</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
  <?php include __DIR__ . '/_header.php'; ?>  <!-- ← ここで1回だけ -->
  <main class="page-main">
    <h1>ご購入ありがとうございました！</h1>
    <?php if ($orderId): ?>
      <p>注文番号：<?= htmlspecialchars($orderId) ?></p>
      <p class="mt-16">
        <a class="btn btn-primary" href="<?= BASE_PATH ?>/my_orders.php">注文履歴を見る</a>
        <a class="btn" href="<?= BASE_PATH ?>/index.php">商品一覧へ戻る</a>
      </p>
    <?php else: ?>
      <p>注文情報を確認できませんでした。</p>
      <p class="mt-16"><a class="btn" href="<?= BASE_PATH ?>/index.php">商品一覧へ</a></p>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
