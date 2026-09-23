#!/usr/bin/env bash
# Deploy en Hostinger (hosting compartido, sin Docker).
# Lo ejecuta GitHub Actions por SSH, y tambien sirve para correrlo a mano.
#
# Antes del PRIMER deploy hay que registrar la migracion que en prod se aplico
# a mano, sin ejecutarla:
#   php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250908204950' --add
set -euo pipefail

APP_DIR="${APP_DIR:-$HOME/domains/atilioautomotores.com/public_html/concesionaria}"
PHP="${PHP_BIN:-php}"
RAMA="${DEPLOY_BRANCH:-main}"

cd "$APP_DIR"

# ff-only a proposito: si alguien edito archivos en el server, el deploy para
# en vez de pisarle el trabajo.
echo "==> git pull"
git pull --ff-only origin "$RAMA"

echo "==> composer"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> migraciones"
"$PHP" bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> assets"
"$PHP" bin/console tailwind:build --minify
"$PHP" bin/console asset-map:compile

echo "==> cache"
"$PHP" bin/console cache:clear
"$PHP" bin/console cache:warmup

echo "==> permisos"
"$PHP" bin/console app:permisos:sincronizar

echo "OK"
