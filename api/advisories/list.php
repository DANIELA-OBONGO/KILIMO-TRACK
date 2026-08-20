<?php
// GET /api/advisories/list.php?region=Nairobi  (region optional)

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', [], 405);
}

$db = getDB();
$region = trim($_GET['region'] ?? '');

if ($region) {
    $stmt = $db->prepare(
        "SELECT a.id, a.title, a.content, a.target_region, a.created_at,
                o.org_name, o.org_type
         FROM advisories a
         JOIN organizations o ON a.org_id = o.id
         WHERE a.target_region LIKE ?
         ORDER BY a.created_at DESC"
    );
    $like = "%$region%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $db->prepare(
        "SELECT a.id, a.title, a.content, a.target_region, a.created_at,
                o.org_name, o.org_type
         FROM advisories a
         JOIN organizations o ON a.org_id = o.id
         ORDER BY a.created_at DESC
         LIMIT 50"
    );
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

respond(true, 'OK', ['advisories' => $rows, 'count' => count($rows)]);
