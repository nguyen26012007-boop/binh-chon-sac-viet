<?php
function votesPath() { return dirname(__DIR__, 2) . '/ielts-zalo-data/pageant-votes.json'; }
function readVotes() {
    $path = votesPath();
    if (!is_file($path)) return ['counts' => [], 'voters' => [], 'users' => []];
    $handle = @fopen($path, 'rb');
    if (!$handle || !flock($handle, LOCK_SH)) throw new RuntimeException('Không thể đọc kết quả.');
    $content = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $votes = $content === '' ? ['counts' => [], 'voters' => []] : json_decode($content, true);
    if (!is_array($votes) || !isset($votes['counts'], $votes['voters'])) throw new RuntimeException('Dữ liệu bình chọn chưa sẵn sàng.');
    $votes['users'] = $votes['users'] ?? [];
    return $votes;
}
function voteCounts($votes, $candidates) {
    $counts = [];
    foreach ($candidates as $candidate) $counts[$candidate['id']] = (int) ($votes['counts'][$candidate['id']] ?? 0);
    return $counts;
}
function browserVoterKey() {
    $voter = $_COOKIE['pageant_voter'] ?? '';
    if (!is_string($voter) || !preg_match('/^[a-f0-9]{64}$/D', $voter)) {
        $voter = bin2hex(random_bytes(32));
        setcookie('pageant_voter', $voter, ['expires' => time() + 31536000, 'path' => '/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
        $_COOKIE['pageant_voter'] = $voter;
    }
    return hash('sha256', $voter);
}
function viewerState($votes, $key) {
    $user = $votes['users'][$key] ?? null;
    return ['registered' => $user !== null, 'blocked' => (bool) ($user['blocked'] ?? false), 'viewer_name' => $user['fullname'] ?? null];
}
function updateVotingData(callable $update) {
    $path = votesPath();
    $directory = dirname($path);
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) throw new RuntimeException('Chưa thể lưu dữ liệu. Vui lòng thử lại.', 500);
    $handle = @fopen($path, 'c+');
    if (!$handle) throw new RuntimeException('Chưa thể lưu dữ liệu. Vui lòng thử lại.', 500);
    try {
        if (!flock($handle, LOCK_EX)) throw new RuntimeException('Chưa thể lưu dữ liệu. Vui lòng thử lại.', 500);
        $content = stream_get_contents($handle);
        $votes = $content === '' ? ['counts' => [], 'voters' => [], 'users' => []] : json_decode($content, true);
        if (!is_array($votes) || !isset($votes['counts'], $votes['voters'])) throw new RuntimeException('Chưa thể đọc dữ liệu. Vui lòng thử lại.', 500);
        $votes['users'] = $votes['users'] ?? [];
        $update($votes);
        $json = json_encode($votes, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        rewind($handle);
        if (fwrite($handle, $json) !== strlen($json) || !ftruncate($handle, strlen($json)) || !fflush($handle)) throw new RuntimeException('Chưa thể lưu dữ liệu. Vui lòng thử lại.', 500);
        return $votes;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
