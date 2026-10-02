<?php
// FR-01: Login ตรวจสอบจาก users.json + PHP Session
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    $u = currentUser();
    redirect($u['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'กรุณากรอก Username และ Password';
    } else {
        $user = findUserByUsername($username);
        // เปรียบเทียบรหัสผ่านแบบตรง (Demo/Mini Project ตามเอกสาร)
        if ($user && $user['password'] === $password) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
            ];
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
        } else {
            $error = 'Username หรือ Password ไม่ถูกต้อง'; // TC-02
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ - ClubConnect</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="header">
  <div class="container header-inner">
    <a class="brand" href="index.php">ClubConnect</a>
  </div>
</header>
<main class="container">
  <div class="card narrow">
    <h2>เข้าสู่ระบบ</h2>
    <p class="muted">ระบบจัดการและลงทะเบียนชมรมออนไลน์ (COMP342)</p>
    <?php if ($error): ?>
      <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <label>Username
        <input type="text" name="username" required value="<?php echo e($_POST['username'] ?? ''); ?>">
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button class="btn" type="submit">Login</button>
    </form>
    <hr>
    <h3>บัญชี Demo (สำหรับอาจารย์ทดลอง)</h3>
    <table>
      <tr><th>บทบาท</th><th>Username</th><th>Password</th></tr>
      <tr><td>Student</td><td>student01</td><td>1234</td></tr>
      <tr><td>Student</td><td>student02</td><td>1234</td></tr>
      <tr><td>Club Admin</td><td>admin01</td><td>1234</td></tr>
    </table>
  </div>
</main>
<footer class="footer"><div class="container"><small>ClubConnect-WebSystem &middot; JSON File Storage (Demo)</small></div></footer>
</body>
</html>
