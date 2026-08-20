<?php
// KilimoTrack — CORS & Response Helpers
// Include this at the top of every API endpoint

// Allow requests from Firebase-hosted frontend AND local development
$allowed_origins = [
    'https://kilimotrack.web.app',
    'http://localhost',
    'http://localhost:80',
    'http://127.0.0.1',
    'null'  // file:// protocol (direct file open)
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle pre-flight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Send a JSON response and exit.
 */
function respond(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get parsed JSON body from request, or fall back to $_POST.
 */
function getBody(): array {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) return $decoded;
    }
    return $_POST;
}

/**
 * Generate a random 64-char hex auth token.
 */
function generateToken(): string {
    return bin2hex(random_bytes(32));
}

/**
 * Handle file upload and return saved filename or null.
 */
function handleUpload(string $field, string $subfolder, array $allowedTypes = ['image/jpeg','image/png','image/gif']): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file     = $_FILES[$field];
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes, true)) {
        return null;
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('', true) . '.' . strtolower($ext);
    $destDir  = __DIR__ . '/../../uploads/' . $subfolder . '/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
        return $filename;
    }
    return null;
}
