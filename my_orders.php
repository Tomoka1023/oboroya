<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
require 'auth_check.php'; // ← ログイン必須

$userId = $_SESSION['user_id'];

// 注文＋明細＋商品をまとめて取得
$sql = "
  SELECT 
    o.id AS order_id,
    o.total_price,
    o.created_at,
    oi.product_id,
    oi.unit_price,
    oi.quantity,
    p.name AS product_name,
    p.image AS product_image
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.id
  JOIN products p ON p.id = oi.product_id
  WHERE o.user_id = ?
  ORDER BY o.created_at DESC, oi.id ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PHP側で注文単位にグルーピング
$orders = [];
foreach ($rows as $r) {
  $oid = $r['order_id'];
  if (!isset($orders[$oid])) {
    $orders[$oid] = [
      'order_id'    => $oid,
      'total_price' => (int)$r['total_price'],
      'created_at'  => $r['created_at'],
      'items'       => [],
    ];
  }
  $orders[$oid]['items'][] = [
    'product_id'   => (int)$r['product_id'],
    'product_name' => $r['product_name'],
    'product_image'=> $r['product_image'],
    'unit_price'   => (int)$r['unit_price'],
    'quantity'     => (int)$r['quantity'],
  ];
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>朧屋 | 注文履歴</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main">
  <div class="container">
    <h1>注文履歴</h1>
  <div class="orders-grid">
    <?php if (empty($orders)): ?>
      <div class="empty">
        <p>まだ注文がありません。</p>
        <!-- <p><a href="index.php" class="btn">商品一覧へ</a></p> -->
      </div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-head">
            <div class="order-meta">
              注文番号：#<?php echo htmlspecialchars($order['order_id']); ?><br>
              注文日時：<?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))); ?><br>
                <a class="btn" href="<?= BASE_PATH ?>/order_detail.php?id=<?php echo (int)$order['order_id']; ?>">
                詳細を見る
                </a>
            </div>
            <div class="total">
              合計：¥<?php echo number_format($order['total_price']); ?>
            </div>
          </div>

          <div class="items">
            <?php foreach ($order['items'] as $it): ?>
              <div class="item">
                <img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($it['product_image']); ?>" alt="">
                <div>
                  <div class="item-name"><?php echo htmlspecialchars($it['product_name']); ?></div>
                    <div class="item-qty">
                        数量：<?php echo (int)$it['quantity']; ?>
                        ／ 小計：¥<?php echo number_format(((int)($it['unit_price'] ?? 0)) * (int)$it['quantity']); ?>
                    </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
    <p><a href="<?= BASE_PATH ?>/index.php" class="btn">← 商品一覧へ戻る</a></p>
  </div>
</main>
  <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
