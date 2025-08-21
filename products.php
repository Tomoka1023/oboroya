<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
$rows = $pdo->query('SELECT id, name, price, stock, image FROM products ORDER BY id DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>朧屋 | 商品管理</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
</head>
<body class="page">
<?php include __DIR__ . '/../_header.php'; ?>
<main class="page-main">
  <div class="wrap">
    <div class="top">
      <h1>商品管理</h1>
      <a class="btn" href="<?= BASE_PATH ?>/admin/product_new.php">＋ 新規追加</a>
    </div>
      <div class="tablep">
        <table>
          <thead><tr><th>ID</th><th>画像</th><th>商品名</th><th>価格</th><th>在庫</th><th>操作</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php if ($r['image']): ?><img src="../images/<?php echo htmlspecialchars($r['image']); ?>" style="width:60px;height:60px;object-fit:contain;border-radius:6px"><?php endif; ?></td>
                <td><?php echo htmlspecialchars($r['name']); ?></td>
                <td>¥<?php echo number_format((int)$r['price']); ?></td>
                <td><?php echo (int)$r['stock']; ?></td>
                <td>
                  <a class="btn" href="<?= BASE_PATH ?>/admin/product_edit.php?id=<?php echo (int)$r['id']; ?>">編集</a>
                  <form action="<?= BASE_PATH ?>/admin/product_delete.php" method="post" style="display:inline"
                        onsubmit="return confirm('削除します。よろしいですか？')">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <button class="btn" type="submit">削除</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <p><a class="btn" href="<?= BASE_PATH ?>/index.php">サイトへ戻る</a></p>
  </div>
</main>
  <?php include __DIR__ . '/../_footer.php'; ?>
</body>
</html>
