<?php
 require __DIR__ . '/admin_check.php';
 require_once __DIR__ . '/../_config.php';
 require_once __DIR__ . '/../db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | 商品追加</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/../_header.php'; ?>
<main class="page-main">
<div class="wrap">
  <div class="top">
    <h1>商品追加</h1>
  </div>
  <div class="tablep">
    <form action="<?= BASE_PATH ?>/admin/product_create.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
      <div>商品名：<input type="text" name="name" required></div>
      <div>価格：<input type="number" name="price" min="0" required></div>
      <div>在庫：<input type="number" name="stock" min="0" required></div>
      <div>画像：<input type="file" name="image" accept="image/*"></div>
      <div>説明：<br><textarea name="description" rows="6" cols="60"></textarea></div>
      <button type="submit">登録する</button>
    </form>
  </div>
</div>
  <p><a href="<?= BASE_PATH ?>/admin/products.php">← 一覧に戻る</a></p>
</main>
  <?php include __DIR__ . '/../_footer.php'; ?>
</body>
</html>
