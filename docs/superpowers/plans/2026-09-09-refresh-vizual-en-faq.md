# Refresh vizual, cursuri speciale, FAQ și versiune engleză: plan de implementare

> **Pentru cine execută:** SUB-SKILL OBLIGATORIU: folosește
> superpowers:subagent-driven-development (recomandat) sau
> superpowers:executing-plans ca să duci planul task cu task. Pașii au
> casete (`- [ ]`) ca să se poată bifa.

**Scop:** antet bleumarin cu nume auriu, fundal cu gradient subtil, corecturi
de text, secțiune de cursuri speciale, FAQ, subsol cu rețele și Camera de
Comerț, poze albastre la „Cum lucrăm", și o versiune engleză a paginilor
Acasă și Despre.

**Arhitectura:** site static, fără build și fără framework. Trei straturi de
CSS care nu se amestecă: `brand/tokens.css` ține sistemul de design și toate
componentele, `assets/site.css` ține doar așezarea în pagină, paginile țin
doar structură și text. Datele reale stau exclusiv în `assets/config.js` și
sunt legate în pagini prin atribute `data-*` de către `assets/site.js`.

**Unelte:** HTML, CSS și JavaScript scris de mână. `tools/audit.sh` (Chrome
headless) pentru verificare, `tools/mobil.sh` pentru captura la 393px,
`tools/voal.py` (Pillow 11.3.0, instalat) pentru grosimea voalului peste
fotografii, `cwebp` (`/opt/homebrew/bin/cwebp`, instalat) pentru conversia
pozelor.

**Spec:** `docs/superpowers/specs/2026-09-09-refresh-vizual-en-faq-design.md`

## Constrângeri globale

Se aplică fiecărui task, fără să se mai repete în el.

- **Totul în română**, inclusiv comentariile din cod. Excepția e conținutul
  paginilor din `/en/`, care e în engleză, dar comentariile din codul lor
  rămân în română.
