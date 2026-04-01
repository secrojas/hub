#!/usr/bin/env bash
# =============================================================
# Hub — First Deploy Script
# Run once on the server after connecting via SSH.
# Usage: bash first-deploy.sh
# =============================================================
set -e

REPO="https://github.com/secrojas/hub.git"
APP_DIR="/home2/srojasap/hub"
PUBLIC_LINK="/home2/srojasap/public_html/hub"

echo "→ Cloning repository..."
git clone "$REPO" "$APP_DIR"

echo "→ Setting up symlink for subdomain document root..."
rm -rf "$PUBLIC_LINK"
ln -s "$APP_DIR/public" "$PUBLIC_LINK"

echo "→ Copying .env template..."
cp "$APP_DIR/deploy/env.production.example" "$APP_DIR/.env"

echo ""
echo "============================================================"
echo "  Manual steps required before running finish-deploy.sh:"
echo "============================================================"
echo ""
echo "  1. Edit the .env file:"
echo "     nano $APP_DIR/.env"
echo ""
echo "  2. Set at minimum:"
echo "     APP_KEY=          (leave blank — generated in next step)"
echo "     DB_DATABASE=srojasap_hub"
echo "     DB_USERNAME=srojasap_hub"
echo "     DB_PASSWORD=<your_password>"
echo ""
echo "  3. Then run:"
echo "     cd $APP_DIR"
echo "     composer install --no-dev --optimize-autoloader"
echo "     php artisan key:generate"
echo "     php artisan storage:link"
echo "     php artisan migrate --force"
echo "     php artisan optimize"
echo ""
echo "  Done. hub.srojas.app should be live."
echo "============================================================"
