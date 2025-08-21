<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$head = $pdo->prepare("
  SELECT o.id, o.created_at, o.total_price, u.name AS user_name, u.email AS user_email
  FROM orders o JOIN users u ON u.id=o.user_id
  WHERE o.id = ?
");
$head->execute([$id]);
$order = $head->fetch(PDO::FETCH_ASSOC);
if (!$order) { header('Location: '.BASE_PATH.'/orders.php'); exit; }

$items = $pdo->prepare("
  SELECT oi.product_id, oi.unit_price, oi.quantity,
         p.name AS product_name, p.image AS product_image
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ?
  ORDER BY oi.id
");
$items->execute([$id]);
$rows = $items->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>管理：注文 #<?= (int)$order['id'] ?></title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__.'/../_header.php'; ?>
<main class="page-main">
  <div class="container">
    <div class="wrap">
      <div class="top" style="display:flex;justify-content:space-between;align-items:center;">
        <h1>注文詳細 #<?= (int)$order['id'] ?></h1>
        <div>
          <a class="btn" href="<?= BASE_PATH ?>/admin/orders.php">一覧へ</a>
          <a class="btn" href="<?= BASE_PATH ?>/admin/order_export.php?id=<?= (int)$order['id'] ?>&csrf=<?= htmlspecialchars($_SESSION['csrf']) ?>">CSV</a>
        </div>
      </div>

      <p class="mt-16">日時：<?= htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))) ?><br>
         顧客：<?= htmlspecialchars($order['user_name']) ?>（<?= htmlspecialchars($order['user_email']) ?>）<br>
         合計：<strong>¥<?= number_format((int)$order['total_price']) ?></strong>
      </p>

      <div class="tablep mt-16">
        <table class="table">
          <thead><tr><th>商品</th><th>画像</th><th>単価</th><th>数量</th><th>小計</th></tr></thead>
          <tbody>
            <?php $sum=0; foreach ($rows as $r): $sub=(int)$r['unit_price']*(int)$r['quantity']; $sum+=$sub; ?>
              <tr>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?php if ($r['product_image']): ?><img src="<?= BASE_PATH ?>/images/<?= htmlspecialchars($r['product_image']) ?>" style="width:60px;height:60px;object-fit:contain;border-radius:6px;margin:0 auto;display:block;"><?php endif; ?></td>
                <td data-label="単価" style="text-align:right;">¥<?= number_format((int)$r['unit_price']) ?></td>
                <td data-label="数量" style="text-align:right;"><?= (int)$r['quantity'] ?></td>
                <td data-label="小計" style="text-align:right;">¥<?= number_format($sub) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr><td colspan="4" style="text-align:right;"><strong>小計（再計算）</strong></td>
                <td style="text-align:right;"><strong>¥<?= number_format($sum) ?></strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php include __DIR__.'/../_footer.php'; ?>
</body>
</html>
