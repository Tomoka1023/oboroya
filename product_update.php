<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400); echo 'Bad CSRF'; exit;
}

$id    = (int)($_POST['id'] ?? 0);
$name  = trim($_POST['name'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$desc  = trim($_POST['description'] ?? '');

$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$cur = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cur) { header('Location: '.BASE_PATH.'/admin/products.php'); exit; }

$imageName = $cur['image']; // 画像未アップ時は現状維持

// 画像アップロード処理
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $err = $_FILES['image']['error'];
    if ($err === UPLOAD_ERR_OK) {
        // サイズ上限（必要なら調整）
        if ($_FILES['image']['size'] > 2*1024*1024) {
            $_SESSION['error'] = '画像サイズは2MBまでです。';
            header('Location: '.BASE_PATH.'/admin/product_edit.php?id='.$id); exit;
        }
        // MIME確認
        $tmp  = $_FILES['image']['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp);
        $ok = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'];
        if (!isset($ok[$mime])) {
            $_SESSION['error'] = '画像形式は jpg / png / gif / webp のみです。';
            header('Location: '.BASE_PATH.'/admin/product_edit.php?id='.$id); exit;
        }

        // 保存先ディレクトリ（なければ作成）
        $relDir = 'products';
        $absDir = __DIR__ . '/../images/' . $relDir;
        if (!is_dir($absDir)) {
            if (!mkdir($absDir, 0777, true)) {
                $_SESSION['error'] = '画像保存フォルダを作成できませんでした。';
                header('Location: '.BASE_PATH.'/admin/product_edit.php?id='.$id); exit;
            }
        }

        $ext = $ok[$mime];
        $base = bin2hex(random_bytes(8));
        $newName = $relDir . '/' . $base . '.' . $ext;                 // DBに入れる相対パス
        $dest    = __DIR__ . '/../images/' . $newName;                 // 実際の保存先

        if (!move_uploaded_file($tmp, $dest)) {
            $_SESSION['error'] = '画像の保存に失敗しました。';
            header('Location: '.BASE_PATH.'/admin/product_edit.php?id='.$id); exit;
        }

        // （任意）古い画像を消したい場合はここで unlink() する
        // if ($imageName && is_file(__DIR__.'/../images/'.$imageName)) { unlink(__DIR__.'/../images/'.$imageName); }

        $imageName = $newName; // 新しいパスで上書き
    } else {
        // 代表的なエラーを人間向けに
        $map = [
            UPLOAD_ERR_INI_SIZE   => 'php.iniのupload_max_filesizeを超えました。',
            UPLOAD_ERR_FORM_SIZE  => 'フォームのMAXサイズを超えました。',
            UPLOAD_ERR_PARTIAL    => 'ファイルが一部しかアップロードされていません。',
            UPLOAD_ERR_NO_TMP_DIR => '一時フォルダがありません。',
            UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました。',
            UPLOAD_ERR_EXTENSION  => '拡張によって中断されました。',
        ];
        $_SESSION['error'] = $map[$err] ?? 'アップロードエラーが発生しました。';
        header('Location: '.BASE_PATH.'/admin/product_edit.php?id='.$id); exit;
    }
}

// DB更新
$upd = $pdo->prepare('UPDATE products SET name=?, price=?, stock=?, image=?, description=? WHERE id=?');
$upd->execute([$name, $price, $stock, $imageName, $desc, $id]);

$_SESSION['flash'] = '商品を更新しました。';
header('Location: '.BASE_PATH.'/admin/products.php'); exit;
?>