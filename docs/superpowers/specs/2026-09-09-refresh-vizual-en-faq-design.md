# Refresh vizual, cursuri speciale, FAQ și versiune engleză

Data: 2026-09-09
Stare: aprobat în discuție, gata de plan de implementare

## Ce se schimbă și de ce

Clientul a cerut, într-o singură listă, trei feluri de schimbări: o revizuire
vizuală a antetului și a fundalului, un set de corecturi de text, și trei
lucruri noi în pagină (cursuri speciale, FAQ, versiune engleză). Documentul
le ține la un loc fiindcă se ating: antetul bleumarin schimbă meniul de
telefon, versiunea engleză dublează antetul, iar cursurile noi intră și în
formular, nu doar în grilă.

Patru decizii au fost luate în discuție și nu se redeschid aici:

1. Numele mărcii devine auriu; linkurile din antet rămân cu text bleumarin.
2. Engleza se face din pagini separate în `/en/`, nu dintr-un comutator JS.
3. Cursurile speciale primesc secțiunea lor, sub Materii.
4. Gradientul de fundal e acceptat ca a treia excepție de la regula
   „fără gradient", iar regula se rescrie în consecință.

## A. Antet, meniu și fundal

### A1. Bara devine bleumarin plin

`.site-head` trece de la sticlă la `background: var(--navy-700)`, adică exact
culoarea butonului primar. `backdrop-filter` se scoate: nu mai are ce filtra
pe sub o suprafață opacă, iar pe telefon filtrul făcea din antet blocul de
referință pentru meniul `fixed`, cu toate capcanele documentate în `site.css`.
Conturul de jos rămâne. Dunga speculară de 1px rămâne, e excepția aprobată.

`<meta name="theme-color">` e deja `#121B52` în toate paginile, deci bara
browserului se potrivea deja cu o bară care abia acum devine bleumarin.

### A2. Numele mărcii, auriu

`.brand-name` primește `color: var(--brass-300)` (`#E3B34A`). Fontul rămâne
Great Vibes, era deja acolo. Raportul pe `--navy-700` e 8.29:1, valoarea
măsurată deja pentru `.ctaband .script`, unde același auriu stă pe același
bleumarin.

`.brand-sub` primește și el auriu, dar **rămâne Montserrat**. Cererea era
„același font ca la Studium Generale by Denisa din banda de CTA", însă
subtitlul are 11px, majuscule și `letter-spacing`, iar Great Vibes are ochiul
foarte mic: la 11px nu se mai citește. Decizia s-a comunicat clientului cu
alternativa (Great Vibes la 16px, fără majuscule) și a rămas așa.
Subtitlul primește `--brass-200` (`#EBCF8B`): 10.6:1 pe `--navy-700`, măsurat.
Marja e mare tocmai fiindcă textul are 11px, unde pragul contează cel mai mult.

Cardul alb de sub siglă (`.brand-mark`) rămâne alb. Sigla e bleumarin, deci
pe o bară bleumarin ar dispărea fără el. Regula asta e din brandbook, placa 01.

### A3. Linkurile: sticla veche pe bleumarin nou

`.navlink` păstrează fundalul pe care îl are bara acum, adică
`--glass-fill-soft` sau `--glass-fill`, cu text `--text` (bleumarin).
La hover transparența dispare: `background: #fff`, restul stării (ridicarea
de 2px și umbra) rămâne cum e.

**Opacitatea e măsurată, nu ghicită.** Compunerea albului peste `--navy-700`
și cerneala `--navy-900` peste rezultat:

| sticlă | compus | cerneala peste ea |
|---|---|---|
| `--glass-fill-soft`, 52% | `#8D92AC` | 5.67:1 |
| `--glass-fill`, 62% | `#A5A8BD` | 7.41:1 |
| `--glass-fill-deep`, 82% | `#D4D6E0` | 12.01:1 |

