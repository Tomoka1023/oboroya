<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';

// ページング
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// フィルタ
$q_raw     = trim($_GET['q'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');

$where  = [];
$params = [];

/** 「#123」(全角＃もOK) なら ID 完全一致 */
$exactId = null;
if ($q_raw !== '' && preg_match('/^[#＃]\s*(\d+)\s*$/u', $q_raw, $m)) { // ← ここをASCIIの ^ に
    $exactId = (int)$m[1];
}

if ($exactId !== null) {
    $where[]  = 'o.id = ?';
    $params[] = $exactId;
} elseif ($q_raw !== '') {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = "%{$q_raw}%";
    $params[] = "%{$q_raw}%";
}

if ($date_from !== '') { $where[] = 'o.created_at >= ?'; $params[] = $date_from.' 00:00:00'; }
if ($date_to   !== '') { $where[] = 'o.created_at <= ?'; $params[] = $date_to  .' 23:59:59'; }

$wsql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

// 件数
$countSql = "SELECT COUNT(*) FROM orders o JOIN users u ON u.id=o.user_id $wsql";
$st = $pdo->prepare($countSql);
$st->execute($params);
$totalRows  = (int)$st->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// 一覧
$sql = "
  SELECT
    o.id, o.created_at, o.total_price,
    u.name AS user_name, u.email AS user_email,
    COALESCE(SUM(oi.quantity),0) AS qty_total,
    COUNT(oi.id) AS line_count
  FROM orders o
  JOIN users u ON u.id = o.user_id
  LEFT JOIN order_items oi ON oi.order_id = o.id
  $wsql
  GROUP BY o.id, o.created_at, o.total_price, u.name, u.email   -- ← エイリアスではなく元列名
  ORDER BY o.created_at DESC
  LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// クエリ文字列（CSV/Pager用）
function qs($over = []) {
  $q = array_merge($_GET, $over);
  return http_build_query($q);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>管理：注文一覧</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/oboroya-mark.svg" type="image/svg+xml">
  <style>
    .filter{display:flex;gap:8px;align-items:end;flex-wrap:wrap;margin:12px 0}
    .filter .row{display:flex;flex-direction:column}
  </style>
</head>
<body class="page">
<?php include __DIR__.'/../_header.php'; ?>
<main class="page-main">
  <div class="container">
    <div class="wrap">
      <div class="top" style="display:flex;justify-content:space-between;align-items:center;">
        <h1>注文管理</h1>
        <div>
          <a class="btn" href="<?= BASE_PATH ?>/admin/orders_export.php?<?= qs(['csrf'=>$_SESSION['csrf']]) ?>">CSVエクスポート</a>
        </div>
      </div>

      <form class="filter" method="get">
        <div class="row">
          <label>キーワード（名前・メール・注文ID）</label>
          <input type="text" name="q" placeholder="例：#9（注文ID） / 名前 / メール" value="<?= htmlspecialchars($q_raw ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="row">
          <label>開始日</label>
          <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="row">
          <label>終了日</label>
          <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <button class="btn btn-primary" type="submit">検索</button>
        <a class="btn" href="<?= BASE_PATH ?>/admin/orders.php">リセット</a>
      </form>

      <div class="tablep">
        <table class="table orders-table">
          <thead>
            <tr><th>ID</th><th>日時</th><th>顧客</th><th>メール</th><th>明細数</th><th>個数</th><th>合計</th><th>操作</th></tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td data-label="ID">#<?= (int)$r['id'] ?></td>
                <td data-label="日時"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at']))) ?></td>
                <td data-label="顧客"><?= htmlspecialchars($r['user_name']) ?></td>
                <td data-label="メール"><?= htmlspecialchars($r['user_email']) ?></td>
                <td data-label="明細数" style="text-align:right;"><?= (int)$r['line_count'] ?></td>
                <td data-label="個数" style="text-align:right;"><?= (int)$r['qty_total'] ?></td>
                <td data-label="合計" style="text-align:right;">¥<?= number_format((int)$r['total_price']) ?></td>
                <td data-label="操作">
                  <a class="btn" href="<?= BASE_PATH ?>/admin/order_detail.php?id=<?= (int)$r['id'] ?>">詳細</a>
                  <a class="btn" href="<?= BASE_PATH ?>/admin/order_export.php?id=<?= (int)$r['id'] ?>&csrf=<?= htmlspecialchars($_SESSION['csrf']) ?>">CSV</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--muted)">該当データがありません</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div style="display:flex;justify-content:center;gap:8px;margin-top:12px">
        <?php if ($page>1): ?>
          <a class="btn" href="<?= BASE_PATH ?>/?<?= qs(['page'=>$page-1]) ?>">← 前へ</a>
        <?php endif; ?>
        <span>Page <?= $page ?> / <?= $totalPages ?>（全<?= $totalRows ?>件）</span>
        <?php if ($page<$totalPages): ?>
          <a class="btn" href="<?= BASE_PATH ?>/?<?= qs(['page'=>$page+1]) ?>">次へ →</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<?php include __DIR__.'/../_footer.php'; ?>
</body>
</html>
