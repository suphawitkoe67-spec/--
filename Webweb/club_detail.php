<?php
// FR-02: รายละเอียดชมรม + ปุ่มสมัคร (disable ถ้าหมดเขต)
require_once __DIR__ . '/includes/functions.php';
requireRole('student');

$id = (int)($_GET['id'] ?? 0);
$club = findClubById($id);
if (!$club) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="alert error">ไม่พบชมรม (Not Found)</div><a class="btn" href="dashboard.php">กลับ</a>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$open = isClubOpen($club);
include __DIR__ . '/includes/header.php';
?>

<div class="card">
  <h2><?php echo e($club['name']); ?></h2>
  <p><span class="badge"><?php echo e($club['category']); ?></span>
    <?php if ($open): ?><span class="badge open">เปิดรับสมัคร</span>
    <?php else: ?><span class="badge closed">ปิดรับสมัคร</span><?php endif; ?>
  </p>
  <p><strong>รายละเอียด:</strong><br><?php echo nl2br(e($club['description'])); ?></p>
  <p><strong>คุณสมบัติผู้สมัคร:</strong><br><?php echo nl2br(e($club['requirements'])); ?></p>
  <p><strong>วันเปิดรับสมัคร:</strong> <?php echo e($club['start_date']); ?></p>
  <p><strong>วันปิดรับสมัคร:</strong> <?php echo e($club['end_date']); ?></p>

  <?php if ($open): ?>
    <a class="btn" href="apply.php?club_id=<?php echo (int)$club['id']; ?>">สมัครเข้าชมรม</a>
  <?php else: ?>
    <button class="btn" disabled>หมดเขตรับสมัครแล้ว</button>
  <?php endif; ?>
  <a class="btn btn-secondary" href="dashboard.php">กลับ</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
