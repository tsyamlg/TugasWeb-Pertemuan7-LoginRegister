<?php
require 'config.php';
guest_only();

$errors = []; $old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { $errors[] = 'Token tidak valid, muat ulang halaman.'; }

    $name  = clean($_POST['name'] ?? '');
    $email = strtolower(clean($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm'] ?? '';
    $old   = ['name' => $name, 'email' => $email];

    if (mb_strlen($name) < 3 || mb_strlen($name) > 50)
        $errors[] = 'Nama harus 3–50 karakter.';
    elseif (!preg_match('/^[\p{L} .\'-]+$/u', $name))
        $errors[] = 'Nama hanya boleh berisi huruf dan spasi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Format email tidak valid.';
    if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass))
        $errors[] = 'Password minimal 8 karakter dan mengandung huruf serta angka.';
    if ($pass !== $pass2)
        $errors[] = 'Konfirmasi password tidak cocok.';
    if (!$errors && find_user($email))
        $errors[] = 'Email sudah terdaftar.';

    if (!$errors) {
        $users = load_users();
        $users[] = [
            'id'         => bin2hex(random_bytes(8)),
            'name'       => $name,
            'email'      => $email,
            'password'   => password_hash($pass, PASSWORD_DEFAULT),
            'created_at' => date('c'),
        ];
        if (save_users($users)) {
            flash('Registrasi berhasil! Silakan login.');
            redirect('login.php');
        }
        $errors[] = 'Gagal menyimpan data. Cek izin folder /data.';
    }
}
page_head('Register'); ?>
<h1>Buat Akun 📝</h1>
<?php foreach ($errors as $er): ?><div class="alert err"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate>
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Nama lengkap<input name="name" value="<?= e($old['name']) ?>" required></label>
  <label>Email<input type="email" name="email" value="<?= e($old['email']) ?>" required></label>
  <label>Password<input type="password" name="password" required></label>
  <label>Konfirmasi password<input type="password" name="confirm" required></label>
  <button>Daftar</button>
</form>
<p class="muted">Sudah punya akun? <a href="login.php">Login</a></p>
<?php page_foot();
