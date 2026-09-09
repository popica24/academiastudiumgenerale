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
assets/foto/materii/  Cele 15 fotografii ale materiilor, plus SURSE.txt
assets/foto/motive/   Cele 4 fotografii din „Cum lucrăm", plus SURSE.txt
tools/audit.sh        Verificarea de contrast, tăiere, suprapunere
tools/voal.py         Cât de gros trebuie voalul peste o fotografie
tools/mobil.sh        Captură la 393px, cât are un iPhone 16
```

## De completat înainte de publicare

Toate în `assets/config.js`, o singură dată pentru tot site-ul:

| Câmp | Ce e | Unde se vede |
|---|---|---|
| `whatsapp` | ✅ `40735433720` | fiecare buton WhatsApp |
| `materii`, `formularMesaj` | ✅ cele 15 materii, profesorii lor și mesajul de WhatsApp | formularul din secțiunea Programare |
| `phone` | ✅ `+40 735 433 720` | subsol |
| `company`, `cui`, `regCom` | ✅ din registrul comerțului | subsol |
| `youtubeId` | ⬜ doar ID-ul din adresa YouTube | secțiunea Video |
| `email`, `address`, `schedule` | ⬜ datele de contact | subsol |
| `facebook`, `instagram` | ⬜ lasă gol ca să dispară din subsol | subsol |

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

Cele patru carduri au fiecare poza lor, în `assets/foto/motive/`, la 1400 px pe
latura lungă. Domeniu public sau CC0, ca și cele de la materii. Proveniența
completă e în `assets/foto/motive/SURSE.txt`.

| Fișier | Cardul | Ce arată | Licență | Sursă |
| --- | --- | --- | --- | --- |
| `parinte.jpg` | Părintele știe ce se întâmplă la curs | Cineva scrie într-un caiet, lângă un laptop | CC0 | Flickr |
| `raport.jpg` | Raport de progres în fiecare lună | O mână parcurge cu pixul un document tipărit | domeniu public | Flickr |
| `recuperare.jpg` | Recuperările nu se pierd | Pagini de calendar, ediție din 1914 | domeniu public | Wikimedia Commons |
| `simulare.jpg` | Simulări în condiții reale | Amfiteatru cu bănci și draperii albastre, studenți așezați la un curs | domeniu public | Wikimedia Commons |

## Fotografiile materiilor

Cele 16 carduri din secțiunea „Materii" au fiecare poza ei, în
`assets/foto/materii/`, la 1100 px pe latura lungă, în jur de 190 KB fiecare,
2,8 MB cu totul. **Toate sunt domeniu public sau CC0**, adică se pot folosi
comercial fără atribuire și fără plată, spre deosebire de `clasa.jpg`.
Proveniența completă, cu link către pagina fiecărui fișier, e în
`assets/foto/materii/SURSE.txt`.

| Fișier | Ce arată | Licență | Sursă |
| --- | --- | --- | --- |
| `romana.jpg` | Scrisoarea lui Neacșu din Câmpulung, 1521, cel mai vechi text păstrat în limba română | domeniu public | Wikimedia Commons |
| `mate-gimnaziu.jpg` | Abac folosit într-un magazin din Otaru | CC0 | Wikimedia Commons |
| `mate-liceu.jpg` | Tablă cu ecuații la un curs de matematică | domeniu public | Wikimedia Commons |
| `istorie.jpg` | Relief de pe Columna lui Traian | domeniu public | Wikimedia Commons |
| `geografie.jpg` | Harta lui Mercator, ediția Lucas 1898 | domeniu public | Wikimedia Commons |
| `logica.jpg` | Piese de șah pe tablă | CC0 | Rawpixel |
| `biologie.jpg` | Obiectivul unui microscop | domeniu public | Flickr |
| `chimie.jpg` | Sticlărie de laborator | domeniu public | Flickr |
| `engleza.jpg` | Cabine telefonice roșii, Londra | CC0 | Wikimedia Commons |
| `spaniola.jpg` | Plaza de España, Sevilla | CC0 | Rawpixel |
| `franceza.jpg` | Turnul Eiffel | domeniu public | Flickr |
| `chineza.jpg` | Caligrafie chinezească în stil semicursiv | CC0 | Wikimedia Commons |
| `coreeana.jpg` | Streașină de templu coreean, Naksansa | CC0 | Rawpixel |
| `maghiara.jpg` | Parlamentul Ungariei, Budapesta | CC0 | Wikimedia Commons |
| `informatica.jpg` | Programarea calculatorului ENIAC, 1946 | domeniu public | Wikimedia Commons |
| `greaca.jpg` | Coloanele unui templu grecesc | CC0 | Rawpixel |

Ca să schimbi una: pui fișierul cu același nume peste cel vechi. Cardul îl
folosește de două ori, o dată estompat ca fundal și o dată clar, deci nu e
nimic de schimbat în HTML. Dacă poza are subiectul descentrat, se adaugă pe
`<article>` un `style="--foto-pos:70% 40%"`.

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
