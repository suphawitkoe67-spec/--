<?php
// 8.4 Application Detail: ดูข้อมูล + อนุมัติ/ไม่อนุมัติ + หมายเหตุ
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$appId = $_GET['id'] ?? $_POST['id'] ?? '';
$app = findApplicationById($appId);
if (!$app) {
    http_response_code(404);
    include __DIR__ . '/../includes/header.php';
    echo '<div class="alert error">ไม่พบใบสมัคร (Not Found)</div><a class="btn" href="applications.php">กลับ</a>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $remark = trim($_POST['remark'] ?? '');
    $newStatus = ($action === 'approve') ? 'APPROVED' : (($action === 'reject') ? 'REJECTED' : '');

    if ($newStatus === '') {
        $message = '<div class="alert error">คำสั่งไม่ถูกต้อง</div>';
    } else {
        $apps = readJson('applications.json');
        foreach ($apps as &$a) {
            if ($a['id'] === $appId) {
                // เตือนถ้าเคยพิจารณาแล้ว (กันเปลี่ยนสถานะซ้ำโดยไม่ตั้งใจ)
                if ($a['status'] !== 'PENDING' && empty($_POST['confirm'])) {
                    $message = '<div class="alert error">ใบสมัครนี้ถูกพิจารณาแล้ว (' . e($a['status']) . ') หากต้องการเปลี่ยนสถานะ ให้ติ๊กยืนยันก่อนกดปุ่มอีกครั้ง</div>';
                    break;
                }
                $a['status'] = $newStatus;
                $a['remark'] = $remark;
                $a['updated_at'] = date('Y-m-d H:i:s');
                $app = $a; // refresh สำหรับแสดงผล
                if (writeJson('applications.json', $apps)) {
                    $message = '<div class="alert success">บันทึกสถานะเป็น ' . e($newStatus) . ' แล้ว</div>';
                } else {
                    $message = '<div class="alert error">บันทึกไม่สำเร็จ</div>';
                }
                break;
            }
        }
        unset($a);
    }
}

include __DIR__ . '/../includes/header.php';
echo $message;
?>
<h2>ใบสมัคร <?php echo e($app['id']); ?>
  <span class="badge status-<?php echo e($app['status']); ?>"><?php echo e($app['status']); ?> (<?php echo e(statusThai($app['status'])); ?>)</span>
</h2>
<div class="card">
  <p><strong>ชมรม:</strong> <?php echo e($app['club_name']); ?></p>
  <p><strong>ผู้สมัคร:</strong> <?php echo e($app['student_name']); ?> (รหัส <?php echo e($app['student_id']); ?>, user <?php echo e($app['student_username'] ?? '-'); ?>)</p>
  <p><strong>คณะ/สาขา:</strong> <?php echo e($app['faculty']); ?> ชั้นปี <?php echo e($app['year']); ?></p>
  <p><strong>เบอร์โทร:</strong> <?php echo e($app['phone']); ?></p>
  <p><strong>เหตุผล:</strong><br><?php echo nl2br(e($app['reason'])); ?></p>
  <p><strong>ประสบการณ์:</strong><br><?php echo nl2br(e($app['experience'])); ?></p>
  <p><strong>ส่งเมื่อ:</strong> <?php echo e($app['created_at']); ?> / <strong>อัปเดต:</strong> <?php echo e($app['updated_at']); ?></p>
  <?php if (!empty($app['document'])): ?>
    <p><strong>เอกสารแนบ:</strong> <a href="../uploads/<?php echo e($app['document']); ?>" target="_blank"><?php echo e($app['document']); ?></a></p>
  <?php else: ?>
    <p class="muted">ไม่มีเอกสารแนบ</p>
  <?php endif; ?>
</div>

<form class="card" method="post" action="application_detail.php?id=<?php echo e($app['id']); ?>">
  <input type="hidden" name="id" value="<?php echo e($app['id']); ?>">
  <label>หมายเหตุ
    <textarea name="remark" rows="3" placeholder="เช่น นัดสัมภาษณ์ / เอกสารไม่ครบ"><?php echo e($app['remark']); ?></textarea>
  </label>
  <?php if ($app['status'] !== 'PENDING'): ?>
    <label><input type="checkbox" name="confirm" value="1"> ยืนยันเปลี่ยนสถานะซ้ำ (ปัจจุบัน: <?php echo e($app['status']); ?>)</label>
  <?php endif; ?>
  <p>
    <button class="btn" type="submit" name="action" value="approve">อนุมัติ</button>
    <button class="btn btn-danger" type="submit" name="action" value="reject">ไม่อนุมัติ</button>
    <a class="btn btn-secondary" href="applications.php">กลับ</a>
  </p>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
