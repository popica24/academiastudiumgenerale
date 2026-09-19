#!/usr/bin/env python3
# Face iconița cursului de Power BI în stilul celor primite de la client,
# pornind de la iconița de Excel, care are exact compoziția de care e nevoie:
# cercul bleumarin, petele albastre, stelele aurii, numele scris dedesubt și
# linia aurie.
#
#   MONTSERRAT_SEMIBOLD=… python3 tools/iconita-powerbi.py
#
# Ce se păstrează din Excel: cercul cu marginea lui, petele, stelele, linia
# aurie și locul numelui. Ce se schimbă: în locul logoului Excel intră barele
# galbene ale Power BI, iar dedesubt scrie „POWER BI".
#
# Cum dispare logoul vechi fără să rămână o gaură: dreptunghiul lui se umple
# prin difuzie, adică se încețoșează de multe ori la rând, iar după fiecare
# trecere pixelii cunoscuți din jur se pun la loc. Petele din fundal sunt
# oricum moi, deci umplutura nu se vede; peste ea vin barele, care acoperă
# mijlocul.
#
# Fontul: Montserrat SemiBold. Nu stă în proiect ca TTF, dar stă subsetat, ca
# woff2, în assets/fonturi/. fontTools îl întoarce la TTF, iar Pillow poate
# scrie cu el. Subsetul are toate literele din „POWER BI".

from PIL import Image, ImageDraw, ImageFilter, ImageFont
import numpy as np, os, tempfile

SURSA = 'assets/foto/cursuri/excel.webp'
IESIRE = 'assets/foto/cursuri/powerbi.webp'
WOFF2 = 'assets/fonturi/montserrat-600-latin.woff2'

# Dreptunghiul logoului Excel, măsurat pe original, cu o margine de siguranță.
LOGO = (138, 126, 462, 428)
# Numele vechi, „EXCEL": literele stau între 233 și 371, cu înălțimea de
# majusculă între 469 și 498. Linia aurie e mai jos, la 528, și rămâne.
TEXT = (200, 455, 400, 508)

im = Image.open(SURSA).convert('RGB')
W, H = im.size

# ── se scoate logoul vechi și se umple locul cu fundalul din jur ─────────────
gaura = Image.new('L', (W, H), 0)
d = ImageDraw.Draw(gaura)
d.rectangle(LOGO, fill=255)
d.rectangle(TEXT, fill=255)
gaura = gaura.filter(ImageFilter.GaussianBlur(2))
masca = np.asarray(gaura).astype(float)[..., None] / 255.0

baza = np.asarray(im).astype(float)
plin = baza.copy()
# media fundalului cunoscut, ca difuzia să nu pornească de la culorile logoului
margine = np.asarray(gaura) < 128
for c in range(3):
    plin[..., c] = np.where(margine[..., 0] if margine.ndim == 3 else margine,
                            baza[..., c], baza[..., c][margine].mean())
for _ in range(60):
    neted = np.asarray(Image.fromarray(plin.astype(np.uint8)).filter(ImageFilter.GaussianBlur(9))).astype(float)
    plin = baza * (1 - masca) + neted * masca
fond = Image.fromarray(plin.astype(np.uint8))

# ── barele Power BI ──────────────────────────────────────────────────────────
# Patru bare care cresc de la stânga la dreapta, cu vârful rotunjit, așezate pe
# aceeași linie. Galbenul e cel al aplicației, #F2C811; fiecare bară are pe
# dreapta o dungă mai închisă, cât să se vadă că e un obiect, nu o pată.
GALBEN = (242, 200, 17)
UMBRA_BARA = (198, 160, 10)

bare = Image.new('RGBA', (W * 2, H * 2), (0, 0, 0, 0))
db = ImageDraw.Draw(bare)
baza_y = 400 * 2
latime = 58 * 2
pas = 78 * 2
stanga = 168 * 2
inaltimi = [110, 170, 230, 290]
# umbra de sub bare, moale, ca în celelalte iconițe
umbra = Image.new('RGBA', (W * 2, H * 2), (0, 0, 0, 0))
du = ImageDraw.Draw(umbra)
for i, h in enumerate(inaltimi):
    x = stanga + i * pas
    du.rounded_rectangle([x + 10, baza_y - h * 2 + 10, x + latime + 10, baza_y + 14],
                         radius=latime // 2, fill=(0, 0, 0, 110))
umbra = umbra.filter(ImageFilter.GaussianBlur(18))
for i, h in enumerate(inaltimi):
    x = stanga + i * pas
    # bara închisă, întreagă, apoi cea deschisă puțin mai îngustă: ce rămâne
    # descoperit pe dreapta e chiar rotunjirea, deci umbra urmează forma barei
    db.rounded_rectangle([x, baza_y - h * 2, x + latime, baza_y], radius=latime // 2, fill=UMBRA_BARA + (255,))
    db.rounded_rectangle([x, baza_y - h * 2, x + latime - 16, baza_y], radius=(latime - 16) // 2, fill=GALBEN + (255,))

fond = fond.convert('RGBA')
fond.alpha_composite(umbra.resize((W, H), Image.LANCZOS))
fond.alpha_composite(bare.resize((W, H), Image.LANCZOS))

# ── numele, în locul lui „EXCEL" ─────────────────────────────────────────────
from fontTools.ttLib import TTFont
cale_ttf = os.path.join(tempfile.mkdtemp(), 'montserrat-600.ttf')
f = TTFont(WOFF2)
f.flavor = None
f.save(cale_ttf)

NUME = 'POWER BI'
CAP = 29          # înălțimea majusculei din „EXCEL", măsurată pe original
SPATIERE = 3      # literele din original stau rărite
CENTRU_X, BAZA_TEXT = 300, 498

marime = 40
font = ImageFont.truetype(cale_ttf, marime)
sus, jos = font.getbbox('E')[1], font.getbbox('E')[3]
marime = round(marime * CAP / (jos - sus))
font = ImageFont.truetype(cale_ttf, marime)

latimi = [font.getlength(ch) for ch in NUME]
total = sum(latimi) + SPATIERE * (len(NUME) - 1)
dt = ImageDraw.Draw(fond)
x = CENTRU_X - total / 2
for ch, w in zip(NUME, latimi):
    # Montserrat 700 nu se servește pe site, iar subsetul e 600: o trăsătură
    # de 1px îngroașă litera exact cât „EXCEL" din original.
    dt.text((x, BAZA_TEXT), ch, font=font, fill=(255, 255, 255, 255), anchor='ls',
            stroke_width=1, stroke_fill=(255, 255, 255, 255))
    x += w + SPATIERE

fond.convert('RGB').save(IESIRE, quality=88, method=6)
print('scris', IESIRE)
