<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
    flash('Anda berhasil logout.');
}
redirect('login.php');
