<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}
header('Cache-Control: no-store');
require_once __DIR__ . '/site-content.php';
require_once __DIR__ . '/voting-library.php';
require_once __DIR__ . '/icons.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = null;
$message = $_SESSION['users_message'] ?? null;
unset($_SESSION['users_message']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        $error = 'Phiên quản trị không hợp lệ. Vui lòng tải lại trang.';
    } else {
        $action = $_POST['action'] ?? '';
        $userId = $_POST['user_id'] ?? '';
        $fullname = is_string($_POST['fullname'] ?? null) ? trim($_POST['fullname']) : '';
        $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
        if (!is_string($action) || !in_array($action, ['edit', 'toggle', 'delete'], true) || !is_string($userId) || !preg_match('/^[a-f0-9]{64}$/D', $userId)) {
            $error = 'Thao tác hoặc người dùng không hợp lệ.';
        } elseif ($action === 'edit' && ($fullname === '' || mb_strlen($fullname) > 100 || preg_match('/[\x00-\x1f\x7f]/u', $fullname) || !preg_match('/^[0-9]{10,11}$/D', $phone))) {
            $error = 'Vui lòng nhập họ tên và số điện thoại gồm 10–11 chữ số.';
        } else {
            try {
                updateVotingData(function (&$votes) use ($action, $userId, $fullname, $phone) {
                    if (!isset($votes['users'][$userId])) throw new RuntimeException('Hồ sơ không còn tồn tại.', 404);
                    if ($action === 'delete') {
                        unset($votes['users'][$userId]);
                    } elseif ($action === 'toggle') {
                        $votes['users'][$userId]['blocked'] = empty($votes['users'][$userId]['blocked']);
                    } else {
                        $votes['users'][$userId]['fullname'] = $fullname;
                        $votes['users'][$userId]['phone'] = $phone;
                    }
                });
                $_SESSION['users_message'] = $action === 'delete' ? 'Đã xóa hồ sơ người dùng. Lượt bình chọn đã ghi nhận được giữ nguyên.' : ($action === 'toggle' ? 'Đã cập nhật quyền bình chọn của người dùng.' : 'Đã lưu thông tin người dùng.');
                header('Location: admin_users.php');
                exit;
            } catch (RuntimeException $exception) {
                $error = $exception->getMessage();
            }
        }
    }
}
try {
    $voteData = readVotes();
} catch (RuntimeException $exception) {
    $voteData = ['counts' => [], 'voters' => [], 'users' => []];
    $error = 'Chưa thể tải danh sách người dùng. Vui lòng thử lại.';
}
$users = $voteData['users'];
$allCount = count($users);
$blockedCount = count(array_filter($users, function ($user) { return !empty($user['blocked']); }));
$votedCount = count(array_intersect_key($users, $voteData['voters']));
$query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
if ($query !== '') {
    $users = array_filter($users, function ($user) use ($query) { return mb_stripos($user['fullname'] . ' ' . $user['phone'], $query) !== false; });
}
uasort($users, function ($a, $b) { return strcmp($b['created_at'], $a['created_at']); });
$filteredCount = count($users);
$pageCount = max(1, (int) ceil($filteredCount / 20));
$page = max(1, min($pageCount, (int) ($_GET['page'] ?? 1)));
$users = array_slice($users, ($page - 1) * 20, 20, true);
$candidateMap = array_column($candidates, null, 'id');
function userDate($date) {
    try { return (new DateTimeImmutable($date))->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))->format('d/m/Y H:i'); }
    catch (Exception $exception) { return '—'; }
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Quản lý người dùng — <?= esc($data['contest_name']) ?></title><link rel="stylesheet" href="app.css?v=4"><link rel="stylesheet" href="pageant.css?v=4"></head><body class="admin-body">
<aside class="admin-sidebar"><?= brand() ?><p class="sidebar-label">KHÔNG GIAN QUẢN TRỊ</p><nav class="sidebar-nav" aria-label="Quản trị"><a href="admin.php#cuoc-thi"><?= icon('crown') ?> Thông tin cuộc thi</a><a href="admin.php#danh-sach"><?= icon('users') ?> Hồ sơ thí sinh</a><a href="admin_codes.php">Mã tham gia</a><a class="active" href="admin_users.php" aria-current="page"><?= icon('users') ?> Người đăng ký</a><a href="admin.php#ket-qua"><?= icon('target') ?> Kết quả bình chọn</a><a href="admin.php#cai-dat"><?= icon('layout') ?> Cài đặt bình chọn</a></nav><div class="sidebar-bottom"><div class="admin-profile"><span class="avatar">AD</span><div><strong>Quản trị viên</strong><small>Pageant Voting Workspace</small></div></div><nav class="sidebar-nav"><a href="admin.php?logout=1"><?= icon('logout') ?> Đăng xuất</a></nav></div></aside>
<div class="admin-main"><header class="admin-topbar"><div class="breadcrumb">Workspace <span>/</span> <strong>Người đăng ký</strong></div><a class="btn btn-light" href="index.php" target="_blank" rel="noopener"><?= icon('eye') ?> Xem trang web</a></header>
<main class="admin-content"><div class="admin-page-heading"><div><h1>Kết nối từ những lượt yêu mến.</h1><p>Quản lý họ tên, số điện thoại và quyền bình chọn của người đăng ký.</p></div><a class="btn btn-light" href="admin.php"><?= icon('back') ?> Quản lý cuộc thi</a></div>
<div class="admin-stats"><div class="admin-stat"><div><p>Người đăng ký</p><strong><?= $allCount ?></strong></div><span class="feature-icon"><?= icon('users') ?></span></div><div class="admin-stat"><div><p>Đã bình chọn</p><strong><?= $votedCount ?></strong></div><span class="feature-icon"><?= icon('heart') ?></span></div><div class="admin-stat"><div><p>Đang bị khóa</p><strong><?= $blockedCount ?></strong></div><span class="feature-icon"><?= icon('lock') ?></span></div></div>
<?php if ($message): ?><div class="notice" role="status"><?= esc($message) ?></div><?php endif; ?><?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif; ?>
<section class="panel"><div class="panel-header"><span class="feature-icon"><?= icon('users') ?></span><div><h2>Danh sách người đăng ký</h2><p><?= $filteredCount ?> hồ sơ<?= $query !== '' ? ' phù hợp với tìm kiếm' : '' ?></p></div></div><div class="panel-body">
<form method="GET" class="users-search"><div class="search-box"><?= icon('search') ?><input name="q" type="search" value="<?= esc($query) ?>" placeholder="Tìm theo họ tên hoặc số điện thoại…" aria-label="Tìm người dùng" maxlength="100"></div><button type="submit" class="btn">Tìm kiếm</button><?php if ($query !== ''): ?><a class="text-button" href="admin_users.php">Xóa lọc</a><?php endif; ?></form>
<div class="users-list">
<?php foreach ($users as $userId => $user): $votedId = $voteData['voters'][$userId] ?? null; $votedCandidate = $candidateMap[$votedId] ?? null; ?>
<article class="user-card" data-user-id="<?= esc($userId) ?>"><div class="user-card-heading"><span class="avatar"><?= esc(mb_substr($user['fullname'], 0, 1)) ?></span><div><h3><?= esc($user['fullname']) ?></h3><p class="user-phone"><?= esc($user['phone']) ?></p></div><span class="pill <?= !empty($user['blocked']) ? 'user-blocked' : '' ?>"><?= !empty($user['blocked']) ? 'Đang khóa' : 'Hoạt động' ?></span></div>
<div class="user-meta"><span>Đăng ký: <strong><?= esc(userDate($user['created_at'])) ?></strong></span><span>Bình chọn gần nhất: <strong><?= $votedCandidate ? esc($votedCandidate['name'] . ' · SBD ' . $votedCandidate['number']) : ($votedId ? 'Thí sinh không còn hiển thị' : 'Chưa bình chọn') ?></strong></span></div>
<div class="user-actions"><details class="user-edit"><summary>Sửa thông tin</summary><form method="POST" class="user-edit-form"><input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>"><input type="hidden" name="user_id" value="<?= esc($userId) ?>"><input type="hidden" name="action" value="edit"><div class="field"><label for="name_<?= esc($userId) ?>">Họ và tên</label><input id="name_<?= esc($userId) ?>" name="fullname" value="<?= esc($user['fullname']) ?>" maxlength="100" required></div><div class="field"><label for="phone_<?= esc($userId) ?>">Số điện thoại</label><input id="phone_<?= esc($userId) ?>" name="phone" type="tel" pattern="[0-9]{10,11}" maxlength="11" value="<?= esc($user['phone']) ?>" required></div><button class="btn" type="submit">Lưu thông tin</button></form></details>
<form method="POST"><input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>"><input type="hidden" name="user_id" value="<?= esc($userId) ?>"><button class="btn btn-light" name="action" value="toggle" type="submit"><?= !empty($user['blocked']) ? 'Mở khóa bình chọn' : 'Khóa bình chọn' ?></button></form>
<form method="POST" data-confirm-delete><input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>"><input type="hidden" name="user_id" value="<?= esc($userId) ?>"><button class="btn btn-light danger-button" name="action" value="delete" type="submit">Xóa hồ sơ</button></form></div>
</article>
<?php endforeach; ?>
<?php if (!$users): ?><p class="empty-results"><?= $query !== '' ? 'Không tìm thấy hồ sơ phù hợp.' : 'Chưa có người đăng ký. Hồ sơ sẽ xuất hiện khi người dùng hoàn tất đăng ký trước khi bình chọn.' ?></p><?php endif; ?>
</div>
<?php if ($pageCount > 1): ?><nav class="users-pagination" aria-label="Phân trang"><?php if ($page > 1): ?><a class="btn btn-light" href="?<?= esc(http_build_query(['q' => $query, 'page' => $page - 1])) ?>">Trang trước</a><?php endif; ?><span>Trang <?= $page ?> / <?= $pageCount ?></span><?php if ($page < $pageCount): ?><a class="btn btn-light" href="?<?= esc(http_build_query(['q' => $query, 'page' => $page + 1])) ?>">Trang sau</a><?php endif; ?></nav><?php endif; ?>
<p class="field-help" style="margin-top:20px">Thông tin đăng ký do người tham gia tự khai. Khóa hồ sơ ngăn bình chọn mới; xóa hồ sơ không xóa lượt đã ghi nhận. Giới hạn người tham gia dựa trên trình duyệt.</p>
</div></section></main></div><script>
document.querySelectorAll('[data-confirm-delete]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm('Xóa họ tên và số điện thoại của hồ sơ này? Lượt bình chọn đã ghi nhận vẫn được giữ nguyên.')) event.preventDefault();
}));
</script></body></html>
