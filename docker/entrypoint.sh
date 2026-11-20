#!/bin/sh
set -e

cd /var/www/html

# Salin template environment saat container pertama kali dibuat.
if [ ! -f .env ]; then
    cp .env.docker .env
fi

# APP_KEY wajib ada. Untuk demo Docker, generate otomatis bila belum diisi.
# Production: set APP_KEY lewat environment/secret manager agar stabil.
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Audit log fail-closed membutuhkan HMAC secret. Generate sekali untuk demo.
if ! grep -q '^AUDIT_LOG_HMAC_SECRET=.\+' .env 2>/dev/null; then
    SECRET=$(php -r 'echo bin2hex(random_bytes(32));')
    sed -i "s/^AUDIT_LOG_HMAC_SECRET=.*/AUDIT_LOG_HMAC_SECRET=${SECRET}/" .env
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan migrate --force

# Cache konfigurasi/view agar production lebih cepat. Route cache tidak dipakai
# karena beberapa route memakai closure.
php artisan config:cache
php artisan view:cache

exec "$@"
