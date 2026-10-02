<?php
// API: รายชื่อชมรม (รองรับ ?q= &category=) ต้อง login ก่อน
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}
$clubs = readJson('clubs.json');
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$out = [];
foreach ($clubs as $c) {
    if ($q !== '' && mb_stripos($c['name'] ?? '', $q) === false) continue;
    if ($cat !== '' && ($c['category'] ?? '') !== $cat) continue;
    $c['open'] = isClubOpen($c);
    $out[] = $c;
}
echo json_encode(['ok' => true, 'data' => $out], JSON_UNESCAPED_UNICODE);
