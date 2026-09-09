# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Ce este

Site static pentru **Academia · Studium Generale by Denisa SRL**, o școală
privată de pregătire pentru Bacalaureat și Evaluarea Națională. Fără build și
fără framework. O singură dependență, de la 2026-09-07: **AOS**, animația la
derulare, luată de pe CDN. Backendul PHP pentru blog e planificat, dar nu
există încă; zonele lui sunt deja marcate în HTML.

### AOS

`aos.css` și `aos.js` 2.3.4 vin de pe cdnjs, cu `integrity` și `crossorigin`
în fiecare din cele patru pagini. Pornirea e în `site.js`, secțiunea 7.

Trei lucruri de nu stricat:

- **`aos.css` ascunde tot ce poartă `data-aos`** până când scriptul îl vede
  intrând în ecran. Dacă scriptul nu ajunge, pagina ar rămâne pe jumătate
  goală, de asta există clasa `fara-aos` pe `<html>`, pusă din `onerror`-ul
  etichetei `<script>` sau din `site.js` când `window.AOS` lipsește. Regula
  care o face să conteze e în `site.css`. Verificat cu CDN-ul blocat.
- **Hero-ul nu are `data-aos`.** Titlul paginii nu are voie să depindă de un
  fișier de pe alt server ca să fie vizibil.
- **`tools/audit.js` neutralizează AOS** înainte de a măsura: auditul verifică
  pagina în repaus, nu la mijlocul unei animații.

La `prefers-reduced-motion: reduce`, AOS își șterge singur atributele și totul
rămâne vizibil, fără mișcare. Verificat.

Tot conținutul e în **română**, inclusiv comentariile din cod. Scrie la fel.

## Comenzi

```bash
python3 -m http.server 8000     # server local; sau deschide direct index.html
tools/audit.sh                  # verifică toate paginile la 1440 / 900 / 560
tools/audit.sh index.html       # o singură pagină
tools/audit.sh index.html 390   # o pagină, o lățime
tools/voal.py poza.jpg          # cât de gros trebuie voalul peste fotografia aia
tools/voal.py --paragraf a.jpg  # la fel, dar cardul are și text mic
tools/mobil.sh index.html x.png # captură la 393px, cât are un iPhone 16
```

**Chrome headless nu coboară sub 500px lățime de fereastră.** O captură cerută
la 393px iese randată la 485 și tăiată pe dreapta, deci arată o minciună.
`tools/mobil.sh` ocolește asta punând pagina într-un iframe de 393px într-o
fereastră mai mare; media queries se uită la lățimea iframe-ului, deci
layout-ul e cel adevărat. La fel se poate injecta și `tools/audit.js` în
iframe, cu `--allow-file-access-from-files`.

## SEO și viteză

- **Fonturile stau în `assets/fonturi/`**, nu la Google. Sunt fișierele lor,
  subsetate la glifele de care are nevoie o pagină în română: 345 KB au
  devenit 92 KB. Regulile `@font-face` sunt la începutul lui `tokens.css`, cu
  `unicode-range`, deci browserul ia doar ce folosește. Șase fețe se
  preîncarcă din `<head>`, cele din primul ecran. Ca să adaugi o greutate,
  ia fișierul de la Google, subsetează-l cu `fontTools` și scrie încă un
  `@font-face`. Montserrat 700 nu se servește: `b` și `strong` sunt 600.
- **`aos.css` nu se mai încarcă.** Site-ul folosește un singur efect,
  `fade-up`, iar regulile lui sunt scrise în `site.css`. De pe CDN vine doar
  `aos.js`, cu `defer`.
- **Pozele sunt `.webp`**, la mărimea la care se afișează: 4.4 MB au devenit
  1.1 MB. Sursele mari, `image.png` și `clasa.jpg`, au rămas pe disc dar nu
  le încarcă nicio pagină.
- **Open Graph e scris în HTML**, fiindcă aplicațiile care fac previzualizarea
  unui link nu rulează JavaScript. **JSON-LD se construiește din `config.js`**
  în `site.js`, secțiunea 9, ca datele de contact să rămână într-un singur
  loc; Google randează JavaScript înainte să le citească.
- **Adresa canonică e `https://studiumgenerale.ro`**, în `<link rel=canonical>`,
  în Open Graph, în `sitemap.xml`, în `robots.txt` și în `site.js`. Domeniul nu
  e încă înregistrat. Până atunci, copia de pe `vercel.app` nu se indexează, ea
  arată spre domeniul adevărat. Dacă adresa se schimbă, se schimbă în toate
  cele cinci locuri.
- **`vercel.json`** ține fonturile un an în cache și restul o lună.

## Telefon

Reperul e **iPhone 16, 393px**. Regulile de telefon stau într-un singur bloc,
`@media (max-width: 560px)` din `assets/site.css`. Ce e decis acolo:

- **Antetul n-are text.** Doar sigla, hamburgerul și WhatsApp. Numele mărcii se
  rupea pe trei rânduri și împingea butoanele.
