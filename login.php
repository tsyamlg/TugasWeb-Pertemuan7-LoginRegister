<?php
require 'config.php';
guest_only();

$error = null;
$info  = flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(clean($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $_SESSION['attempts'] = $_SESSION['attempts'] ?? 0;

    if (!csrf_ok()) {
        $error = 'Token tidak valid, muat ulang halaman.';
    } elseif ($_SESSION['attempts'] >= 5 && time() - ($_SESSION['last_try'] ?? 0) < 60) {
        $error = 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.';
    } else {
        $user = find_user($email);
        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);           // cegah session fixation
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
            $_SESSION['attempts'] = 0;
            redirect('dashboard.php');
        }
        $_SESSION['attempts']++; $_SESSION['last_try'] = time();
        $error = 'Email atau password salah.';
    }
}
page_head('Login'); ?>
<h1>Login 🔐</h1>
<?php if ($info): ?><div class="alert ok"><?= e($info) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Email<input type="email" name="email" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button>Masuk</button>
</form>
<p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p>
<?php page_foot();
