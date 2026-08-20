<?php
// POST /api/marketplace/contact_requests/create.php
// Authenticated buyer initiates contact with a seller about a listing.
// The seller's phone number is NEVER exposed publicly — only via this controlled flow.

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

// Must be authenticated
$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'You must be logged in as a farmer/buyer to contact a seller', [], 403);
}
$buyer_id = $auth['id'];

$body       = getBody();
$listing_id = (int)($body['listing_id'] ?? 0);
$message    = trim($body['message'] ?? '') ?: null;

if (!$listing_id) {
    respond(false, 'listing_id is required', [], 422);
}

$db = getDB();

// Get the listing and verify it's available + get seller_farmer_id
$lstmt = $db->prepare(
    "SELECT id, farmer_id, product_name, status FROM listings WHERE id = ? AND status = 'available' LIMIT 1"
);
$lstmt->bind_param("i", $listing_id);
$lstmt->execute();
$listing = $lstmt->get_result()->fetch_assoc();
$lstmt->close();

if (!$listing) {
    respond(false, 'Listing not found or is no longer available', [], 404);
}

$seller_id = (int)$listing['farmer_id'];

// Buyer cannot contact themselves
if ($buyer_id === $seller_id) {
    respond(false, 'You cannot contact yourself about your own listing', [], 400);
}

// Check for an existing pending request from this buyer on this listing
$dupStmt = $db->prepare(
    "SELECT id FROM contact_requests WHERE listing_id = ? AND buyer_farmer_id = ? AND status = 'pending' LIMIT 1"
);
$dupStmt->bind_param("ii", $listing_id, $buyer_id);
$dupStmt->execute();
if ($dupStmt->get_result()->num_rows > 0) {
    respond(false, 'You already have a pending contact request for this listing', [], 409);
}
$dupStmt->close();

// Get buyer name + phone for the request record
$bStmt = $db->prepare("SELECT name, phone FROM farmers WHERE id = ? LIMIT 1");
$bStmt->bind_param("i", $buyer_id);
$bStmt->execute();
$buyer = $bStmt->get_result()->fetch_assoc();
$bStmt->close();

$stmt = $db->prepare(
    "INSERT INTO contact_requests (listing_id, buyer_farmer_id, buyer_name, buyer_phone, seller_farmer_id, message, status)
     VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);
$stmt->bind_param("iissss",
    $listing_id, $buyer_id, $buyer['name'], $buyer['phone'], $seller_id, $message
);

if ($stmt->execute()) {
    respond(true, 'Contact request sent! The seller will be able to see your request.', [
        'request_id' => $db->insert_id
    ], 201);
} else {
    respond(false, 'Failed to send request: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
