<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); echo 'Bad CSRF'; exit; }

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// （任意）画像ファイル削除したい場合は先にファイル名取得してunlink()する
$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$cur = $stmt->fetch(PDO::FETCH_ASSOC);

$del = $pdo->prepare('DELETE FROM products WHERE id = ?');
$del->execute([$id]);

// （任意）unlink(__DIR__.'/../images/'.$cur['image']);

header('Location: '.BASE_PATH.'/admin/products.php'); exit;
?>