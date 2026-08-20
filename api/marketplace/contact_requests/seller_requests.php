<?php
// GET /api/marketplace/contact_requests/seller_requests.php
// Authenticated seller sees contact requests on their listings.
// Buyer phone number is revealed HERE — only to the authenticated seller.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer/seller accounts can access this endpoint', [], 403);
}
$seller_id = $auth['id'];

$db   = getDB();
$stmt = $db->prepare(
    "SELECT cr.id, cr.status, cr.message, cr.created_at,
            cr.buyer_name, cr.buyer_phone,
            l.product_name, l.id AS listing_id
     FROM contact_requests cr
     JOIN listings l ON cr.listing_id = l.id
     WHERE cr.seller_farmer_id = ?
     ORDER BY cr.created_at DESC"
);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

// buyer_phone is intentionally included here — only seller can see this
respond(true, 'OK', ['requests' => $rows, 'count' => count($rows)]);