- **Hamburgerul e desenat din trei linii**, nu dintr-o imagine, și se deschide
  în X. Atenție la regula care ascunde etichetele butoanelor din antet: e
  scrisă `span:not(.burger-linii)` tocmai ca să nu înghită iconița.
- **Meniul acoperă ecranul** și pornește din marginea de jos a antetului, cu
  aceeași culoare ca el, bleumarinul mărcii, ca să se citească drept
  continuarea barei. Cât e deschis, nu e nevoie de nimic care să blocheze
  derularea paginii de dedesubt: meniul acoperă tot ecranul, iar
  `overscroll-behavior: contain`, pe `#meniu.is-open`, oprește singur
  scrollul să treacă mai departe.
- **Grilele folosesc `minmax(min(380px, 100%), 1fr)`.** Fără `min()`, o coloană
  de 380px într-un ecran de 393 împinge cardurile în afara paginii pe dreapta.
- **O recenzie pe ecran**, lată exact cât textul de deasupra ei. Nu `100vw`:
  ar include bara de derulare și ar lăsa gutierele inegale.
- **Opțiunile formularului stau două pe rând**, cu textul la stânga. Pașii cu
  cel mult trei opțiuni primesc clasa `putine` și trec pe o coloană, fiindcă
  acolo etichetele sunt propoziții.
- **Totul e aliniat la stânga.** Nimic nu se centrează.

`tools/audit.sh` rulează headless (Chrome, Brave sau Chromium, ce găsește) și
raportează, pe fiecare lățime: contrastul calculat pe fundalul **compus**
(nu pe cel declarat), text tăiat, elemente ieșite din ecran, suprapuneri de
text pe cutii de linie reale și derulare orizontală a paginii. Rulează-l după
orice schimbare de layout sau culoare. Zero peste tot înseamnă curat.

Din 2026-09-07, auditul iese **zero peste tot**. Falsul pozitiv de la butonul
WhatsApp a dispărut de la sine: butonul nu mai e o tentă `color-mix()`, pe care
parserul n-o putea citi, ci verdele plin al aplicației, scris `rgb()`.

## Arhitectura

Trei straturi, în ordinea în care se încarcă. **Nu le amesteca.**

1. `brand/tokens.css`: sistemul de design, culori, tente, sticlă, tipografie,
   colțuri, spațiere **și toate componentele** (`.btn`, `.card`, `.offer`,
   `.tag`, `.meter`, `.chip`, `.bubble`, `.stat`, `.person`, `.avatar`,
   `.navlink`, `.ctaband`, `.field`, `.p-*`). O componentă nouă se adaugă aici.
2. `assets/site.css`: **doar** aranjarea în pagină, secțiuni, grile, antet,
   subsol, hero, carusel, articol. Dacă scrii aici o culoare sau o umbră
   literală, e semn că trebuia să fie un token.
3. Paginile: doar structură și text.

`brand/brandbook.html` e manualul, cu motivele din spatele fiecărei reguli.
Citește-l înainte să schimbi ceva vizual; e publicat și la
https://claude.ai/code/artifact/90cf675a-1888-4355-b5ed-46ee18430677

### Datele reale

`assets/config.js` e **singurul** fișier cu numere de telefon, linkuri sau ID-uri.
`assets/site.js` le leagă prin atribute `data-*` (`data-wa`, `data-phone`,
`data-company`...). Nicio pagină nu conține date de contact. Tot de acolo vin
materiile și profesorii pentru formularul din secțiunea Programare: trei
întrebări (vârstă, materie, singur sau în grupă) care se termină cu un mesaj
de WhatsApp compus din răspunsuri. Calendly a fost scos cu totul.

Cât timp o valoare arată a substituent (`XXX`, `exemplu`, `ID_VIDEO`), elementul
primește automat o etichetă roșie „de completat" în pagină, iar clicul explică
ce lipsește. Verificarea e insensibilă la majuscule; nu o slăbi.

### Zonele pentru PHP

Antetul și subsolul sunt identice octet cu octet în cele patru pagini, ca să
poată fi extrase în `header.php` / `footer.php` fără nicio schimbare de stil.
Dacă modifici unul, modifică-le pe toate patru. `blog.html` și
`blog-articol.html` au comentariile `DE AICI PRELUAT DE PHP` în jurul zonelor
care vor deveni buclă și șablon.

## Capcane care au costat deja timp

- **Clasele `.p-*` se scriu ultimele** în `tokens.css`. `.chip` și `.p-butter`
  au aceeași specificitate, deci decide ordinea. Mutate mai sus, fiecare
  componentă își suprascrie tenta cu alb și nu se vede nicio eroare.
- **`url()` într-o proprietate personalizată CSS** se rezolvă față de foaia de
  stil, nu față de pagină. `--foto:url('assets/...')` folosit din
  `assets/site.css` devine `assets/assets/...` și cade tăcut. Folosește `<img>`.
