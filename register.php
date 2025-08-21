<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $pass === '') {
        $err = '未入力の項目があります。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'メールアドレスの形式が不正です。';
    } else {
        // 既に登録済みか確認
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $err = 'このメールアドレスは既に登録されています。';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users(name, email, password, created_at) VALUES(?, ?, ?, NOW())');
            $stmt->execute([$name, $email, $hash]);

            // 自動ログイン
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            header('Location: '.BASE_PATH.'/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | 新規登録</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main auth-wrap">
<div class="auth-card card">
    <h1 class="text-center">新規登録</h1>

    <?php if ($err): ?>
        <p class="error" style="margin-top:8px;"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <form method="post" class="mt-16">
        <div class="row">
            <label>名前：</label>
            <input type="text" name="name" required>
        </div>
        <div class="row">
            <label>メール：</label>
            <input type="email" name="email" required>
        </div>
        <div class="row">
            <label>パスワード：</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary mt-16" style="width:100%;">登録する</button>
    </form>

    <p class="text-center mt-16"><a href="<?= BASE_PATH ?>/login.php">ログインへ</a></p>
</main>
    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
