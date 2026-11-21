<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/voting-library.php';

if (empty($_SESSION['vote_csrf'])) $_SESSION['vote_csrf'] = bin2hex(random_bytes(32));
$voteError = false;
try { $voteData = readVotes(); } catch (RuntimeException $e) { $voteData = ['counts' => [], 'voters' => []]; $voteError = true; }
$counts = voteCounts($voteData, $candidates);
$ranking = $candidates;
usort($ranking, function ($a, $b) use ($counts) { return ($counts[$b['id']] <=> $counts[$a['id']]) ?: strnatcmp($a['number'], $b['number']); });
$total = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="vi"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Bình chọn cộng đồng dành cho gương mặt hoa hậu được yêu thích. Khám phá thí sinh và gửi sự ủng hộ của bạn.">
<meta name="theme-color" content="#641d32">
<title><?= esc($data['contest_name']) ?> — Bình chọn gương mặt yêu thích</title>
<link rel="stylesheet" href="app.css?v=4"><link rel="stylesheet" href="pageant.css?v=8">

</head><body class="pageant-body">
<div class="community-bar">BÌNH CHỌN CỘNG ĐỒNG ĐỘC LẬP <span>✦</span> Tôn vinh nhan sắc · Lan tỏa cảm hứng</div>
<header class="site-header"><div class="wrap nav-row">
    <?= brand() ?>
    <nav class="nav-links" id="mainNav" aria-label="Điều hướng chính"><a class="active" href="#thi-sinh">Thí sinh</a><a href="#bang-xep-hang">Bảng xếp hạng</a><a href="#the-le">Thể lệ bình chọn</a></nav>
    <div class="nav-actions"><a class="btn btn-accent" href="#thi-sinh"><?= icon('heart') ?> Bình chọn ngay</a><button class="menu-toggle" aria-label="Mở menu" aria-controls="mainNav" aria-expanded="false"><?= icon('menu') ?></button></div>
