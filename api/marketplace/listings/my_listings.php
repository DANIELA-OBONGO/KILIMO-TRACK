<?php
// GET /api/marketplace/listings/my_listings.php
// Returns all listings belonging to the authenticated farmer/seller.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can access this endpoint', [], 403);
}
$farmer_id = $auth['id'];

$db   = getDB();
$stmt = $db->prepare(
    "SELECT l.id, l.product_name, l.category, l.description, l.quantity, l.unit,
            l.price, l.location, l.listing_image, l.status, l.created_at, l.updated_at,
            COUNT(cr.id) AS contact_request_count
     FROM listings l
     LEFT JOIN contact_requests cr ON cr.listing_id = l.id AND cr.status = 'pending'
     WHERE l.farmer_id = ?
     GROUP BY l.id
     ORDER BY l.created_at DESC"
);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

respond(true, 'OK', ['listings' => $rows, 'count' => count($rows)]);