- **Poiret One are o singură greutate, 400.** Orice `font-weight` mai mare e
  bold sintetic și strică litera. Sub **26px** nu se folosește deloc; acolo
  trece pe Montserrat 600 (regula e deja în `tokens.css`).
- **Peticul pentru `ț` și `Ț` nu se șterge.** Subsetul latin-ext servit de
  Google pentru Poiret One conține `ș` dar nu și `ț`. `@font-face` cu
  `PoiretTComma` livrează cele două glife decupate din fontul complet. Fără el,
  orice cuvânt cu `ț` cade pe fontul de sistem și iese vizibil mai gros.
- **Conturul de 2px nu e decorativ.** Fundalul e alb (`#FCFBF7`) și cardurile
  sunt aproape albe: diferența de ton e 1.04:1. Rama e singurul lucru care le
  separă de pagină.
- **Aurul e măsurat în afiș**, nu ales pe lângă el: `#C2840A` e tonul din
  mijlocul literelor lui „GRATUITĂ", `#E3B34A` lumina de pe ele, `#9C6304`
  umbra, care e și singurul auriu care trece de 4.5:1 pe hârtie, deci singurul
  bun pentru text mic. Aurul și bleumarinul sunt cele două culori ale mărcii.
- **Caligrafia are fontul ei, Great Vibes**, cel mai apropiat de semnătura de
  sub blazon. Intră doar prin `.script` și prin numele mărcii din antet,
  niciodată la text de citit. Se scrie legat: `letter-spacing` rămâne zero,
  altfel se rup legăturile dintre litere.
- **Butonul de WhatsApp poartă verdele lor**, `#25D366`, cu glifa oficială pusă
  ca mască CSS. Textul e cerneala sistemului, nu alb: alb pe verdele lor dă
  1.98:1, cerneala dă 8.78:1.
- **Sistemul e bleumarinul siglei.** `--navy-700` (`#121B52`) și `--navy-900`
  (`#0B1936`) sunt măsurate direct în blazon: prima e cununa și toca, a doua
  panglica și semnătura. Verdele de lauri a ieșit din sistem în 2026-09-06;
  dacă mai găsești `--laurel-*` undeva, e cod rămas în urmă. Cardul alb de sub
  siglă nu mai desparte două închisuri, doar așază blazonul.
- **Tenta de brand e `--p-indigo`** (`#D8DCEC`), nu `--p-sage`. Sage a rămas în
  paletă, dar numai ca tentă de stare: reușit, disponibil.

## Reguli de conținut și stil

- **Fără cratime lungi** (caracterul „em dash”) în text, cod sau documentație. Rescrie propoziția:
  virgulă pentru inciză, punct când sunt două fraze, două puncte pentru
  etichetă-plus-explicație, `·` ca separator în titluri. Cratima scurtă (–)
  rămâne pentru intervale reale (`Luni–vineri`, `VII–VIII`).
- **Fără gradient**, cu două excepții aprobate, amândouă structurale: dunga
  speculară de 1px de pe muchia sticlei și masca care topește marginile
  fotografiilor. A doua nu e o umplere, e o mască: două treceri liniare,
  una pe orizontală și una pe verticală, păstrate doar unde se suprapun, ca
  fiecare latură a cardului să se stingă la fel, iar colțurile de două ori.
  Fără forme colorate în fundal.
- **Titlurile sunt propoziții întregi**, într-un singur `h1`, nu cuvinte în
  casete separate.
- **Contrast minim 4.5:1** pentru text normal. Peste fotografie, calculează
  voalul pornind de la cel mai luminos pixel al imaginii, nu din ochi.

## Ce nu e adevărat în pagină

Firma e înregistrată la **2025-11-03**, deci prima sesiune de examene prin care
a trecut e cea din **iunie 2026**. Orice rezultat datat 2025 e imposibil.

Sunt încă inventate și nu au voie să ajungă publice așa: cifrele din hero
(media 8.40, 92%), cele patru recenzii, prețurile. Fotografia din
`assets/foto/` e o previzualizare Adobe Stock nelicențiată, arată un birou și
nu o școală, și are 640×360. Detalii în `README.md`.

Cele 15 materii din secțiunea „Materii” sunt reale. Numele profesorilor nu
apar nicăieri în pagină: au fost pe carduri o vreme, dar au fost scoase.
Secțiunea „Profesori”, cu trei nume inventate, a fost ștearsă; linkurile către
ea au dispărut din meniu și din subsol.

Fotografia din „Despre” (`assets/foto/despre.jpg`, derivată din `image.png`) e
a școlii, primită de la client: singura cu oameni reali și fără problemă de
licență. Restul, cele 16 de la materii (`assets/foto/materii/`) și cele
patru din „Cum lucrăm” (`assets/foto/motive/`), sunt domeniu public sau CC0,
deci se pot publica. Proveniența fiecăreia e într-un `SURSE.txt` lângă ele și în
tabelele din `README.md`. `clasa.jpg`, previzualizarea Adobe Stock, nu mai e
folosit nicăieri.
