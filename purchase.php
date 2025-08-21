<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();
require 'auth_check.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: '.BASE_PATH.'/cart.php');
    exit;
}

$userId = $_SESSION['user_id'];

// トランザクション開始
$pdo->beginTransaction();

try {
    $total = 0;
    $priceMap = [];

    // 在庫確認＆合計算出のために一旦ループ
    // 在庫は行ロックして厳密にチェック（InnoDB前提）
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$item['id']]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) throw new Exception('存在しない商品が含まれています。');
        
        if ((int)$p['stock'] < (int)$item['quantity']) {
            throw new Exception("『{$p['name']}』の在庫が不足しています。残り{$p['stock']}個です。");
        }

        $unit = (int)$p['price'];
        $qty = (int)$item['quantity'];
        $total += $unit * $qty;

        $priceMap[(int)$item['id']] = $unit;
    }

    // 注文作成（先にレコード作ってから明細を入れる）
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, created_at) VALUES (?, 0, NOW())");
    $stmt->execute([$userId]);
    $orderId = $pdo->lastInsertId();

    // 明細作成＋在庫を減らす
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, unit_price, quantity) VALUES (?, ?, ?, ?)
    ");
    $stockStmt = $pdo->prepare("
        UPDATE products SET stock = stock - ? WHERE id = ?
    ");

    foreach ($_SESSION['cart'] as $item) {
        $pid = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $unit = (int)$priceMap[$pid];

        $itemStmt->execute([$orderId, $pid, $unit, $qty]);
        $stockStmt->execute([$qty, $pid]);
    }

    // 合計金額を反映
    $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmt->execute([(int)$total, (int)$orderId]);

    // コミット
    $pdo->commit();

    // カート空
    unset($_SESSION['cart']);

    // 完了ページへ
    header("Location: purchase_complete.php?order_id=" . urlencode($orderId));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    // エラーメッセージをセッションに入れてカートへ戻す
    $_SESSION['purchase_error'] = $e->getMessage();
    header('Location: '.BASE_PATH.'/cart.php');
    exit;
}
?>