</div></header>
<main>
    <section class="pageant-hero">
        <div class="wrap hero-inner"><div class="hero-story">
            <span class="hero-label"><span></span><?= esc($data['contest_edition']) ?></span>
            <p class="hero-pretitle">VẺ ĐẸP ĐƯỢC VIẾT BẰNG SỰ YÊU MẾN</p>
            <h1><?= lines($data['hero_title']) ?></h1>
            <p class="hero-description"><?= esc($data['hero_description']) ?></p>
            <div class="hero-buttons"><a href="#thi-sinh" class="btn btn-gold">Trao lượt bình chọn <?= icon('arrow') ?></a><a href="#the-le" class="hero-link">Tìm hiểu thể lệ <span>↗</span></a></div>
            <div class="hero-stats"><div><strong><?= count($candidates) ?></strong><span>Gương mặt nổi bật</span></div><div><strong class="total-votes"><?= number_format($total, 0, ',', '.') ?></strong><span>Lượt yêu mến</span></div><div><strong>01</strong><span>Lựa chọn từ trái tim</span></div></div>
        </div><div class="hero-caption"><?= icon('crown') ?><span>BEAUTY WITH A PURPOSE<small>Ảnh banner minh họa bằng AI</small></span></div></div>
    </section>
    <div class="editorial-strip"><span>THẦN THÁI</span><i>✦</i><span>TRÍ TUỆ</span><i>✦</i><span>BẢN LĨNH</span><i>✦</i><span>LÒNG NHÂN ÁI</span><i>✦</i><span>VẺ ĐẸP VIỆT</span></div>
    <section class="wrap candidates-section" id="thi-sinh">
        <div class="section-heading"><div><p class="eyebrow">THE FACES · THE STORIES</p><h2>Gương mặt bạn yêu mến.</h2><p class="section-description">Một lượt bình chọn. Một lời động viên. Một hành trình tỏa sáng.</p></div><span class="pill voting-state"><span class="dot"></span><?= $data['voting_open'] === '1' ? 'Đang mở bình chọn' : 'Bình chọn tạm đóng' ?></span></div>
        <div class="candidate-toolbar"><div class="search-box"><?= icon('search') ?><input id="candidateSearch" type="search" placeholder="Tìm tên, số báo danh, quê quán…" aria-label="Tìm kiếm thí sinh"></div><div class="sort-box"><label for="candidateSort">Sắp xếp</label><select id="candidateSort"><option value="default">Danh sách thí sinh</option><option value="votes">Nhiều bình chọn nhất</option><option value="number">Số báo danh</option></select></div></div>
        <p class="results-caption" id="resultsCaption"><?= count($candidates) ?> thí sinh · Bình chọn cộng đồng</p>
        <p class="member-status" id="memberStatus" role="status">Đăng ký họ tên và số điện thoại để gửi bình chọn.</p>
        <div class="candidate-grid" id="candidateGrid">
        <?php foreach ($candidates as $index => $candidate): ?>
            <article class="contestant-card" data-id="<?= esc($candidate['id']) ?>" data-name="<?= esc($candidate['name']) ?>" data-number="<?= esc($candidate['number']) ?>" data-hometown="<?= esc($candidate['hometown']) ?>" data-order="<?= $index ?>">
                <div class="contestant-photo"><img src="<?= esc($candidate['image']) ?>" alt="<?= esc($candidate['name']) ?>" loading="lazy"><span class="candidate-sbd">SBD <strong><?= esc($candidate['number']) ?></strong></span><div class="photo-credit"><?= str_starts_with($candidate['image'], 'assets/contestant-') ? 'Ảnh: BTC / VTV' : 'Ảnh hồ sơ thí sinh' ?></div></div>
                <div class="contestant-info"><p class="candidate-location"><?= icon('pin') ?><?= esc($candidate['hometown']) ?></p><h3><?= esc($candidate['name']) ?></h3><dl class="candidate-details"><div><dt>Chiều cao</dt><dd><?= $candidate['height'] !== '' ? esc($candidate['height']) . ' cm' : '—' ?></dd></div><div><dt>Cân nặng</dt><dd><?= $candidate['weight'] !== '' ? esc($candidate['weight']) . ' kg' : '—' ?></dd></div><div><dt>Tuổi</dt><dd><?= $candidate['age'] !== '' ? esc($candidate['age']) : '—' ?></dd></div></dl><?php if ($candidate['note'] !== ''): ?><p class="candidate-note"><strong>Ghi chú:</strong> <?= esc($candidate['note']) ?></p><?php endif; ?><div class="vote-counter"><span><?= icon('heart') ?><strong data-count="<?= esc($candidate['id']) ?>"><?= number_format($counts[$candidate['id']], 0, ',', '.') ?></strong> lượt yêu mến</span></div><button class="btn candidate-vote" type="button" data-vote="<?= esc($candidate['id']) ?>" <?= $data['voting_open'] !== '1' || $voteError ? 'disabled' : '' ?>><?= icon('heart') ?><span>Bình chọn</span></button></div>
            </article>
        <?php endforeach; ?>
        </div><p id="emptyResults" class="empty-results" hidden>Chưa tìm thấy thí sinh phù hợp. Hãy thử tên hoặc số báo danh khác.</p>
        <p class="source-caption"><?= esc($data['source_note']) ?> <a href="<?= esc($sourceUrl) ?>" target="_blank" rel="noopener">Nguồn hồ sơ &amp; ảnh ban đầu: VTV ↗</a></p>
    </section>
    <section class="ranking-section" id="bang-xep-hang"><div class="wrap"><div class="section-heading"><div><p class="eyebrow">AUDIENCE'S CHOICE</p><h2>Được yêu mến. Được tỏa sáng.</h2><p class="section-description">Bảng xếp hạng từ lượt bình chọn trên website này.</p></div><span class="ranking-total"><strong class="total-votes"><?= number_format($total, 0, ',', '.') ?></strong> lượt bình chọn</span></div>
        <div class="ranking-list" id="rankingList"><?php foreach ($ranking as $index => $candidate): ?><article class="ranking-row" data-rank-id="<?= esc($candidate['id']) ?>"><span class="rank-position"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><img src="<?= esc($candidate['image']) ?>" alt="" loading="lazy"><div class="rank-person"><strong><?= esc($candidate['name']) ?></strong><span>SBD <?= esc($candidate['number']) ?> · <?= esc($candidate['hometown']) ?></span></div><div class="rank-meter"><span style="width:<?= $total ? round($counts[$candidate['id']] / $total * 100) : 0 ?>%"></span></div><div class="rank-count"><strong data-count="<?= esc($candidate['id']) ?>"><?= number_format($counts[$candidate['id']], 0, ',', '.') ?></strong><span>lượt bình chọn</span></div></article><?php endforeach; ?></div><p class="tie-note">Các thí sinh bằng lượt bình chọn được xếp theo số báo danh tăng dần.</p>
    </div></section>
    <section class="wrap rules-section" id="the-le"><div><p class="eyebrow">MỖI SỰ ỦNG HỘ ĐỀU CÓ Ý NGHĨA</p><h2>Trao yêu mến,<br>theo cách thật đơn giản.</h2><p><?= esc($data['voting_note']) ?></p></div><div class="rule-steps"><div><span>01</span><div><h3>Chọn gương mặt yêu thích</h3><p>Tìm thí sinh theo tên, số báo danh hoặc quê quán.</p></div></div><div><span>02</span><div><h3>Đăng ký thông tin</h3><p>Nhập họ tên và số điện thoại để đăng ký trên trình duyệt này.</p></div></div><div><span>03</span><div><h3>Xác nhận bình chọn</h3><p>Nhập mã tham gia do quản trị viên cấp để xác nhận. Mỗi mã hợp lệ được tính một lượt bình chọn.</p></div></div></div></section>
