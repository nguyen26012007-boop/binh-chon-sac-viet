#!/bin/sh
set -eu
: "${VOTING_DATA_DIR:=/var/www/data}"
: "${PORT:=8080}"
: "${ADMIN_PASSWORD_HASH:?Set ADMIN_PASSWORD_HASH before deploying}"
case "$PORT" in ''|*[!0-9]*) echo "Invalid PORT" >&2; exit 1;; esac
if [ ! -d "$VOTING_DATA_DIR" ]; then
    echo "Mount a persistent Railway volume at $VOTING_DATA_DIR" >&2
    exit 1
fi
mkdir -p "$VOTING_DATA_DIR/uploads"
if [ -f /opt/pageant-seed/site_data.json ] && [ ! -f "$VOTING_DATA_DIR/site_data.json" ]; then
    cp /opt/pageant-seed/site_data.json "$VOTING_DATA_DIR/site_data.json"
fi
cp -Rn /opt/pageant-seed/uploads/. "$VOTING_DATA_DIR/uploads/"
ln -sfn "$VOTING_DATA_DIR/uploads" /var/www/html/uploads
chown -R www-data:www-data "$VOTING_DATA_DIR"
chmod 700 "$VOTING_DATA_DIR"
find "$VOTING_DATA_DIR" -type d -exec chmod 700 {} \;
find "$VOTING_DATA_DIR" -type f -exec chmod 600 {} \;
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground
