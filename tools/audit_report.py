"""Citește rezultatul injectat de tools/audit.js și îl rezumă."""
import sys, re, json, html

d = sys.stdin.read()
m = re.search(r'<pre id="AUDIT">@@AUDIT@@(.*?)@@END@@</pre>', d, re.S)
tag = sys.argv[1] if len(sys.argv) > 1 else ""
if not m:
    print(f"=== {tag} === fără rezultat (pagina nu s-a încărcat?)")
    raise SystemExit(1)
o = json.loads(html.unescape(m.group(1)))
scroll = o["docScroll"]
print(f'=== {tag} ===  lățime doc {scroll["scrollW"]}/{scroll["clientW"]}'
      f' | contrast {len(o["contrast"])}'
      f' | text tăiat {len(o["clipped"])}'
      f' | în afara ecranului {len(o["overflow"])}'
      f' | suprapuneri {len(o["overlap"])}')
for key, label in [("contrast", "CONTRAST"), ("clipped", "TĂIAT"), ("overflow", "AFARĂ")]:
    seen = set()
    for it in o[key]:
        k = json.dumps(it, sort_keys=True)[:110]
        if k in seen:
            continue
        seen.add(k)
        print(f'   {label:9} {json.dumps(it, ensure_ascii=False)[:170]}')
        if len(seen) >= 6:
            break
