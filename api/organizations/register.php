<?php
// POST /api/organizations/register.php
// Register an NGO, Manufacturing Company, or Government Firm

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$body = getBody();

// --- Required fields ---
$org_name      = trim($body['org_name']      ?? '');
$org_type      = trim($body['org_type']      ?? '');
$contact_email = trim($body['contact_email'] ?? '');
$password      = trim($body['password']      ?? '');

$valid_types = ['NGO', 'Manufacturing Company', 'Government Firm'];
if (!$org_name || !$org_type || !$contact_email || !$password) {
    respond(false, 'org_name, org_type, contact_email, and password are required', [], 422);
}
if (!in_array($org_type, $valid_types, true)) {
    respond(false, 'org_type must be one of: ' . implode(', ', $valid_types), [], 422);
}
if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email address', [], 422);
}
if (strlen($password) < 6) {
    respond(false, 'Password must be at least 6 characters', [], 422);
}

// --- Optional fields ---
$contact_name  = trim($body['contact_name']  ?? '') ?: null;
$contact_phone = trim($body['contact_phone'] ?? '') ?: null;
$location      = trim($body['location']      ?? '') ?: null;

$db   = getDB();
$hash = password_hash($password, PASSWORD_BCRYPT);
$token = generateToken();

// Check duplicate email
$check = $db->prepare("SELECT id FROM organizations WHERE contact_email = ?");
$check->bind_param("s", $contact_email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    respond(false, 'An organization with this email already exists', [], 409);
}
$check->close();

$stmt = $db->prepare(
    "INSERT INTO organizations (org_name, org_type, contact_name, contact_email, contact_phone, location, password_hash, auth_token)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssssssss",
    $org_name, $org_type, $contact_name, $contact_email, $contact_phone, $location, $hash, $token
);

if ($stmt->execute()) {
    $org_id = $db->insert_id;
    respond(true, 'Organization registered successfully', [
        'organization' => [
            'id'            => $org_id,
            'org_name'      => $org_name,
            'org_type'      => $org_type,
            'contact_email' => $contact_email,
            'location'      => $location,
            'account_type'  => 'organization',
        ],
        'token' => $token
    ], 201);
} else {
    respond(false, 'Registration failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
