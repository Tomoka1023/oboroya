<?php
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
session_start();


if (empty($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = '/login.php';
    header('Location: '.BASE_PATH.'/login.php'); exit;
}

$stmt = $pdo->prepare('SELECT is_admin, name FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || (int)$user['is_admin'] !== 1) {
    http_response_code(403);
    echo '403 Forbidden（管理者のみ）';
    exit;
}

// CSRFトークン（簡易）
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>