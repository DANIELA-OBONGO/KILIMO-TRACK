<?php
// KilimoTrack — Database Configuration (Railway MySQL)

define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASS', 'fgNdDsOPKfXSIjOwRbpqqvkJODuCcEoR');
define('DB_NAME', 'railway');
define('DB_PORT', 3306);

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
