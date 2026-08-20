<?php
// GET /api/pest_logs/list.php
// SECURITY: farmer_id comes from verified auth token only.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can access pest logs', [], 403);
}
$farmer_id = $auth['id'];

$db   = getDB();
$stmt = $db->prepare(
    "SELECT id, pest_name, field_name, severity, date_observed, treatment_applied, status, pest_image, notes, created_at
     FROM pest_logs WHERE farmer_id = ? ORDER BY date_observed DESC"
);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

$active   = array_filter($rows, fn($r) => $r['status'] === 'Active');
$resolved = array_filter($rows, fn($r) => $r['status'] === 'Resolved');

respond(true, 'OK', [
    'records'        => $rows,
    'count'          => count($rows),
    'active_count'   => count($active),
    'resolved_count' => count($resolved),
]);
