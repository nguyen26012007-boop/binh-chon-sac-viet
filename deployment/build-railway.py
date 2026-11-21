from pathlib import Path
import shutil
import zipfile

workspace = Path(__file__).resolve().parent.parent
release = workspace / 'deployment' / 'railway'
web = release / 'web'
web.mkdir(parents=True, exist_ok=True)
names = [
    'index.php', 'home-view.php', 'site-content.php', 'icons.php',
    'voting-library.php', 'vote.php', 'register.php', 'admin.php',
    'admin-view.php', 'admin_users.php', 'admin_codes.php', 'login-view.php',
    'pageant.js', 'pageant.css', 'pageant-admin.js', 'app.css',
    'send_otp.php', 'verify_otp.php',
]
for name in names:
    shutil.copy2(workspace / name, web / name)
shutil.copytree(workspace / 'assets', web / 'assets', dirs_exist_ok=True)

path = web / 'voting-library.php'
source = path.read_text(encoding='utf-8').replace(
    "return dirname(__DIR__, 2) . '/ielts-zalo-data/pageant-votes.json';",
    "return rtrim(getenv('VOTING_DATA_DIR') ?: '/var/www/data', '/\\\\') . '/pageant-votes.json';"
)
path.write_text(source, encoding='utf-8')
path = web / 'site-content.php'
source = path.read_text(encoding='utf-8').replace(
    "$dataFile = __DIR__ . '/site_data.json';",
    "$dataFile = rtrim(getenv('VOTING_DATA_DIR') ?: '/var/www/data', '/\\\\') . '/site_data.json';"
)
path.write_text(source, encoding='utf-8')
(web / 'admin_login.php').write_text('''<?php
session_start();
header('Cache-Control: no-store');
$passwordHash = getenv('ADMIN_PASSWORD_HASH') ?: '';
if ($passwordHash === '') {
    http_response_code(503);
    exit('Admin authentication is not configured.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? null;
    if (is_string($password) && password_verify($password, $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Thông tin đăng nhập không hợp lệ.';
}
require_once __DIR__ . '/site-content.php';
require __DIR__ . '/login-view.php';
''', encoding='utf-8')

seed = release / 'seed'
(seed / 'uploads').mkdir(parents=True, exist_ok=True)
(seed / 'uploads' / '.keep').touch()
if (workspace / 'site_data.json').is_file():
    shutil.copy2(workspace / 'site_data.json', seed / 'site_data.json')
if (workspace / 'uploads').is_dir():
    for photo in (workspace / 'uploads').iterdir():
        if photo.is_file() and photo.suffix.lower() in {'.jpg', '.jpeg', '.png', '.webp'}:
            shutil.copy2(photo, seed / 'uploads' / photo.name)

archive = workspace / 'deployment' / 'ielts-zalo-railway.zip'
with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as bundle:
    for path in sorted(release.rglob('*')):
        if path.is_file():
            bundle.write(path, path.relative_to(release))
print(f'Prepared {archive.name} ({archive.stat().st_size:,} bytes)')
