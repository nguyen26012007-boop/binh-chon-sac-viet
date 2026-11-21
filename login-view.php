<?php require_once __DIR__ . '/icons.php'; ?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Đăng nhập quản trị — <?= esc($data['contest_name']) ?></title><link rel="stylesheet" href="app.css?v=4"><link rel="stylesheet" href="pageant.css?v=4"></head>
<body class="login-body">
    <section class="login-art"><?= brand() ?><div><span class="eyebrow">PAGEANT VOTING WORKSPACE</span><h1>Phía sau mỗi<br>hành trình tỏa sáng.</h1><p>Chăm chút hồ sơ thí sinh, kết nối sự yêu mến và lan tỏa vẻ đẹp từ cộng đồng.</p></div><footer><?= esc($data['contest_name']) ?> · Beauty, community &amp; inspiration.</footer></section>
    <main class="login-form-area"><div class="login-box"><div class="modal-icon"><?= icon('lock') ?></div><span class="eyebrow">CHÀO MỪNG TRỞ LẠI</span><h2>Không gian của bạn.</h2><p>Đăng nhập để quản lý thí sinh và bình chọn.</p>
        <?php if (isset($error)): ?><div class="notice error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="POST"><div class="field"><label for="password">Mật khẩu quản trị</label><div class="password-wrap"><input type="password" name="password" id="password" placeholder="Nhập mật khẩu của bạn" autocomplete="current-password" required><button type="button" class="password-toggle" aria-label="Hiện mật khẩu" aria-pressed="false"><?= icon('eye') ?></button></div></div><button class="btn" type="submit">Đăng nhập <?= icon('arrow') ?></button></form>
        <a class="back-link" href="index.php"><?= icon('back') ?> Trở về trang chủ</a>
    </div></main>
    <script>
    document.querySelector('.password-toggle').addEventListener('click', function () {
        const password = document.getElementById('password');
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        this.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        this.setAttribute('aria-pressed', String(show));
    });
    </script>
</body></html>
