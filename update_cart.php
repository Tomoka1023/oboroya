<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_POST['product_id'], $_POST['action']) || !is_numeric($_POST['product_id'])) {
    header('Location: '.BASE_PATH.'/cart.php'); exit;
}

$product_id = (int)$_POST['product_id'];
$action = $_POST['action'];

// カート上に対象が無ければ終了
if (!isset($_SESSION['cart'][$product_id])) {
    $_SESSION['error'] = 'カートに対象商品がありません。';
    header('Location: '.BASE_PATH.'/cart.php'); exit;
}

if ($action === 'delete') {
    unset($_SESSION['cart'][$product_id]);
    $_SESSION['flash'] = '商品を削除しました。';
    header('Location: '.BASE_PATH.'/cart.php'); exit;
}

if ($action === 'update') {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $quantity = max(1, $quantity); // 最低1

    // 在庫チェック
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['error'] = '商品が見つかりませんでした。';
    } else {
        $stock = (int)$p['stock'];
        if ($stock <= 0) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['error'] = '在庫切れのためカートから削除しました。';
        } else {
            $newQty = min($quantity, $stock); // 在庫上限までに丸める
            $_SESSION['cart'][$product_id]['quantity'] = $newQty;

            if ($newQty < $quantity) {
                $_SESSION['flash'] = "在庫上限（{$stock}個）までに調整しました。";
            } else {
                $_SESSION['flash'] = '数量を更新しました。';
            }
        }
    }

    header('Location: '.BASE_PATH.'/cart.php'); exit;
}

// 想定外actionはカートへ
header('Location: '.BASE_PATH.'/cart.php'); exit;
?>