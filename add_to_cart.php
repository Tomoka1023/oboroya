<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    header('Location: '.BASE_PATH.'/index.php');
    exit;
}

$product_id = (int)$_POST['product_id'];
$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$qty = max(1, $qty); // 最低1

// 商品情報を取得
$stmt = $pdo->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = '商品が見つかりませんでした。';
    header('Location: '.BASE_PATH.'/cart.php');
    exit;
}
if ((int)$product['stock'] <= 0) {
    $_SESSION['error'] = '在庫切れのため追加できません。';
    header('Location: '.BASE_PATH.'/cart.php'); 
    exit;
}

// カート初期化
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// 既にカートにある数量
$current = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id]['quantity'] : 0;

// 追加できる上限 = 在庫 - いまカートにある数
$maxAddable = max(0, (int)$product['stock'] - $current);
$addQty = min($qty, $maxAddable);

if ($addQty <= 0) {
    $_SESSION['error'] = '在庫数を超えて追加はできません。';
    header('Location: '.BASE_PATH.'/cart.php'); exit;
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] = $current + $addQty;
} else {
    $_SESSION['cart'][$product_id] = [
        'id'    => $product['id'],
        'name'  => $product['name'],
        'price' => (int)$product['price'],
        'image' => $product['image'],
        'quantity' => $addQty
    ];
}

if ($addQty < $qty) {
    $_SESSION['flash'] = '在庫上限まで追加しました。';
} else {
    $_SESSION['flash'] = 'カートに追加しました。';
}

header('Location: '.BASE_PATH.'/cart.php'); exit;
?>