<?php
// POST /api/marketplace/listings/add.php
// Authenticated farmer/seller creates a product listing.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can create listings', [], 403);
}
$farmer_id = $auth['id'];

$body = getBody();

$product_name = trim($body['product_name'] ?? '');
if (!$product_name) {
    respond(false, 'product_name is required', [], 422);
}

$category    = trim($body['category']    ?? '') ?: null;
$description = trim($body['description'] ?? '') ?: null;
$quantity    = isset($body['quantity']) && is_numeric($body['quantity']) ? (float)$body['quantity'] : null;
$unit        = trim($body['unit']        ?? '') ?: null;
$price       = isset($body['price']) && is_numeric($body['price']) ? (float)$body['price'] : null;
$location    = trim($body['location']    ?? '') ?: null;
$status      = 'available'; // Always start as available

// Handle listing image upload
$listing_image = handleUpload('listing_image', 'listing_images');

$db   = getDB();
$stmt = $db->prepare(
    "INSERT INTO listings (farmer_id, product_name, category, description, quantity, unit, price, location, listing_image, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssdsdsss",
    $farmer_id, $product_name, $category, $description,
    $quantity, $unit, $price, $location, $listing_image, $status
);

if ($stmt->execute()) {
    $listing_id = $db->insert_id;
    respond(true, 'Listing created successfully', ['id' => $listing_id], 201);
} else {
    respond(false, 'Failed to create listing: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
