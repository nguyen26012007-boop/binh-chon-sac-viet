<?php
session_start();
$admin_pass = 'concac123'; // Đã đổi mật khẩu theo yêu cầu

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_pass) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Sai mật khẩu!";
    }
}

require_once __DIR__ . '/site-content.php';
require __DIR__ . '/login-view.php';
?>