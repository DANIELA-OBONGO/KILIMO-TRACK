<?php
// GET /api/marketplace/listings/detail.php?id=N
// Public — returns a single listing's full details.
// NOTE: seller phone/email are NOT included in public response.
// They are only revealed through the contact_requests flow.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    respond(false, 'id is required', [], 422);
}

$db   = getDB();
$stmt = $db->prepare(
    "SELECT l.id, l.product_name, l.category, l.description, l.quantity, l.unit, l.price,
            l.location, l.listing_image, l.status, l.created_at, l.updated_at,
            f.name AS seller_name, f.farm_name, f.location AS seller_location
     FROM listings l
     JOIN farmers f ON l.farmer_id = f.id
     WHERE l.id = ? AND l.status != 'archived'
     LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if (!$listing) {
    respond(false, 'Listing not found', [], 404);
}

respond(true, 'OK', ['listing' => $listing]);