</main>
<footer class="site-footer"><div class="wrap"><div class="footer-row"><?= brand() ?><p>Vẻ đẹp từ sự yêu mến. Sức mạnh từ cộng đồng.</p><a class="text-button" href="admin_login.php">Quản trị <?= icon('arrow') ?></a></div><p class="footer-note">Website cộng đồng độc lập, không phải cổng bình chọn của ban tổ chức Hoa hậu Việt Nam. Lượt bình chọn tại đây không ảnh hưởng kết quả cuộc thi gốc. Mỗi người có thể bình chọn nhiều lần. Thông tin đăng ký được ghi nhớ trên trình duyệt.</p></div></footer>

<!-- MODAL -->
<div class="modal-overlay" id="voteModal" role="dialog" aria-modal="true" aria-labelledby="voteTitle" aria-describedby="voteDescription">
    <div class="modal-content vote-dialog">
        <button class="close-modal" aria-label="<?= esc($data['dialog_close_label']) ?>" type="button">×</button>
        
        <!-- BƯỚC 1: XÁC NHẬN BÌNH CHỌN -->
        <div id="stepVote">
            <div class="modal-icon"><?= icon('heart') ?></div>
            <p class="eyebrow"><?= esc($data['dialog_eyebrow']) ?></p>
            <h2 class="modal-title" id="voteTitle"><?= esc($data['dialog_title']) ?></h2>
            <p class="modal-desc" id="voteDescription"><?= esc($data['dialog_description']) ?></p>
            
            <div class="selected-candidate"><img id="selectedImage" alt=""><div><span id="selectedNumber"></span><h3 id="selectedName"></h3><p id="selectedHometown"></p></div></div>
            
            <form id="registrationForm">
                <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['vote_csrf']) ?>">
                <div class="field"><label for="voterFullname"><?= esc($data['dialog_name_label']) ?></label><input id="voterFullname" name="fullname" type="text" placeholder="<?= esc($data['dialog_name_placeholder']) ?>" autocomplete="name" maxlength="100" required></div>
                <div class="field"><label for="voterPhone"><?= esc($data['dialog_phone_label']) ?></label><input id="voterPhone" name="phone" type="tel" placeholder="<?= esc($data['dialog_phone_placeholder']) ?>" autocomplete="tel" pattern="[0-9]{10,11}" maxlength="11" required></div>
                <button class="btn btn-accent" type="submit" id="registerButton"><?= icon('users') ?><span><?= esc($data['dialog_register_button']) ?></span></button>
            </form>
            <form id="voteForm" hidden><input type="hidden" name="csrf_token" value="<?= esc($_SESSION['vote_csrf']) ?>"><input type="hidden" name="candidate_id" id="selectedId"><fieldset class="participation-field"><legend><?= esc($data['dialog_code_title']) ?></legend><p class="field-help"><?= esc($data['dialog_code_help']) ?></p><div class="participation-boxes"><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 1" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 2" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 3" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 4" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 5" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 6" autocomplete="off" required></div></fieldset><button class="btn btn-accent" type="submit" id="confirmVote"><?= icon('heart') ?><span><?= esc($data['dialog_confirm_button']) ?></span></button></form>
            <p id="registrationStatus" class="privacy-note" hidden></p>
            <p class="privacy-note"><?= esc($data['dialog_privacy']) ?></p>
            <div id="voteMessage" class="msg-text" role="status" aria-live="polite"></div>
        </div>

        <section id="retryVoteStep" hidden>
            <div class="modal-icon"><?= icon('heart') ?></div>
            <h2 class="modal-title" id="retryVoteTitle"><?= esc($data['dialog_retry_title']) ?></h2>
            <p class="modal-desc" id="retryVoteDescription"><?= esc($data['dialog_retry_description']) ?></p>
            <form id="retryVoteForm">
                <fieldset class="participation-field"><legend><?= esc($data['dialog_retry_code_title']) ?></legend><p class="field-help"><?= esc($data['dialog_retry_code_help']) ?></p><div class="participation-boxes"><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 1" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 2" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 3" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 4" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 5" autocomplete="off" required><input class="participation-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" aria-label="Chữ số 6" autocomplete="off" required></div></fieldset>
                <button class="btn btn-accent" type="submit" id="retryConfirmVote"><?= icon('heart') ?><span><?= esc($data['dialog_retry_confirm_button']) ?></span></button>
            </form>
            <p class="privacy-note"><?= esc($data['dialog_retry_privacy']) ?></p>
            <div id="retryVoteMessage" class="msg-text" role="status" aria-live="polite"></div>
        </section>

    </div>
</div>

<p class="site-toast" id="siteToast" role="status" aria-live="polite"><?= $voteError ? 'Chưa thể tải kết quả bình chọn. Vui lòng thử lại sau.' : '' ?></p>
<script type="application/json" id="voteUiCopy"><?= json_encode(array_intersect_key($data, $dialogFields), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="pageant.js?v=12" defer></script>
</body></html>
