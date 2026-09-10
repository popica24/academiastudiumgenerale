# Taie cele 21 de iconite din colajul primit de la client si le scrie peste
# fisierele din assets/foto/materii/ si assets/foto/cursuri/. Fiecare cerc iese
# intr-un patrat, pe acelasi bleumarin --navy-900 cu fundalul cardului, ca sa nu
# ramana colturi albe. Ordinea cercurilor in colaj, citita de la stanga la
# dreapta si de sus in jos, e chiar ordinea cardurilor din pagina.
#
#   python3 tools/taie-colaj.py        (se ruleaza din radacina proiectului)
from PIL import Image, ImageDraw, ImageFilter
import numpy as np, os

SURSA = 'assets/foto/materii-colaj.png'
NAVY  = (11, 25, 54)          # --navy-900, fundalul lui .foto-card
LATURA = 600                  # patratul de iesire
UMPLERE = 0.94                # cat din patrat ocupa cercul

NUME = [
  ('materii','romana'), ('materii','mate-gimnaziu'), ('materii','mate-liceu'),
  ('materii','istorie'), ('materii','geografie'), ('materii','logica'),
  ('materii','biologie'), ('materii','chimie'), ('materii','engleza'),
  ('materii','spaniola'), ('materii','franceza'), ('materii','chineza'),
  ('materii','coreeana'), ('materii','maghiara'), ('materii','informatica'),
  ('materii','greaca'), ('cursuri','excel'), ('cursuri','contabilitate'),
  ('cursuri','dictie'), ('cursuri','dezvoltare'), ('cursuri','financiara'),
]

im = Image.open(SURSA).convert('RGB')
a = np.asarray(im).astype(int)
intunecat = a.sum(2) < 400

def benzi(v, prag):
    out, s = [], None
    for i, x in enumerate(v):
        if x > prag and s is None: s = i
        elif x <= prag and s is not None: out.append((s, i - 1)); s = None
    if s is not None: out.append((s, len(v) - 1))
    return out

randuri  = benzi(intunecat.sum(1), 5)
coloane  = benzi(intunecat.sum(0), 5)

celule = []
for y0, y1 in randuri:
    for x0, x1 in coloane:
        if intunecat[y0:y1+1, x0:x1+1].sum() > 5000:
            celule.append((x0, y0, x1, y1))

assert len(celule) == len(NUME), f'{len(celule)} cercuri gasite, {len(NUME)} asteptate'

SUPRA = 4   # se lucreaza la rezolutie de 4x, ca marginea cercului sa iasa neteda
for (x0, y0, x1, y1), (dosar, nume) in zip(celule, NUME):
    r = min(x1 - x0, y1 - y0) / 2
    cx, cy = (x0 + x1) / 2, (y0 + y1) / 2
    disc = im.crop((round(cx - r), round(cy - r), round(cx + r), round(cy + r)))

    d = round(LATURA * UMPLERE)
    disc = disc.resize((d, d), Image.LANCZOS)

    masca = Image.new('L', (d * SUPRA, d * SUPRA), 0)
    ImageDraw.Draw(masca).ellipse((0, 0, d * SUPRA - 1, d * SUPRA - 1), fill=255)
    masca = masca.resize((d, d), Image.LANCZOS)

    out = Image.new('RGB', (LATURA, LATURA), NAVY)
    out.paste(disc, ((LATURA - d) // 2, (LATURA - d) // 2), masca)

    cale = f'assets/foto/{dosar}/{nume}.webp'
    out.save(cale, 'WEBP', quality=88, method=6)
    print(f'{cale:44} {os.path.getsize(cale)/1024:6.1f} KB')
