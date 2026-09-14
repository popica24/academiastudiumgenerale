# Academia · Studium Generale by Denisa

Site static, fără backend. Tot ce ține de aspect vine din manualul de brand;
paginile nu conțin culori, mărimi sau umbre scrise de mână.

## Fișiere

```
index.html            Landing: hero, metodă, materii (15 carduri), video,
                      recenzii (carusel), programare
despre.html           Pagina cu descrierea („O pagina cu o descriere")
blog.html             Lista de articole
blog-articol.html     Șablonul unui articol

brand/tokens.css      Sistemul: culori, tente, sticlă, tipografie, componente
brand/brandbook.html  Manualul de brand. De citit înainte de orice schimbare
assets/site.css       Doar aranjarea în pagină. Fără componente noi.
assets/config.js      SINGURUL loc cu date reale
assets/site.js        WhatsApp, formularul de potrivire, video, carusel, meniu,
                      AOS, numerele care urcă din hero
assets/logo.webp      Sigla
assets/og.jpg         Imaginea de previzualizare pentru linkuri
assets/fonturi/       Fonturile, subsetate pentru română
assets/foto/materii/  Cele 16 iconițe ale materiilor, plus SURSE.txt
assets/foto/motive/   Cele 4 fotografii din „Cum lucrăm", plus SURSE.txt
assets/foto/cursuri/  Cele 5 iconițe ale cursurilor speciale, plus SURSE.txt
assets/foto/profesori/ Cele 8 portrete de profesor, plus SURSE.txt
tools/audit.sh        Verificarea de contrast, tăiere, suprapunere
tools/voal.py         Cât de gros trebuie voalul peste o fotografie
tools/mobil.sh        Captură la 393px, cât are un iPhone 16
tools/taie-colaj.py   Taie cele 21 de iconițe din colajul primit de la client
tools/taie-profesori.py Taie cele 8 portrete din capturile de pe Instagram
```

## De completat înainte de publicare

Toate în `assets/config.js`, o singură dată pentru tot site-ul:

| Câmp | Ce e | Unde se vede |
|---|---|---|
| `whatsapp` | ✅ `40735433720` | fiecare buton WhatsApp |
| `materii`, `formularMesaj` | ✅ cele 15 materii, profesorii lor și mesajul de WhatsApp | formularul din secțiunea Programare |
| `phone` | ✅ `+40 735 433 720` | subsol |
| `company`, `cui`, `regCom` | ✅ din registrul comerțului | subsol |
| `email`, `address` | ✅ datele de contact | subsol |
| `schedule` (în `texte.ro`/`texte.en`) | ✅ `Luni–vineri, 08:00–21:00`, tradus | subsol, FAQ |
| `facebook`, `instagram` | ✅ adresele reale, sau lasă gol ca să dispară din subsol | subsol |
| `cameraComert` | ✅ linkul de membru la Camera de Comerț Elenă-Română | subsol |

**Despre formular:** cele trei întrebări din secțiunea Programare se hrănesc din
`materii`. O materie cu `pana: 14` apare doar la gimnaziu, una cu `de: 15` doar
la liceu, restul la orice vârstă. La capăt, `formularMesaj` se completează cu
răspunsurile (`%MATERIE%`, `%VARSTA%`, `%MOD%`) și deschide WhatsApp. Ca să
schimbi întrebările sau ordinea lor, `PASI` din `assets/site.js`.

Cât timp o valoare e necompletată, elementul primește o etichetă roșie
**„… de completat"** direct în pagină, iar clicul explică ce lipsește.
Etichetele dispar singure când valorile devin reale.

## Ce mai e de înlocuit

> ⚠️ **Cifrele și recenziile de pe pagina principală sunt inventate.**
> Firma e înregistrată la **3 noiembrie 2025**, deci prima sesiune de examene
> prin care a trecut e cea din **iunie 2026**. Textele au fost corectate la
> 2026, dar rămân exemple de așezare în pagină, nu rezultate reale. Media,
> procentul de creștere și cele patru recenzii trebuie înlocuite cu date
> adevărate sau șterse înainte de publicare.