Se alege `--glass-fill` (62%): trece confortabil și păstrează bara vizibil
bleumarin pe sub linkuri, ceea ce e chiar efectul cerut. `tools/audit.sh`
confirmă pe fundalul compus, fiindcă asta calculează el.

`.navlink[aria-current="page"]` nu mai poate fi `--navy-700` pe `--navy-700`:
pagina curentă devine alb plin cu text bleumarin, adică starea de hover
făcută permanentă.

Hamburgerul desenează liniile din `currentColor`, deci `.burger` primește
`color: var(--brass-300)` și iese auriu fără altă schimbare. Butonul de
WhatsApp nu se atinge: verdele `#25D366` cu cerneală peste el e regulă de
marcă și dă 8.78:1 indiferent de bara de sub el.

Linkul „Sari la conținut" e un `.btn` normal și devine vizibil la focus peste
bara bleumarin: se verifică la audit că nu iese cu contrast prea mic.

### A4. Consecința pe telefon

Meniul pe tot ecranul (`#meniu.is-open` sub 560px) folosește acum `--bg`,
hârtia paginii, tocmai ca să se citească drept continuarea barei. Cu bara
bleumarin, regula se inversează: meniul deschis devine `--navy-700`, iar
`#meniu .navlink` din interiorul lui primește text auriu, fără fundal, cu
linia despărțitoare într-un bleumarin mai deschis (`--navy-500`) în loc de
`--border`, care e o piatră deschisă și n-ar mai fi vizibilă.

`html.meniu-deschis .site-head` stingea `backdrop-filter` și punea `--bg`;
regula se rescrie pe bleumarin. Blocul de 860px, unde meniul e un panou
plutitor și nu ecran plin, primește același tratament: `--glass-fill-deep`
peste bleumarin nu mai are sens, panoul devine `--navy-700` cu contur.

### A5. Gradientul de fundal

`body` primește un gradient vertical de la `--paper` (`#FCFBF7`) sus către
`#F3F4F7` jos, un albastru foarte spălăcit. Se atașează cu
`background-attachment: fixed`, ca tranziția să fie citită ca lumină pe hârtie
și nu ca o bandă care se mișcă la derulare.

Capătul de jos nu e ales din ochi. `--navy-50` (`#EDEFF7`), albastrul cel mai
deschis din sistem, ar fi părut candidatul evident, dar pe el textul estompat
`--stone-500` (`#726F67`) cade la 4.37:1, adică sub prag. Măsurat pas cu pas
pe drumul de la hârtie la `--navy-50`:

| capăt | `--stone-500` peste el |
|---|---|
| `#FCFBF7`, hârtia de acum | 4.85:1 |
| `#F6F6F7` | 4.65:1 |
| `#F3F4F7`, capătul ales | 4.56:1 |
| `#F0F1F7` | 4.45:1, cade |
| `#EDEFF7`, `--navy-50` | 4.37:1, cade |

Deci albastrul poate merge cel mult 60% din drum. Asta stabilește cât de
subtil trebuie să fie gradientul, și e chiar ce a cerut clientul.

**Fallback-ul plat nu e opțional.** `tools/audit.js`, linia 30, citește
`getComputedStyle(n).backgroundColor`. Un gradient stă în `background-image`,
deci `backgroundColor` ar întoarce transparent și tot lanțul de compunere a
fundalului pentru calculul contrastului ar deveni oarb, fără să dea eroare.
Se declară deci și `background-color`, cu **capătul albastru** `#F3F4F7`, nu
cu tonul din mijloc: așa auditul are ce citi și măsoară peste tot cazul cel
mai rău, nu unul mediu care ar ascunde josul paginii.

### A6. Regula de brand se rescrie

`CLAUDE.md`, secțiunea „Reguli de conținut și stil", și `brand/brandbook.html`
trec de la două excepții la trei. Se scrie motivul: fundalul paginii e
singurul loc unde un gradient nu desenează un obiect, ci descrie lumina pe
suportul pe care stau toate obiectele. Componentele rămân fără gradient.

