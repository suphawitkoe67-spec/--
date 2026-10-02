<?php
// FR-04 + FR-05: ฟอร์มใบสมัคร + อัปโหลดเอกสาร (ตรวจทั้ง JS และ PHP)
require_once __DIR__ . '/includes/functions.php';
requireRole('student');

$user = currentUser();
$clubId = (int)($_GET['club_id'] ?? $_POST['club_id'] ?? 0);
$club = findClubById($clubId);
if (!$club) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="alert error">ไม่พบชมรม (Not Found)</div><a class="btn" href="dashboard.php">กลับ</a>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
// หมดเขตแล้วสมัครไม่ได้ (Validation ข้อ 5)
if (!isClubOpen($club)) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="alert error">หมดเขตรับสมัครแล้ว ไม่สามารถสมัครชมรมนี้ได้</div>';
    echo '<a class="btn btn-secondary" href="club_detail.php?id=' . (int)$club['id'] . '">กลับ</a>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$errors = [];
$success = null;

// ค่า default จากโปรไฟล์ (แก้ไขได้ก่อนส่ง)
$old = [
    'student_name' => $user['name'],
    'student_id' => ($user['username'] === 'student01') ? '6712231102' : (($user['username'] === 'student02') ? '6712231103' : ''),
    'faculty' => '',
    'year' => '',
    'phone' => '',
    'reason' => '',
    'experience' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // กรณี POST ใหญ่เกิน post_max_size ของ php.ini ทำให้ $_POST/$_FILES ว่างทั้งหมด
    // ให้แจ้งกติกา 10MB แทนที่จะเงียบหาย (TC-06)
    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors['document'] = 'ขนาดไฟล์ต้องไม่เกิน 10MB';
    }
    foreach ($old as $k => $v) {
        $old[$k] = trim($_POST[$k] ?? '');
    }

    // ตรวจ required
    $required = ['student_name' => 'ชื่อ', 'student_id' => 'รหัสนักศึกษา', 'faculty' => 'คณะ/สาขา',
                 'year' => 'ชั้นปี', 'phone' => 'เบอร์โทร', 'reason' => 'เหตุผลที่ต้องการสมัคร'];
    foreach ($required as $field => $label) {
        if ($old[$field] === '') {
            $errors[$field] = 'กรุณากรอก' . $label;
        }
    }

    // ตรวจไฟล์แนบ (optional แต่ถ้าแนบต้องผ่านกติกา)
    $savedFile = '';
    if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['document'];
        // กรณีไฟล์ใหญ่จนเกินค่า php.ini (UPLOAD_ERR_INI_SIZE=1) ให้แจ้งกติกา 10MB โดยตรง (TC-06)
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errors['document'] = 'ขนาดไฟล์ต้องไม่เกิน 10MB';
        } elseif ($f['error'] !== UPLOAD_ERR_OK) {
            $errors['document'] = 'อัปโหลดไฟล์ไม่สำเร็จ';
        } else {
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($f['size'] > $maxSize) {
                $errors['document'] = 'ขนาดไฟล์ต้องไม่เกิน 10MB'; // TC-06
            }
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $errors['document'] = 'รองรับเฉพาะไฟล์ PDF, JPG, JPEG, PNG';
            }
            // ตรวจ MIME เบื้องต้น (ถ้าเครื่องมี fileinfo)
            if (function_exists('mime_content_type')) {
                $mime = @mime_content_type($f['tmp_name']);
                $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
                // บางไฟล์ PDF อาจ detect เป็นอย่างอื่น ให้พึ่งนามสกุลร่วมด้วย
                if ($mime && !in_array($mime, $allowedMime) && !in_array($ext, $allowedExt)) {
                    $errors['document'] = 'ประเภทไฟล์ไม่รองรับ';
                }
            }
        }
    }

    if (empty($errors)) {
        // เตรียมชื่อไฟล์ปลอดภัย กันชื่อซ้ำ: APPxxxx_uniq.ext
        $appId = generateApplicationId();
        if ($savedFile === '' && isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $safeName = $appId . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) {
                @mkdir(UPLOAD_DIR, 0777, true);
            }
            $dest = UPLOAD_DIR . '/' . $safeName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $dest)) {
                $savedFile = $safeName;
            } else {
                $errors['document'] = 'บันทึกไฟล์ไม่สำเร็จ กรุณาลองใหม่';
            }
        }

        if (empty($errors)) {
            $now = date('Y-m-d H:i:s');
            $apps = readJson('applications.json');
            $apps[] = [
                'id' => $appId,
                'student_user_id' => $user['id'],
                'student_username' => $user['username'],
                'club_id' => $club['id'],
                'club_name' => $club['name'],
                'student_name' => $old['student_name'],
                'student_id' => $old['student_id'],
                'faculty' => $old['faculty'],
                'year' => $old['year'],
                'phone' => $old['phone'],
                'reason' => $old['reason'],
                'experience' => $old['experience'],
                'document' => $savedFile,
                'status' => 'PENDING',
                'remark' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (writeJson('applications.json', $apps)) {
                $success = $appId;
            } else {
                $errors['form'] = 'บันทึกข้อมูลไม่สำเร็จ กรุณาลองใหม่';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h2>สมัครเข้าชมรม: <?php echo e($club['name']); ?></h2>

<?php if ($success): ?>
  <div class="alert success">ส่งใบสมัครสำเร็จ เลขที่ใบสมัคร <strong><?php echo e($success); ?></strong> สถานะ PENDING (รอตรวจสอบ)</div>
  <a class="btn" href="application_status.php">ดูสถานะใบสมัคร</a>
  <a class="btn btn-secondary" href="dashboard.php">กลับหน้าชมรม</a>
<?php else: ?>
  <?php if (isset($errors['form'])): ?>
    <div class="alert error"><?php echo e($errors['form']); ?></div>
  <?php endif; ?>
  <form class="card" id="applyForm" method="post" enctype="multipart/form-data" action="apply.php?club_id=<?php echo (int)$club['id']; ?>" novalidate>
    <input type="hidden" name="club_id" value="<?php echo (int)$club['id']; ?>">
    <label>ชื่อ-นามสกุล *
      <input type="text" name="student_name" value="<?php echo e($old['student_name']); ?>" class="<?php echo isset($errors['student_name']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['student_name'])): ?><small class="err"><?php echo e($errors['student_name']); ?></small><?php endif; ?>
    </label>
    <label>รหัสนักศึกษา *
      <input type="text" name="student_id" value="<?php echo e($old['student_id']); ?>" class="<?php echo isset($errors['student_id']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['student_id'])): ?><small class="err"><?php echo e($errors['student_id']); ?></small><?php endif; ?>
    </label>
    <label>คณะ/สาขา *
      <input type="text" name="faculty" placeholder="เช่น วิทยาศาสตร์/คอมพิวเตอร์" value="<?php echo e($old['faculty']); ?>" class="<?php echo isset($errors['faculty']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['faculty'])): ?><small class="err"><?php echo e($errors['faculty']); ?></small><?php endif; ?>
    </label>
    <label>ชั้นปี *
      <select name="year" class="<?php echo isset($errors['year']) ? 'invalid' : ''; ?>">
        <option value="">-- เลือก --</option>
        <?php foreach (['1','2','3','4'] as $y): ?>
          <option value="<?php echo $y; ?>" <?php echo ($old['year'] === $y) ? 'selected' : ''; ?>>ปี <?php echo $y; ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['year'])): ?><small class="err"><?php echo e($errors['year']); ?></small><?php endif; ?>
    </label>
    <label>เบอร์โทร *
      <input type="tel" name="phone" value="<?php echo e($old['phone']); ?>" class="<?php echo isset($errors['phone']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['phone'])): ?><small class="err"><?php echo e($errors['phone']); ?></small><?php endif; ?>
    </label>
    <label>เหตุผลที่ต้องการสมัคร *
      <textarea name="reason" rows="3" class="<?php echo isset($errors['reason']) ? 'invalid' : ''; ?>"><?php echo e($old['reason']); ?></textarea>
      <?php if (isset($errors['reason'])): ?><small class="err"><?php echo e($errors['reason']); ?></small><?php endif; ?>
    </label>
    <label>ประสบการณ์/ความสนใจ
      <textarea name="experience" rows="3"><?php echo e($old['experience']); ?></textarea>
    </label>
    <label>เอกสารแนบ (PDF/JPG/PNG ไม่เกิน 10MB)
      <input type="file" name="document" id="document" accept=".pdf,.jpg,.jpeg,.png">
      <?php if (isset($errors['document'])): ?><small class="err"><?php echo e($errors['document']); ?></small><?php endif; ?>
      <small class="muted" id="fileMsg"></small>
    </label>
    <button class="btn" type="submit">ส่งใบสมัคร</button>
    <a class="btn btn-secondary" href="club_detail.php?id=<?php echo (int)$club['id']; ?>">ย้อนกลับ</a>
  </form>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
