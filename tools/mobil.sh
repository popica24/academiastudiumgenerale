#!/usr/bin/env bash
# Chrome headless nu coboară sub 500px lățime de fereastră, deci pagina se
# randează într-un iframe de 393px, cât are un iPhone 16, într-o fereastră mai
# mare. Media queries se uită la lățimea iframe-ului, deci layout-ul e real.
#
#   tools/mobil.sh index.html captura.png [inaltime]
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT="$(pwd)"
PAGE="${1:-index.html}"; OUT="${2:-/tmp/mobil.png}"; H="${3:-852}"
BROWSER=""
for b in "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
         "/Applications/Brave Browser.app/Contents/MacOS/Brave Browser" \
         "$(command -v chromium || true)"; do
  [ -x "$b" ] && BROWSER="$b" && break
done
cat > _mobil.html <<HTML
<!doctype html><meta charset="utf-8">
<style>html,body{margin:0;background:#fff}iframe{width:393px;height:${H}px;border:0;display:block}</style>
<iframe src="file://$ROOT/$PAGE"></iframe>
HTML
"$BROWSER" --headless --disable-gpu --allow-file-access-from-files \
  --virtual-time-budget=18000 --window-size=393,$H \
  --screenshot="$OUT" "file://$ROOT/_mobil.html" 2>/dev/null
rm -f _mobil.html
