<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | カート</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main">
    <h1>カートの中身</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <p style="color:green;"><?php echo htmlspecialchars($_SESSION['flash']); ?></p>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <p>カートは空です。</p>
        <a href="<?= BASE_PATH ?>/index.php" class="back-link">← 商品一覧に戻る</a>
    <?php else: ?>
        <?php foreach ($cart as $item): ?>
            <div class="cart-item">
                <img src="<?= BASE_PATH ?>/images/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                <div>
                    <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                    <p>価格：¥<?php echo number_format($item['price']); ?></p>
                    
                    <form action="<?= BASE_PATH ?>/update_cart.php" method="POST" style="margin-top:10px;">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">

                        数量：
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px;">

                        <button type="submit" name="action" value="update">更新</button>
                        <button type="submit" name="action" value="delete" onclick="return confirm('本当に削除しますか？');">削除</button>
                    </form>

                    <p>小計：¥<?php echo number_format($item['price'] * $item['quantity']); ?></p>
                </div>
            </div>
            <?php $total += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>

        <p class="total">合計金額：¥ <?php echo number_format($total); ?></p>
        <form action="<?= BASE_PATH ?>/purchase.php" method="post" style="margin-top:20px;">
        <button class="btn" type="submit" onclick="return confirm('購入を確定します。よろしいですか？');">
            購入する
        </button>
        </form>
        <form action="<?= BASE_PATH ?>/clear_cart.php" method="post" style="margin:20px 0 10px 0;">
        <button class="btn" type="submit" onclick="return confirm('カートを空にします。よろしいですか？');">
            カートを空にする
        </button>
        </form>
        <a href="<?= BASE_PATH ?>/index.php" class="back-link">← 買い物を続ける</a>
    <?php endif; ?>
</main>
    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
