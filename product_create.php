<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400); echo 'Bad CSRF'; exit;
}

$name = trim($_POST['name'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$desc = trim($_POST['description'] ?? '');
$imageName = null;

// 画像アップロード（任意）
if (!empty($_FILES['image']['tmp_name'])) {
    $tmp = $_FILES['image']['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $ok = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'];
    if (!isset($ok[$mime])) { $_SESSION['error'] = '画像形式は jpg/png/gif/webp のみ'; header('Location: '.BASE_PATH.'/admin/product_new.php'); exit; }
    if ($_FILES['image']['size'] > 2*1024*1024) { $_SESSION['error'] = '画像サイズは2MBまで'; header('Location: '.BASE_PATH.'/admin/product_new.php'); exit; }

    $relDir = 'products';
    $absDir = __DIR__ . '/../images/' . $relDir;
    if (!is_dir($absDir)) { mkdir($absDir, 0777, true); }

    $ext = $ok[$mime];
    $base = bin2hex(random_bytes(8));
    $imageName = $relDir . '/' . $base . '.' . $ext;
    $dest = __DIR__ . '/../images/' . $imageName; // 保存先

    if (!move_uploaded_file($tmp, $dest)) {
         $_SESSION['error']='アップロード失敗'; header('Location: '.BASE_PATH.'/admin/product_new.php'); exit;
    }
}

$stmt = $pdo->prepare('INSERT INTO products(name, price, image, description, stock, created_at)
                       VALUES(?, ?, ?, ?, ?, NOW())');
$stmt->execute([$name, $price, $imageName, $desc, $stock]);

$_SESSION['flash'] = '商品を登録しました。';
header('Location: '.BASE_PATH.'/admin/products.php'); exit;
?>