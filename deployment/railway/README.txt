IELTS-ZALO / SAC VIET - PHP DEPLOYMENT

1. Connect your Railway account and create a service from this directory.
   The root contains the Dockerfile; deploy with one replica.
2. Attach a persistent volume with mount path /var/www/data.
3. Set ADMIN_PASSWORD_HASH to a PHP password_hash() value for a new admin
   password, using the provider's secret settings. Do not commit passwords.
   VOTING_DATA_DIR defaults to /var/www/data; PORT is provided by Railway.
4. Deploy, generate the service's public domain, and open it over HTTPS.
5. Log in at /admin_login.php and verify admin editing, code issuance and voting.

The package includes website source, public assets and optional content seeds.
Names, phone numbers, issued codes and voting records start in the mounted
volume. Back up this volume. Existing local participant records are not bundled.
Admin content, votes and uploaded photos persist there across deployments.

Regenerate this release after local changes:
    python deployment/build-railway.py

No MySQL server is required. Required PHP extension: mbstring.
Production source uses environment-based admin authentication. Local XAMPP
authentication is unchanged. Legacy Telegram and database files are excluded.