- **Recenziile** din `index.html` sunt exemple de așezare. Se înlocuiesc cu
  cele reale înainte de publicare.
- **`assets/foto/clasa.jpg` nu mai e folosit de nicio pagină.** Era pe toate
  cele patru carduri din „Cum lucrăm", e o previzualizare Adobe Stock
  (`t4.ftcdn.net`) **nelicențiată pentru producție**, arată un birou și are
  640×360. Acum fiecare card are poza lui. Fișierul a rămas pe disc, dar se
  poate șterge oricând.
- **Prețurile** sunt plauzibile, nu reale. Cele 15 materii din secțiunea
  „Materii" sunt reale. Numele profesorilor nu apar în pagină; la Maghiară
  profesorul e încă necunoscut, vezi `materii` în `assets/config.js`.

## Fotografia din „Despre"

`assets/foto/image.png`, poza primită de la client, are 3.1 MB și 1638×2048.
Pagina folosește o derivată, `assets/foto/despre.webp`: 719×900, 40 KB, aceeași
poză. Originalul a rămas pe disc, neatins, dar nu îl încarcă nicio pagină; se
poate șterge sau păstra ca sursă pentru alte tăieturi.

E singura fotografie din proiect cu oameni reali și fără problemă de licență:
nu vine dintr-un fond de imagini, e a școlii. `<figcaption>` e gol: se
completează când se știe cine e în poză.

## Fotografiile din „Cum lucrăm"

Cele patru carduri au fiecare poza lor, în `assets/foto/motive/`, la 1100 px pe
latura lungă. Domeniu public sau CC0. Sunt singurele fotografii din carduri
rămase în pagină, materiile trecând între timp pe iconițe. Proveniența completă
e în `assets/foto/motive/SURSE.txt`.

Clientul a cerut ca acest card să predomine cu albastru. Trei din patru
ieșeau calde pe fișierul brut și au primit, la 2026-09-10, un viraj rece:
gri pe luminanță, cu autocontrast, apoi colorizare pe trei puncte, aceiași
parametri pentru toate trei. Fișierele calde, dinainte de viraj, rămân
recuperabile din istoricul git. `simulare.jpg` nu a fost atinsă, era deja
albastră.

| Fișier | Cardul | Ce arată | Licență | Sursă | Prelucrare |
| --- | --- | --- | --- | --- | --- |
| `parinte.jpg` | Părintele știe ce se întâmplă la curs | Cineva scrie într-un caiet, lângă un laptop | CC0 | Flickr | viraj rece |
| `raport.jpg` | Raport de progres în fiecare lună | O mână parcurge cu pixul un document tipărit | domeniu public | Flickr | viraj rece |
| `recuperare.jpg` | Recuperările nu se pierd | Pagini de calendar, ediție din 1914 | domeniu public | Wikimedia Commons | viraj rece |
| `simulare.jpg` | Simulări în condiții reale | Amfiteatru cu bănci și draperii albastre, studenți așezați la un curs | domeniu public | Wikimedia Commons | neatinsă |

## Iconițele materiilor și ale cursurilor

Cele 16 carduri din „Materii" și cele 5 din „Cursuri speciale" nu mai poartă
fotografii, ci câte o iconiță rotundă. Toate 21 vin dintr-un singur colaj
primit de la client la 2026-09-10, păstrat ca `assets/foto/materii-colaj.png`,
și sunt decupate din el cu `tools/taie-colaj.py`: fiecare cerc ajunge într-un
pătrat de 600×600, așezat pe același bleumarin `--navy-900` cu fundalul
cardului, ca să nu rămână colțuri albe. Între 15 și 27 KB fiecare, 484 KB cu
totul, față de 1,1 MB cât aveau fotografiile.

Numele materiei e scris chiar în iconiță. De aici vin trei lucruri:

- **Titlul cardului a trecut pe `.sr-only`.** Rămâne în pagină, pentru
  cititoarele de ecran și pentru Google, dar nu se mai vede scris de două ori.
  Pe `en/index.html` numele rămâne în română, fiindcă e desenat în fișier, iar
  fișierele sunt aceleași; titlul citit de cititoarele de ecran e tradus.
- **Cardul e pătrat**, nu de 210px înălțime ca înainte. Cercul trebuie să
  încapă întreg, altfel numele din el coboară sub 9px și nu se mai citește.
- **Grila lor e `.grid-materie`, nu `.grid-3`.** Patru coloane, ca `.grid-3`,
  dar cu pragul la 250px, ca să nu treacă niciodată la cinci: un card de 261px
  are numele scris în iconiță la vreo 12px, iar mai mic de atât nu se mai
  citește.

Cardul nu mai are nici voal, nici topirea marginilor: poza vine deja tăiată pe
culoarea cardului, deci nu e nimic de topit, iar voalul ar stinge auriul
degeaba. Stratul estompat rămâne în HTML, dar e ascuns din CSS: nu are ce
umple, poza acoperă cardul până în colțuri.

**Limita lor e rezoluția.** În colaj fiecare cerc are 243px, deci fișierele de
600px sunt mărite de 2,5 ori. La lățimea cardului, 261px pe desktop, sunt de
fapt micșorate și se văd curat; pe telefon, unde cardul ajunge la 353px, se
vede că literele sunt moi. Dacă vine un colaj mai mare
sau câte un fișier per materie, se pune peste `materii-colaj.png` și se rulează
din nou `tools/taie-colaj.py`; nu e nimic de schimbat în HTML.

Ca să schimbi o singură iconiță: pui fișierul cu același nume peste cel vechi,
pătrat și pe bleumarinul cardului. Cardul îl folosește de două ori, o dată
estompat ca fundal și o dată clar, dar la materii se vede doar al doilea.

Fotografiile de dinainte, cele 16 de la materii și cele 5 de la cursuri, erau
domeniu public sau CC0 și au fost înlocuite peste, cu același nume. Nu le mai
folosește nicio pagină; se recuperează din istoricul git, iar proveniența lor
scrisă rămâne în comitul de dinainte de 2026-09-10.

## Profesorii

Secțiunea „Profesori" din `index.html` și `en/index.html` are opt carduri,
`.person.person-foto`, componenta fiind în `tokens.css`. Fotografiile sunt
portrete rotunde în `assets/foto/profesori/`, decupate din capturi ale
postărilor de pe Instagramul școlii cu `tools/taie-profesori.py`; textele sunt
scurtate din aceleași postări, unde fiecare profesor s-a prezentat singur.
Proveniența, fișier cu fișier, e în `assets/foto/profesori/SURSE.txt`.

Cardul e un rând orizontal: cercul cu poza, apoi numele în Poiret One,
materia ca `.tag`, o linie de pregătire și, pe toată lățimea cardului, o frază
din postarea lui. Grila e `.grid-profesori`, două pe rând, fiindcă citatul are
nevoie de măsură; pe telefon poza urcă deasupra textului. Cardul compact din
brandbook, `.person` simplu, rămâne cum era: e varianta fără fotografie.

**Ce nu e gata în secțiunea asta:**

- **Două postări nu spun numele profesorului**, cel de română și cel de
  biologie. Cardurile lor poartă eticheta roșie „Numele de completat", prin
  `data-nedefinit`, ca orice altă valoare care lipsește. Numele profesoarei de
  română se citește pe diploma din fotografia ei, dar nu a fost luat de acolo:
  școala nu l-a scris în text.
- **Trei materii predate aici nu apar în secțiunea „Materii":** araba, artele
  plastice și psihologia cu logopedie. Ori intră între materii, ori între
  cursurile speciale, ori profesorii lor ies din pagină. Nota din josul
  secțiunii spune asta pe șleau, ca să nu se publice așa.
