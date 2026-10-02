<?php
// FR-06: สร้าง/แก้ไขชมรม (Club Admin) -> บันทึกแล้วแสดงบน Student Dashboard ทันที
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$clubs = readJson('clubs.json');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = null;
if ($id > 0) {
    $editing = findClubById($id);
}
$errors = [];
$message = '';

// ลบชมรม (?delete=id)
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $clubs = array_values(array_filter($clubs, function ($c) use ($delId) { return (int)$c['id'] !== $delId; }));
    if (writeJson('clubs.json', $clubs)) {
        $message = '<div class="alert success">ลบชมรมแล้ว</div>';
    } else {
        $message = '<div class="alert error">ลบไม่สำเร็จ</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formId = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $start = trim($_POST['start_date'] ?? '');
    $end = trim($_POST['end_date'] ?? '');

    if ($name === '') $errors[] = 'กรุณากรอกชื่อชมรม';
    if ($category === '') $errors[] = 'กรุณาเลือก/กรอกประเภท';
    if ($start === '' || $end === '') $errors[] = 'กรุณากำหนดวันเริ่มและวันสิ้นสุด';
    if ($start !== '' && $end !== '' && $start > $end) $errors[] = 'วันเริ่มต้องไม่เกินวันสิ้นสุด';

    if (empty($errors)) {
        $clubs = readJson('clubs.json');
        if ($formId > 0) {
            foreach ($clubs as &$c) {
                if ((int)$c['id'] === $formId) {
                    $c['name'] = $name; $c['category'] = $category;
                    $c['description'] = $description; $c['requirements'] = $requirements;
                    $c['start_date'] = $start; $c['end_date'] = $end;
                    break;
                }
            }
            unset($c);
            $message = '<div class="alert success">แก้ไขชมรมแล้ว</div>';
        } else {
            $maxId = 0;
            foreach ($clubs as $c) { if ((int)$c['id'] > $maxId) $maxId = (int)$c['id']; }
            $clubs[] = [
                'id' => $maxId + 1, 'name' => $name, 'category' => $category,
                'description' => $description, 'requirements' => $requirements,
                'start_date' => $start, 'end_date' => $end,
            ];
            $message = '<div class="alert success">สร้างชมรมใหม่แล้ว (TC-08: Student จะเห็นทันที)</div>';
        }
        if (writeJson('clubs.json', $clubs)) {
            $editing = null;
            $id = 0;
        } else {
            $message = '<div class="alert error">บันทึกไม่สำเร็จ</div>';
        }
    } else {
        $message = '<div class="alert error">' . e(implode('<br>', $errors)) . '</div>';
        // คงค่าฟอร์มไว้
        $editing = ['id' => $formId, 'name' => $name, 'category' => $category,
            'description' => $description, 'requirements' => $requirements,
            'start_date' => $start, 'end_date' => $end];
    }
}

$clubs = readJson('clubs.json');
$categories = ['ดนตรี', 'กีฬา', 'วิชาการ', 'เทคโนโลยี', 'ศิลปะ', 'จิตอาสา'];

include __DIR__ . '/../includes/header.php';
echo $message;
?>
<h2><?php echo $editing ? 'แก้ไขชมรม' : 'สร้างชมรมใหม่'; ?></h2>
<form class="card" method="post" action="club_form.php<?php echo $editing ? '?id=' . (int)$editing['id'] : ''; ?>">
  <input type="hidden" name="id" value="<?php echo $editing ? (int)$editing['id'] : 0; ?>">
  <label>ชื่อชมรม *
    <input type="text" name="name" required value="<?php echo e($editing['name'] ?? ''); ?>">
  </label>
  <label>ประเภท *
    <input type="text" name="category" list="catList" required placeholder="เช่น ดนตรี" value="<?php echo e($editing['category'] ?? ''); ?>">
    <datalist id="catList">
      <?php foreach ($categories as $cat): ?><option value="<?php echo e($cat); ?>"><?php endforeach; ?>
    </datalist>
  </label>
  <label>รายละเอียด
    <textarea name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea>
  </label>
  <label>คุณสมบัติผู้สมัคร
    <textarea name="requirements" rows="2"><?php echo e($editing['requirements'] ?? ''); ?></textarea>
  </label>
  <label>วันเริ่มรับสมัคร *
    <input type="date" name="start_date" required value="<?php echo e($editing['start_date'] ?? ''); ?>">
  </label>
  <label>วันสิ้นสุดรับสมัคร *
    <input type="date" name="end_date" required value="<?php echo e($editing['end_date'] ?? ''); ?>">
  </label>
  <button class="btn" type="submit">บันทึก</button>
  <?php if ($editing): ?><a class="btn btn-secondary" href="club_form.php">ยกเลิก</a><?php endif; ?>
  <a class="btn btn-secondary" href="announcements.php">กลับหน้าจัดการ</a>
</form>

<h2>ชมรมทั้งหมด</h2>
<table>
  <tr><th>ID</th><th>ชื่อ</th><th>ประเภท</th><th>ช่วงรับสมัคร</th><th></th></tr>
  <?php foreach ($clubs as $c): ?>
  <tr>
    <td><?php echo (int)$c['id']; ?></td>
    <td><?php echo e($c['name']); ?></td>
    <td><?php echo e($c['category']); ?></td>
    <td><?php echo e($c['start_date']); ?> ถึง <?php echo e($c['end_date']); ?></td>
    <td>
      <a class="btn btn-small" href="club_form.php?id=<?php echo (int)$c['id']; ?>">แก้ไข</a>
      <a class="btn btn-small btn-danger" href="club_form.php?delete=<?php echo (int)$c['id']; ?>" onclick="return confirm('ลบชมรมนี้?')">ลบ</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
