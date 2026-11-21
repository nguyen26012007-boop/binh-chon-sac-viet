<?php
$defaults = [
    'contest_name' => 'Sắc Việt',
    'contest_edition' => 'BEAUTY & INSPIRATION',
    'hero_title' => 'Tỏa sáng bản lĩnh.<br>Chạm đến vương miện.',
    'hero_description' => 'Mỗi vẻ đẹp là một câu chuyện. Hãy trao sự yêu mến cho gương mặt truyền cảm hứng và cùng lan tỏa vẻ đẹp Việt.',
    'voting_open' => '1',
    'voting_note' => 'Đăng ký họ tên và số điện thoại trước khi bình chọn. Bạn có thể bình chọn miễn phí nhiều lần cho cùng một thí sinh hoặc các thí sinh khác. Mỗi mã tham gia hợp lệ do quản trị viên cấp được dùng cho một lượt bình chọn.',
    'source_note' => 'Hồ sơ tham khảo từ Hoa hậu Việt Nam 2024 (đã kết thúc). Đây là bình chọn cộng đồng độc lập; kết quả không liên quan đến cuộc thi gốc.'
];
$dialogFields = [
    'dialog_eyebrow' => ['Dòng giới thiệu', 'MỘT LỰA CHỌN TỪ TRÁI TIM'],
    'dialog_title' => ['Tiêu đề bảng', 'Gửi sự yêu mến của bạn.'],
    'dialog_description' => ['Mô tả phía dưới tiêu đề', 'Bạn đang bình chọn cho thí sinh dưới đây. Vui lòng kiểm tra trước khi xác nhận.'],
    'dialog_number_prefix' => ['Chữ trước số báo danh', 'SBD'],
    'dialog_name_label' => ['Nhãn ô họ tên', 'Họ và tên'],
    'dialog_name_placeholder' => ['Gợi ý trong ô họ tên', 'Nhập họ tên của bạn'],
    'dialog_phone_label' => ['Nhãn ô số điện thoại', 'Số điện thoại'],
    'dialog_phone_placeholder' => ['Gợi ý trong ô số điện thoại', 'Nhập 10–11 chữ số'],
    'dialog_register_button' => ['Nút đăng ký', 'Đăng ký để bình chọn'],
    'dialog_register_busy' => ['Nút khi đang xử lý đăng ký', 'Đang đăng ký…'],
    'dialog_privacy' => ['Ghi chú cuối bảng', 'Thông tin đăng ký được dùng để quản lý lượt bình chọn trên website này.'],
    'dialog_registered_prefix' => ['Chữ trước tên người đã đăng ký', 'Đã đăng ký:'],
    'dialog_code_title' => ['Tiêu đề phần nhập mã', 'Mã tham gia do quản trị viên cấp'],
    'dialog_code_help' => ['Hướng dẫn nhập mã', 'Nhập mã 6 chữ số của chương trình, được cấp riêng cho số điện thoại đã đăng ký. Mỗi mã dùng cho một lượt bình chọn.'],
    'dialog_confirm_button' => ['Nút xác nhận bình chọn', 'Xác nhận bình chọn'],
    'dialog_code_busy' => ['Nút khi đang kiểm tra mã', 'Đang kiểm tra mã tham gia…'],
    'dialog_repeat_button' => ['Nút bình chọn tiếp', 'Bình chọn bằng mã mới'],
    'dialog_success' => ['Thông báo bình chọn thành công', 'Đã ghi nhận bình chọn. Cảm ơn bạn!'],
    'dialog_code_required' => ['Thông báo chưa nhập đủ mã', 'Vui lòng nhập đủ 6 chữ số của mã tham gia do quản trị viên cấp.'],
    'dialog_code_invalid' => ['Thông báo mã sai, đã dùng hoặc hết hạn', 'Mã tham gia không hợp lệ, đã dùng hoặc hết hạn. Vui lòng kiểm tra và nhập lại mã do quản trị viên cấp.'],
    'dialog_retry_title' => ['Tiêu đề bảng nhắc nhập lại mã', 'Có lẽ bạn đã nhập sai.'],
    'dialog_retry_description' => ['Nội dung bảng nhắc nhập lại mã', 'Vui lòng nhập mã mà admin đã gửi.'],
    'dialog_retry_code_title' => ['Tiêu đề phần nhập mã ở bảng 2', 'Mã tham gia do quản trị viên cấp'],
    'dialog_retry_code_help' => ['Hướng dẫn nhập mã ở bảng 2', 'Nhập mã 6 chữ số của chương trình, được cấp riêng cho số điện thoại đã đăng ký. Mỗi mã dùng cho một lượt bình chọn.'],
    'dialog_retry_confirm_button' => ['Nút xác nhận ở bảng 2', 'Xác nhận bình chọn'],
    'dialog_retry_code_busy' => ['Nút khi kiểm tra mã ở bảng 2', 'Đang kiểm tra mã tham gia…'],
    'dialog_retry_privacy' => ['Ghi chú cuối bảng 2', 'Thông tin đăng ký được dùng để quản lý lượt bình chọn trên website này.'],
    'dialog_retry_code_invalid' => ['Thông báo mã không hợp lệ ở bảng 2', 'Mã tham gia không hợp lệ, đã dùng hoặc hết hạn. Vui lòng kiểm tra và nhập lại mã do quản trị viên cấp.'],
    'dialog_retry_code_required' => ['Thông báo chưa nhập đủ mã ở bảng 2', 'Vui lòng nhập đủ 6 chữ số của mã tham gia do quản trị viên cấp.'],
    'dialog_retry_code_limit' => ['Thông báo nhập sai quá nhiều lần ở bảng 2', 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 10 phút.'],
    'dialog_retry_close_label' => ['Nhãn trợ năng của nút đóng bảng 2', 'Đóng bình chọn'],
    'dialog_code_limit' => ['Thông báo nhập sai quá nhiều lần', 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 10 phút.'],
    'dialog_close_label' => ['Nhãn trợ năng của nút đóng', 'Đóng bình chọn'],
];
foreach ($dialogFields as $key => $field) $defaults[$key] = $field[1];
$dialogGroups = [
    'Nội dung chung' => ['dialog_eyebrow', 'dialog_title', 'dialog_description', 'dialog_number_prefix', 'dialog_privacy', 'dialog_close_label'],
    'Bước 1 · Họ tên và số điện thoại' => ['dialog_name_label', 'dialog_name_placeholder', 'dialog_phone_label', 'dialog_phone_placeholder', 'dialog_register_button', 'dialog_register_busy', 'dialog_registered_prefix'],
    'Bước 2 · Mã tham gia' => ['dialog_code_title', 'dialog_code_help', 'dialog_confirm_button', 'dialog_code_busy', 'dialog_repeat_button'],
    'Bảng 2 · Nhập lại mã' => ['dialog_retry_title', 'dialog_retry_description', 'dialog_retry_code_title', 'dialog_retry_code_help', 'dialog_retry_confirm_button', 'dialog_retry_code_busy', 'dialog_retry_privacy', 'dialog_retry_code_invalid', 'dialog_retry_code_required', 'dialog_retry_code_limit', 'dialog_retry_close_label'],
    'Thông báo kết quả' => ['dialog_success', 'dialog_code_required', 'dialog_code_invalid', 'dialog_code_limit'],
];
$sourceUrl = 'https://vtv.vn/chung-ket-hoa-hau-viet-nam-2024-25-nguoi-dep-tranh-vuong-mien-100250627202954559.htm';
$sampleCandidates = [
    ['687', 'Hà Trúc Linh', 'Phú Yên'],
    ['220', 'Trần Ngọc Châu Anh', 'Hà Nội'],
    ['136', 'Nguyễn Thị Vân Nhi', 'Hải Phòng'],
    ['870', 'Hồ Ngọc Phương Linh', 'Hà Tĩnh'],
    ['983', 'Phạm Thùy Dương', 'Hà Nội']
];
foreach ($sampleCandidates as $index => $candidate) {
    $i = $index + 1;
    $defaults['candidate_' . $i . '_number'] = $candidate[0];
    $defaults['candidate_' . $i . '_name'] = $candidate[1];
    $defaults['candidate_' . $i . '_hometown'] = $candidate[2];
    $defaults['candidate_' . $i . '_image'] = 'assets/contestant-' . $candidate[0] . '.jpg';
    foreach (['height', 'weight', 'age', 'note'] as $field) $defaults['candidate_' . $i . '_' . $field] = '';
}
$dataFile = __DIR__ . '/site_data.json';
$stored = is_file($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$data = array_replace($defaults, is_array($stored) ? $stored : []);
foreach (['code_title', 'code_help', 'confirm_button', 'code_busy', 'privacy', 'code_invalid', 'code_required', 'code_limit', 'close_label'] as $suffix) {
    $key = 'dialog_retry_' . $suffix;
    if (!is_array($stored) || !array_key_exists($key, $stored)) $data[$key] = $data['dialog_' . $suffix];
}
$candidates = [];
for ($i = 1; $i <= count($sampleCandidates); $i++) {
    $candidates[] = ['id' => (string) $i, 'number' => $data['candidate_' . $i . '_number'], 'name' => $data['candidate_' . $i . '_name'], 'hometown' => $data['candidate_' . $i . '_hometown'], 'image' => $data['candidate_' . $i . '_image'], 'height' => $data['candidate_' . $i . '_' . 'height'], 'weight' => $data['candidate_' . $i . '_' . 'weight'], 'age' => $data['candidate_' . $i . '_' . 'age'], 'note' => $data['candidate_' . $i . '_' . 'note']];
}
function esc($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function lines($value) {
    return nl2br(esc(preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $value)));
}