## B. Corecturi de text

Toate în `index.html`, cu o singură atingere în `despre.html`.

| unde | acum | devine |
|---|---|---|
| titlul secțiunii Materii | Șaisprezece materii, o singură școală | Toate materiile, o singură academie |
| paragraful de la Programare | Nu cereți nimic pe încredere | Nu faceți nimic pe încredere |
| cardul Simulări | Lunar pentru grupele de Bacalaureat | Pentru grupele de Bacalaureat |

Fraza despre prima ședință apare în cinci locuri și se schimbă în toate:

- `index.html:7`, `<meta name="description">`
- `index.html:88`, nota din hero
- `index.html:344`, paragraful de la Programare
- `index.html:360`, titlul din banda de CTA
- `despre.html:129`, „Veniți la o discuție, apoi decideți"

Forma lungă, pentru banda de CTA:
**„Prima ședință nu e o lecție: ne cunoaștem, stabilim obiective, stabilim
rezonanța"**. Unde nu încape întreagă (meta description, nota din hero),
se scurtează păstrând cele trei verbe, fiindcă ele sunt cererea. Nota din hero
devine: „Prima ședință durează 50 de minute: ne cunoaștem, stabilim obiective
și stabilim rezonanța. Nu costă nimic."

Titlul din `despre.html` devine „Veniți să ne cunoaștem, apoi decideți",
ca să nu rămână singurul loc care mai spune „discuție".

### B1. Ordinea secțiunilor

Secțiunea `#video` urcă deasupra secțiunii `#metoda`. E o mutare de bloc în
`index.html`, fără schimbare de stil. `data-aos-delay` din cardurile de motive
nu se atinge, întârzierile sunt relative la intrarea fiecărui card în ecran,
nu la poziția secțiunii în pagină.

## C. Secțiuni noi

### C1. Cursuri speciale

Secțiune nouă în `index.html`, imediat sub `#materii`, cu `id="cursuri"`.
Cinci carduri `foto-card materie`, aceeași mecanică cu două `<img>` (stratul
estompat și cel clar) ca la materii: Excel, Contabilitate, Dicție,
Dezvoltare personală, Educație financiară.

Titlu: „Cursuri speciale". Supratitlu: „Dincolo de programă". Un paragraf
scurt care spune că nu sunt materii de examen, ci cursuri care se pot lua
separat.

Pozele: cinci fișiere noi în `assets/foto/materii/`, aceeași convenție de
nume (`excel.webp`, `contabilitate.webp`, `dictie.webp`, `dezvoltare.webp`,
`financiara.webp`), aceeași cerință de licență, adăugate în `SURSE.txt` și în
tabelul din `README.md`.

Meniul din antet și lista din subsol primesc un link către `#cursuri`.

### C2. Cursurile în formular

`config.js` capătă o listă nouă, `cursuriSpeciale`, cu aceeași formă ca
`materii` (`nume`, `profesori`). Pasul cu materia din formular afișează două
grupuri, cu un titlu mic peste fiecare: „Materii" și „Cursuri speciale".

Cine predă cursurile speciale nu se știe. `profesori` rămâne `[]`, exact ca
la Maghiară acum, iar mecanismul existent pune singur eticheta roșie
„de completat". Nu se inventează nume.

Pasul de vârstă rămâne pentru toate opțiunile: e o singură cale prin formular,
nu două, iar vârsta e utilă și pentru un curs de dicție.

`hasOfferCatalog` din JSON-LD (`site.js`, secțiunea 9) citește azi doar
`C.materii`. Se extinde ca să adune și `C.cursuriSpeciale`, altfel Google ar
vedea o ofertă din care lipsesc cinci lucruri.

### C3. FAQ

Secțiune nouă în `index.html`, cu `id="intrebari"`, așezată între `#recenzii`
și `#programare`. Structură: un `<details>` per întrebare, cu `<summary>`
ca întrebare. Nu se folosește JavaScript pentru deschidere; `<details>` face
asta nativ, e accesibil din tastatură fără nicio linie de cod, și funcționează
și dacă scriptul nu ajunge.

