#!/usr/bin/env bash
# Régénère docs/guides/pdf/*.pdf à partir des guides Markdown (nécessite PHP 8.3 et Google Chrome).
set -euo pipefail
cd "$(dirname "$0")"
tmp=$(mktemp -d); trap 'rm -rf "$tmp"' EXIT
mkdir -p pdf
for f in *.md; do
    n=${f%.md}; [ "$n" = README ] && n=00-sommaire
    php md2html.php "$f" "$tmp/$n.html"
    google-chrome --headless=new --disable-gpu --no-sandbox --no-pdf-header-footer --print-to-pdf="pdf/$n.pdf" "file://$tmp/$n.html" >/dev/null 2>&1
    echo "pdf/$n.pdf"
done
