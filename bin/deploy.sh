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

# Esta verificacion va ANTES de tocar nada. Tailwind no se compila en este
# hosting: el binario es de Bun y necesita cargar lightningcss como libreria
# nativa desde /tmp, que esta montado noexec. Lo compila GitHub Actions y lo
# sube por scp. Si no llego, abortamos con el sitio todavia intacto: la vez que
# esto fallo a mitad de camino, produccion quedo sirviendo el diseno nuevo sin
# CSS y devolvia 500 en todas las paginas.
if [ ! -f var/tailwind/app.built.css ]; then
    echo "ERROR: falta var/tailwind/app.built.css. Lo compila el workflow y lo sube por scp." >&2
    exit 1
fi

# ff-only a proposito: si alguien edito archivos en el server, el deploy para
# en vez de pisarle el trabajo.
echo "==> git pull"
git pull --ff-only origin "$RAMA"

echo "==> composer"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> migraciones"
"$PHP" bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> assets"
"$PHP" bin/console asset-map:compile

echo "==> cache"
"$PHP" bin/console cache:clear
"$PHP" bin/console cache:warmup

echo "==> permisos"
"$PHP" bin/console app:permisos:sincronizar

# Que el deploy "termine bien" no significa que el sitio ande. Esto lo comprueba.
echo "==> smoke test"
CODIGO=$(curl -s -o /dev/null -w "%{http_code}" "${SMOKE_URL:-https://atilioautomotores.com/login}")
if [ "$CODIGO" != "200" ]; then
    echo "ERROR: el login devolvio $CODIGO en vez de 200. Revisar antes de dar el deploy por bueno." >&2
    exit 1
fi
echo "    login responde 200"

echo "OK"
