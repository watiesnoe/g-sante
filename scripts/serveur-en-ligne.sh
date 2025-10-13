#!/usr/bin/env bash
# Démonstration sur un poste de développement : lance un second serveur G-SANTÉ en mode
# « en ligne » (espace patient), avec sa propre base, à côté du serveur local habituel.
#
#   scripts/serveur-en-ligne.sh installer          crée la base et ses tables
#   scripts/serveur-en-ligne.sh relier <UUID> <nom> enregistre un établissement, affiche clé et secret
#   scripts/serveur-en-ligne.sh demarrer [port]     démarre le serveur (8001 par défaut)
#
# En production, le serveur en ligne s'installe chez un hébergeur (docs/guides/10-installation.md).
set -euo pipefail
cd "$(dirname "$0")/.."

BASE="${GSANTE_EN_LIGNE_DB:-gsante_en_ligne}"
MYSQL="${MYSQL:-/opt/lampp/bin/mysql}"
export GSANTE_MODE=en_ligne DB_DATABASE="$BASE"

case "${1:-}" in
    installer)
        "$MYSQL" -h 127.0.0.1 -u "${DB_USERNAME:-root}" -e "CREATE DATABASE IF NOT EXISTS \`$BASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        php artisan migrate --force
        echo "Base $BASE prête."
        ;;
    relier)
        php artisan en-ligne:site "${2:?UUID requis}" "${3:?Nom requis}" ${4:-}
        ;;
    demarrer)
        PORT="${2:-8001}"
        echo "Espace patient : http://127.0.0.1:$PORT/patient   (Ctrl+C pour arrêter)"
        cd public && exec php -S "127.0.0.1:$PORT" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
        ;;
    *)
        sed -n '2,9p' "$0"; exit 1
        ;;
esac
