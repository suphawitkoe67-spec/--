<?php
// API: ประกาศทั้งหมด (ต้อง login)
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}
echo json_encode(['ok' => true, 'data' => readJson('announcements.json')], JSON_UNESCAPED_UNICODE);
