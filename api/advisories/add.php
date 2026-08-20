<?php
// POST /api/advisories/add.php
// Organization posts an advisory alert.
// SECURITY: org_id comes from verified auth token.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/verify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

$auth = requireAuth();
if ($auth['type'] !== 'organization') {
    respond(false, 'Only organization accounts can post advisories', [], 403);
}
$org_id = $auth['id'];

$body = getBody();

$title         = trim($body['title']         ?? '');
$content       = trim($body['content']       ?? '');
$target_region = trim($body['target_region'] ?? '') ?: null;

if (!$title || !$content) {
    respond(false, 'title and content are required', [], 422);
}

$db   = getDB();
$stmt = $db->prepare(
    "INSERT INTO advisories (org_id, title, content, target_region) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isss", $org_id, $title, $content, $target_region);

if ($stmt->execute()) {
    respond(true, 'Advisory posted successfully', ['id' => $db->insert_id], 201);
} else {
    respond(false, 'Insert failed: ' . $stmt->error, [], 500);
}
$stmt->close();
$db->close();
