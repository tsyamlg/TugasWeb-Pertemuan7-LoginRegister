<?php
declare(strict_types=1);
session_start();

const USERS_FILE = __DIR__ . '/data/users.json';

/* ---------- Sanitasi ---------- */
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function clean(string $s): string { return trim(strip_tags($s)); }

/* ---------- "Database" JSON ---------- */
function load_users(): array {
    if (!file_exists(USERS_FILE)) return [];
    $d = json_decode((string) file_get_contents(USERS_FILE), true);
    return is_array($d) ? $d : [];
}
function save_users(array $users): bool {
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}
function find_user(string $email): ?array {
    foreach (load_users() as $u) {
        if (strcasecmp($u['email'], $email) === 0) return $u;
    }
    return null;
}

/* ---------- Session & CSRF ---------- */
function is_logged_in(): bool { return isset($_SESSION['user']); }
function redirect(string $to): void { header("Location: $to"); exit; }
function require_login(): void { if (!is_logged_in()) redirect('login.php'); }
function guest_only(): void { if (is_logged_in()) redirect('dashboard.php'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
function flash(?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}

/* ---------- Layout ---------- */
function page_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Tugas Rutin 7</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>"></head>
<body>
<div class="bg" aria-hidden="true">
  <span class="em e1">🔐</span><span class="em e2">🔑</span><span class="em e3">🛡️</span>
  <span class="em e4">📝</span><span class="em e5">🗄️</span><span class="em e6">🏠</span>
</div>
<div class="stage"><main class="card" id="tilt">
  <span class="badge">TUGAS RUTIN 7</span>
<?php }
function page_foot(): void { ?>
</main></div>
<script>
const c=document.getElementById('tilt'),s=c.parentElement;
if(!matchMedia('(prefers-reduced-motion: reduce)').matches){
  s.addEventListener('mousemove',e=>{const r=s.getBoundingClientRect(),
    x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
    c.style.transform=`rotateY(${x*6}deg) rotateX(${-y*6}deg)`;});
  s.addEventListener('mouseleave',()=>c.style.transform='');
}
</script>
</body></html>
<?php }
