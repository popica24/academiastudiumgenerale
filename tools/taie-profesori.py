#!/usr/bin/env python3
# Taie portretele profesorilor din capturile postarilor de pe Instagram si le
# scrie in assets/foto/profesori/. Capturile sunt facute pe desktop, deci
# postarea are poza in stanga si textul in dreapta; se lucreaza doar in poza.
#
#   python3 tools/taie-profesori.py          proba: o plansa cu toate cele opt
#   python3 tools/taie-profesori.py scrie    scrie fisierele .webp
#
# Fara argument nu atinge nimic din assets/: scrie doar plansa de proba, ca sa
# se vada cadrajul inainte de a suprascrie ceva. Plansa iese langa script.
#
# Cadrajul nu se calculeaza, se masoara. Un detector de fete nu exista in
# proiect si nici nu ar sti cat aer sa lase deasupra capului, deci fiecare
# poza are randul ei in FETE, reglat uitandu-te la plansa:
#
#   (x, y)  centrul fetei in pixelii capturii
#   latime  latimea fetei, tot in pixeli
#   scara   de cate ori mai lat decat fata se taie patratul
#   jos     cu cat coboara centrul cadrului sub centrul fetei, in latimi de
#           fata; peste zero inseamna mai mult aer deasupra capului
#
# Ordinea randurilor e ordinea alfabetica a capturilor, iar NUME, mai jos, le
# da numele de fisier in aceeasi ordine. Daca se adauga o captura noua,
# amandoua listele cresc odata.

from PIL import Image, ImageDraw
import glob, os, sys

CAPTURI = 'assets/teachers/*.png'
IESIRE  = 'assets/foto/profesori'
LATURA  = 560          # patratul scris pe disc
CREM    = (252, 251, 247)

FETE = [
    (1666,  663, 557, 2.75, 0.70),
    (1857, 1247, 610, 3.00, 0.55),
    (1035,  875, 690, 4.00, 0.15),
    (1446, 1061, 796, 3.00, 0.55),
    (1380, 1114, 875, 3.50, 0.35),
    (1141,  663, 769, 3.00, 0.70),
    (1512,  663, 424, 3.30, 0.65),
    (1857,  796, 292, 2.80, 0.60),
]
NUME = ['romana', 'araba', 'arte', 'geografie',
        'franceza', 'psihologie', 'biologie', 'chineza']

fisiere = sorted(glob.glob(CAPTURI))
assert len(fisiere) == len(FETE) == len(NUME), \
    f'{len(fisiere)} capturi, {len(FETE)} cadraje, {len(NUME)} nume'

plansa = Image.new('RGB', (4 * 320, 2 * 320), CREM)
taieturi = []
for i, (f, (fx, fy, fw, scara, jos)) in enumerate(zip(fisiere, FETE)):
    im = Image.open(f).convert('RGB')
    lat = int(fw * scara)
    x0 = fx - lat // 2
    y0 = int(fy + jos * fw) - lat // 2
    # poza postarii ocupa coltul din stanga-sus al capturii, restul e panoul
    # de text; cadrul nu are voie sa iasa din ea
    x0 = max(0, min(x0, 2860 - lat))
    y0 = max(0, min(y0, 3000 - lat))
    taieturi.append(im.crop((x0, y0, x0 + lat, y0 + lat)))

    # in plansa, cercul, ca sa se vada exact ce ramane in pagina
    p = taieturi[-1].resize((320, 320), Image.LANCZOS)
    masca = Image.new('L', (320, 320), 0)
    ImageDraw.Draw(masca).ellipse((0, 0, 319, 319), fill=255)
    celula = Image.new('RGB', (320, 320), CREM)
    celula.paste(p, (0, 0), masca)
    plansa.paste(celula, ((i % 4) * 320, (i // 4) * 320))

proba = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'proba-profesori.png')
plansa.save(proba)
print('plansa de proba:', proba)

if len(sys.argv) > 1 and sys.argv[1] == 'scrie':
    os.makedirs(IESIRE, exist_ok=True)
    for taietura, nume in zip(taieturi, NUME):
        cale = os.path.join(IESIRE, nume + '.webp')
        taietura.resize((LATURA, LATURA), Image.LANCZOS).save(
            cale, 'WEBP', quality=86, method=6)
        print(f'{cale:40} {os.path.getsize(cale) / 1024:6.1f} KB')
