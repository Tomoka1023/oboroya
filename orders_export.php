<?php
require __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../_config.php';
require_once __DIR__ . '/../db.php';
if (!hash_equals($_SESSION['csrf'] ?? '', $_GET['csrf'] ?? '')) { http_response_code(400); exit('Bad CSRF'); }

$q         = trim($_GET['q'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');

$where = []; $params = [];
if ($q !== '') { $where[]='(u.name LIKE ? OR u.email LIKE ? OR o.id = ?)'; $params[]="%{$q}%"; $params[]="%{$q}%"; $params[]=ctype_digit($q)?(int)$q:0; }
if ($date_from !== '') { $where[]='o.created_at >= ?'; $params[]=$date_from.' 00:00:00'; }
if ($date_to   !== '') { $where[]='o.created_at <= ?'; $params[]=$date_to.' 23:59:59'; }
$wsql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

$sql = "
  SELECT o.id, o.created_at, u.name, u.email, o.total_price,
         COALESCE(SUM(oi.quantity),0) AS qty_total,
         COUNT(oi.id) AS line_count
  FROM orders o
  JOIN users u ON u.id=o.user_id
  LEFT JOIN order_items oi ON oi.order_id=o.id
  $wsql
  GROUP BY o.id, o.created_at, u.name, u.email, o.total_price
  ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV出力
$filename = 'orders_'.date('Ymd_His').'.csv';
header('Content-Type: text/csv; charset=SJIS-win');
header('Content-Disposition: attachment; filename='.$filename);
echo chr(0xEF).chr(0xBB).chr(0xBF); // Excel対策(BOM付きUTF-8)にしたい場合は上のcharsetを text/csv; charset=UTF-8 に

$out = fopen('php://output', 'w');
fputcsv($out, ['注文ID','日時','顧客名','メール','明細数','数量合計','合計(円)']);
foreach ($rows as $r) {
  fputcsv($out, [
    $r['id'],
    $r['created_at'],
    $r['name'],
    $r['email'],
    $r['line_count'],
    $r['qty_total'],
    $r['total_price'],
  ]);
}
fclose($out);
exit;
?>