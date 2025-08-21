<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | 商品一覧</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">

<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main">
    <h1>商品一覧</h1>
    <div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product">
            <img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($product['image']); ?>" alt="">
            <h2>
                <a href="<?= BASE_PATH ?>/detail.php?id=<?php echo $product['id']; ?>">
                    <?php echo htmlspecialchars($product['name']); ?>
                </a>
            </h2>
            <p>¥<?php echo number_format($product['price']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