- **Prezentarea profesoarei de franceză e scrisă în engleză** pe Instagram.
  Pagina românească o dă tradusă, pagina engleză o dă în original.
- **Două postări anunță 150 de lei pe ședință.** Prețul nu a fost trecut în
  pagină: site-ul nu are încă o secțiune de prețuri, iar cele din discuții sunt
  plauzibile, nu reale.

Capturile brute nu sunt în git, sunt 75 MB de PNG; vezi `.gitignore`.

## Când vine backendul PHP

Două zone sunt deja marcate cu comentarii în HTML:

- `blog.html` → `DE AICI PRELUAT DE PHP`: bucla înlocuiește cele trei
  carduri `a.card.articol-card`.
- `blog-articol.html` → `ȘABLON DE ARTICOL`: titlu, tag, dată, autor și corp.

Antetul și subsolul sunt identice în cele patru pagini, ca să poată fi
extrase în `header.php` / `footer.php` fără nicio modificare de stil.

## SEO

`robots.txt`, `sitemap.xml` și metadatele din `<head>`: titlu, descriere,
canonic, Open Graph (cu `assets/og.jpg`, 1200×630) și Twitter card. Datele
structurate, JSON-LD de tip `EducationalOrganization` cu adresă, telefon,
program, catalogul de materii și firul Ariadnei, se construiesc din
`assets/config.js`, deci nu se pot desincroniza de datele reale.

**Toate adresele arată spre `https://studiumgenerale.ro`,** care nu e încă
înregistrat. Se schimbă în cinci locuri: cele patru pagini, `sitemap.xml`,
`robots.txt` și `assets/site.js`.

## Singura dependență

**AOS 2.3.4**, animația la derulare, de pe `cdnjs.cloudflare.com`, cu hash de
integritate în fiecare pagină. Ce trebuie știut:

- dacă CDN-ul nu răspunde, clasa `fara-aos` de pe `<html>` readuce la vedere tot
  ce urma să fie animat, deci pagina rămâne întreagă, doar fără mișcare;
- hero-ul nu e animat, ca titlul să nu depindă de un fișier extern;
- la `prefers-reduced-motion: reduce`, AOS se dezactivează singur;
- `tools/audit.js` îl neutralizează înainte să măsoare.

Ca să scoți AOS: ștergi cele două etichete din `<head>` și dinaintea lui
`config.js` în cele patru pagini, secțiunea 7 din `site.js`, regula `fara-aos`
din `site.css` și atributele `data-aos` din HTML.

## Reguli care nu se încalcă

Detaliile sunt în manual (`brand/brandbook.html`), dar pe scurt:

1. **Fără gradient** și fără forme colorate în fundal, cu trei excepții, toate
   structurale: dunga de 1px de pe muchia sticlei, masca ce topește marginile
   fotografiilor și fundalul paginii, care se răcește discret spre josul ei.
2. **Fiecare obiect are contur** de 2px navy 900. Fundalul e aproape alb și
   curat, deci rama e singurul lucru care separă un card de pagină. Nu se
   scoate.
3. **Butonul de WhatsApp e verdele lor** (`#25D366`) cu glifa oficială, iar
   textul de pe el e cerneala sistemului, nu alb.
4. **Culorile vin din siglă:** `--navy-700` (`#121B52`) și `--navy-900`
   (`#0B1936`), măsurate în blazon. Pe o suprafață bleumarin plină, sigla trece
   integral pe alb.
5. **Poiret One nu coboară sub 26px** și nu are decât greutatea 400.
   Sub prag se folosește Montserrat 600.
6. **Nu se șterge peticul pentru „ț"** din `tokens.css`. Subsetul Google al
   lui Poiret One nu conține ț și Ț; fără petic, orice cuvânt cu ț arată reparat.
7. Componente noi se adaugă în `tokens.css`, nu în `site.css`.

## Rulare locală

Fără build. Se deschide `index.html` în browser, sau:

```
python3 -m http.server 8000
```
