<?php
// File: api/order.php
// Basic PHP API that accepts JSON {snackId, quantity, cash}
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
// CORS preflight
http_response_code(200);
exit;
}


// Read the raw input
$input = json_decode(file_get_contents('php://input'), true);


if (!is_array($input)) {
echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
exit;
}


$snackId = $input['snackId'] ?? null;
$quantity = $input['quantity'] ?? null;
$cash = $input['cash'] ?? null;


// Validate presence
if ($snackId === null || $quantity === null || $cash === null || $snackId === '' || $quantity === '' || $cash === '') {
echo json_encode(['success' => false, 'message' => 'One or more fields are empty.']);
exit;
}


// Validate data types
if (!is_numeric($quantity) || !is_numeric($cash)) {
echo json_encode(['success' => false, 'message' => 'Quantity and cash must be numeric.']);
exit;
}


$quantity = intval($quantity);
$cash = floatval($cash);


if ($quantity <= 0) {
echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
exit;
}


// Catalog (must match the client)
$catalog = [
'szz' => ['name' => "Sizzling Bangus", 'price' => 1.50],
'lmp' => ['name' => 'Lumpia', 'price' => 14.0],
'mkmk' => ['name' => 'Mik Mik', 'price' => 45.0]
];


if (!array_key_exists($snackId, $catalog)) {
echo json_encode(['success' => false, 'message' => 'Unknown snack selected.']);
exit;
}


$unitPrice = $catalog[$snackId]['price'];
$snackName = $catalog[$snackId]['name'];
$total = round($unitPrice * $quantity, 2);


// Check for valid cash (non-negative)
if ($cash < 0) {
echo json_encode(['success' => false, 'message' => 'Cash cannot be negative.']);
exit;
}


// Transaction check
if ($cash < $total) {
$needed = round($total - $cash, 2);
echo json_encode(['success' => false, 'message' => "Insufficient cash. Need additional ₱$needed.", 'total' => $total]);
exit;
}


$change = round($cash - $total, 2);


echo json_encode([
'success' => true,
'message' => 'Transaction successful. Thank you for your purchase!',
'snackId' => $snackId,
'snackName' => $snackName,
'quantity' => $quantity,
'unitPrice' => $unitPrice,
'total' => $total,
'cash' => $cash,
'change' => $change
]);
exit;
?>