<?php
// 8.3 Application List + Filter สถานะ
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$apps = readJson('applications.json');
$filter = strtoupper(trim($_GET['status'] ?? ''));
if (!in_array($filter, ['PENDING', 'APPROVED', 'REJECTED'])) {
    $filter = '';
}
$list = [];
foreach ($apps as $a) {
    if ($filter !== '' && $a['status'] !== $filter) continue;
    $list[] = $a;
}
usort($list, function ($x, $y) { return strcmp($y['created_at'], $x['created_at']); });

include __DIR__ . '/../includes/header.php';
?>
<h2>รายการใบสมัคร</h2>
<p>
  <a class="btn btn-small <?php echo $filter === '' ? '' : 'btn-secondary'; ?>" href="applications.php">ทั้งหมด</a>
  <a class="btn btn-small <?php echo $filter === 'PENDING' ? '' : 'btn-secondary'; ?>" href="applications.php?status=PENDING">PENDING</a>
  <a class="btn btn-small <?php echo $filter === 'APPROVED' ? '' : 'btn-secondary'; ?>" href="applications.php?status=APPROVED">APPROVED</a>
  <a class="btn btn-small <?php echo $filter === 'REJECTED' ? '' : 'btn-secondary'; ?>" href="applications.php?status=REJECTED">REJECTED</a>
</p>
<?php if (empty($list)): ?>
  <p class="muted">ยังไม่มีใบสมัครในกลุ่มนี้</p>
<?php else: ?>
<table>
  <tr><th>Application ID</th><th>นักศึกษา</th><th>ชมรม</th><th>วันที่ส่ง</th><th>สถานะ</th><th></th></tr>
  <?php foreach ($list as $a): ?>
  <tr>
    <td><?php echo e($a['id']); ?></td>
    <td><?php echo e($a['student_name']); ?><br><small class="muted"><?php echo e($a['student_username'] ?? ''); ?></small></td>
    <td><?php echo e($a['club_name']); ?></td>
    <td><?php echo e($a['created_at']); ?></td>
    <td><span class="badge status-<?php echo e($a['status']); ?>"><?php echo e($a['status']); ?></span></td>
    <td><a class="btn btn-small" href="application_detail.php?id=<?php echo e($a['id']); ?>">ดูรายละเอียด</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
