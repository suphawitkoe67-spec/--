<?php
// 8.1 Admin Dashboard: สรุปจำนวนชมรม/ใบสมัครตามสถานะ
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$clubs = readJson('clubs.json');
$apps = readJson('applications.json');

$countPending = 0; $countApproved = 0; $countRejected = 0;
foreach ($apps as $a) {
    if ($a['status'] === 'PENDING') $countPending++;
    elseif ($a['status'] === 'APPROVED') $countApproved++;
    elseif ($a['status'] === 'REJECTED') $countRejected++;
}

include __DIR__ . '/../includes/header.php';
?>
<h2>Admin Dashboard</h2>
<div class="grid">
  <div class="card stat"><div class="num"><?php echo count($clubs); ?></div><div>จำนวนชมรม</div></div>
  <div class="card stat"><div class="num"><?php echo count($apps); ?></div><div>ใบสมัครทั้งหมด</div></div>
  <div class="card stat"><div class="num"><?php echo $countPending; ?></div><div>PENDING (รอตรวจสอบ)</div></div>
  <div class="card stat"><div class="num"><?php echo $countApproved; ?></div><div>APPROVED (อนุมัติ)</div></div>
  <div class="card stat"><div class="num"><?php echo $countRejected; ?></div><div>REJECTED (ไม่อนุมัติ)</div></div>
</div>
<p>
  <a class="btn" href="announcements.php">จัดการชมรม / ประกาศ</a>
  <a class="btn" href="applications.php">ดูใบสมัคร</a>
  <a class="btn btn-secondary" href="../logout.php">ออกจากระบบ</a>
</p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
