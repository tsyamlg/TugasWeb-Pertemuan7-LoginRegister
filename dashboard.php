<?php
require 'config.php';
require_login();
$u = $_SESSION['user'];
$full = find_user($u['email']);
$since = $full ? date('d M Y', strtotime($full['created_at'])) : '-';
page_head('Dashboard'); ?>
<span class="chip">● Sesi aktif</span>
<h1>Halo, <?= e(explode(' ', $u['name'])[0]) ?> 👋</h1>
<p class="muted">Anda berhasil masuk. Halaman ini hanya bisa dibuka setelah login.</p>
<div class="stats">
  <div class="stat"><small>Nama</small><strong><?= e($u['name']) ?></strong></div>
  <div class="stat"><small>Email</small><strong><?= e($u['email']) ?></strong></div>
  <div class="stat"><small>Bergabung</small><strong><?= e($since) ?></strong></div>
  <div class="stat"><small>Keamanan</small><strong>Hash + CSRF ✓</strong></div>
</div>
<form method="post" action="logout.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <button class="ghost">Logout</button>
</form>
<?php page_foot();
