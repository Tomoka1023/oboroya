<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

// IDが渡っていない場合は一覧へ
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: '.BASE_PATH.'/index.php');
    exit;
}

$id = (int)$_GET['id'];

// 商品を取得
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// 見つからなければ一覧へ
if (!$product) {
    header('Location: '.BASE_PATH.'/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> | 商品詳細</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main">
    <div class="product-detail">
        <img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($product['image']); ?>" alt="">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p>¥<?php echo number_format($product['price']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <p>在庫：<?php echo $product['stock']; ?></p>

        <?php $stock = (int)$product['stock']; ?>
        <form action="<?= BASE_PATH ?>/add_to_cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            数量：
            <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>" style="width:80px;">
            <button type="submit" <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                カートに入れる
            </button>
        </form>
        <?php if ($stock <= 0): ?>
            <p style="color:red;">在庫切れです</p>
        <?php endif; ?>

        <a class="back-link" href="<?= BASE_PATH ?>/index.php">← 商品一覧に戻る</a>
    </div>
</main>
    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
