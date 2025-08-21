<?php
session_start();
if (empty($_SESSION['user_id'])) {
    // デフォルトは今のURLに戻す
    $returnTo = $_SERVER['REQUEST_URI'] ?? 'index.php';

    // POSTで来たページ（例：purchase.php）は直接戻すと失敗するので cart.php に寄せる
    $script = basename($_SERVER['SCRIPT_NAME']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($script, ['purchase.php', 'add_to_cart.php'], true)) {
        $returnTo = 'cart.php';
    }

    // オープンリダイレクト対策：自サイト内パス以外は弾く（簡易チェック）
    if (strpos($returnTo, '://') !== false) {
        $returnTo = 'index.php';
    }

    $_SESSION['redirect_to'] = $returnTo;
    header('Location: '.BASE_PATH.'/login.php');
    exit;
}
?>