- **Fără cratime lungi** (caracterul „em dash") nicăieri: nici în text, nici
  în cod, nici în mesajele de commit. Se rescrie propoziția. Cratima scurtă
  (`–`) rămâne doar pentru intervale reale (`Luni–vineri`, `VII–VIII`).
- **Fără gradient**, cu trei excepții și numai ele: dunga speculară de 1px de
  pe muchia sticlei, masca care topește marginile fotografiilor, și fundalul
  paginii (adăugat în Task 3). Componentele rămân fără gradient.
- **Contrast minim 4.5:1** pentru text normal, 3:1 pentru text de la 26px în
  sus. Se calculează pe fundalul **compus**, nu pe cel declarat. Nu se
  acceptă nicio culoare nouă fără un număr măsurat.
- **Stratul corect:** o componentă nouă se scrie în `brand/tokens.css`, nu în
  `assets/site.css`. O culoare sau o umbră literală scrisă în `site.css` e
  semn că trebuia să fie un token. Clasele `.p-*` rămân **ultimele** în
  `tokens.css`; nimic nu se scrie sub ele.
- **Nicio dată de contact în HTML.** Telefon, e-mail, adresă, linkuri de
  rețele, ID-uri: doar în `assets/config.js`, legate prin `data-*`.
- **Antetul și subsolul sunt identice octet cu octet** în cele patru pagini
  românești (`index.html`, `despre.html`, `blog.html`, `blog-articol.html`),
  ca să poată fi extrase în `header.php` / `footer.php`. Dacă un task atinge
  antetul sau subsolul, le atinge pe toate patru, cu exact aceiași octeți.
  Din Task 11, aceeași regulă separat pentru cele două pagini engleze.
- **Hero-ul nu primește niciodată `data-aos`.** Titlul paginii nu are voie să
  depindă de un fișier de pe alt server ca să fie vizibil.
- **Nu se inventează date.** Ce nu se știe (profesori, linkuri, răspunsuri
  neconfirmate) primește valoare substituent în `config.js` (`XXX`), care
  declanșează singură eticheta roșie „de completat" prin `PLACEHOLDER` din
  `assets/site.js:9`.

## Cum se testează în proiectul ăsta

Nu există framework de teste și nu se adaugă unul. Ciclul de verificare e:

```bash
tools/audit.sh                    # toate paginile, la 1440 / 900 / 560
tools/audit.sh index.html 560     # o pagină, o lățime
tools/mobil.sh index.html out.png # captură reală la 393px
```

`tools/audit.sh` raportează, pe fiecare lățime: contrast pe fundal compus,
text tăiat, elemente ieșite din ecran, suprapuneri de text pe cutii de linie
reale, derulare orizontală. **Zero peste tot înseamnă curat.** Din 2026-09-07
auditul iese zero, deci orice raport nenul apărut în timpul planului e cauzat
de munca asta și se repară în task-ul care l-a produs, nu mai târziu.

Ordinea în fiecare task e aceeași și nu se scurtcircuitează:

1. rulează verificarea **înainte** de schimbare și notează ce iese
2. fă schimbarea
3. rulează verificarea din nou
4. compară: diferența trebuie să fie exact cea așteptată
5. comite

## Structura fișierelor

**Modificate:**

| fișier | ce primește |
|---|---|
| `brand/tokens.css` | token `--bg-rece`, `body` cu gradient, componenta `.faq`, `.navlink` neatins |
| `assets/site.css` | antet bleumarin, meniu de telefon bleumarin, secțiunea de cursuri |
| `assets/config.js` | `cursuriSpeciale`, `cameraComert`, `facebook`, `instagram`, tabela `texte` |
| `assets/site.js` | grupuri în formular, `FAQPage`, `data-camera`, alegerea limbii |
| `index.html` | corecturi de text, video mutat, cursuri speciale, FAQ, subsol |
| `despre.html` | o corectură de text, antet și subsol |
| `blog.html`, `blog-articol.html` | doar antet și subsol, ca să rămână identice |
| `sitemap.xml` | cele două adrese engleze |
| `tools/audit.sh` | suport pentru pagini din subdirectoare |
| `CLAUDE.md`, `README.md`, `brand/brandbook.html` | regulile și evidențele schimbate |

**Create:**

| fișier | responsabilitate |
|---|---|
| `en/index.html` | pagina principală în engleză |
| `en/despre.html` | pagina Despre în engleză |
| `assets/foto/motive/*.webp` | patru poze albastre care le înlocuiesc pe cele de acum |
| `assets/foto/cursuri/*.webp` | cinci poze pentru cursurile speciale |
| `assets/foto/cursuri/SURSE.txt` | proveniența și licența celor cinci |

---

### Task 1: Antetul devine bleumarin, cu numele mărcii auriu

**Fișiere:**
- Modifică: `assets/site.css:31-61` (blocul antetului)
- Verifică: `tools/audit.sh` la 1440 / 900 / 560

**Interfețe:**
- Produce: bara `--navy-700` opacă, fără `backdrop-filter`. Task 2 se
  bazează pe faptul că filtrul a dispărut de pe `.site-head`.
- Nu atinge: `.navlink` din `tokens.css`. Componenta rămâne cum e, fiindcă
  aceeași clasă e folosită și în subsol (`index.html`, linkurile Facebook și
  Instagram), unde stă pe hârtie, nu pe bleumarin. Se scriu doar suprascrieri
  cu prefix `.site-head`, care e așezare în pagină, deci `site.css`.

- [ ] **Pasul 1: Rulează auditul înainte, ca să ai punctul de plecare**

```bash
tools/audit.sh index.html
```

Așteptat: zero peste tot, pe toate cele trei lățimi. Dacă nu e zero, oprește-te
și raportează: înseamnă că depozitul nu era curat înainte să începi.

- [ ] **Pasul 2: Fă bara opacă**

În `assets/site.css`, înlocuiește regula `.site-head`:

```css
/* --- Antet: bleumarinul mărcii, plin ------------------------------------
   Bara nu mai e de sticlă. Motivele, în ordine: e culoarea butonului primar,
   deci antetul și acțiunea principală vorbesc aceeași limbă; și, scoțând
   `backdrop-filter`, dispare capcana care făcea din antet blocul de
   referință pentru meniul `fixed` de pe telefon (vezi blocul de 560px).
   `theme-color` din cele patru pagini e deja #121B52, deci bara browserului
   se potrivea cu o bară care abia acum devine bleumarin.                  */
.site-head {
  position: sticky; top: 0; z-index: 50;
  padding-block: var(--sp-3);
  background: var(--navy-700);
  border-bottom: var(--stroke);
}
```

Cele două linii `-webkit-backdrop-filter` și `backdrop-filter` dispar. Regula
`.site-head::before`, dunga speculară, rămâne neatinsă.

- [ ] **Pasul 3: Auriu pe numele mărcii, cerneală pe linkuri**

Imediat după regula `.brand-sub` din `assets/site.css`, adaugă:

```css
/* Marca pe bleumarin: același auriu care e deja pe banda de CTA. Măsurat:
   --brass-300 pe --navy-700 dă 8.29:1, --brass-200 dă 10.6:1. Subtitlul ia
   tonul mai deschis fiindcă are 11px, unde pragul contează cel mai mult.
   Subtitlul rămâne Montserrat: Great Vibes are ochiul foarte mic și la 11px
   nu se mai citește. Caligrafia rămâne doar pe „Academia".                */
.site-head .brand      { color: var(--brass-300); }
.site-head .brand-name { color: var(--brass-300); }
.site-head .brand-sub  { color: var(--brass-200); }
.site-head .burger     { color: var(--brass-300); }

/* Linkurile primesc sticla pe care o avea bara până acum, cu cerneala peste
   ea. Măsurat: alb 62% peste --navy-700 compune #A5A8BD, iar --navy-900
   peste el dă 7.41:1. La 52% ar fi 5.67:1, tot peste prag, dar bara ar
   rămâne prea închisă pe sub linkuri; la 82% s-ar albi și n-ar mai citi
   nimeni bleumarinul de dedesubt.
   La hover transparența dispare de tot: alb plin, 17.4:1.
   Conturul rămâne --navy-900 și pe bleumarin: se vede ca o muchie închisă,
   iar regula sistemului e că el nu dispare niciodată.                     */
.site-head .navlink { background: var(--glass-fill); color: var(--text); }
.site-head .navlink:hover { background: var(--white); }
.site-head .navlink[aria-current="page"] {
  background: var(--white); color: var(--navy-700);
}
```

Regula pentru pagina curentă e obligatorie: în `tokens.css` ea e
`background: var(--navy-700); color: #fff`, adică bleumarin pe bleumarin.
Specificitatea `.site-head .navlink[aria-current="page"]` (0,3,0) o bate pe
cea din tokens (0,2,0).

- [ ] **Pasul 4: Rulează auditul și compară**

```bash
tools/audit.sh index.html
tools/audit.sh despre.html
```

Așteptat: tot zero. Dacă apare o linie de contrast pe `.navlink` sau pe
`.brand-sub`, valoarea aleasă nu ține și se urcă o treaptă:
`--glass-fill-deep` pentru linkuri, `--brass-100` pentru subtitlu. Nu se
trece mai departe cu un raport nenul.

- [ ] **Pasul 5: Uită-te la ea**

```bash
python3 -m http.server 8000
```

Deschide `http://localhost:8000/` și verifică trei lucruri pe care auditul nu
le prinde: butonul de WhatsApp a rămas verde, sigla se vede pe cardul ei alb,
și numele mărcii nu s-a rupt pe două rânduri.

- [ ] **Pasul 6: Comite**

```bash
git add assets/site.css
git commit -m "Antetul, bleumarin plin, cu numele mărcii auriu

Bara ia culoarea butonului primar și pierde backdrop-filter, care era și
capcana meniului fixed de pe telefon. Numele mărcii primește aurul de pe
banda de CTA, 8.29:1; linkurile păstrează sticla veche, 7.41:1 măsurat pe
fundalul compus, și la hover se opacizează de tot."
```

---

### Task 2: Meniul de telefon urmează bara

**Fișiere:**
- Modifică: `assets/site.css:303-323` (blocul de 860px) și
  `assets/site.css:351-373` (meniul și linkurile din blocul de 560px)
- Verifică: `tools/audit.sh index.html 560` și `tools/mobil.sh`

**Interfețe:**
- Consumă din Task 1: `.site-head` e opac și nu mai are `backdrop-filter`.
- Produce: meniul deschis, bleumarin, cu linkuri aurii.

**De ce e nevoie de task-ul ăsta:** meniul pe tot ecranul folosește azi `--bg`,
hârtia paginii, tocmai ca să se citească drept continuarea barei. Cu bara
bleumarin, regula se inversează, altfel meniul deschis arată ca o foaie albă
lipită sub o bară închisă.

- [ ] **Pasul 1: Vezi cum arată acum, ca să ai cu ce compara**

```bash
tools/mobil.sh index.html /tmp/meniu-inainte.png
```

- [ ] **Pasul 2: Panoul de sub 860px**

În blocul `@media (max-width: 860px)`, în regula `#meniu`, înlocuiește
`background: var(--glass-fill-deep);` cu:

```css
    background: var(--navy-700);
```

și șterge din aceeași regulă cele două linii `-webkit-backdrop-filter` și
`backdrop-filter`: n-au ce filtra pe sub o suprafață opacă.

- [ ] **Pasul 3: Ecranul plin de sub 560px**

În blocul `@media (max-width: 560px)`:

Regula `html.meniu-deschis .site-head` se șterge cu totul. Exista ca să
stingă `backdrop-filter` cât e meniul deschis; filtrul nu mai există din
Task 1, iar bara e deja opacă.

În `#meniu.is-open`, `background: var(--bg);` devine:

```css
    background: var(--navy-700);
```

iar cele două linii de `backdrop-filter` de sub el se șterg.

În `#meniu .navlink`, adaugă culoarea și schimbă linia despărțitoare:

```css
  #meniu .navlink {
    text-align: left; border: 0; border-radius: 0; background: none;
    font-family: var(--font-primary); font-size: var(--fs-xl);
    font-weight: var(--fw-regular); letter-spacing: var(--ls-snug);
    padding: var(--sp-4) 0;
    /* Aurul de pe bară, 8.29:1 pe bleumarin. Linia despărțitoare trece de la
       --border, o piatră deschisă care ar dispărea, la un bleumarin mai
       deschis, care se vede fără să taie.                                 */
    color: var(--brass-300);
    border-bottom: 1.5px solid var(--navy-500);
  }
```

Cele două linii `-webkit-backdrop-filter` și `backdrop-filter` din regula asta
se șterg.

Regula pentru pagina curentă din meniu devine:

```css
  #meniu .navlink[aria-current="page"] { background: none; color: var(--white); }
```

`--accent-text` (`#9C6304`) era ales pentru hârtie și dă 1.86:1 pe bleumarin,
adică invizibil. Albul dă 16.10:1 și marchează pagina curentă mai clar decât
o nuanță de auriu lângă alt auriu.

- [ ] **Pasul 4: Verifică la lățimea reală**

```bash
tools/audit.sh index.html 560
tools/mobil.sh index.html /tmp/meniu-dupa.png
```

Așteptat: audit zero. În captură, deschide meniul și verifică vizual că
pornește exact din marginea de jos a barei, fără dungă de altă culoare între
ele, și că se derulează dacă nu încap toate linkurile.

- [ ] **Pasul 5: Comite**

```bash
git add assets/site.css
git commit -m "Meniul de telefon, pe aceeași hârtie ca bara, adică bleumarin

Meniul deschis se citea drept continuarea barei fiindcă amândouă erau albe.
Bara fiind acum bleumarin, meniul îl urmează, cu linkuri aurii și linie
despărțitoare navy-500. Regula care stingea backdrop-filter cât e meniul
deschis dispare: filtrul nu mai există."
```

---

### Task 3: Gradientul de fundal și a treia excepție

**Fișiere:**
- Modifică: `brand/tokens.css:174` (rolurile semantice), `brand/tokens.css:279`
  (regula `body`)
- Modifică: `CLAUDE.md`, secțiunea „Reguli de conținut și stil"
- Modifică: `brand/brandbook.html`, placa despre gradient

**Interfețe:**
- Produce: tokenul `--bg-rece`, folosit doar de `body`.

- [ ] **Pasul 1: Confirmă singur limita, nu o lua pe încredere**

```bash
python3 - <<'PY'
def lin(c):
    c/=255
    return c/12.92 if c<=0.03928 else ((c+0.055)/1.055)**2.4
def L(h):
    h=h.lstrip('#'); r,g,b=(int(h[i:i+2],16) for i in (0,2,4))
    return 0.2126*lin(r)+0.7152*lin(g)+0.0722*lin(b)
def cr(a,b):
    la,lb=L(a),L(b); hi,lo=max(la,lb),min(la,lb)
    return (hi+0.05)/(lo+0.05)
for capat in ('#FCFBF7','#F6F6F7','#F3F4F7','#F0F1F7','#EDEFF7'):
    print(capat, 'stone-500:', round(cr('#726F67',capat),2),
                 'stone-600:', round(cr('#57544D',capat),2))
PY
```

Așteptat: `#F3F4F7` dă 4.56:1 pentru `--stone-500`, iar `#F0F1F7` dă 4.45:1,
adică sub prag. Asta e motivul pentru care capătul albastru e `#F3F4F7` și nu
`--navy-50`. Dacă numerele ies altfel, oprește-te și raportează.

- [ ] **Pasul 2: Adaugă tokenul**

În `brand/tokens.css`, în secțiunea 6, sub `--bg`:

```css
  --bg:        var(--paper);
  /* Capătul rece al hârtiei, în josul paginii. Nu e ales din ochi și nu poate
     merge mai departe către albastru: pe #F0F1F7, textul estompat
     --stone-500 cade la 4.45:1, sub prag. Aici e 4.56:1.                  */
  --bg-rece:   #F3F4F7;
```

- [ ] **Pasul 3: Pune gradientul pe `body`**

```css
body {
  margin: 0;
  /* A treia excepție de la „fără gradient", și singura care nu desenează un
     obiect: descrie lumina pe suportul pe care stau toate obiectele.
     `background-color` NU e decorativ și nu se șterge. tools/audit.js, linia
     30, citește getComputedStyle(n).backgroundColor, iar un gradient stă în
     background-image. Fără culoarea plată, auditul ar citi transparent și ar
     tăcea, fără să dea eroare. Se declară capătul cel mai închis, ca fiecare
     măsurătoare să cadă pe cazul cel mai rău, nu pe unul mediu.
     `fixed` ține gradientul în ecran, nu întins pe toată înălțimea
     documentului, unde pe o pagină de 8000px n-ar mai fi vizibil deloc.
     Pe iOS, `fixed` se comportă ca `scroll`; e o degradare blândă, pagina
     rămâne corectă.                                                       */
  background-color: var(--bg-rece);
  background-image: linear-gradient(180deg, var(--bg) 0%, var(--bg-rece) 100%);
  background-attachment: fixed;
  color: var(--text);
  font-family: var(--font-secondary);
  font-size: var(--fs-base);
  line-height: var(--lh-body);
  -webkit-font-smoothing: antialiased;
}
```

- [ ] **Pasul 4: Rulează auditul pe tot**

```bash
tools/audit.sh
```

Așteptat: zero peste tot, pe toate cele patru pagini. Auditul măsoară acum
totul peste `#F3F4F7`, deci dacă un text era la limită pe hârtie, aici se
vede. Orice linie apărută se repară aici, nu mai târziu.

- [ ] **Pasul 5: Rescrie regula în CLAUDE.md**

În secțiunea „Reguli de conținut și stil", prima frază despre gradient devine:

```markdown
- **Fără gradient**, cu trei excepții aprobate, toate structurale: dunga
  speculară de 1px de pe muchia sticlei; masca care topește marginile
  fotografiilor; și fundalul paginii, singurul gradient care nu desenează un
  obiect, ci lumina pe suportul pe care stau toate obiectele. A doua nu e o
  umplere, e o mască: două treceri liniare, una pe orizontală și una pe
  verticală, păstrate doar unde se suprapun, ca fiecare latură a cardului să
  se stingă la fel, iar colțurile de două ori. A treia e limitată de contrast:
  capătul rece e `#F3F4F7` fiindcă pe `#F0F1F7` textul estompat cade la
  4.45:1. Fără forme colorate în fundal.
```

- [ ] **Pasul 6: Aceeași regulă în brandbook**

Găsește placa despre gradient în `brand/brandbook.html` și adaugă a treia
excepție, cu același motiv și cu numărul măsurat. Manualul e locul unde scrie
**de ce**, deci explicația despre `tools/audit.js` și culoarea plată de
rezervă intră aici, nu doar în comentariul din CSS.

- [ ] **Pasul 7: Comite**

```bash
git add brand/tokens.css CLAUDE.md brand/brandbook.html
git commit -m "Fundalul se răcește către albastru, ca a treia excepție

Capătul rece e #F3F4F7, nu --navy-50: pe #F0F1F7 textul estompat cade la
4.45:1, sub prag. Culoarea plată declarată alături de gradient nu e
decorativă, tools/audit.js citește backgroundColor, iar un gradient stă în
background-image; fără ea auditul ar fi tăcut. Regula e rescrisă în CLAUDE.md
și în manual."
```

---

### Task 4: Corecturile de text și video mutat sus

**Fișiere:**
- Modifică: `index.html` (liniile 7, 88, 152, 179, 344, 360, plus blocul `#video`)
- Modifică: `despre.html:129`

**Interfețe:**
- Produce: textele finale pe care Task 11 le traduce în engleză. Dacă un text
  se schimbă aici, versiunea engleză îl urmează, nu invers.

- [ ] **Pasul 1: Numără ocurențele înainte, ca să știi când ai terminat**

```bash
grep -n "nu o lecție\|o discuție\|Nu cereți nimic\|Șaisprezece materii\|Lunar pentru grupele" index.html despre.html
```

Așteptat: șapte linii. Dacă ies mai multe sau mai puține, cineva a atins
fișierele între timp; citește-le înainte să schimbi ceva.

- [ ] **Pasul 2: Cele trei înlocuiri simple**

| fișier și linie | acum | devine |
|---|---|---|
| `index.html:179` | `Șaisprezece materii, o singură școală` | `Toate materiile, o singură academie` |
| `index.html:344` | `Nu cereți nimic pe încredere` | `Nu faceți nimic pe încredere` |
| `index.html:152` | `Lunar pentru grupele de Bacalaureat, cu timp` | `Pentru grupele de Bacalaureat, cu timp` |

Atenție la a treia: propoziția începe cu „Lunar", deci după ștergere trebuie
majusculă pe „Pentru". Fraza întreagă devine:

```html
        <p>Pentru grupele de Bacalaureat, cu timp cronometrat și barem oficial. Nota din simulare intră în raport, ca să se vadă evoluția, nu impresia.</p>
```

- [ ] **Pasul 3: Fraza despre prima ședință, în patru locuri**

Forma lungă, pentru banda de CTA (`index.html:360`):

```html
        <h3>Prima ședință nu e o lecție: ne cunoaștem, stabilim obiective, stabilim rezonanța</h3>
```

`index.html:7`, meta description:

```html
<meta name="description" content="Studium Generale by Denisa. Pregătire individuală sau în grupe de maximum trei elevi, cu părintele la curent după fiecare ședință. Prima ședință nu e o lecție: ne cunoaștem și stabilim obiective.">
```

Scurtată fiindcă Google taie descrierea în jur de 160 de caractere, iar cele
trei verbe puse întregi ar împinge afară numele școlii.

`index.html:88`, nota din hero:

```html
    <p class="hero-note">Prima ședință durează 50 de minute: ne cunoaștem, stabilim obiective și stabilim rezonanța. Nu costă nimic.</p>
```

`index.html:344`, paragraful de la Programare, cu ambele corecturi în el:

```html
      <p>Nu faceți nimic pe încredere: spuneți-ne vârsta, materia și dacă preferați singur sau în grupă, iar la capăt vă spunem cine predă și deschidem conversația pe WhatsApp. Prima ședință durează 50 de minute, nu costă nimic și nu obligă la nimic: ne cunoaștem, stabilim obiective și stabilim rezonanța.</p>
```

`despre.html:129`, ca să nu rămână singurul loc care mai spune „discuție":

```html
        <h3>Veniți să ne cunoaștem, apoi decideți</h3>
```

- [ ] **Pasul 4: Mută secțiunea de video deasupra secțiunii „Patru lucruri"**

În `index.html`, taie blocul întreg `<!-- ── Video ── -->` cu tot cu
`<section class="wrap" id="video"> ... </section>` și lipește-l **înainte** de
`<!-- ── De ce noi ── -->`, adică imediat după închiderea secțiunii de hero.

Nu se schimbă nimic altceva. `data-aos-delay` din cardurile de motive rămâne
exact cum e: întârzierile sunt relative la intrarea fiecărui card în ecran,
nu la poziția secțiunii în pagină.

- [ ] **Pasul 5: Verifică**

```bash
grep -n "nu o lecție\|Nu cereți nimic\|Șaisprezece materii\|Lunar pentru grupele" index.html despre.html
```

Așteptat: **nicio linie**. Apoi:

```bash
grep -n 'id="video"\|id="metoda"' index.html
```

Așteptat: `id="video"` pe o linie mai mică decât `id="metoda"`.

```bash
tools/audit.sh index.html && tools/audit.sh despre.html
```

Așteptat: zero. Titlul din banda de CTA e acum mult mai lung, deci uită-te
special la lățimea de 560: dacă se taie, e o linie în raport.

- [ ] **Pasul 6: Comite**

```bash
git add index.html despre.html
git commit -m "Corecturile de text și video-ul mutat deasupra motivelor

Prima ședință nu mai e „o discuție", ci ne cunoaștem, stabilim obiective și
stabilim rezonanța, în toate cele cinci locuri unde apărea. Materiile devin
„Toate materiile, o singură academie", simulările nu mai sunt lunare, iar
„nu cereți nimic pe încredere\" devine „nu faceți nimic pe încredere\"."
```

---

### Task 5: Pozele albastre din „Cum lucrăm"

**Fișiere:**
- Înlocuiește: `assets/foto/motive/parinte.webp`, `raport.webp`,
  `recuperare.webp`, `simulare.webp`
- Modifică: `assets/foto/motive/SURSE.txt`
- Modifică: `index.html`, atributele `--voal-foto` de pe cele patru carduri
- Modifică: `README.md`, tabelul pozelor din „Cum lucrăm"

**Interfețe:**
- Produce: patru fișiere cu aceleași nume și aceleași căi. HTML-ul nu se
  schimbă în afară de valoarea voalului, deci nimic altceva nu se rupe.

**Constrângeri, în ordinea în care contează:**

1. **Licența.** Doar domeniu public sau CC0, ca acum: se pot folosi comercial,
   fără atribuire. Wikimedia Commons și Flickr, cu filtrul de licență pus.
   Dacă pentru un card nu găsești o poză albastră cu licență curată,
   **cardul rămâne cu poza lui de acum** și raportezi. Nu se folosește ceva
   nesigur și nu se inventează o sursă.
2. **Subiectul rămâne același.** Textul cardului descrie ce se vede:
   `parinte` (cineva scrie lângă un laptop), `raport` (un document parcurs cu
   pixul), `recuperare` (un calendar), `simulare` (o sală de clasă goală).
3. **Albastrul domină.** Verificabil, nu din ochi: se măsoară la Pasul 3.
4. **Mărimea:** 1100px lățime, ca acum, `.webp`.

- [ ] **Pasul 1: Notează valorile de acum, ca să ai de unde te întoarce**

```bash
grep -n "voal-foto" index.html
cp -r assets/foto/motive /tmp/motive-vechi
```

Așteptat: patru valori, între 0.48 și 0.60.

- [ ] **Pasul 2: Caută și descarcă**

Caută pe Wikimedia Commons și pe Flickr, cu filtrul de licență pe domeniu
public sau CC0, fotografii cu albastru dominant pentru cele patru subiecte.
Descarcă originalele într-un director temporar și notează pentru fiecare:
pagina sursei, adresa fișierului, licența și ce se vede în poză. Ai nevoie de
toate patru pentru `SURSE.txt`.

- [ ] **Pasul 3: Verifică măsurat că albastrul chiar domină**

```bash
python3 - /tmp/poze-noi/*.jpg <<'PY'
import sys
from PIL import Image
for cale in sys.argv[1:]:
    im = Image.open(cale).convert("RGB").resize((100, 100))
    px = list(im.getdata())
    r = sum(p[0] for p in px) / len(px)
    g = sum(p[1] for p in px) / len(px)
    b = sum(p[2] for p in px) / len(px)
    albastra = sum(1 for p in px if p[2] > p[0] + 8 and p[2] > p[1] + 4)
    print("%-40s R%3.0f G%3.0f B%3.0f  pixeli albaștri %3d%%  %s"
          % (cale.split('/')[-1], r, g, b, albastra,
             "ok" if b > r and albastra >= 50 else "NU DOMINĂ"))
PY
```

Așteptat: toate patru cu media albastrului peste cea a roșului și cel puțin
jumătate din pixeli albaștri. O poză care nu trece nu se folosește: caută
alta, nu ajusta pragul.

- [ ] **Pasul 4: Convertește la mărimea la care se afișează**

```bash
for f in /tmp/poze-noi/*.jpg; do
  nume=$(basename "$f" .jpg)
  python3 -c "
from PIL import Image
im = Image.open('$f').convert('RGB')
im = im.resize((1100, round(1100 * im.size[1] / im.size[0])), Image.LANCZOS)
im.save('/tmp/poze-noi/$nume.png')
"
  cwebp -q 82 "/tmp/poze-noi/$nume.png" -o "assets/foto/motive/$nume.webp"
done
ls -la assets/foto/motive/
```

Așteptat: patru `.webp`, fiecare sub 200 KB. Dacă una iese mult mai mare,
coboară calitatea la 78 și repetă; regula proiectului e că pozele sunt la
mărimea la care se afișează, nu mai mari.

- [ ] **Pasul 5: Recalculează voalul fiecărei poze**

```bash
tools/voal.py --paragraf assets/foto/motive/*.webp
```

Cardurile au și titlu, și paragraf, deci pragul e 4.5:1 și `--paragraf` e
obligatoriu. Valorile de acum (0.48 până la 0.60) **nu se transferă**: sunt
legate de pozele vechi.

Pune fiecare valoare pe cardul ei, în `index.html`:

```html
      <article class="foto-card reason" style="--voal-foto:rgba(11, 25, 54, 0.NN)" data-aos="fade-up" data-aos-delay="0">
```

unde `0.NN` e exact ce a scris `voal.py` pentru poza aceea. Nu se rotunjește
în jos și nu se alege din ochi.

- [ ] **Pasul 6: Rescrie evidența**

`assets/foto/motive/SURSE.txt` păstrează formatul de acum: pentru fiecare
poză, ce arată, licența, sursa, pagina, fișierul. Actualizează și tabelul
corespunzător din `README.md`.

- [ ] **Pasul 7: Verifică**

```bash
tools/audit.sh index.html
```

Așteptat: zero. Contrastul textului alb peste voal e chiar lucrul pe care
auditul îl măsoară pe fundal compus, deci un voal prea subțire apare aici.

Uită-te apoi la pagină în browser: cele patru carduri trebuie să se citească
ca o familie, nu ca patru poze fără legătură.

- [ ] **Pasul 8: Comite**

```bash
git add assets/foto/motive index.html README.md
git commit -m "Poze albastre la „Cum lucrăm\", cu voalul recalculat

Aceleași patru subiecte, aceleași nume de fișier, domeniu public sau CC0 ca
înainte. Albastrul dominant e verificat pe pixeli, nu din ochi, iar
--voal-foto e recalculat cu tools/voal.py --paragraf pentru fiecare poză
nouă: valorile vechi erau legate de pozele vechi."
```

---

### Task 6: Cursurile speciale, în pagină și în formular

**Fișiere:**
- Creează: `assets/foto/cursuri/excel.webp`, `contabilitate.webp`,
  `dictie.webp`, `dezvoltare.webp`, `financiara.webp`
- Creează: `assets/foto/cursuri/SURSE.txt`
- Modifică: `assets/config.js` (lista nouă `cursuriSpeciale`)
- Modifică: `assets/site.js` (secțiunea 2, formularul; secțiunea 9, JSON-LD)
- Modifică: `index.html` (secțiune nouă, plus meniu și subsol)
- Modifică: `despre.html`, `blog.html`, `blog-articol.html` (doar antet și
  subsol, ca să rămână identice octet cu octet)
- Modifică: `README.md`

**Interfețe:**
- Produce: `C.cursuriSpeciale`, aceeași formă ca `C.materii`
  (`{ nume, profesori, numeEn? }`). Task 10 adaugă `numeEn`.
- Produce: secțiunea `#cursuri` în `index.html`, la care trimit meniul și
  subsolul din toate cele patru pagini.

- [ ] **Pasul 1: Adaugă lista în config**

În `assets/config.js`, imediat sub `materii`:

```js
  /* --- Cursuri speciale ---------------------------------------------------
     Nu sunt materii de examen și nu se pregătește nimic cu ele: se iau
     separat, la orice vârstă. Aceeași formă ca `materii`, deci intră în
     același pas al formularului, doar sub alt titlu.
     DE COMPLETAT: cine predă fiecare. Cât timp `profesori` e gol, pagina își
     pune singură eticheta roșie, exact ca la Maghiară.                    */
  cursuriSpeciale: [
    { nume: "Excel",                profesori: [] },
    { nume: "Contabilitate",        profesori: [] },
    { nume: "Dicție",               profesori: [] },
    { nume: "Dezvoltare personală", profesori: [] },
    { nume: "Educație financiară",  profesori: [] },
  ],
```

- [ ] **Pasul 2: Pozele**

Cinci fotografii, una pentru fiecare curs. Regulile sunt aceleași ca pentru
orice poză din proiect, scrise aici întregi ca să nu fie nevoie de alt task:

1. **Licența:** doar domeniu public sau CC0, de pe Wikimedia Commons sau
   Flickr cu filtrul de licență pus. Dacă pentru un curs nu găsești nimic
   curat, **cardul nu se publică cu o poză nesigură**: raportezi și cursul
   rămâne pe listă fără card, până se rezolvă.
2. **Mărimea:** 800px lățime, ca restul cardurilor de materie.
3. **Evidența:** pagina sursei, adresa fișierului, licența și ce se vede,
   scrise în `assets/foto/cursuri/SURSE.txt`, în formatul folosit de
   `assets/foto/materii/SURSE.txt`.

Conversia:

```bash
mkdir -p assets/foto/cursuri
for f in /tmp/poze-cursuri/*.jpg; do
  nume=$(basename "$f" .jpg)
  python3 -c "
from PIL import Image
im = Image.open('$f').convert('RGB')
im = im.resize((800, round(800 * im.size[1] / im.size[0])), Image.LANCZOS)
im.save('/tmp/poze-cursuri/$nume.png')
"
  cwebp -q 82 "/tmp/poze-cursuri/$nume.png" -o "assets/foto/cursuri/$nume.webp"
done
```

Voalul:

```bash
tools/voal.py assets/foto/cursuri/*.webp
```

Fără `--paragraf`: cardurile de curs au doar titlu, deci pragul e 3:1, ca la
materii. Dacă maximul cerut e sub 0.48, nu se scrie nimic pe carduri:
`.materie` are deja `--voal-foto: rgba(11, 25, 54, 0.48)` în `tokens.css`.
Dacă vreo poză cere mai mult, doar acel card primește valoarea lui.

- [ ] **Pasul 3: Secțiunea în pagină**

În `index.html`, imediat după închiderea secțiunii `#materii`:

```html
  <!-- ── Cursuri speciale ────────────────────────────────────────────── -->
  <section class="wrap" id="cursuri">
    <div class="sec-head wrap-text" data-aos="fade-up">
      <p class="eyebrow">Dincolo de programă</p>
      <h2>Cursuri speciale</h2>
      <p>Nu se dă examen la ele și nu se trec în catalog. Se iau separat, la orice vârstă, de obicei în paralel cu pregătirea pentru examen.</p>
    </div>

    <div class="grid grid-3">
      <article class="foto-card materie" data-aos="fade-up" data-aos-delay="0">
        <img class="foto-blur" src="assets/foto/cursuri/excel.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <img class="foto-clar" src="assets/foto/cursuri/excel.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <h3>Excel</h3>
      </article>

      <article class="foto-card materie" data-aos="fade-up" data-aos-delay="70">
        <img class="foto-blur" src="assets/foto/cursuri/contabilitate.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <img class="foto-clar" src="assets/foto/cursuri/contabilitate.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <h3>Contabilitate</h3>
      </article>

      <article class="foto-card materie" data-aos="fade-up" data-aos-delay="140">
        <img class="foto-blur" src="assets/foto/cursuri/dictie.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <img class="foto-clar" src="assets/foto/cursuri/dictie.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <h3>Dicție</h3>
      </article>

      <article class="foto-card materie" data-aos="fade-up" data-aos-delay="210">
        <img class="foto-blur" src="assets/foto/cursuri/dezvoltare.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <img class="foto-clar" src="assets/foto/cursuri/dezvoltare.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <h3>Dezvoltare personală</h3>
      </article>

      <article class="foto-card materie" data-aos="fade-up" data-aos-delay="0">
        <img class="foto-blur" src="assets/foto/cursuri/financiara.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <img class="foto-clar" src="assets/foto/cursuri/financiara.webp" alt="" aria-hidden="true" loading="lazy" decoding="async">
        <h3>Educație financiară</h3>
      </article>
    </div>
  </section>
```

- [ ] **Pasul 4: Linkul, în toate cele patru pagini**

În antet, în `<nav id="meniu">`, între „Materii" și „Blog":

```html
      <a class="navlink" href="index.html#cursuri">Cursuri</a>
```

În subsol, în coloana „Site", sub „Materii":

```html
          <li><a href="index.html#cursuri">Cursuri speciale</a></li>
```

**Amândouă se scriu identic în `index.html`, `despre.html`, `blog.html` și
`blog-articol.html`.** Verifică la Pasul 8 că sunt identice octet cu octet.

- [ ] **Pasul 5: Formularul, cu două grupuri**

În `assets/site.js`, secțiunea 2. Înlocuiește linia
`var materii = C.materii || [];` cu:

```js
    /* Materiile de examen și cursurile speciale merg prin același pas, dar
       sub titluri diferite: cine caută pregătire pentru Bacalaureat nu
       trebuie să dea peste Excel în aceeași grămadă. Lista de lucru se
       compune o dată, cu grupul scris pe fiecare intrare, ca filtrarea după
       vârstă și căutarea profesorului să rămână un singur cod.           */
    var GRUP_MATERII = "Materii";
    var GRUP_CURSURI = "Cursuri speciale";
    var materii = adunaOferta();

    function adunaOferta() {
      var lista = [];
      [[C.materii, GRUP_MATERII], [C.cursuriSpeciale, GRUP_CURSURI]]
        .forEach(function (pereche) {
          (pereche[0] || []).forEach(function (m) {
            var copie = {};
            for (var k in m) if (Object.prototype.hasOwnProperty.call(m, k)) copie[k] = m[k];
            copie.grup = pereche[1];
            lista.push(copie);
          });
        });
      return lista;
    }
```

Pasul cu materia își schimbă `optiuni` în `grupuri`:

```js
      {
        cheie: "materie",
        intrebare: "Ce materie te-ar interesa?",
        grupuri: function () {
          return [GRUP_MATERII, GRUP_CURSURI].map(function (g) {
            return {
              titlu: g,
              optiuni: materiiLaVarsta(raspunsuri.varsta)
                .filter(function (m) { return m.grup === g; })
                .map(function (m) { return { eticheta: m.nume, valoare: m.nume }; })
            };
          }).filter(function (g) { return g.optiuni.length; });
        }
      },
```

- [ ] **Pasul 6: Desenează grupurile**

În `deseneazaPas`, înlocuiește bucata care construiește lista de opțiuni.
Scoate în afară construirea unui buton, ca să nu fie scrisă de două ori:

```js
      /* Un pas are ori `optiuni`, ori `grupuri`. Grupurile sunt aceleași
         butoane, doar cu un titlu mic peste fiecare teanc.               */
      var grupuri = pas.grupuri ? pas.grupuri()
                                : [{ titlu: null, optiuni: pas.optiuni() }];
      var cate = grupuri.reduce(function (n, g) { return n + g.optiuni.length; }, 0);

      function faButon(o) {
        var b = document.createElement("button");
        b.type = "button";
        b.className = "optiune";
        b.textContent = o.eticheta;
        b.addEventListener("click", function () {
          b.classList.add("e-ales");
          raspunsuri[pas.cheie] = o.valoare;
          indice++;
          setTimeout(function () {
            if (indice >= PASI.length) deseneazaRezultat(); else deseneazaPas();
          }, 140);
        });
        return b;
      }

      grupuri.forEach(function (g) {
        if (g.titlu) {
          var h = document.createElement("p");
          h.className = "formular-grup";
          h.textContent = g.titlu;
          nod.appendChild(h);
        }
        var lista = document.createElement("div");
        /* Sub patru opțiuni textele sunt lungi („În grupă de maximum trei"),
           deci pe telefon stau una sub alta, nu două pe rând. Se numără
           opțiunile pasului întreg, nu ale grupului: două teancuri de câte
           trei nu fac un pas cu texte lungi.                              */
        lista.className = "formular-optiuni" + (cate < 4 ? " putine" : "");
        g.optiuni.forEach(function (o) { lista.appendChild(faButon(o)); });
        nod.appendChild(lista);
      });
```

Titlul de grup e o componentă mică, deci intră în `brand/tokens.css`, lângă
`.formular-optiuni` (linia 632), nu în `site.css`. Nu se scrie la sfârșitul
fișierului: blocul `TENTE` cu clasele `.p-*` trebuie să rămână ultimul.

```css
/* Titlul unui teanc de opțiuni. Discret: e o etichetă, nu o întrebare. */
.formular-grup {
  margin: var(--sp-6) 0 0; font-size: var(--fs-sm);
  font-weight: var(--fw-semibold); letter-spacing: .06em;
  text-transform: uppercase; color: var(--text-muted);
}
.formular-grup + .formular-optiuni { margin-top: var(--sp-3); }
```

- [ ] **Pasul 7: Cursurile intră și în datele structurate**

În `assets/site.js`, secțiunea 9, `hasOfferCatalog` citește azi doar
`C.materii`. Google ar vedea o ofertă din care lipsesc cinci lucruri.
Înlocuiește condiția și bucla ca să adune din amândouă:

```js
    var oferta = (C.materii || []).concat(C.cursuriSpeciale || []);
    if (oferta.length) {
      var vazute = [];
      oferta.forEach(function (m) {
        if (vazute.indexOf(m.nume) === -1) vazute.push(m.nume);
      });
```

Restul blocului rămâne cum e.

- [ ] **Pasul 8: Verifică**

```bash
# antetul și subsolul, identice în cele patru pagini
for f in index.html despre.html blog.html blog-articol.html; do
  sed -n '/<header class="site-head">/,/<\/header>/p' "$f" | md5 -q
done | sort -u | wc -l
```

Așteptat: `1`. Dacă iese `2` sau mai mult, antetele s-au desincronizat și
regula pentru `header.php` e ruptă. Repetă pentru `<footer`.

```bash
tools/audit.sh index.html
```

Așteptat: zero. Apoi, în browser, parcurge formularul: la pasul cu materia
trebuie să apară două teancuri, cu titlurile lor; alege „Excel" și verifică
că apare eticheta roșie „de completat" în loc de un nume de profesor
inventat.

- [ ] **Pasul 9: Comite**

```bash
git add assets/config.js assets/site.js brand/tokens.css index.html despre.html blog.html blog-articol.html assets/foto/cursuri README.md
git commit -m "Cursuri speciale: secțiune proprie, grup în formular, ofertă în JSON-LD

Cele cinci nu sunt materii de examen, deci au secțiunea lor sub Materii și un
teanc separat în pasul cu materia. Lista de lucru a formularului se compune
o dată, cu grupul scris pe fiecare intrare, ca filtrarea după vârstă și
căutarea profesorului să rămână un singur cod. Cine le predă rămâne de
completat, marcat automat."
```

---

### Task 7: FAQ, cu date structurate citite din pagină

**Fișiere:**
- Modifică: `brand/tokens.css` (componenta `.faq`, scrisă **înainte** de
  blocul TENTE)
- Modifică: `index.html` (secțiune nouă între `#recenzii` și `#programare`)
- Modifică: `assets/site.js` (secțiunea 9, obiect `FAQPage`)

**Interfețe:**
- Produce: secțiunea `#intrebari`, cu structura pe care o citește JSON-LD-ul:
  `.faq details > summary` e întrebarea, `.faq-raspuns` e răspunsul.

**Ce nu se scrie:** nimic despre prețuri, contract, durata angajamentului sau
garanții. Nu există date. O întrebare fără răspuns adevărat nu se pune.

- [ ] **Pasul 1: Componenta**

În `brand/tokens.css`, **înainte** de comentariul `TENTE` (linia 833). Nimic
nu se scrie sub blocul `.p-*`.

```css
/* --- Întrebări frecvente -------------------------------------------------
   `<details>`, nu un acordeon din JavaScript: browserul îl deschide singur,
   îl face accesibil din tastatură fără nicio linie de cod, și îl arată
   întreg dacă scriptul nu ajunge. Săgeata implicită se scoate și se
   desenează una proprie, ca să se rotească.                              */
.faq { display: flex; flex-direction: column; gap: var(--sp-3); }
.faq details {
  background: var(--glass-fill);
  -webkit-backdrop-filter: var(--glass-blur); backdrop-filter: var(--glass-blur);
  border: var(--stroke); border-radius: var(--r-lg);
  padding: var(--sp-5) var(--sp-6);
}
.faq summary {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: var(--sp-4); cursor: pointer; list-style: none;
  font-family: var(--font-primary); font-size: var(--fs-lg);
  letter-spacing: var(--ls-snug); line-height: var(--lh-heading);
  color: var(--text);
}
.faq summary::-webkit-details-marker { display: none; }
.faq summary::after {
  content: ""; flex: none; width: 12px; height: 12px; margin-top: 6px;
  border-right: 2px solid var(--text); border-bottom: 2px solid var(--text);
  transform: rotate(45deg);
  transition: transform var(--dur-base) var(--ease);
}
.faq details[open] summary::after { transform: rotate(-135deg); }
.faq summary:focus-visible { outline: 2px solid var(--focus-ring); outline-offset: 4px; }
.faq-raspuns {
  margin: var(--sp-4) 0 0; max-width: 68ch;
  font-size: var(--fs-sm); color: var(--text-secondary);
}
```

- [ ] **Pasul 2: Secțiunea, între Recenzii și Programare**

```html
  <!-- ── Întrebări frecvente ─────────────────────────────────────────── -->
  <section class="wrap" id="intrebari">
    <div class="sec-head wrap-text" data-aos="fade-up">
      <p class="eyebrow">Întrebări frecvente</p>
      <h2>Ce ne întreabă părinții cel mai des</h2>
    </div>

    <div class="faq wrap-text" data-faq data-aos="fade-up">
      <details>
        <summary>Câți elevi sunt într-o grupă?</summary>
        <p class="faq-raspuns">Maximum trei, niciodată mai mulți. Nu e o formulare de marketing: dacă al patrulea vrea aceeași oră, se deschide altă grupă. La trei, fiecare elev vorbește la fiecare ședință.</p>
      </details>

      <details>
        <summary>Ce se întâmplă dacă elevul lipsește de la o ședință?</summary>
        <p class="faq-raspuns">Ședința se reprogramează în aceeași săptămână sau se recuperează online. Nu se șterge din abonament și nu se pierde.</p>
      </details>

      <details>
        <summary>Ce conține raportul lunar și cine îl primește?</summary>
        <p class="faq-raspuns">Un document scurt: ce s-a parcurs, procentul din programă, la ce capitole stă prost și ce se face luna următoare. Ajunge la părinte, nu doar la elev. Pentru grupele de Bacalaureat intră în el și nota din simulare, ca să se vadă evoluția, nu impresia.</p>
      </details>

      <details>
        <summary>Cum arată prima ședință?</summary>
        <p class="faq-raspuns">Durează 50 de minute, nu costă nimic și nu obligă la nimic. Nu e o lecție: ne cunoaștem, stabilim obiective și stabilim rezonanța. Abia după ea se discută dacă și cum se continuă.</p>
      </details>

      <details>
        <summary>Ce se predă?</summary>
        <p class="faq-raspuns">Șaisprezece materii pentru Evaluarea Națională și Bacalaureat, de la română și matematică până la coreeană, plus cinci cursuri care nu țin de programă: Excel, contabilitate, dicție, dezvoltare personală și educație financiară.</p>
      </details>

      <details>
        <summary>Unde se țin ședințele și în ce interval?</summary>
        <p class="faq-raspuns">La <span data-address></span>. Program: <span data-schedule></span>. Ședințele de recuperare se pot ține și online.</p>
      </details>
    </div>

    <p class="note wrap-text" style="margin-top:var(--sp-6)"><b>De confirmat înainte de publicare.</b> Răspunsurile de mai sus sunt scrise din ce spune deja site-ul. Nu conțin nimic despre prețuri, contract sau durata angajamentului, fiindcă acele date nu există încă.</p>
  </section>
```

Adresa și programul vin din `config.js` prin `data-address` și
`data-schedule`, ca peste tot: nicio dată de contact nu se scrie în HTML.

- [ ] **Pasul 3: JSON-LD citit din pagină, nu dintr-o a doua listă**

În `assets/site.js`, secțiunea 9. Fișierul emite deja un `@graph`
(`var noduri = [scoala];`, linia 656), deci noul obiect se adaugă în el.
Scrie blocul de mai jos imediat după `var noduri = [scoala];`:

```js
    /* FAQ, ca date structurate. Întrebările se citesc din DOM, nu dintr-o
       listă copiată în config.js: un text scris în două locuri se
       desincronizează la prima corectură, iar Google ar ajunge să arate
       varianta veche. Se emite doar dacă secțiunea există în pagină, deci
       Despre și blogul nu îl capătă.
       Rulează după secțiunea 4, care umple `data-address` și `data-schedule`,
       deci `textContent` e deja complet.                                  */
    var faq = document.querySelector("[data-faq]");
    var intrebari = faq ? faq.querySelectorAll("details") : [];
    if (intrebari.length) {
      noduri.push({
        "@type": "FAQPage",
        "@id": baza + "/#intrebari",
        mainEntity: Array.prototype.map.call(intrebari, function (d) {
          var q = d.querySelector("summary");
          var a = d.querySelector(".faq-raspuns");
          return {
            "@type": "Question",
            name: q ? q.textContent.trim() : "",
            acceptedAnswer: {
              "@type": "Answer",
              text: a ? a.textContent.replace(/\s+/g, " ").trim() : ""
            }
          };
        })
      });
    }
```

Nu e nevoie de nicio altă schimbare: `noduri` e deja împachetat în
`{"@context": "https://schema.org", "@graph": noduri}` la sfârșitul secțiunii.

- [ ] **Pasul 4: Verifică**

```bash
tools/audit.sh index.html
```

Așteptat: zero, pe toate cele trei lățimi. Verifică special la 560: săgeata
și întrebarea stau pe același rând și o întrebare lungă trebuie să curgă, nu
să împingă săgeata afară.

În browser, cu consola deschisă:

```js
JSON.parse(document.querySelector('script[type="application/ld+json"]').textContent)
```

Așteptat: un obiect care conține și `EducationalOrganization`, și `FAQPage`
cu șase întrebări, iar a șasea are adresa și programul completate, nu goale.

Verifică apoi cu tastatura: Tab până la prima întrebare, Enter o deschide.
Dacă nu, `list-style: none` a înghițit și comportamentul, nu doar săgeata.

- [ ] **Pasul 5: Comite**

```bash
git add brand/tokens.css index.html assets/site.js
git commit -m "Întrebări frecvente, cu date structurate citite din pagină

Șase întrebări scrise strict din ce spune deja site-ul, nimic despre prețuri
sau contract, cu o notă vizibilă că răspunsurile așteaptă confirmare.
Acordeonul e <details>, nu JavaScript: e accesibil din tastatură fără nicio
linie de cod și rămâne întreg dacă scriptul nu ajunge. JSON-LD-ul citește
întrebările din DOM ca să nu existe același text în două locuri."
```

---

### Task 8: Subsolul: Camera de Comerț și rețelele

**Fișiere:**
- Modifică: `assets/config.js` (`facebook`, `instagram`, `cameraComert`)
- Modifică: `assets/site.js` (secțiunea 4, legarea lui `data-camera`)
- Modifică: `index.html`, `despre.html`, `blog.html`, `blog-articol.html`
  (rândul de jos al subsolului, identic în toate patru)

**Interfețe:**
- Consumă din secțiunea 4 a lui `site.js`: mecanismul `data-*` și
  `PLACEHOLDER` de la linia 9, care marchează singur valorile substituent.
- Produce: `data-camera`, legat la fel ca `data-phone` și `data-email`.

- [ ] **Pasul 1: Adaugă valorile în config**

```js
  /* --- Rețele (lasă gol ca să dispară din subsol) ------------------------- */
  facebook: "https://www.facebook.com/p/Academia-Studium-Generale-by-Denisa-61581913795617/",
  instagram: "https://www.instagram.com/academia_studium_generale/",

  /* Pagina de membru la Camera de Comerț.
     DE COMPLETAT: adresa o dă clientul. Cât timp scrie XXX, subsolul își pune
     singur eticheta roșie.                                                */
  cameraComert: "XXX",
```

Legarea pentru Facebook și Instagram există deja (`assets/site.js:321`), deci
linkurile apar singure în subsol și intră singure în `sameAs` din JSON-LD.
Nu se scrie cod nou pentru ele.

- [ ] **Pasul 2: Leagă `data-camera`**

În `assets/site.js`, secțiunea 4, lângă legarea pentru telefon și e-mail:

```js
    /* Camera de Comerț: doar linkul vine din config, textul stă în pagină.
       E o afirmație despre firmă, nu o dată de contact, deci se citește și
       fără JavaScript; doar adresa lipsește până o dă clientul.          */
    document.querySelectorAll("[data-camera]").forEach(function (el) {
      if (isReal(C.cameraComert)) {
        el.href = C.cameraComert;
        el.target = "_blank";
        el.rel = "noopener";
      } else {
        el.removeAttribute("href");
        el.setAttribute("data-nedefinit", "Camera de Comerț");
      }
    });
```

Verifică întâi ce stil folosește fișierul în jur: dacă restul secțiunii merge
pe `Array.prototype.forEach.call(...)`, scrie la fel, nu introduce alt idiom.

- [ ] **Pasul 3: Rândul de jos al subsolului, în toate cele patru pagini**

```html
    <div class="foot-bottom">
      <span>© <span id="an"></span> <span data-company></span> · CUI <span data-cui></span> · Reg. Com. <span data-regCom></span></span>
      <span>Membru Oficial <a data-camera>Camera de Comerț</a></span>
    </div>
```

Textul „Membru Oficial" rămâne în pagină: e o afirmație despre firmă, nu o
dată de contact, deci se citește și fără JavaScript. Doar adresa vine din
config.

Dacă `.foot-bottom` e azi un singur rând centrat, adaugă în `assets/site.css`,
lângă regula lui:

```css
/* două afirmații pe rândul de jos: firma la stânga, apartenența la dreapta */
.foot-bottom { display: flex; flex-wrap: wrap; gap: var(--sp-3) var(--sp-6);
  justify-content: space-between; }
```

- [ ] **Pasul 4: Verifică**

```bash
for f in index.html despre.html blog.html blog-articol.html; do
  sed -n '/<footer class="site-foot">/,/<\/footer>/p' "$f" | md5 -q
done | sort -u | wc -l
```

Așteptat: `1`.

```bash
tools/audit.sh
```

Așteptat: zero peste tot. În browser: Facebook și Instagram trebuie să fie
linkuri adevărate, iar „Camera de Comerț" trebuie să poarte eticheta roșie
„de completat", fiindcă valoarea e `XXX`. Un clic pe ea explică ce lipsește.

- [ ] **Pasul 5: Comite**

```bash
git add assets/config.js assets/site.js assets/site.css index.html despre.html blog.html blog-articol.html
git commit -m "Subsol: rețelele legate și apartenența la Camera de Comerț

Facebook și Instagram erau deja legate în cod, le lipseau doar adresele, deci
apar și în subsol, și în sameAs din datele structurate. Linkul Camerei de
Comerț rămâne XXX până îl dă clientul, deci se marchează singur."
```

---

### Task 9: `tools/audit.sh` învață pagini din subdirectoare

**Fișiere:**
- Modifică: `tools/audit.sh`

**Interfețe:**
- Produce: `tools/audit.sh en/index.html` funcționează. Task 11 depinde de el;
  fără task-ul ăsta, paginile engleze n-ar putea fi verificate deloc.

**De ce nu merge acum:** scriptul scrie copia de lucru la rădăcină, ca
`_audit_$page`. Pentru `en/index.html` asta ar însemna `_audit_en/index.html`,
un director care nu există, deci scriptul crapă. Și chiar dacă n-ar crăpa, o
copie a unei pagini din `en/` pusă la rădăcină și-ar pierde toate căile
relative `../assets/`, deci ar fi măsurată o pagină fără CSS.

- [ ] **Pasul 1: Arată singur că e stricat**

```bash
mkdir -p en && cp index.html en/proba.html
tools/audit.sh en/proba.html; echo "cod de ieșire: $?"
```

Așteptat: eroare, nu un raport. Notează mesajul exact.

- [ ] **Pasul 2: Copia de lucru stă lângă original**

În `tools/audit.sh`, înlocuiește bucla:

```bash
for page in "${PAGES[@]}"; do
  # Copia de lucru stă lângă original, nu la rădăcină: o pagină din en/ are
  # căile scrise cu ../, iar mutată la rădăcină ar rămâne fără CSS și ar fi
  # măsurată o pagină goală. Din același motiv, calea către audit.js urcă
  # cu tot atâtea ../ câte are pagina.
  dir="$(dirname "$page")"
  base="$(basename "$page")"
  tmp="$dir/_audit_$base"
  sus=""
  [ "$dir" != "." ] && sus="../"
  python3 - "$page" "$tmp" "${sus}tools/audit.js" <<'PY'
import sys
src, dst, script = sys.argv[1], sys.argv[2], sys.argv[3]
s = open(src, encoding='utf-8').read()
open(dst, 'w', encoding='utf-8').write(
    s.replace('</body>', '<script src="%s"></' % script + 'script>\n</body>'))
PY
  for w in "${WIDTHS[@]}"; do
    "$BROWSER" --headless --disable-gpu --virtual-time-budget=10000 \
      --window-size="$w",2000 --dump-dom "file://$ROOT/$tmp" 2>/dev/null \
      | python3 tools/audit_report.py "$page @ ${w}px"
  done
  rm -f "$tmp"
done
```

- [ ] **Pasul 3: NU atinge lista implicită de pagini**

Lista implicită rămâne cele patru pagini românești. Paginile engleze se adaugă
în Task 11, odată cu fișierele lor. Motivul e că între task-ul ăsta și Task 11
mai rulează verificări cu `tools/audit.sh` fără argumente, iar o listă care
trimite la fișiere inexistente le-ar face să raporteze erori care n-au nicio
legătură cu munca din acel moment.

- [ ] **Pasul 4: Verifică**

```bash
tools/audit.sh en/proba.html
```

Așteptat: un raport normal, cu zero, pe toate cele trei lățimi, exact ca
pentru `index.html`. Dacă raportează contraste catastrofale, copia nu și-a
găsit CSS-ul: verifică prefixul `../`.

```bash
tools/audit.sh index.html
```

Așteptat: tot zero. Task-ul nu are voie să strice cazul care mergea.

- [ ] **Pasul 5: Curăță proba și comite**

```bash
rm -f en/proba.html; rmdir en 2>/dev/null
git add tools/audit.sh
git commit -m "Auditul verifică și pagini din subdirectoare

Copia de lucru stă acum lângă original, nu la rădăcină: o pagină din en/ își
scrie căile cu ../ și, mutată la rădăcină, ar fi fost măsurată fără CSS, deci
raportul ar fi arătat contraste inventate. Calea către audit.js urcă odată cu
pagina."
```

---

### Task 10: Textele în două limbi, într-un singur loc

**Fișiere:**
- Modifică: `assets/config.js` (tabela `texte`, câmpurile `numeEn`)
- Modifică: `assets/site.js` (secțiunile 1, 2, 9)

**Interfețe:**
- Produce: `C.texte.ro` și `C.texte.en`, cu aceleași chei; funcția `T(cheie)`
  din `site.js`, care alege ramura după `document.documentElement.lang`.
  Task 11 scrie paginile care se bazează pe ea.
- Nu atinge: telefon, e-mail, adresă, CUI, rețele. **Datele de contact rămân
  o singură dată în config**, indiferent de limbă.

- [ ] **Pasul 1: Tabela de texte**

În `assets/config.js`, după `formularMesaj`:

```js
  /* --- Textele care depind de limbă ---------------------------------------
     Aici intră doar ce se schimbă între română și engleză: întrebările
     formularului și mesajele de WhatsApp. Telefonul, adresa și restul datelor
     de firmă NU se dublează: sunt aceleași în orice limbă și rămân mai sus,
     scrise o singură dată.
     `site.js` alege ramura după `lang` de pe <html>, deci o pagină nouă nu
     are nevoie de nicio linie de cod, doar de atributul corect.          */
  texte: {
    ro: {
      whatsappMessage:
        "Bună ziua! Am găsit Academia pe site și aș vrea detalii despre pregătirea pentru ",
      formularMesaj:
        "Bună ziua, aș dori să particip la cursurile de %MATERIE%, îmi puteți da " +
        "mai multe detalii? Am %VARSTA% ani și aș vrea pregătire %MOD%.",
      varsta: "Câți ani ai?",
      materie: "Ce materie te-ar interesa?",
      mod: "Vrei să înveți singur sau în grupă?",
      grupMaterii: "Materii",
      grupCursuri: "Cursuri speciale",
      individual: "Singur, unu la unu",
      inGrupa: "În grupă de maximum trei",
      valIndividual: "individuală",
      valInGrupa: "în grupă",
      saiMult: "19 sau mai mult",
      pasul: "Pasul %N% din %TOTAL%",
      inapoi: "← Înapoi",
      gata: "Gata",
    },
    en: {
      whatsappMessage:
        "Hello! I found the Academy online and I would like details about preparation for ",
      formularMesaj:
        "Hello, I would like to join the %MATERIE% classes, could you send me " +
        "more details? I am %VARSTA% years old and I would prefer %MOD% lessons.",
      varsta: "How old are you?",
      materie: "Which subject are you interested in?",
      mod: "Would you rather study alone or in a small group?",
      grupMaterii: "Subjects",
      grupCursuri: "Special courses",
      individual: "Alone, one to one",
      inGrupa: "In a group of no more than three",
      valIndividual: "one to one",
      valInGrupa: "small group",
      saiMult: "19 or older",
      pasul: "Step %N% of %TOTAL%",
      inapoi: "← Back",
      gata: "Done",
    },
  },
```

`whatsappMessage` și `formularMesaj` rămân și în afara tabelei, cum sunt
acum: `site.js` le folosește ca rezervă dacă tabela lipsește, deci un config
vechi nu rupe pagina.

- [ ] **Pasul 2: Numele englezești ale materiilor**

Adaugă `numeEn` pe intrările din `materii` unde numele diferă:

```js
    { nume: "Română",      numeEn: "Romanian",   profesori: ["Luiza"] },
    { nume: "Matematică",  numeEn: "Mathematics", profesori: ["Andra"],  pana: 14 },
    { nume: "Matematică",  numeEn: "Mathematics", profesori: ["Andrei"], de: 15 },
    { nume: "Istorie",     numeEn: "History",     profesori: ["Ștefania"] },
    { nume: "Geografie",   numeEn: "Geography",   profesori: ["Alexandra"] },
    { nume: "Logică",      numeEn: "Logic",       profesori: ["Sara"] },
    { nume: "Biologie",    numeEn: "Biology",     profesori: ["Andra", "Cătălin"] },
    { nume: "Chimie",      numeEn: "Chemistry",   profesori: ["Ioana"] },
    { nume: "Engleză",     numeEn: "English",     profesori: ["Denisa", "Ștefania"] },
    { nume: "Spaniolă",    numeEn: "Spanish",     profesori: ["Diana"] },
    { nume: "Franceză",    numeEn: "French",      profesori: ["Sara", "Ilinca"] },
    { nume: "Chineză",     numeEn: "Chinese",     profesori: ["Alex", "Ana"] },
    { nume: "Coreeană",    numeEn: "Korean",      profesori: ["Jun"] },
    { nume: "Maghiară",    numeEn: "Hungarian",   profesori: [] },
    { nume: "Informatică", numeEn: "Computer science", profesori: ["Andrei"] },
    { nume: "Greacă",      numeEn: "Greek",       profesori: ["Denisa"] },
```

Și pe cursurile speciale:

```js
    { nume: "Excel",                profesori: [] },
    { nume: "Contabilitate",        numeEn: "Accounting",         profesori: [] },
    { nume: "Dicție",               numeEn: "Diction",            profesori: [] },
    { nume: "Dezvoltare personală", numeEn: "Personal development", profesori: [] },
    { nume: "Educație financiară",  numeEn: "Financial literacy", profesori: [] },
```

„Excel" n-are `numeEn`: e același cuvânt. Când `numeEn` lipsește, se
folosește `nume`.

- [ ] **Pasul 3: Funcția care alege limba**

În `assets/site.js`, sus, lângă `PLACEHOLDER` (linia 9):

```js
  /* Limba paginii se citește de pe <html lang>, nu din adresă: o pagină nouă
     are nevoie doar de atributul corect, nu de o linie în cod aici.       */
  var LIMBA = (document.documentElement.lang || "ro").slice(0, 2);
  var TEXTE = (C.texte && C.texte[LIMBA]) || (C.texte && C.texte.ro) || {};
  function T(cheie, rezerva) {
    return TEXTE[cheie] !== undefined ? TEXTE[cheie] : rezerva;
  }
  /* Numele materiei în limba paginii; fără traducere, rămâne cel românesc. */
  function numeMaterie(m) { return (LIMBA === "en" && m.numeEn) || m.nume; }
```

- [ ] **Pasul 4: Folosește-o în formular și în WhatsApp**

În secțiunea 1, mesajul implicit devine
`T("whatsappMessage", C.whatsappMessage)`.

În secțiunea 2, înlocuiește textele scrise direct:

- `intrebare: "Câți ani ai?"` devine `intrebare: T("varsta", "Câți ani ai?")`
- la fel pentru `materie` și `mod`
- `"19 sau mai mult"` devine `T("saiMult", "19 sau mai mult")`
- cele două opțiuni de mod își iau `eticheta` din `T("individual", ...)` și
  `T("inGrupa", ...)`, iar `valoare` din `T("valIndividual", ...)` și
  `T("valInGrupa", ...)`
- `GRUP_MATERII` și `GRUP_CURSURI` devin `T("grupMaterii", "Materii")` și
  `T("grupCursuri", "Cursuri speciale")`
- `numar.textContent = "Pasul " + (indice + 1) + " din " + PASI.length;`
  devine:
  ```js
      numar.textContent = T("pasul", "Pasul %N% din %TOTAL%")
        .replace("%N%", indice + 1).replace("%TOTAL%", PASI.length);
  ```
- `b.textContent = "← Înapoi";` devine `b.textContent = T("inapoi", "← Înapoi");`
- `numar.textContent = "Gata";` din `deseneazaRezultat` devine
  `T("gata", "Gata")`
- eticheta fiecărei materii devine `numeMaterie(m)` în loc de `m.nume`
- `mesajWhatsApp` ia șablonul din `T("formularMesaj", C.formularMesaj)`

**Atenție:** `raspunsuri.materie` se compară cu `m.nume` în `profesoriPentru`.
Dacă eticheta devine `numeMaterie(m)`, valoarea trebuie să rămână `m.nume`,
altfel căutarea profesorului nu mai găsește nimic în engleză. Deci:

```js
            return { eticheta: numeMaterie(m), valoare: m.nume };
```

iar în `mesajWhatsApp`, materia care intră în mesaj se traduce la ieșire:

```js
    function mesajWhatsApp() {
      var sablon = T("formularMesaj", C.formularMesaj) || "";
      var alesa = materii.filter(function (m) { return m.nume === raspunsuri.materie; })[0];
      return sablon.replace("%MATERIE%", alesa ? numeMaterie(alesa) : raspunsuri.materie)
                   .replace("%VARSTA%", raspunsuri.varsta)
                   .replace("%MOD%", raspunsuri.mod);
    }
```

- [ ] **Pasul 5: Datele structurate știu limba**

În secțiunea 9:

```js
      inLanguage: LIMBA === "en" ? "en" : "ro-RO",
```

Iar firul Ariadnei nu are voie să pună titluri românești pe o pagină engleză.
`aici` se calculează azi din `location.pathname.split("/").pop()`, deci
`en/despre.html` dă tot `despre.html`. Înlocuiește tabela cu una pe limbi și
compune adresa cu prefixul limbii:

```js
    var aici = location.pathname.split("/").pop();
    var prefix = LIMBA === "en" ? "/en/" : "/";
    var TITLURI = LIMBA === "en"
      ? { "despre.html": "About" }
      : { "despre.html": "Despre", "blog.html": "Blog", "blog-articol.html": "Articol" };
    if (TITLURI[aici]) {
      noduri.push({
        "@type": "BreadcrumbList",
        itemListElement: [
          { "@type": "ListItem", position: 1,
            name: LIMBA === "en" ? "Home" : "Acasă", item: baza + prefix },
          { "@type": "ListItem", position: 2, name: TITLURI[aici],
            item: baza + prefix + aici }
        ]
      });
    }
```

`scoala.url` și `"@id"` rămân pe adresa românească: e aceeași școală, nu două.

- [ ] **Pasul 6: Verifică fără să existe încă vreo pagină engleză**

```bash
tools/audit.sh index.html
```

Așteptat: zero. Nimic vizibil nu s-a schimbat în română, fiindcă `T()` cade
pe ramura `ro`, care are exact textele de dinainte.

În browser, pe `index.html`, parcurge formularul până la capăt și verifică
mesajul de WhatsApp: trebuie să fie identic cu cel de dinainte de task.
Apoi, în consolă:

```js
document.documentElement.lang = "en";
```

și reîncarcă: nu e un test adevărat (pagina e în română), dar dacă formularul
se desenează în engleză, legarea funcționează.

- [ ] **Pasul 7: Comite**

```bash
git add assets/config.js assets/site.js
git commit -m "Textele formularului, în două limbi, alese după lang

Tabela `texte` din config ține doar ce se schimbă între limbi: întrebările și
mesajele de WhatsApp. Datele de contact rămân scrise o singură dată, regula
proiectului nu se atinge. Materia intră în mesaj tradusă, dar valoarea
păstrată rămâne cea românească, altfel căutarea profesorului n-ar mai găsi
nimic. Firul Ariadnei nu mai pune titluri românești pe adresa engleză."
```

---

### Task 11: Paginile engleze, comutatorul și hreflang

**Fișiere:**
- Creează: `en/index.html`, `en/despre.html`
- Modifică: `index.html`, `despre.html`, `blog.html`, `blog-articol.html`
  (comutatorul în antet, plus `hreflang` în `<head>` la primele două)
- Modifică: `sitemap.xml`
- Modifică: `CLAUDE.md`, secțiunea „Zonele pentru PHP"

**Interfețe:**
- Consumă din Task 10: `C.texte.en`, `numeEn`, `LIMBA`, `T()`.
- Consumă din Task 9: `tools/audit.sh` merge pe pagini din subdirectoare.

- [ ] **Pasul 1: Comutatorul, în cele patru pagini românești**

În antet, în `.head-actions`, **înainte** de butonul de WhatsApp:

```html
      <a class="navlink lang-comutator" href="en/index.html" hreflang="en" lang="en">EN</a>
```

Pe paginile din `en/`, același element arată spre română:

```html
      <a class="navlink lang-comutator" href="../index.html" hreflang="ro" lang="ro">RO</a>
```

Adresa e scrisă în HTML, nu calculată în JavaScript: sunt șase pagini, nu
șase sute, iar un comutator care depinde de script ar dispărea dacă scriptul
nu ajunge.

`blog.html` și `blog-articol.html` nu au pereche engleză, deci comutatorul lor
trimite la `en/index.html`. E onest: duce la versiunea engleză a site-ului,
nu pretinde că există un blog tradus.

În `assets/site.css`, lângă regulile antetului:

```css
/* Comutatorul de limbă: pastilă îngustă, doar două litere. Pe telefon iese
   din bară și intră în meniu, unde e loc: la 393px bara are deja siglă,
   hamburger și WhatsApp.                                                  */
.lang-comutator { padding-inline: var(--sp-3); font-weight: var(--fw-semibold); }
@media (max-width: 560px) {
  .head-actions .lang-comutator { display: none; }
}
```

Și, ca să apară în meniul de telefon, adaugă-l și în `<nav id="meniu">`, la
capăt, în toate paginile:

```html
      <a class="navlink lang-meniu" href="en/index.html" hreflang="en" lang="en">English</a>
```

```css
/* În bară, versiunea din meniu ar fi un al doilea comutator: se ascunde. */
.lang-meniu { display: none; }
@media (max-width: 560px) { #meniu .lang-meniu { display: block; } }
```

- [ ] **Pasul 2: `hreflang` în ambele sensuri**

În `<head>`-ul lui `index.html`, sub `<link rel="canonical">`:

```html
<link rel="alternate" hreflang="ro" href="https://studiumgenerale.ro/">
<link rel="alternate" hreflang="en" href="https://studiumgenerale.ro/en/">
<link rel="alternate" hreflang="x-default" href="https://studiumgenerale.ro/">
```

În `despre.html`:

```html
<link rel="alternate" hreflang="ro" href="https://studiumgenerale.ro/despre.html">
<link rel="alternate" hreflang="en" href="https://studiumgenerale.ro/en/despre.html">
<link rel="alternate" hreflang="x-default" href="https://studiumgenerale.ro/despre.html">
```

Aceleași trei linii, identice, se pun și în paginile engleze corespunzătoare.
Google ignoră o pereche `hreflang` care nu se confirmă în ambele sensuri, deci
jumătate de treabă nu valorează nimic.

`blog.html` și `blog-articol.html` nu primesc `hreflang`: n-au pereche.

- [ ] **Pasul 3: `en/index.html`**

Pornește de la `index.html` și tradu-l bloc cu bloc. Structura rămâne
identică: aceleași clase, aceleași `data-*`, aceleași `data-aos`. Ce se
schimbă:

**Căile.** Toate devin relative la `en/`:
`assets/` devine `../assets/`, `brand/` devine `../brand/`,
`index.html` devine `index.html` (pagina engleză), iar `blog.html` devine
`../blog.html`.

**`<html lang="en">`.** De asta depinde tot Task 10.

**`<head>`:**

```html
<title>Academia · exam preparation in Bucharest, for the Romanian Baccalaureate</title>
<meta name="description" content="Studium Generale by Denisa. One to one or in groups of no more than three students, with a written progress report every month. The first session is not a lesson: we get to know each other and set goals.">
<link rel="canonical" href="https://studiumgenerale.ro/en/">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="ro_RO">
<meta property="og:url" content="https://studiumgenerale.ro/en/">
```

`theme-color`, `author`, `og:image` și dimensiunile lui rămân neschimbate.

**Titlurile secțiunilor:**

| română | engleză |
|---|---|
| Pregătire serioasă pentru Bacalaureat și Evaluarea Națională | Serious preparation for the Baccalaureate and the National Evaluation |
| Cum lucrăm / Patru lucruri pe care le facem altfel | How we work / Four things we do differently |
| Video de prezentare / Cum arată de fapt o ședință | Video / What a session actually looks like |
| Ce se predă / Toate materiile, o singură academie | What we teach / Every subject, one academy |
| Dincolo de programă / Cursuri speciale | Beyond the curriculum / Special courses |
| Ce spun părinții / Recenzii | What parents say / Reviews |
| Întrebări frecvente / Ce ne întreabă părinții cel mai des | Frequently asked / What parents ask most often |
| Programare / Trei întrebări și știți cu cine lucrați | Booking / Three questions and you know who you would work with |
| Prima ședință nu e o lecție: ne cunoaștem, stabilim obiective, stabilim rezonanța | The first session is not a lesson: we get to know each other, we set goals, we see if we click |

**Nota de sub recenzii și cea de sub FAQ se traduc și ele.** Sunt
avertismente că textul nu e încă real; o pagină din care lipsesc arată ca și
cum recenziile inventate ar fi adevărate.

**Ce nu se traduce:** numele materiilor din carduri rămân în engleză conform
tabelului `numeEn` din Task 10, ca să se potrivească cu formularul. Numele
mărcii, „Academia · Studium Generale by Denisa", rămâne neschimbat.

- [ ] **Pasul 4: `en/despre.html`**

Aceeași metodă, pornind de la `despre.html`. `canonical` devine
`https://studiumgenerale.ro/en/despre.html`.

- [ ] **Pasul 5: Lista implicită a auditului și `sitemap.xml`**

În `tools/audit.sh`, abia acum, când fișierele există:

```bash
[ -z "${PAGES[0]}" ] && PAGES=(index.html despre.html blog.html blog-articol.html en/index.html en/despre.html)
```

Apoi `sitemap.xml`:

```xml
  <url>
    <loc>https://studiumgenerale.ro/en/</loc>
    <lastmod>2026-09-09</lastmod>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://studiumgenerale.ro/en/despre.html</loc>
    <lastmod>2026-09-09</lastmod>
    <priority>0.7</priority>
  </url>
```

Actualizează `lastmod` și la cele patru adrese românești: s-au schimbat
în planul ăsta.

- [ ] **Pasul 6: Verifică**

```bash
tools/audit.sh en/index.html
tools/audit.sh en/despre.html
tools/audit.sh
```

Așteptat: zero peste tot, pe toate șase paginile. Dacă paginile engleze
raportează contraste ciudate, prima bănuială e o cale `../` uitată, deci CSS
lipsă; verifică în browser că pagina arată ca site-ul, nu ca HTML gol.

```bash
# antetul, identic între cele două pagini engleze
for f in en/index.html en/despre.html; do
  sed -n '/<header class="site-head">/,/<\/header>/p' "$f" | md5 -q
done | sort -u | wc -l
```

Așteptat: `1`. Repetă pentru `<footer` și pentru cele patru pagini românești,
separat.

```bash
# hreflang confirmat în ambele sensuri
grep -c 'rel="alternate"' index.html despre.html en/index.html en/despre.html
```

Așteptat: `3` la fiecare.

În browser: deschide `en/index.html`, parcurge formularul până la capăt și
verifică mesajul de WhatsApp. Trebuie să fie în engleză și să conțină numele
englezesc al materiei. Verifică apoi că butonul RO duce înapoi la pagina
românească și că butonul EN de pe pagina românească duce încoace.

- [ ] **Pasul 7: Scrie costul în CLAUDE.md**

În secțiunea „Zonele pentru PHP", înlocuiește prima frază:

```markdown
Antetul și subsolul sunt identice octet cu octet în cele patru pagini
românești, ca să poată fi extrase în `header.php` / `footer.php` fără nicio
schimbare de stil. Paginile din `en/` au propria pereche, identică între ele
dar nu cu cele românești: căile către `assets/` și `brand/` urcă un nivel, iar
textele sunt traduse. Deci un text din antet se schimbă acum în **șase**
fișiere, nu în patru.
```

Adaugă și versiunea engleză în lista de comenzi și în descrierea proiectului
din capul fișierului.

- [ ] **Pasul 8: Comite**

```bash
git add en index.html despre.html blog.html blog-articol.html assets/site.css sitemap.xml tools/audit.sh CLAUDE.md
git commit -m "Versiune engleză pentru Acasă și Despre, cu hreflang și comutator

Pagini separate, nu comutator din JavaScript: Google indexează amândouă
limbile și linkul trimis pe WhatsApp poate fi cel englezesc. hreflang e scris
în ambele sensuri, altfel Google îl ignoră. Blogul rămâne românesc, fiind
încă machetă pentru un backend care nu există.

Antetul se schimbă acum în șase fișiere, nu în patru; scris în CLAUDE.md."
```

---

### Task 12: Verificarea finală și evidențele

**Fișiere:**
- Modifică: `CLAUDE.md` (ce nu e adevărat în pagină), `README.md`

- [ ] **Pasul 1: Auditul pe tot, pe toate lățimile**

```bash
tools/audit.sh
```

Așteptat: **zero peste tot**, pe cele șase pagini și pe cele trei lățimi.
Orice altceva se repară acum, nu se raportează ca „minor".

- [ ] **Pasul 2: Telefonul, la lățimea adevărată**

```bash
tools/mobil.sh index.html /tmp/final-ro.png
tools/mobil.sh en/index.html /tmp/final-en.png
```

Chrome headless nu coboară sub 500px lățime de fereastră, deci o captură
cerută la 393px iese randată la 485 și tăiată pe dreapta. `tools/mobil.sh`
ocolește asta punând pagina într-un iframe de 393px. Uită-te la capturi:
antet fără text de marcă, meniu bleumarin pe tot ecranul, o recenzie pe
ecran, opțiunile formularului două pe rând, totul aliniat la stânga.

- [ ] **Pasul 3: Fără AOS**

Blochează `cdnjs.cloudflare.com` în browser și reîncarcă `index.html`.
Așteptat: pagina se vede întreagă. `aos.css` nu se mai încarcă din 2026-09-07,
dar clasa `fara-aos` de pe `<html>` trebuie să apară, pusă din `onerror`-ul
etichetei `<script>`. Cele două secțiuni noi, `#cursuri` și `#intrebari`, au
`data-aos`, deci sunt exact cele care ar dispărea dacă plasa de siguranță e
ruptă.

- [ ] **Pasul 4: Fără mișcare**

Pornește `prefers-reduced-motion: reduce` din unelte și reîncarcă. Așteptat:
totul vizibil, nimic nu se mișcă, iar acordeonul FAQ se deschide fără
animație de săgeată.

- [ ] **Pasul 5: Datele structurate**

Trece prin validatorul Google pentru date structurate cele două pagini
principale. Așteptat: `EducationalOrganization` cu **20** de oferte în catalog și
`FAQPage` cu șase întrebări, fără erori. Douăzeci, nu 21: `hasOfferCatalog`
deduplică după nume, iar „Matematică" apare de două ori în `materii`, o dată
pentru gimnaziu și o dată pentru liceu. Cardurile din pagină rămân 16, fiindcă
acolo sunt două carduri distincte. Avertismentele despre câmpuri opționale se
pot ignora.

- [ ] **Pasul 6: Actualizează ce nu e adevărat în pagină**

În `CLAUDE.md`, secțiunea „Ce nu e adevărat în pagină", adaugă la lista de
lucruri care nu au voie să ajungă publice așa cum sunt:

```markdown
Sunt încă neconfirmate și marcate ca atare: cele șase răspunsuri din FAQ,
scrise din ce spune deja site-ul dar necitite de client; cine predă cele cinci
cursuri speciale; și adresa paginii de membru al Camerei de Comerț. Primele au
o notă vizibilă în pagină, ultimele două poartă eticheta roșie „de completat".
```

Actualizează și numărul materiilor unde apare: „Cele 15 materii din secțiunea
Materii sunt reale" rămâne adevărat ca număr de materii distincte, dar în
pagină sunt 16 carduri, fiindcă matematica are unul de gimnaziu și unul de
liceu. Scrie asta explicit, ca să nu mai pară o contradicție. Adaugă și cele
cinci cursuri speciale, care nu sunt materii de examen.

Tabelele de poze din `README.md` au fost deja actualizate în Task 5 și Task 6.
Aici doar **verifici** că fiecare fișier din `assets/foto/motive/` și
`assets/foto/cursuri/` are un rând în tabel și în `SURSE.txt`:

```bash
for d in motive cursuri; do
  for f in assets/foto/$d/*.webp; do
    n=$(basename "$f" .webp)
    grep -q "$n" "assets/foto/$d/SURSE.txt" || echo "LIPSEȘTE din SURSE.txt: $f"
    grep -q "$n" README.md || echo "LIPSEȘTE din README.md: $f"
  done
done
```

Așteptat: nicio linie.

- [ ] **Pasul 7: Comite**

```bash
git add CLAUDE.md README.md
git commit -m "Evidențele, aduse la zi după refresh

Ce e marcat „de completat\" și ce e scris dar neconfirmat, plus tabelele de
poze cu cele nouă fotografii noi."
```

---

## Ce rămâne de făcut după plan

Lucruri care nu se pot închide din cod și pentru care clientul trebuie
întrebat. Se raportează la final, într-o listă scurtă:

1. **Linkul Camerei de Comerț.** Marcat „de completat" în subsol.
2. **Cine predă cele cinci cursuri speciale.** Marcate „de completat" în
   formular.
3. **Cele șase răspunsuri din FAQ.** Scrise din ce spune deja site-ul, dar
   necitite de client. Nota vizibilă din pagină se șterge doar după ce le
   confirmă.
4. **Subtitlul din antet.** A rămas Montserrat, nu Great Vibes, fiindcă la
   11px caligrafia nu se citește. Dacă îl vrea totuși caligrafic, trebuie
   urcat la 16px și scoase majusculele.
5. **Ce era deja neadevărat și rămâne așa:** cifrele din hero (media 8.40,
   92%), cele patru recenzii, prețurile.
