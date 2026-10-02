<?php
// Header กลางของทุกหน้า (เรียกใช้หลัง functions.php)
$user = currentUser();
$isAdminPage = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base = $isAdminPage ? '../' : '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ClubConnect-WebSystem</title>
<link rel="stylesheet" href="<?php echo $base; ?>assets/style.css">
</head>
<body>
<header class="header">
  <div class="container header-inner">
    <a class="brand" href="<?php echo $base; ?>index.php">ClubConnect</a>
    <nav class="nav">
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="<?php echo $base; ?>admin/dashboard.php">สรุปภาพรวม</a>
          <a href="<?php echo $base; ?>admin/announcements.php">จัดการชมรม</a>
          <a href="<?php echo $base; ?>admin/applications.php">ใบสมัคร</a>
        <?php else: ?>
          <a href="<?php echo $base; ?>dashboard.php">ชมรม</a>
          <a href="<?php echo $base; ?>application_status.php">สถานะใบสมัคร</a>
        <?php endif; ?>
        <span class="user">สวัสดี, <?php echo e($user['name']); ?> (<?php echo e($user['role']); ?>)</span>
        <a class="btn btn-small" href="<?php echo $base; ?>logout.php">ออกจากระบบ</a>
      <?php else: ?>
        <a href="<?php echo $base; ?>login.php">เข้าสู่ระบบ</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
