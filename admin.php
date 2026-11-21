<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/site-content.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        $error = 'Phiên chỉnh sửa không hợp lệ. Vui lòng tải lại trang.';
    } else {
        foreach ($defaults as $key => $default) {
            if (str_ends_with($key, '_image')) continue;
            if (!is_string($_POST[$key] ?? null) || strlen($_POST[$key]) > 6000) {
                $error = 'Nội dung không hợp lệ hoặc quá dài. Vui lòng kiểm tra lại.';
                break;
            }
            $data[$key] = trim($_POST[$key]);
            if (isset($dialogFields[$key]) && $data[$key] === '') {
                $error = 'Vui lòng nhập nội dung cho: ' . $dialogFields[$key][0] . '.';
                break;
            }
        }
        if (!$error && ($data['contest_name'] === '' || $data['hero_title'] === '' || !in_array($data['voting_open'], ['0', '1'], true))) {
            $error = 'Vui lòng nhập tên cuộc thi, thông điệp và chọn trạng thái bình chọn hợp lệ.';
        }
        $numbers = [];
        for ($i = 1; !$error && $i <= count($sampleCandidates); $i++) {
            $prefix = 'candidate_' . $i . '_';
            $number = $data[$prefix . 'number'];
            if ($number === '' || strlen($number) > 80 || $data[$prefix . 'name'] === '' || $data[$prefix . 'hometown'] === '') {
                $error = 'Mỗi thí sinh cần có số báo danh, họ tên và quê quán hợp lệ.';
            } elseif (in_array(mb_strtolower($number), $numbers, true)) {
                $error = 'Số báo danh của các thí sinh không được trùng nhau.';
            }
            $numbers[] = mb_strtolower($number);
            foreach (['height' => 300, 'weight' => 500, 'age' => 120] as $field => $maximum) {
                $value = $data[$prefix . $field];
                $pattern = $field === 'age' ? '/^[0-9]{1,3}$/D' : '/^[0-9]{1,3}(?:\.[0-9])?$/D';
                if ($value !== '' && (!preg_match($pattern, $value) || (float) $value <= 0 || (float) $value > $maximum)) {
                    $labels = ['height' => 'Chiều cao', 'weight' => 'Cân nặng', 'age' => 'Tuổi'];
                    $error = $labels[$field] . ' của thí sinh #' . $i . ' phải lớn hơn 0 và không quá ' . $maximum . '.';
                    break;
                }
            }
            if (mb_strlen($data[$prefix . 'note']) > 1000) $error = 'Ghi chú của thí sinh #' . $i . ' không được quá 1.000 ký tự.';
        }
        for ($i = 1; !$error && $i <= count($sampleCandidates); $i++) {
            $field = 'candidate_' . $i . '_image';
            $upload = $_FILES[$field] ?? null;
            if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) continue;
            if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 5 * 1024 * 1024) {
                $error = 'Không thể tải ảnh thí sinh #' . $i . '. Vui lòng chọn ảnh không quá 5 MB.';
                break;
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
            $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($types[$mime]) || !@getimagesize($upload['tmp_name'])) {
                $error = 'Ảnh thí sinh #' . $i . ' không hợp lệ. Chỉ hỗ trợ JPG, PNG và WebP.';
                break;
            }
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $error = 'Chưa thể tạo thư mục ảnh. Vui lòng thử lại.';
                break;
            }
            $relative = 'uploads/' . bin2hex(random_bytes(12)) . '.' . $types[$mime];
            if (!move_uploaded_file($upload['tmp_name'], __DIR__ . '/' . $relative)) {
                $error = 'Chưa thể lưu ảnh. Vui lòng thử lại.';
                break;
            }
            $data[$field] = $relative;
        }
        if (!$error) {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            if (@file_put_contents($dataFile, $json, LOCK_EX) === false) {
                $error = 'Chưa thể lưu nội dung. Vui lòng thử lại.';
            } else {
                $_SESSION['admin_message'] = 'Đã lưu thông tin cuộc thi, hồ sơ thí sinh và cài đặt bình chọn.';
                header('Location: admin.php');
                exit;
            }
        }
    }
}
$candidates = [];
for ($i = 1; $i <= count($sampleCandidates); $i++) {
    $prefix = 'candidate_' . $i . '_';
    $candidates[] = ['id' => (string) $i, 'number' => $data[$prefix . 'number'], 'name' => $data[$prefix . 'name'], 'hometown' => $data[$prefix . 'hometown'], 'image' => $data[$prefix . 'image'], 'height' => $data[$prefix . 'height'], 'weight' => $data[$prefix . 'weight'], 'age' => $data[$prefix . 'age'], 'note' => $data[$prefix . 'note']];
}
require __DIR__ . '/admin-view.php';
