<?php
// POST /api/pest_logs/add.php
// SECURITY: farmer_id is taken from the verified auth token, NOT the request body.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'farmer') {
    respond(false, 'Only farmer accounts can log pest outbreaks', [], 403);
}
$farmer_id = $auth['id'];

$body = getBody();

$pest_name     = trim($body['pest_name']     ?? '');
$date_observed = trim($body['date_observed'] ?? '');
$severity      = trim($body['severity']      ?? 'Low');

if (!$pest_name || !$date_observed) {
    respond(false, 'pest_name and date_observed are required', [], 422);
}

$valid_severities = ['Low', 'Medium', 'High'];
if (!in_array($severity, $valid_severities, true)) {
    respond(false, 'severity must be Low, Medium, or High', [], 422);
}

$field_name        = trim($body['field_name']        ?? '') ?: null;
$treatment_applied = trim($body['treatment_applied'] ?? '') ?: null;
$status            = trim($body['status']            ?? 'Active');
$notes             = trim($body['notes']             ?? '') ?: null;
if (!in_array($status, ['Active', 'Resolved'], true)) $status = 'Active';

$pest_image = handleUpload('pest_image', 'pest_images');

$db   = getDB();
$stmt = $db->prepare(
    "INSERT INTO pest_logs (farmer_id, pest_name, field_name, severity, date_observed, treatment_applied, status, pest_image, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "issssssss",
    $farmer_id, $pest_name, $field_name, $severity, $date_observed, $treatment_applied, $status, $pest_image, $notes
);

if ($stmt->execute()) {
    respond(true, 'Pest log added successfully', ['id' => $db->insert_id], 201);
} else {
    respond(false, 'Insert failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
