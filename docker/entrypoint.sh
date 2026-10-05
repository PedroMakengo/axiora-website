#!/bin/sh
# =====================================================================
# Arranque do contentor: prepara as pastas dos volumes, instala/actualiza
# a base de dados (database/instalar.php) e só depois inicia o Apache.
# =====================================================================
set -e

RAIZ=/var/www/html

mkdir -p "$RAIZ/assets/uploads" "$RAIZ/storage/ratelimit" "$RAIZ/storage/sessoes"

# Volume de uploads novo: repõe a protecção da pasta (não executar código).
if [ ! -f "$RAIZ/assets/uploads/.htaccess" ] && [ -f "$RAIZ/docker/uploads.htaccess" ]; then
    cp "$RAIZ/docker/uploads.htaccess" "$RAIZ/assets/uploads/.htaccess"
fi

chown -R www-data:www-data "$RAIZ/assets/uploads" "$RAIZ/storage"

if [ "${DB_AUTO_INSTALAR:-1}" = "1" ]; then
    php "$RAIZ/database/instalar.php" --esperar || {
        echo "[entrypoint] AVISO: a instalação da base de dados falhou — o site arranca na mesma; verifique as variáveis DB_*."
    }
fi

exec docker-php-entrypoint "$@"
