<?php
// KilimoTrack — Token Verification Helper
// Include this in any endpoint that requires an authenticated user.
// Usage:
//   require_once __DIR__ . '/../auth/verify.php';
//   $auth = requireAuth();  // returns ['id'=>…, 'type'=>'farmer'|'org']
// If the token is invalid or missing, respond() will exit with 401.

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Validate the Bearer token from the Authorization header.
 * Returns ['id' => int, 'type' => 'farmer'|'organization'] or exits with 401.
 */
function requireAuth(): array {
    // Accept token from Authorization header or X-Auth-Token header
    $token = null;
    $headers = getallheaders();
    foreach ($headers as $k => $v) {
        if (strtolower($k) === 'authorization') {
            if (preg_match('/^Bearer\s+(.+)$/i', $v, $m)) {
                $token = trim($m[1]);
            }
        }
        if (strtolower($k) === 'x-auth-token') {
            $token = trim($v);
        }
    }

    if (!$token || strlen($token) < 16) {
        respond(false, 'Authentication required. Please log in.', [], 401);
    }

    $db = getDB();

    // Check farmers table
    $stmt = $db->prepare("SELECT id FROM farmers WHERE auth_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $stmt->close();
        $db->close();
        return ['id' => (int)$row['id'], 'type' => 'farmer'];
    }
    $stmt->close();

    // Check organizations table
    $stmt2 = $db->prepare("SELECT id FROM organizations WHERE auth_token = ? LIMIT 1");
    $stmt2->bind_param("s", $token);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    if ($res2->num_rows > 0) {
        $row2 = $res2->fetch_assoc();
        $stmt2->close();
        $db->close();
        return ['id' => (int)$row2['id'], 'type' => 'organization'];
    }
    $stmt2->close();
    $db->close();

    respond(false, 'Invalid or expired session. Please log in again.', [], 401);
}
