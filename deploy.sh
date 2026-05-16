#!/bin/bash
# Deploy script — aggiorna da git preservando config e users
# Uso: sudo bash /var/www/laboratori/deploy.sh

set -e

DIR="/var/www/laboratori"
PROTECTED=("private/config.php" "private/users.php")

cd "$DIR"

# 1. Backup file protetti
for f in "${PROTECTED[@]}"; do
    if [ -f "$f" ]; then
        cp "$f" "/tmp/$(basename $f).bak"
        echo "✅ Backup: $f"
    fi
done

# 2. Reset e pull
git checkout -- . 2>/dev/null || true
git pull origin main
echo "✅ Git pull completato"

# 3. Ripristina file protetti
for f in "${PROTECTED[@]}"; do
    bak="/tmp/$(basename $f).bak"
    if [ -f "$bak" ]; then
        cp "$bak" "$f"
        rm "$bak"
        echo "✅ Ripristinato: $f"
    fi
done

# 4. Permessi corretti
chown -R www-data:www-data "$DIR"
chmod -R 750 "$DIR"
chmod 640 private/config.php private/users.php 2>/dev/null || true

echo ""
echo "🚀 Deploy completato!"
