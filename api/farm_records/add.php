<?php
// POST /api/farm_records/add.php
// Add a farm record for the authenticated farmer.
// SECURITY: farmer_id is taken from the verified auth token, NOT the request body.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

// ── Authenticate ─────────────────────────────────────────────────
$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can add farm records', [], 403);
}
$farmer_id = $auth['id'];

// ── Parse body ────────────────────────────────────────────────────
$body = getBody();

$crop_type     = trim($body['crop_type']    ?? '');
$season_type   = trim($body['season_type']  ?? '');
$planting_date = trim($body['planting_date'] ?? '');

if (!$crop_type || !$season_type || !$planting_date) {
    respond(false, 'crop_type, season_type, and planting_date are required', [], 422);
}

$valid_seasons = ['Long Rains', 'Short Rains', 'Dry Season', 'Irrigation'];
if (!in_array($season_type, $valid_seasons, true)) {
    respond(false, 'Invalid season_type. Must be one of: ' . implode(', ', $valid_seasons), [], 422);
}

$harvest_date  = trim($body['harvest_date']  ?? '') ?: null;
$growth_stage  = trim($body['growth_stage']  ?? '') ?: null;
$acreage       = isset($body['acreage']) && is_numeric($body['acreage']) ? (float)$body['acreage'] : null;
$field_name    = trim($body['field_name']    ?? '') ?: null;
$notes         = trim($body['notes']         ?? '') ?: null;
$farm_photo    = handleUpload('farm_photo', 'farm_photos', ['image/jpeg','image/png','application/pdf']);

$db   = getDB();
$stmt = $db->prepare(
    "INSERT INTO farm_records (farmer_id, crop_type, season_type, planting_date, harvest_date, growth_stage, acreage, field_name, notes, farm_photo)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "isssssdsss",
    $farmer_id, $crop_type, $season_type, $planting_date, $harvest_date,
    $growth_stage, $acreage, $field_name, $notes, $farm_photo
);

if ($stmt->execute()) {
    respond(true, 'Farm record added successfully', ['id' => $db->insert_id], 201);
} else {
    respond(false, 'Insert failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
