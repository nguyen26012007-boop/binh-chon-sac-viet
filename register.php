<?php
session_start();
require_once __DIR__ . '/voting-library.php';
require_once __DIR__ . '/config.php'; // Gọi file cấu hình để lấy Token Telegram

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Tắt hiển thị lỗi để không làm hỏng JSON
error_reporting(0);
ini_set('display_errors', 0);

function registrationReply($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    registrationReply(405, ['success' => false, 'message' => 'Vui lòng sử dụng form đăng ký.']);
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || empty($_SESSION['vote_csrf']) || !hash_equals($_SESSION['vote_csrf'], $token)) {
    registrationReply(403, ['success' => false, 'message' => 'Phiên đã hết hạn. Vui lòng tải lại trang.']);
}

$fullname = is_string($_POST['fullname'] ?? null) ? trim($_POST['fullname']) : '';
$phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';

if ($fullname === '' || mb_strlen($fullname) > 100 || !preg_match('/^[0-9]{10,11}$/D', $phone)) {
    registrationReply(422, ['success' => false, 'message' => 'Vui lòng nhập họ tên và số điện thoại gồm 10–11 chữ số.']);
}

try {
    $key = browserVoterKey();
    $votes = updateVotingData(function (&$votes) use ($key, $fullname, $phone) {
        if (!empty($votes['users'][$key]['blocked'])) throw new RuntimeException('Hồ sơ của bạn đã bị khóa bình chọn.', 403);
        $votes['users'][$key] = array_merge($votes['users'][$key] ?? [], [
            'fullname' => $fullname,
            'phone' => $phone,
            'created_at' => $votes['users'][$key]['created_at'] ?? date('c'),
            'blocked' => false,
        ]);
    });

    // Gửi thông báo Telegram
    if (defined('TELEGRAM_BOT_TOKEN') && defined('TELEGRAM_CHAT_ID')) {
        $telegramText = "🔔 *ĐĂNG KÝ BÌNH CHỌN MỚI*\n"
            . "Họ tên: {$fullname}\n"
            . "SĐT: {$phone}\n"
            . "Thời gian: " . date('d/m/Y H:i:s');

        $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => http_build_query([
                'chat_id' => TELEGRAM_CHAT_ID,
                'text' => $telegramText,
                'parse_mode' => 'Markdown'
            ])
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    registrationReply(200, array_merge(['success' => true, 'message' => 'Đăng ký thành công.'], viewerState($votes, $key)));
} catch (Throwable $e) {
    $status = $e->getCode() === 403 ? 403 : 500;
    registrationReply($status, ['success' => false, 'message' => $status === 403 ? $e->getMessage() : 'Chưa thể lưu đăng ký. Vui lòng thử lại.']);
}
?>