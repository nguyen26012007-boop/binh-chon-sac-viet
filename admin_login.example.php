<?php
session_start();
header('Cache-Control: no-store');
$passwordHash = getenv('ADMIN_PASSWORD_HASH') ?: '';
if ($passwordHash === '') {
    http_response_code(503);
    exit('Admin authentication is not configured.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? null;
    if (is_string($password) && password_verify($password, $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Thông tin đăng nhập không hợp lệ.';
}
require_once __DIR__ . '/site-content.php';
require __DIR__ . '/login-view.php';
