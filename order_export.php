<?php
require __DIR__ . '/admin_check.php';
if (!hash_equals($_SESSION['csrf'] ?? '', $_GET['csrf'] ?? '')) { http_response_code(400); exit('Bad CSRF'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$head = $pdo->prepare("SELECT o.id, o.created_at, u.name, u.email, o.total_price FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?");
$head->execute([$id]); $o = $head->fetch(PDO::FETCH_ASSOC);
if (!$o) { exit('not found'); }

$sql = "SELECT p.name, oi.unit_price, oi.quantity FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=? ORDER BY oi.id";
$st = $pdo->prepare($sql); $st->execute([$id]); $items = $st->fetchAll(PDO::FETCH_ASSOC);

$filename = 'order_'.$id.'.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename='.$filename);
echo chr(0xEF).chr(0xBB).chr(0xBF);

$out = fopen('php://output', 'w');
fputcsv($out, ['注文ID',$o['id']]);
fputcsv($out, ['日時',$o['created_at']]);
fputcsv($out, ['顧客',$o['name']]);
fputcsv($out, ['メール',$o['email']]);
fputcsv($out, []); // 空行
fputcsv($out, ['商品名','単価(円)','数量','小計(円)']);
$sum = 0;
foreach ($items as $it) {
  $sub = (int)$it['unit_price'] * (int)$it['quantity'];
  $sum += $sub;
  fputcsv($out, [$it['name'], $it['unit_price'], $it['quantity'], $sub]);
}
fputcsv($out, []); 
fputcsv($out, ['合計(保存値)', $o['total_price']]);
fputcsv($out, ['合計(再計算)', $sum]);
fclose($out);
exit;
?>