Stilul intră în `tokens.css` ca o componentă nouă, `.faq`, nu în `site.css`:
e un obiect, nu o așezare în pagină. Se scrie **înaintea** blocului cu clasele
`.p-*`, fiindcă acelea trebuie să rămână ultimele.

Șase întrebări, redactate strict din ce e deja adevărat pe site:

1. Câți elevi sunt într-o grupă?
2. Ce se întâmplă dacă elevul lipsește de la o ședință?
3. Ce conține raportul lunar și cine îl primește?
4. Cum arată prima ședință?
5. Ce materii se predau?
6. Unde se țin ședințele și în ce interval?

Nu se scrie nimic despre prețuri, contract, durata angajamentului sau
garanții: nu există date. Secțiunea primește o notă vizibilă „de confirmat
înainte de publicare", în același ton cu nota de sub recenzii, care se șterge
după ce clientul citește răspunsurile.

JSON-LD: `site.js`, secțiunea 9, capătă un al doilea obiect, de tip `FAQPage`,
construit **citind DOM-ul secțiunii**, nu dintr-o listă duplicată în
`config.js`. Motivul e regula proiectului: un text scris în două locuri se
desincronizează la prima corectură. Se emite doar dacă secțiunea există în
pagină, deci `despre.html` și blogul nu îl capătă.

### C4. Subsol: Camera de Comerț și rețele

`config.js` capătă `cameraComert`, adresa paginii de membru. Clientul o dă
mai târziu, deci valoarea de start e `"XXX"`, care declanșează automat
eticheta roșie „de completat" prin `PLACEHOLDER` din `site.js`, linia 9.

`facebook` și `instagram` se completează cu adresele primite. Legarea lor
există deja (`site.js`, linia 321), deci linkurile apar singure în subsol și
intră singure în `sameAs` din JSON-LD.

Rândul de jos al subsolului capătă „Membru Oficial Camera de Comerț", cu
„Camera de Comerț" ca link, legat printr-un atribut `data-camera` nou, în
aceeași manieră cu `data-phone` și `data-email`. Nicio adresă nu se scrie în
HTML.

## D. Pozele albastre din „Cum lucrăm"

Cele patru fotografii din `assets/foto/motive/` se înlocuiesc cu fotografii în
care albastrul domină. Subiectele rămân aceleași, ca textul cardurilor să
continue să descrie ce se vede.

Constrângeri, în ordinea în care contează:

1. **Licența.** Doar domeniu public sau CC0, ca acum. Dacă pentru un card nu
   se găsește o fotografie albastră cu licență curată, cardul rămâne cu poza
   lui de acum și se raportează, nu se folosește ceva nesigur.
2. **Mărimea.** `.webp`, la mărimea la care se afișează, ca restul.
3. **Voalul.** `--voal-foto` de pe fiecare card se recalculează cu
   `tools/voal.py --paragraf`, pornind de la cel mai luminos pixel al noii
   imagini. Valorile de acum (0.48 până la 0.60) nu se transferă: sunt legate
   de pozele vechi.
4. **Evidența.** `assets/foto/motive/SURSE.txt` și tabelul din `README.md` se
   rescriu cu proveniența nouă.

Pozele vechi se șterg din depozit după ce cele noi sunt în pagină; nu rămân
fișiere pe care nu le încarcă nimeni, cu excepția celor deja documentate ca
surse mari (`image.png`, `clasa.jpg`).

## E. Versiunea engleză

### E1. Ce se traduce

`/en/index.html` și `/en/despre.html`. Blogul rămâne doar în română: sunt
machete pentru un backend care nu există, iar traducerea lor ar fi text care
se aruncă. Linkul „Blog" din meniul englez trimite la `../blog.html`.

### E2. Legăturile dintre versiuni

