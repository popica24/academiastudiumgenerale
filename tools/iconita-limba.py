#!/usr/bin/env python3
# Face iconița unei limbi noi în stilul celor din colajul clientului, pornind de
# la iconița maghiarei, care are exact compoziția de care e nevoie: un steag în
# vânt, o bulă de conversație, stele aurii, numele scris dedesubt și linia aurie.
#
#   python3 tools/iconita-limba.py            scrie turca.webp și romana-straini.webp
#
# Ce se păstrează din maghiară: fundalul, stelele, bula, linia aurie și, mai ales,
# forma steagului cu umbrele lui. Ce se schimbă: modelul de pe steag și numele.
#
# Cum se schimbă modelul fără să se piardă valul: pentru fiecare pixel al
# steagului se află unde cade pe steagul întins (u pe lățime, v pe înălțime,
# între 0 și 1), din marginea de sus și de jos a steagului pe coloana lui. Apoi
# culoarea nouă e culoarea modelului în (u, v), înmulțită cu cât de luminos era
# pixelul față de media dungii lui din steagul maghiar. Așa faldurile și lumina
# rămân exact unde erau.
#
# Numele e scris cu Montserrat Bold, mărimea potrivită după lățimea lui
# „MAGHIARĂ" din original. Fontul TTF nu stă în proiect; scriptul îl caută în
# FONT de mai jos, iar la nevoie se ia de pe github.com/JulietaUla/Montserrat.

from PIL import Image, ImageDraw, ImageFont, ImageFilter
import numpy as np, math, os, sys

SURSA = 'assets/foto/materii/maghiara.webp'
IESIRE = 'assets/foto/materii'
FONT = os.environ.get('MONTSERRAT_BOLD', 'Montserrat-Bold.ttf')

im = np.asarray(Image.open(SURSA).convert('RGB')).astype(float)
H, W, _ = im.shape
r, g, b = im[..., 0], im[..., 1], im[..., 2]
yy, xx = np.mgrid[0:H, 0:W]

# ── bula: elipsă plus coadă, măsurate pe original ─────────────────────────────
bula = ((xx - 428) / 76.0) ** 2 + ((yy - 342) / 60.0) ** 2 <= 1.0
coada = Image.new('L', (W, H), 0)
ImageDraw.Draw(coada).polygon([(372, 378), (410, 392), (366, 418)], fill=255)
bula |= np.asarray(coada) > 0
bula = np.asarray(Image.fromarray((bula * 255).astype(np.uint8)).filter(ImageFilter.MaxFilter(5))) > 0

# ── steagul: tot ce e roșu, alb sau verde în dreptunghiul lui, fără bulă ─────
zona = (xx >= 140) & (xx <= 452) & (yy >= 120) & (yy <= 405)
rosu = (r > 140) & (g < 140) & (b < 120)
alb = (r > 175) & (g > 175) & (b > 170) & (np.abs(r - b) < 40)
verde = (g > 90) & (r < 140) & (g > r + 15) & (g > b)
steag = zona & (rosu | alb | verde) & ~bula
# umple găurile mici din masca steagului (umbre între dungi)
steag = np.asarray(Image.fromarray((steag * 255).astype(np.uint8))
                   .filter(ImageFilter.MaxFilter(3)).filter(ImageFilter.MinFilter(3))) > 0
steag &= zona & ~bula

# marginile steagului pe fiecare coloană
coloane = np.where(steag.any(0))[0]
st, dr = coloane.min(), coloane.max()
sus = np.full(W, np.nan); jos = np.full(W, np.nan)
for x in coloane:
    ys = np.where(steag[:, x])[0]
    sus[x], jos[x] = ys.min(), ys.max()
# sub bulă marginea de jos lipsește: se prelungește din coloanele vecine
valid = ~np.isnan(jos)
xs = np.arange(W)
sus = np.interp(xs, xs[valid], sus[valid])
jos_lin = np.interp(xs, xs[valid & (xs < 355)], jos[valid & (xs < 355)])
panta = np.polyfit(xs[valid & (xs > 200) & (xs < 350)], jos[valid & (xs > 200) & (xs < 350)], 1)
jos = np.where(xs < 355, jos_lin, np.polyval(panta, xs))
# steagul întreg, inclusiv partea ascunsă de bulă, ca modelul să continue sub ea
plin = (xx >= st) & (xx <= dr) & (yy >= sus[None, :]) & (yy <= jos[None, :])

u = np.clip((xx - st) / float(dr - st), 0, 1)
v = np.clip((yy - sus[None, :]) / np.maximum(jos - sus, 1)[None, :], 0, 1)

# luminozitatea relativă: pixelul față de media dungii lui
lum = 0.299 * r + 0.587 * g + 0.114 * b
dunga = np.minimum((v * 3).astype(int), 2)
clase = [rosu, alb, verde]
umbra = np.zeros_like(lum)
sigur = np.zeros_like(lum, dtype=bool)
for k in range(3):
    # doar pixelii care chiar au culoarea dungii lor; cei de la granița dintre
    # dungi sunt amestecați și ar desena o linie închisă pe steagul nou
    m = steag & (dunga == k) & clase[k]
    m &= np.asarray(Image.fromarray((m * 255).astype(np.uint8)).filter(ImageFilter.MinFilter(5))) > 0
    umbra[m] = lum[m] / np.median(lum[m])
    sigur |= m
