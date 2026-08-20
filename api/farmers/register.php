<?php
// POST /api/farmers/register.php
// Register a new Independent Farmer account

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$body = getBody();

// --- Required fields ---
$name     = trim($body['name']     ?? '');
$phone    = trim($body['phone']    ?? '');
$password = trim($body['password'] ?? '');

if (!$name || !$phone || !$password) {
    respond(false, 'name, phone, and password are required', [], 422);
}
if (strlen($password) < 6) {
    respond(false, 'Password must be at least 6 characters', [], 422);
}

// --- Optional fields ---
$email     = trim($body['email']     ?? '') ?: null;
$location  = trim($body['location']  ?? '') ?: null;
$farm_name = trim($body['farm_name'] ?? '') ?: null;
$acreage   = isset($body['acreage']) && is_numeric($body['acreage']) ? (float)$body['acreage'] : null;

// --- Handle profile photo upload (if multipart form) ---
$profile_photo = handleUpload('profile_photo', 'profile_photos');

$db   = getDB();
$hash = password_hash($password, PASSWORD_BCRYPT);
$token = generateToken();

// Check duplicate phone
$check = $db->prepare("SELECT id FROM farmers WHERE phone = ?");
$check->bind_param("s", $phone);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    respond(false, 'A farmer with this phone number already exists', [], 409);
}
$check->close();

// Check duplicate email
if ($email) {
    $chkEmail = $db->prepare("SELECT id FROM farmers WHERE email = ?");
    $chkEmail->bind_param("s", $email);
    $chkEmail->execute();
    if ($chkEmail->get_result()->num_rows > 0) {
        respond(false, 'A farmer with this email already exists', [], 409);
    }
    $chkEmail->close();
}

$stmt = $db->prepare(
    "INSERT INTO farmers (name, phone, email, location, farm_name, acreage, password_hash, profile_photo, auth_token)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssssssss",
    $name, $phone, $email, $location, $farm_name, $acreage, $hash, $profile_photo, $token
);

if ($stmt->execute()) {
    $farmer_id = $db->insert_id;
    respond(true, 'Farmer registered successfully', [
        'farmer' => [
            'id'         => $farmer_id,
            'name'       => $name,
            'phone'      => $phone,
            'email'      => $email,
            'location'   => $location,
            'farm_name'  => $farm_name,
            'account_type' => 'farmer',
        ],
        'token'  => $token
    ], 201);
} else {
    respond(false, 'Registration failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