Fiecare pagină primește, în `<head>`:

- `hreflang="ro"` către varianta românească
- `hreflang="en"` către varianta engleză
- `hreflang="x-default"` către varianta românească

Legăturile se scriu în ambele sensuri: și paginile românești le capătă.
Google ignoră o pereche `hreflang` care nu se confirmă reciproc.

Fiecare pagină engleză are `canonical` propriu, `og:locale` `en_US`,
`og:locale:alternate` `ro_RO`, și `lang="en"` pe `<html>`.

`sitemap.xml` capătă cele două adrese noi.

### E3. Comutatorul RO/EN

În antet, lângă butonul de WhatsApp, o pereche de linkuri scurte. Pagina
curentă e marcată cu `aria-current`. Pe telefon intră în meniu, nu în bară:
bara are deja siglă, hamburger și WhatsApp, iar regula de la 393px e că nu
mai încape nimic.

Adresa perechii se scrie în HTML, nu se calculează în JS: sunt patru pagini,
nu patru sute, iar un comutator care depinde de script ar dispărea dacă
scriptul nu ajunge.

### E4. Textele formularului

`config.js` capătă o tabelă `texte`, cu două chei, `ro` și `en`, care ține
etichetele pașilor, întrebările și `formularMesaj`. `site.js` alege ramura
după `document.documentElement.lang`.

Numele materiilor se traduc și ele, printr-un câmp `numeEn` opțional pe
fiecare intrare din `materii` și `cursuriSpeciale`. Când lipsește, se
folosește numele românesc: „Excel" nu are traducere.

**Datele de contact nu se dublează.** Telefon, e-mail, adresă, CUI și rețele
rămân o singură dată în `config.js`. Regula proiectului rămâne intactă:
un singur fișier cu date reale.

`whatsappMessage` capătă și el o variantă engleză, ca vizitatorul englez să
deschidă conversația într-o limbă pe care o poate continua.

### E5. Costul, spus înainte

Antetul și subsolul sunt azi identice octet cu octet în cele patru pagini
românești, ca să poată fi extrase în `header.php` fără nicio schimbare de
stil. Paginile engleze **nu pot** fi identice cu ele: căile către `assets/`
și `brand/` capătă `../`, iar textele sunt traduse.

Regula devine: identice între cele patru românești, și separat identice între
cele două englezești. De acum, un text din antet se schimbă în șase fișiere.
`CLAUDE.md`, secțiunea „Zonele pentru PHP", se actualizează cu asta, altfel
următoarea sesiune va crede că sunt patru.

## Verificare

Nimic nu se declară gata fără dovadă rulată:

1. `tools/audit.sh` pe toate paginile, inclusiv cele două engleze, la 1440,
   900 și 560. Zero peste tot. Auditul acoperă contrastul pe fundal compus,
   textul tăiat, ieșirile din ecran, suprapunerile și derularea orizontală.
2. `tools/mobil.sh` la 393px pe `index.html`, pentru meniul bleumarin deschis
   și pentru cele două secțiuni noi.
3. Verificare cu CDN-ul blocat, ca pagina să rămână întreagă fără AOS.
4. Verificare la `prefers-reduced-motion: reduce`.
5. JSON-LD trecut prin validatorul Google pentru cele două tipuri,
   `EducationalOrganization` și `FAQPage`.
6. Verificare vizuală că numele mărcii nu s-a rupt pe două rânduri pe
   niciuna dintre lățimi, acum că are și un comutator de limbă lângă el.

## Ce rămâne neadevărat în pagină

Lista din `CLAUDE.md` nu se scurtează prin munca asta și se lungește cu două
intrări:

- răspunsurile din FAQ, până le confirmă clientul
- profesorii cursurilor speciale, marcați „de completat"
- adresa paginii de membru al Camerei de Comerț, marcată „de completat"

Cifrele din hero, cele patru recenzii și prețurile rămân inventate, ca acum.
