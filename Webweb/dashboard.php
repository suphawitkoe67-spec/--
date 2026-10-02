<?php
// FR-02 + FR-03: Student Dashboard - รายชื่อชมรม + ค้นหา/กรอง
require_once __DIR__ . '/includes/functions.php';
requireRole('student');

$user = currentUser();
$clubs = readJson('clubs.json');
$announcements = readJson('announcements.json');

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

// รวบรวมหมวดทั้งหมดสำหรับ dropdown
$allCategories = [];
foreach ($clubs as $c) {
    if (!empty($c['category']) && !in_array($c['category'], $allCategories)) {
        $allCategories[] = $c['category'];
    }
}
sort($allCategories);

// กรอง: ค้นหาจากชื่อ (case-insensitive, รองรับไทย) + หมวด
$filtered = [];
foreach ($clubs as $c) {
    if ($q !== '' && mb_stripos($c['name'] ?? '', $q) === false
        && mb_stripos($c['description'] ?? '', $q) === false) {
        continue;
    }
    if ($category !== '' && ($c['category'] ?? '') !== $category) {
        continue;
    }
    $filtered[] = $c;
}

include __DIR__ . '/includes/header.php';
?>

<h2>รายชื่อชมรม</h2>

<?php if (!empty($announcements)): ?>
<div class="card">
  <h3>ประกาศรับสมัคร</h3>
  <?php foreach ($announcements as $an): ?>
    <div class="announce">
      <strong><?php echo e($an['title']); ?></strong>
      <span class="muted"><?php echo e($an['created_at'] ?? ''); ?></span>
      <p><?php echo e($an['message']); ?></p>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<form class="filter" method="get" action="dashboard.php">
  <input type="text" name="q" placeholder="ค้นหาชื่อชมรม เช่น ดนตรี" value="<?php echo e($q); ?>">
  <select name="category">
    <option value="">ทุกประเภท</option>
    <?php foreach ($allCategories as $cat): ?>
      <option value="<?php echo e($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
        <?php echo e($cat); ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">ค้นหา</button>
  <?php if ($q !== '' || $category !== ''): ?>
    <a class="btn btn-secondary" href="dashboard.php">ล้าง</a>
  <?php endif; ?>
</form>

<?php if (empty($filtered)): ?>
  <p class="muted">ไม่พบชมรมที่ตรงกับเงื่อนไข</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($filtered as $c): ?>
    <?php $open = isClubOpen($c); ?>
    <div class="card">
      <h3><?php echo e($c['name']); ?></h3>
      <p><span class="badge"><?php echo e($c['category']); ?></span>
        <?php if ($open): ?>
          <span class="badge open">เปิดรับ</span>
        <?php else: ?>
          <span class="badge closed">ปิดรับ</span>
        <?php endif; ?>
      </p>
      <p><?php echo e(mb_substr($c['description'] ?? '', 0, 120)); ?></p>
      <p class="muted">รับสมัคร: <?php echo e($c['start_date']); ?> ถึง <?php echo e($c['end_date']); ?></p>
      <a class="btn btn-small" href="club_detail.php?id=<?php echo (int)$c['id']; ?>">ดูรายละเอียด</a>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
