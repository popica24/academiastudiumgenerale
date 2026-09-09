#!/usr/bin/env bash
# Verifică paginile: contrast calculat pe fundalul compus, text tăiat,
# elemente ieșite din ecran, suprapuneri de text, derulare orizontală.
# Rulează headless, fără server. Nu are nevoie de nimic instalat în proiect.
#
#   tools/audit.sh                 toate paginile, la 1440 / 900 / 560
#   tools/audit.sh index.html      o singură pagină
#   tools/audit.sh index.html 390  o pagină, o lățime
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT="$(pwd)"

BROWSER=""
for b in "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
         "/Applications/Brave Browser.app/Contents/MacOS/Brave Browser" \
         "$(command -v chromium || true)"; do
  [ -x "$b" ] && BROWSER="$b" && break
done
[ -z "$BROWSER" ] && { echo "Nu găsesc Chrome/Brave/Chromium."; exit 1; }

PAGES=("${1:-}")
[ -z "${PAGES[0]}" ] && PAGES=(index.html despre.html blog.html blog-articol.html en/index.html en/despre.html)
WIDTHS=("${2:-}")
[ -z "${WIDTHS[0]}" ] && WIDTHS=(1440 900 560)

for page in "${PAGES[@]}"; do
  # Copia de lucru stă lângă original, nu la rădăcină: o pagină din en/ are
  # căile scrise cu ../, iar mutată la rădăcină ar rămâne fără CSS și ar fi
  # măsurată o pagină goală. Din același motiv, calea către audit.js urcă
  # cu tot atâtea ../ câte are pagina.
  dir="$(dirname "$page")"
  base="$(basename "$page")"
  tmp="$dir/_audit_$base"
  sus=""
  [ "$dir" != "." ] && sus="../"
  python3 - "$page" "$tmp" "${sus}tools/audit.js" <<'PY'
import sys
src, dst, script = sys.argv[1], sys.argv[2], sys.argv[3]
s = open(src, encoding='utf-8').read()
open(dst, 'w', encoding='utf-8').write(
    s.replace('</body>', '<script src="%s"></' % script + 'script>\n</body>'))
PY
  for w in "${WIDTHS[@]}"; do
    "$BROWSER" --headless --disable-gpu --virtual-time-budget=10000 \
      --window-size="$w",2000 --dump-dom "file://$ROOT/$tmp" 2>/dev/null \
      | python3 tools/audit_report.py "$page @ ${w}px"
  done
  rm -f "$tmp"
done
