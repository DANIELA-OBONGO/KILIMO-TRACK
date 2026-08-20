<?php
// GET /api/soil_records/list.php
// SECURITY: farmer_id comes from verified auth token only.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can access soil records', [], 403);
}
$farmer_id = $auth['id'];

$db   = getDB();
$stmt = $db->prepare(
    "SELECT id, field_name, soil_type, ph_level, moisture_level, test_date, recommendation, notes, created_at
     FROM soil_records WHERE farmer_id = ? ORDER BY test_date DESC"
);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate average pH
$avg_ph = null;
if (count($rows) > 0) {
    $avg_ph = round(array_sum(array_column($rows, 'ph_level')) / count($rows), 2);
}

$db->close();
respond(true, 'OK', ['records' => $rows, 'count' => count($rows), 'average_ph' => $avg_ph]);
