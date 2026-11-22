#!/bin/sh
set -e
: "${PORT:=8080}"

# Đảm bảo chỉ một MPM (prefork) được nạp, ngay lúc container khởi động
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Log để dễ chẩn đoán
echo "MPM enabled:"; ls /etc/apache2/mods-enabled | grep -i mpm || true
grep -rniE "LoadModule +mpm_" /etc/apache2 --include=*.conf --include=*.load 2>/dev/null | grep -v mods-available || true

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
