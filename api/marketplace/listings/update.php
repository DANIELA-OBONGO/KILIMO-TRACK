<?php
// POST /api/marketplace/listings/update.php
// Authenticated seller updates their OWN listing only.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can update listings', [], 403);
}
$farmer_id = $auth['id'];

$body = getBody();
$listing_id = (int)($body['listing_id'] ?? 0);
if (!$listing_id) {
    respond(false, 'listing_id is required', [], 422);
}

$db = getDB();

// OWNERSHIP CHECK — must own the listing
$own = $db->prepare("SELECT id FROM listings WHERE id = ? AND farmer_id = ? LIMIT 1");
$own->bind_param("ii", $listing_id, $farmer_id);
$own->execute();
if ($own->get_result()->num_rows === 0) {
    respond(false, 'Listing not found or you do not own it', [], 403);
}
$own->close();

// Build update
$fields = []; $params = []; $types = '';
$allowed = ['product_name'=>'s','category'=>'s','description'=>'s','quantity'=>'d','unit'=>'s','price'=>'d','location'=>'s'];
$statusValues = ['available','unavailable','sold','archived'];

foreach ($allowed as $col => $t) {
    if (isset($body[$col])) {
        $val = is_string($body[$col]) ? trim($body[$col]) : $body[$col];
        $fields[] = "$col = ?"; $params[] = $val; $types .= $t;
    }
}
if (isset($body['status']) && in_array($body['status'], $statusValues, true)) {
    $fields[] = "status = ?"; $params[] = $body['status']; $types .= 's';
}
if (!$fields) {
    respond(false, 'No fields to update', [], 422);
}

$params[] = $listing_id; $types .= 'i';
$stmt = $db->prepare("UPDATE listings SET " . implode(', ', $fields) . " WHERE id = ?");
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    respond(true, 'Listing updated successfully');
} else {
    respond(false, 'Update failed: ' . $stmt->error, [], 500);
}
$stmt->close(); $db->close();
