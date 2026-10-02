<?php
// FR-08: นักศึกษาดูเฉพาะใบสมัครของตัวเอง + กันเปิดของคนอื่น
require_once __DIR__ . '/includes/functions.php';
requireRole('student');

$user = currentUser();
$apps = readJson('applications.json');

// ถ้ามี ?id= แสดงรายละเอียดใบเดียว (ตรวจ ownership)
$single = null;
if (isset($_GET['id'])) {
    $single = findApplicationById($_GET['id']);
    if (!$single) {
        http_response_code(404);
        include __DIR__ . '/includes/header.php';
        echo '<div class="alert error">ไม่พบใบสมัคร (Not Found)</div><a class="btn" href="application_status.php">กลับ</a>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }
    if ((int)$single['student_user_id'] !== (int)$user['id']) {
        http_response_code(403);
        include __DIR__ . '/includes/header.php';
        echo '<div class="alert error">ไม่มีสิทธิ์ดูใบสมัครของนักศึกษาคนอื่น</div><a class="btn" href="application_status.php">กลับ</a>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }
    include __DIR__ . '/includes/header.php';
    ?>
    <h2>ใบสมัคร <?php echo e($single['id']); ?></h2>
    <div class="card">
      <p><strong>ชมรม:</strong> <?php echo e($single['club_name']); ?></p>
      <p><strong>วันที่สมัคร:</strong> <?php echo e($single['created_at']); ?></p>
      <p><strong>สถานะ:</strong> <span class="badge status-<?php echo e($single['status']); ?>"><?php echo e($single['status']); ?> (<?php echo e(statusThai($single['status'])); ?>)</span></p>
      <p><strong>หมายเหตุจากผู้ดูแล:</strong> <?php echo e($single['remark'] !== '' ? $single['remark'] : '-'); ?></p>
      <hr>
      <p><strong>ชื่อ:</strong> <?php echo e($single['student_name']); ?> (<?php echo e($single['student_id']); ?>)</p>
      <p><strong>คณะ/สาขา:</strong> <?php echo e($single['faculty']); ?> ชั้นปี <?php echo e($single['year']); ?></p>
      <p><strong>เบอร์โทร:</strong> <?php echo e($single['phone']); ?></p>
      <p><strong>เหตุผล:</strong><br><?php echo nl2br(e($single['reason'])); ?></p>
      <p><strong>ประสบการณ์:</strong><br><?php echo nl2br(e($single['experience'])); ?></p>
      <?php if (!empty($single['document'])): ?>
        <p><strong>เอกสารแนบ:</strong> <a href="uploads/<?php echo e($single['document']); ?>" target="_blank"><?php echo e($single['document']); ?></a></p>
      <?php endif; ?>
    </div>
    <a class="btn btn-secondary" href="application_status.php">กลับ</a>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// รายการทั้งหมดของตัวเอง
$mine = [];
foreach ($apps as $a) {
    if ((int)$a['student_user_id'] === (int)$user['id']) {
        $mine[] = $a;
    }
}
usort($mine, function ($x, $y) { return strcmp($y['created_at'], $x['created_at']); });

include __DIR__ . '/includes/header.php';
?>
<h2>สถานะใบสมัครของฉัน</h2>
<?php if (empty($mine)): ?>
  <p class="muted">ยังไม่มีใบสมัคร <a href="dashboard.php">ไปค้นหาชมรม</a></p>
<?php else: ?>
<table>
  <tr><th>Application ID</th><th>ชมรม</th><th>วันที่สมัคร</th><th>สถานะ</th><th>หมายเหตุ</th><th></th></tr>
  <?php foreach ($mine as $a): ?>
  <tr>
    <td><?php echo e($a['id']); ?></td>
    <td><?php echo e($a['club_name']); ?></td>
    <td><?php echo e($a['created_at']); ?></td>
    <td><span class="badge status-<?php echo e($a['status']); ?>"><?php echo e($a['status']); ?> (<?php echo e(statusThai($a['status'])); ?>)</span></td>
    <td><?php echo e($a['remark'] !== '' ? $a['remark'] : '-'); ?></td>
    <td><a class="btn btn-small" href="application_status.php?id=<?php echo e($a['id']); ?>">ดู</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
