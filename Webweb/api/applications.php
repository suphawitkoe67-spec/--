<?php
// API: ใบสมัคร - student เห็นเฉพาะของตัวเอง, admin เห็นทั้งหมด (?status= กรองได้)
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}
$user = currentUser();
$apps = readJson('applications.json');
$status = strtoupper(trim($_GET['status'] ?? ''));
$out = [];
foreach ($apps as $a) {
    if ($user['role'] === 'student' && (int)$a['student_user_id'] !== (int)$user['id']) continue;
    if ($status !== '' && $a['status'] !== $status) continue;
    $out[] = $a;
}
echo json_encode(['ok' => true, 'data' => $out], JSON_UNESCAPED_UNICODE);
