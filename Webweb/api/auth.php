<?php
// API: ตรวจสอบสิทธิ์ + คืน session ปัจจุบัน (ตัวอย่างโครง api/)
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}
$u = currentUser();
echo json_encode(['ok' => true, 'user' => ['username' => $u['username'], 'name' => $u['name'], 'role' => $u['role']]], JSON_UNESCAPED_UNICODE);
