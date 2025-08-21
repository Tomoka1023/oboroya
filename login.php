<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, $user['password'])) {
        $err = 'メールまたはパスワードが違います。';
    } else {
        // セッション固定攻撃対策：ID再発行
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];

        $to = $_SESSION['redirect_to'] ?? 'index.php';
        unset($_SESSION['redirect_to']); // 一度使ったら消す
        header('Location: ' . $to);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | ログイン</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/_header.php'; ?>
<main class="page-main auth-wrap">
<div class="auth-card card">
    <h1 class="text-center">ログイン</h1>

    <?php if ($err): ?>
        <p class="error" style="margin-top:8px;"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <form method="post" class="mt-16">
        <div class="row">
            <label>メール：</label>
            <input type="email" name="email" required>
        </div>
        <div class="row">
            <label>パスワード：</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary mt-16" style="width:100%">ログイン</button>
    </form>

    <p class="text-center mt-16"><a href="<?= BASE_PATH ?>/register.php">新規登録へ</a></p>
</main>
    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
