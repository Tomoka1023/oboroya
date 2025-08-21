<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { header('Location: '.BASE_PATH.'/admin/products.php'); exit; }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | 商品編集</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/../_header.php'; ?>
<main class="page-main">
<div class="wrap">
    <div class="top">
      <h1>商品編集 #<?php echo (int)$p['id']; ?></h1>
    </div>
    <div class="tablep">
      <form action="<?= BASE_PATH ?>/admin/product_update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
        <div>商品名：<input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" required></div>
        <div>価格：<input type="number" name="price" min="0" value="<?php echo (int)$p['price']; ?>" required></div>
        <div>在庫：<input type="number" name="stock" min="0" value="<?php echo (int)$p['stock']; ?>" required></div>
        <div>画像：<input type="file" name="image" accept="image/*">
          <?php if ($p['image']): ?>
            <div>現在：<img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($p['image']); ?>" style="width:80px;height:80px;object-fit:contain;"></div>
          <?php endif; ?>
        </div>
        <div>説明：<br><textarea name="description" rows="6" cols="60"><?php echo htmlspecialchars($p['description']); ?></textarea></div>
        <button type="submit">更新する</button>
      </form>
    </div>
</div>
  <p><a href="<?= BASE_PATH ?>/admin/products.php">← 一覧に戻る</a></p>
</main>
  <?php include __DIR__ . '/../_footer.php'; ?>
</body>
</html>
