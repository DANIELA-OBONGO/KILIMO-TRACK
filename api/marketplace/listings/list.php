<?php
// GET /api/marketplace/listings/list.php
// Public endpoint — browse all available listings.
// Supports: ?search=maize&category=Grains&location=Nairobi&page=1

require_once __DIR__ . '/../../cors.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$db = getDB();

// ── Filters ────────────────────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset   = ($page - 1) * $per_page;

// Build WHERE clause
$where  = ["l.status = 'available'"];
$params = [];
$types  = '';

if ($search) {
    $where[]  = "(l.product_name LIKE ? OR l.description LIKE ? OR l.category LIKE ?)";
    $like     = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
if ($category) {
    $where[]  = "l.category = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($location) {
    $where[]  = "l.location LIKE ?";
    $params[] = "%$location%";
    $types   .= 's';
}

$whereSQL = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM listings l WHERE $whereSQL");
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

// Fetch listings — show seller's farm name and location but NOT personal contact info
$stmt = $db->prepare(
    "SELECT l.id, l.product_name, l.category, l.description, l.quantity, l.unit, l.price,
            l.location, l.listing_image, l.status, l.created_at,
            f.name AS seller_name, f.farm_name, f.location AS seller_location
     FROM listings l
     JOIN farmers f ON l.farmer_id = f.id
     WHERE $whereSQL
     ORDER BY l.created_at DESC
     LIMIT ? OFFSET ?"
);

$params[] = $per_page;
$params[] = $offset;
$types   .= 'ii';

if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

// Get unique categories for filter UI
respond(true, 'OK', [
    'listings'   => $rows,
    'count'      => count($rows),
    'total'      => (int)$total,
    'page'       => $page,
    'per_page'   => $per_page,
    'total_pages'=> ceil($total / $per_page),
]);
