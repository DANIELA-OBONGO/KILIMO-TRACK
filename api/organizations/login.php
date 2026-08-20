<?php
// POST /api/organizations/login.php

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$body     = getBody();
$email    = trim($body['contact_email'] ?? $body['email'] ?? '');
$password = trim($body['password'] ?? '');

if (!$email || !$password) {
    respond(false, 'Email and password are required', [], 422);
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, org_name, org_type, contact_email, contact_phone, location, password_hash FROM organizations WHERE contact_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    respond(false, 'No organization found with that email', [], 401);
}

$org = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $org['password_hash'])) {
    respond(false, 'Incorrect password', [], 401);
}

$token = generateToken();
$upd   = $db->prepare("UPDATE organizations SET auth_token = ? WHERE id = ?");
$upd->bind_param("si", $token, $org['id']);
$upd->execute();
$upd->close();
$db->close();

unset($org['password_hash']);
$org['account_type'] = 'organization';

respond(true, 'Login successful', ['organization' => $org, 'token' => $token]);
