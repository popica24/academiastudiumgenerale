# -*- coding: utf-8 -*-
"""Cât de gros trebuie voalul peste o fotografie, calculat din poză.

Regula din brandbook: contrastul se calculează pornind de la cel mai luminos
pixel al imaginii, nu din ochi. Aici „cel mai luminos" e percentila 99.5 pe
banda în care stă textul, ca un singur pixel ars să nu întunece tot cardul.

    tools/voal.py assets/foto/materii/*.jpg          titlu de 26px, prag 3:1
    tools/voal.py --paragraf assets/foto/motive/*.jpg   plus text mic, 4.5:1

Rezultatul se pune pe card: style="--voal-foto:rgba(11, 25, 54, 0.52)".
Cere Pillow. Fără el, voalul rămâne cel implicit din tokens.css, 0.65, care
e sigur pentru orice fotografie, inclusiv una albă.
"""
import sys
from PIL import Image

VOAL = (11, 25, 54)          # --navy-900
BANDA_TITLU = (0.05, 0.35)
BANDA_TEXT = (0.60, 0.98)


def lin(c):
    c /= 255.0
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4


def luminanta(px):
    r, g, b = px[:3]
    return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b)


def contrast_alb(alfa, poza):
    """Contrastul textului alb peste voal, cu poza dedesubt."""
    compus = [alfa * v + (1 - alfa) * p for v, p in zip(VOAL, poza)]
    return 1.05 / (luminanta(compus) + 0.05)


def pixel_luminos(im, sus, jos, p=0.995):
    w, h = im.size
    px = list(im.crop((0, int(h * sus), w, int(h * jos))).getdata())
    px.sort(key=luminanta)
    return px[min(len(px) - 1, int(len(px) * p))]


def voal_minim(poza, prag):
    alfa = 0.20
    while alfa < 0.96:
        if contrast_alb(alfa, poza) >= prag:
            return round(alfa, 2)
        alfa += 0.01
    return 0.95


def main(argv):
    cu_paragraf = "--paragraf" in argv
    fisiere = [a for a in argv if not a.startswith("--")]
    if not fisiere:
        print(__doc__)
        return 1
    for cale in fisiere:
        im = Image.open(cale).convert("RGB")
        im = im.resize((200, max(1, int(200 * im.size[1] / im.size[0]))))
        cere = voal_minim(pixel_luminos(im, *BANDA_TITLU), 3.0)
        if cu_paragraf:
            cere = max(cere, voal_minim(pixel_luminos(im, *BANDA_TEXT), 4.5))
        print("%-44s voal %.2f" % (cale, cere))
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