# golurile (granițele, sub bulă) se umplu din vecini, apoi totul se netezește:
# faldurile sunt largi, iar zgomotul de compresie nu are ce căuta în umbră
def netezeste(a, raza):
    # gaussian separabil, în numpy: Pillow nu estompează imagini cu virgulă
    x = np.arange(-3 * raza, 3 * raza + 1)
    k = np.exp(-x ** 2 / (2.0 * raza ** 2)); k /= k.sum()
    a = np.apply_along_axis(lambda rand: np.convolve(rand, k, mode='same'), 1, a)
    return np.apply_along_axis(lambda col: np.convolve(col, k, mode='same'), 0, a)
num = netezeste(np.where(sigur, umbra, 0), 7)
den = netezeste(sigur.astype(float), 7)
umbra = np.clip(num / np.maximum(den, 1e-3), 0.6, 1.2)
umbra[den < 1e-3] = 1.0

# ── fundalul fără steag și fără nume: pictat din vecini, prin difuzie ────────
text = (xx >= 160) & (xx <= 440) & (yy >= 445) & (yy <= 505)
de_sters = np.asarray(Image.fromarray(((plin | steag | text) & ~bula).astype(np.uint8) * 255)
                      .filter(ImageFilter.MaxFilter(9))) > 0
de_sters &= ~bula
fundal = im.copy()
fundal[de_sters] = np.nan
for _ in range(400):
    medie = np.nanmean(np.stack([np.roll(fundal, 1, 0), np.roll(fundal, -1, 0),
                                 np.roll(fundal, 1, 1), np.roll(fundal, -1, 1)]), axis=0)
    fundal = np.where(de_sters[..., None], np.where(np.isnan(fundal), medie, 0.5 * fundal + 0.5 * medie), im)
fundal = np.nan_to_num(fundal)

def model_turca(u, v):
    """Steagul Turciei, 3:2. Coordonate în înălțimi de steag."""
    X, Y = u * 1.5, v
    c = np.empty(u.shape + (3,)); c[:] = (214, 48, 46)
    lat = ((X - 0.5) ** 2 + (Y - 0.5) ** 2 <= 0.25 ** 2) & ~((X - 0.5625) ** 2 + (Y - 0.5) ** 2 <= 0.2 ** 2)
    c[lat] = (246, 244, 240)
    # steaua: cinci colțuri, centrul la 0.7833 H, raza 0.125 H, un colț spre lance
    cx, cy, R, rr = 0.7833, 0.5, 0.125, 0.125 * 0.382
    puncte = []
    for i in range(10):
        a = math.pi + i * math.pi / 5
        rad = R if i % 2 == 0 else rr
        puncte.append((cx + rad * math.cos(a), cy + rad * math.sin(a)))
    stea = Image.new('L', (1500, 1000), 0)
    ImageDraw.Draw(stea).polygon([(px * 1000, py * 1000) for px, py in puncte], fill=255)
    stea = np.asarray(stea)
    ix = np.clip((X * 1000).astype(int), 0, 1499); iy = np.clip((Y * 1000).astype(int), 0, 999)
    c[stea[iy, ix] > 0] = (246, 244, 240)
    return c

def model_romania(u, v):
    c = np.empty(u.shape + (3,))
    c[u < 1 / 3] = (38, 76, 168)
    c[(u >= 1 / 3) & (u < 2 / 3)] = (246, 202, 64)
    c[u >= 2 / 3] = (214, 56, 50)
    return c

def scrie(nume_fisier, model, randuri):
    culori = model(u, v) * umbra[..., None]
    out = np.where(plin[..., None], culori, fundal)
    # marginea steagului, netezită, ca să nu fie în trepte
    alfa = np.asarray(Image.fromarray((plin * 255).astype(np.uint8)).filter(ImageFilter.GaussianBlur(0.8))) / 255.0
    out = fundal * (1 - alfa[..., None]) + np.where(plin[..., None], culori, fundal) * alfa[..., None]
    out = np.where(bula[..., None], im, out)
    img = Image.fromarray(np.clip(out, 0, 255).astype(np.uint8))

    # numele, desenat la 3x și micșorat, ca literele să fie la fel de moi ca în colaj
    S = 3
    strat = Image.new('L', (W * S, H * S), 0)
    d = ImageDraw.Draw(strat)
    ref = ImageFont.truetype(FONT, 100 * S)
    lat_ref = d.textlength('MAGHIARĂ', font=ref)
    marime = 100 * 241 / (lat_ref / S)             # „MAGHIARĂ" are 241px în original
    if len(randuri) > 1:
        marime *= 0.7
    font = ImageFont.truetype(FONT, int(marime * S))
    y0 = 478 if len(randuri) == 1 else 458
    for i, rand in enumerate(randuri):
        lat = d.textlength(rand, font=font)
        d.text(((W * S - lat) / 2, (y0 + i * marime * 1.22) * S), rand, font=font, fill=255, anchor='lm')
    strat = strat.resize((W, H), Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.35))
    alb_text = Image.new('RGB', (W, H), (246, 246, 246))
    img.paste(alb_text, (0, 0), strat)
    img.save(os.path.join(IESIRE, nume_fisier), 'webp', quality=90, method=6)
    print('scris', nume_fisier)

if not os.path.exists(FONT):
    sys.exit('lipsește fontul: ' + FONT)
scrie('turca.webp', model_turca, ['TURCĂ'])
scrie('romana-straini.webp', model_romania, ['ROMÂNĂ', 'PENTRU STRĂINI'])
