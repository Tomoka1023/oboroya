<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
require 'auth_check.php'; // ログイン必須（ここで session_start 済み）

$userId  = $_SESSION['user_id'] ?? 0;
$orderId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
  header('Location: '.BASE_PATH.'/my_orders.php'); exit;
}

/** 注文ヘッダー（本人の注文かチェック） */
$head = $pdo->prepare("
  SELECT id, user_id, total_price, created_at
  FROM orders
  WHERE id = ? AND user_id = ?
");
$head->execute([$orderId, $userId]);
$order = $head->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  // 存在しない or 他人の注文 → 履歴へ
  header('Location: '.BASE_PATH.'/my_orders.php'); exit;
}

/** 注文明細（購入時単価が無いDBでも動くように COALESCE） */
$itemsStmt = $pdo->prepare("
  SELECT 
    oi.product_id,
    oi.quantity,
    COALESCE(oi.unit_price, p.price) AS unit_price,
    p.name  AS product_name,
    p.image AS product_image
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ?
  ORDER BY oi.id ASC
");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// 小計合計（表示用に再計算：orders.total_price はDB保存値）
$calcTotal = 0;
foreach ($items as $it) {
  $calcTotal += (int)$it['unit_price'] * (int)$it['quantity'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>注文詳細 #<?php echo htmlspecialchars($order['id']); ?></title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main">
  <div class="container">
    <h1>注文詳細</h1>
    <div class="card">
      <div class="meta">
        注文番号：#<?php echo htmlspecialchars($order['id']); ?><br>
        注文日時：<?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))); ?>
      </div>

      <div class="grid">
        <?php foreach ($items as $it): ?>
          <?php
            $unit = (int)$it['unit_price'];
            $qty  = (int)$it['quantity'];
            $sub  = $unit * $qty;
          ?>
          <div class="item">
            <img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($it['product_image']); ?>" alt="">
            <div>
              <div class="name"><?php echo htmlspecialchars($it['product_name']); ?></div>
              <div class="price-row">
                単価：¥<?php echo number_format($unit); ?> ／ 数量：<?php echo $qty; ?> ／ 小計：¥<?php echo number_format($sub); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="total">
        合計（保存値）：¥<?php echo number_format((int)$order['total_price']); ?><br>
        合計（再計算）：¥<?php echo number_format($calcTotal); ?>
      </div>

      <div class="actions">
        <a class="btn" href="<?= BASE_PATH ?>/my_orders.php">← 注文履歴へ戻る</a>
        <a class="btn" href="<?= BASE_PATH ?>/index.php">商品一覧へ</a>
      </div>
    </div>
  </div>
</main>
  <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
