<?php
// POST /api/farmers/login.php
// Authenticate a farmer

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$body     = getBody();
$phone    = trim($body['phone']    ?? $body['identifier'] ?? '');
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

if ((!$phone && !$email) || !$password) {
    respond(false, 'Phone/email and password are required', [], 422);
}

$db = getDB();

if ($phone) {
    $stmt = $db->prepare("SELECT id, name, phone, email, location, farm_name, password_hash FROM farmers WHERE phone = ?");
    $stmt->bind_param("s", $phone);
} else {
    $stmt = $db->prepare("SELECT id, name, phone, email, location, farm_name, password_hash FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    respond(false, 'No account found with those credentials', [], 401);
}

$farmer = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $farmer['password_hash'])) {
    respond(false, 'Incorrect password', [], 401);
}

// Refresh token on each login
$token = generateToken();
$upd   = $db->prepare("UPDATE farmers SET auth_token = ? WHERE id = ?");
$upd->bind_param("si", $token, $farmer['id']);
$upd->execute();
$upd->close();
$db->close();

unset($farmer['password_hash']);
$farmer['account_type'] = 'farmer';

respond(true, 'Login successful', ['farmer' => $farmer, 'token' => $token]);
