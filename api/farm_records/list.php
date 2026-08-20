<?php
// GET /api/farm_records/list.php
// Returns farm records for the authenticated farmer.
// SECURITY: farmer_id comes from the verified token, not URL params.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can access farm records', [], 403);
}
$farmer_id = $auth['id'];

$db   = getDB();
$stmt = $db->prepare(
    "SELECT id, crop_type, season_type, planting_date, harvest_date, growth_stage, acreage, field_name, notes, farm_photo, created_at
     FROM farm_records WHERE farmer_id = ? ORDER BY created_at DESC"
);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

respond(true, 'OK', ['records' => $rows, 'count' => count($rows)]);
