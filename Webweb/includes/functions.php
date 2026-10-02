<?php
// ClubConnect-WebSystem : ฟังก์ชันกลาง (ใช้ร่วมกันทุกหน้า)
// อ้างอิง Req: JSON storage, Session, Validation, Backup, Escape Output
// ไฟล์นี้ถูก include เป็นอันดับแรกของทุกหน้า

// Polyfill กรณีเครื่องไม่มี ext-mbstring (เช่น PHP แบบ minimal)
// ระบบจะยังทำงานได้ปกติ (ค้นหาภาษาไทยด้วย stripos แบบ byte-safe)
if (!function_exists('mb_stripos')) {
    function mb_stripos($haystack, $needle, $offset = 0, $encoding = null) {
        return stripos($haystack, $needle, $offset);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = null) {
        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }
}
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
date_default_timezone_set('Asia/Bangkok');

// โฟลเดอร์ข้อมูล (ใช้ path แบบ relative จากไฟล์นี้ จะได้ย้ายเครื่องได้)
define('BASE_PATH', dirname(__DIR__));
define('DATA_DIR', BASE_PATH . '/data');
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('BACKUP_DIR', DATA_DIR . '/backup');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- JSON helpers ----------

// อ่านไฟล์ JSON คืนค่าเป็น array (ถ้าไฟล์เสีย/ไม่มี ให้คืน [])
function readJson($filename) {
    $path = DATA_DIR . '/' . $filename;
    if (!file_exists($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// สำรองไฟล์ JSON ก่อนเขียนทับ (เก็บใน data/backup/ชื่อไฟล์.YYYYMMDD_HHMMSS.bak)
function backupJson($filename) {
    $src = DATA_DIR . '/' . $filename;
    if (!file_exists($src)) {
        return true;
    }
    if (!is_dir(BACKUP_DIR)) {
        @mkdir(BACKUP_DIR, 0777, true);
    }
    $dst = BACKUP_DIR . '/' . $filename . '.' . date('Ymd_His') . '.bak';
    return @copy($src, $dst);
}

// เขียนไฟล์ JSON พร้อม backup + LOCK_EX (กันเขียนชนกันเบื้องต้น)
function writeJson($filename, $data) {
    backupJson($filename);
    $path = DATA_DIR . '/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $result = file_put_contents($path, $json, LOCK_EX);
    return $result !== false;
}

// ---------- Domain helpers ----------

function findUserByUsername($username) {
    $users = readJson('users.json');
    foreach ($users as $u) {
        if (isset($u['username']) && $u['username'] === $username) {
            return $u;
        }
    }
    return null;
}

function findUserById($id) {
    $users = readJson('users.json');
    foreach ($users as $u) {
        if (isset($u['id']) && (int)$u['id'] === (int)$id) {
            return $u;
        }
    }
    return null;
}

function findClubById($id) {
    $clubs = readJson('clubs.json');
    foreach ($clubs as $c) {
        if (isset($c['id']) && (int)$c['id'] === (int)$id) {
            return $c;
        }
    }
    return null;
}

function findApplicationById($appId) {
    $apps = readJson('applications.json');
    foreach ($apps as $a) {
        if (isset($a['id']) && $a['id'] === $appId) {
            return $a;
        }
    }
    return null;
}

// สร้าง Application ID อัตโนมัติ เช่น APP0001, APP0002
function generateApplicationId() {
    $apps = readJson('applications.json');
    $max = 0;
    foreach ($apps as $a) {
        if (isset($a['id']) && preg_match('/^APP(\d+)$/', $a['id'], $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }
    return sprintf('APP%04d', $max + 1);
}

// ตรวจว่าชมรมยังเปิดรับสมัครอยู่ไหม (เทียบวันที่วันนี้กับ start/end)
function isClubOpen($club) {
    if (empty($club['start_date']) || empty($club['end_date'])) {
        return false;
    }
    $today = date('Y-m-d');
    return ($today >= $club['start_date'] && $today <= $club['end_date']);
}

function statusThai($status) {
    if ($status === 'APPROVED') {
        return 'อนุมัติ';
    }
    if ($status === 'REJECTED') {
        return 'ไม่อนุมัติ';
    }
    return 'รอตรวจสอบ'; // PENDING
}

// ---------- Auth / Session ----------

function currentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isLoggedIn() {
    return currentUser() !== null;
}

// ต้อง login ก่อน ถ้าไม่ได้ login ให้ redirect ไปหน้า login
function requireLogin() {
    if (!isLoggedIn()) {
        // รองรับทั้งไฟล์ที่อยู่ root และใน admin/
        $login = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../login.php' : 'login.php';
        header('Location: ' . $login);
        exit;
    }
}

// ต้องมี role ตามที่กำหนด (ตรวจฝั่ง Server ห้ามเชื่อ role จาก client)
function requireRole($role) {
    requireLogin();
    $user = currentUser();
    if (!isset($user['role']) || $user['role'] !== $role) {
        // Student พยายามเข้า admin -> ส่งกลับ dashboard ของตัวเอง
        if (isset($user['role']) && $user['role'] === 'student') {
            header('Location: ../dashboard.php');
            // กรณีเรียกจาก root (กันไว้)
            if (strpos($_SERVER['PHP_SELF'], '/admin/') === false) {
                header('Location: dashboard.php');
            }
            exit;
        }
        header('Location: login.php');
        exit;
    }
}

// ---------- Output / misc ----------

// Escape HTML ป้องกัน XSS (รองรับภาษาไทย UTF-8)
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
