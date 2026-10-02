<?php
// 8.2 จัดการประกาศ + ทางเข้าจัดการชมรม (announcements.json CRUD อย่างง่าย)
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$message = '';
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $ans = readJson('announcements.json');
    $ans = array_values(array_filter($ans, function ($a) use ($delId) { return (int)$a['id'] !== $delId; }));
    $message = writeJson('announcements.json', $ans)
        ? '<div class="alert success">ลบประกาศแล้ว</div>'
        : '<div class="alert error">ลบไม่สำเร็จ</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $clubId = (int)($_POST['club_id'] ?? 0);
    $msg = trim($_POST['message'] ?? '');
    if ($title === '' || $msg === '') {
        $message = '<div class="alert error">กรุณากรอกหัวข้อและข้อความประกาศ</div>';
    } else {
        $ans = readJson('announcements.json');
        $maxId = 0;
        foreach ($ans as $a) { if ((int)$a['id'] > $maxId) $maxId = (int)$a['id']; }
        $ans[] = ['id' => $maxId + 1, 'club_id' => $clubId, 'title' => $title,
                  'message' => $msg, 'created_at' => date('Y-m-d H:i:s')];
        $message = writeJson('announcements.json', $ans)
            ? '<div class="alert success">สร้างประกาศแล้ว Student จะเห็นบน Dashboard</div>'
            : '<div class="alert error">บันทึกไม่สำเร็จ</div>';
    }
}

$clubs = readJson('clubs.json');
$ans = readJson('announcements.json');

include __DIR__ . '/../includes/header.php';
echo $message;
?>
<h2>จัดการชมรม / ประกาศรับสมัคร</h2>
<p><a class="btn" href="club_form.php">+ สร้าง / แก้ไขชมรม</a></p>

<h3>สร้างประกาศใหม่</h3>
<form class="card" method="post" action="announcements.php">
  <label>หัวข้อ *
    <input type="text" name="title" required>
  </label>
  <label>ชมรมที่เกี่ยวข้อง
    <select name="club_id">
      <option value="0">-- ทั่วไป --</option>
      <?php foreach ($clubs as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>ข้อความ *
    <textarea name="message" rows="3" required></textarea>
  </label>
  <button class="btn" type="submit">บันทึกประกาศ</button>
</form>

<h3>ประกาศทั้งหมด</h3>
<?php if (empty($ans)): ?>
  <p class="muted">ยังไม่มีประกาศ</p>
<?php else: ?>
<table>
  <tr><th>ID</th><th>หัวข้อ</th><th>ข้อความ</th><th>วันที่</th><th></th></tr>
  <?php foreach ($ans as $a): ?>
  <tr>
    <td><?php echo (int)$a['id']; ?></td>
    <td><?php echo e($a['title']); ?></td>
    <td><?php echo e(mb_substr($a['message'] ?? '', 0, 80)); ?></td>
    <td><?php echo e($a['created_at']); ?></td>
    <td><a class="btn btn-small btn-danger" href="announcements.php?delete=<?php echo (int)$a['id']; ?>" onclick="return confirm('ลบประกาศนี้?')">ลบ</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
