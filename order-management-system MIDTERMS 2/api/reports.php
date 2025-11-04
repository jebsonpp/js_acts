<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';

header('Content-Type: application/json');

// Require login
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

// Validate dates
$where = '';
$params = [];
if ($start && $end) {
    $where = "WHERE DATE(o.date_added) BETWEEN ? AND ?";
    $params = [$start, $end];
}

// Prepare query
$sql = "SELECT o.id, o.order_json, o.total, o.added_by, o.date_added, u.username
        FROM orders o
        LEFT JOIN users u ON o.added_by = u.id
        $where
        ORDER BY o.date_added DESC";

$stmt = $mysqli->prepare($sql);
if ($where) {
    $stmt->bind_param('ss', ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$total_sum = 0;

while ($r = $result->fetch_assoc()) {
    $items_json = json_decode($r['order_json'], true) ?: [];
    $rows[] = [
        'id' => $r['id'],
        'items_json' => $items_json,
        'total' => $r['total'],
        'username' => $r['username'] ?? 'Unknown',
        'date_added' => $r['date_added']
    ];
    $total_sum += floatval($r['total']);
}

echo json_encode([
    'success' => true,
    'rows' => $rows,
    'total_sum' => $total_sum
]);
