<?php
// หน้าแรก: กระจายตามสถานะ login/role (FR-01)
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}
$user = currentUser();
if ($user['role'] === 'admin') {
    redirect('admin/dashboard.php');
} else {
    redirect('dashboard.php');
}
