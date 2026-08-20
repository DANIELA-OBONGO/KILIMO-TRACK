<?php
// POST /api/soil_records/add.php
// SECURITY: farmer_id is taken from the verified auth token, NOT the request body.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can add soil records', [], 403);
}
$farmer_id = $auth['id'];

$body = getBody();

$ph_level  = isset($body['ph_level']) && is_numeric($body['ph_level']) ? (float)$body['ph_level'] : null;
$test_date = trim($body['test_date'] ?? '');

if ($ph_level === null || !$test_date) {
    respond(false, 'ph_level and test_date are required', [], 422);
}
if ($ph_level < 0 || $ph_level > 14) {
    respond(false, 'pH level must be between 0 and 14', [], 422);
}

$field_name     = trim($body['field_name']     ?? '') ?: null;
$soil_type      = trim($body['soil_type']      ?? '') ?: null;
$moisture_level = trim($body['moisture_level'] ?? '') ?: null;
$notes          = trim($body['notes']          ?? '') ?: null;

// Auto-generate recommendation based on pH
if ($ph_level < 5.5) {
    $recommendation = 'Strongly acidic soil. Apply agricultural lime at 500–1000 kg/acre. Retest after 6 weeks.';
} elseif ($ph_level < 6.0) {
    $recommendation = 'Slightly acidic. Apply agricultural lime at 250 kg/acre.';
} elseif ($ph_level <= 7.0) {
    $recommendation = 'Optimal pH range for most crops. No soil amendment needed.';
} elseif ($ph_level <= 7.5) {
    $recommendation = 'Slightly alkaline. Apply sulfur or acidifying fertilizer if deficiencies appear.';
} else {
    $recommendation = 'Strongly alkaline. Apply elemental sulfur. Consult your agricultural extension officer.';
}
// User-supplied recommendation overrides auto
if (!empty($body['recommendation'])) {
    $recommendation = trim($body['recommendation']);
}

$db   = getDB();
$stmt = $db->prepare(
    "INSERT INTO soil_records (farmer_id, field_name, soil_type, ph_level, moisture_level, test_date, recommendation, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issdssss", $farmer_id, $field_name, $soil_type, $ph_level, $moisture_level, $test_date, $recommendation, $notes);

if ($stmt->execute()) {
    respond(true, 'Soil record added successfully', ['id' => $db->insert_id, 'recommendation' => $recommendation], 201);
} else {
    respond(false, 'Insert failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
