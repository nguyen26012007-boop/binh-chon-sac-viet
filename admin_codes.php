<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/site-content.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/voting-library.php';
header('Cache-Control: no-store');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = null;
$issued = $_SESSION['issued_participation_code'] ?? null;
unset($_SESSION['issued_participation_code']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        $error = 'Phiên không hợp lệ. Vui lòng tải lại trang.';
    } elseif (!preg_match('/^[0-9]{10,11}$/D', $phone)) {
        $error = 'Nhập số điện thoại người nhận gồm 10–11 chữ số.';
    } else {
        try {
            updateVotingData(function (&$votes) use ($phone, &$issued) {
                $votes['participation_codes'] = $votes['participation_codes'] ?? [];
                do {
                    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $digest = hash('sha256', $code);
                } while (isset($votes['participation_codes'][$digest]));
                $expires = time() + 86400;
                $votes['participation_codes'][$digest] = ['phone' => $phone, 'created_at' => time(), 'expires_at' => $expires, 'used_at' => null];
                $issued = ['code' => $code, 'phone' => $phone, 'expires_at' => $expires];
            });
            $_SESSION['issued_participation_code'] = $issued;
            header('Location: admin_codes.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Chưa thể tạo mã. Vui lòng thử lại.';
        }
    }
}
try { $records = array_reverse(readVotes()['participation_codes'] ?? [], true); }
catch (Throwable $e) { $records = []; $error = 'Chưa thể đọc danh sách mã.'; }
?>
<!doctype html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mã tham gia — <?= esc($data['contest_name']) ?></title><link rel="stylesheet" href="app.css?v=4"><link rel="stylesheet" href="pageant.css?v=7"></head>
<body><main class="wrap" style="max-width:760px;padding-top:35px;padding-bottom:45px">
<a class="btn btn-light" href="admin.php">← Quản trị bình chọn</a>
<h1 style="margin:24px 0 12px">Mã tham gia</h1>
<p>Mỗi mã gồm 6 chữ số, dùng cho một lượt bình chọn của số điện thoại được chỉ định và hết hạn sau 24 giờ. Tạo mã mới khi người dùng muốn bình chọn tiếp.</p>
<?php if ($error): ?><p class="notice error" role="alert"><?= esc($error) ?></p><?php endif; ?>
<?php if ($issued): ?><section class="notice" role="status" style="margin-top:20px"><p>Mã dành cho <strong><?= esc($issued['phone']) ?></strong>:</p><p style="font-size:32px;letter-spacing:.2em;font-weight:700" id="issuedCode"><?= esc($issued['code']) ?></p><p>Mã chỉ hiển thị lần này. Sao chép và gửi riêng cho người nhận.</p></section><?php endif; ?>
<section class="panel" style="margin:24px 0"><div class="panel-body"><form method="POST" id="codeIssueForm"><input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>"><div class="field"><label for="recipientPhone">Số điện thoại người nhận</label><input id="recipientPhone" name="phone" type="tel" pattern="[0-9]{10,11}" maxlength="11" required autocomplete="off"></div><button class="btn" type="submit">Tạo mã tham gia</button></form></div></section>
<h2 style="margin-bottom:15px">Mã đã phát hành</h2>
<p class="field-help">Lịch sử chỉ hiển thị người nhận và trạng thái.</p>
<?php foreach (array_slice($records, 0, 50, true) as $record): ?><div class="ranking-row"><strong><?= esc($record['phone']) ?></strong><span><?= !empty($record['used_at']) ? 'Đã dùng' : ($record['expires_at'] <= time() ? 'Hết hạn' : 'Chưa dùng') ?></span></div><?php endforeach; ?>
<?php if (!$records): ?><p>Chưa có mã nào được tạo.</p><?php endif; ?>
</main></body></html>
