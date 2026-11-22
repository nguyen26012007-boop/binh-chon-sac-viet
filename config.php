<?php
// Cấu hình đọc từ biến môi trường (không đưa secret vào Git).
// Đặt TELEGRAM_BOT_TOKEN và TELEGRAM_CHAT_ID trong Railway Variables để bật thông báo Telegram.
$telegramToken = getenv('TELEGRAM_BOT_TOKEN');
$telegramChat = getenv('TELEGRAM_CHAT_ID');
if (is_string($telegramToken) && $telegramToken !== '' && is_string($telegramChat) && $telegramChat !== '') {
    define('TELEGRAM_BOT_TOKEN', $telegramToken);
    define('TELEGRAM_CHAT_ID', $telegramChat);
}
