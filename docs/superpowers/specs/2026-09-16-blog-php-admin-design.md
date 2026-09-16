# Blog în PHP și tablou de administrare

Data: 2026-09-16

## Ce construim

Blogul devine dinamic. Articolele stau în MySQL și se scriu dintr-un tablou de
administrare la `/admin`, protejat de un singur cont. Paginile publice de la
`/blog` le citesc din bază și le toarnă într-un șablon. Restul site-ului, cele
șapte pagini HTML, rămâne exact cum e.

Până acum blogul erau două fișiere statice, `blog.html` cu trei carduri scrise
de mână și `blog-articol.html` cu un articol de probă. Nimic din ele nu se
putea schimba fără un commit.

## Ce e decis

Patru întrebări, patru răspunsuri, de la client la 2026-09-16:

1. **Antetul și subsolul** se extrag în `header.php` și `footer.php`, folosite
   doar de paginile PHP. Cele șapte pagini HTML nu se ating.
2. **Adresele** sunt `/blog/` pentru listă și `/blog/titlul-articolului` pentru
   un articol, cu redirect 301 de la vechile `/blog.html` și
   `/blog-articol.html`.
3. **Câmpurile** unui articol sunt cele pe care le arată deja șablonul: titlu,
   slug, etichetă, rezumat, text, dată, stare. Timpul de citit se calculează.
4. **Contul** se face o singură dată, dintr-o pagină `setup.php` care refuză să
   mai ruleze după ce există un cont.

## Versiunea de PHP

**8.1**, spusă de client la 2026-09-16. Deci se pot folosi tipurile pe
proprietăți, `match`, proprietățile promovate în constructor și `readonly`.
Se folosesc unde fac codul mai scurt sau mai greu de greșit, nu fiindcă
există: un blog cu două tabele nu are nevoie de ierarhii de clase.

Un lucru pe care 8.1 îl schimbă și contează aici: `PDO` aruncă implicit
excepții de la 8.0 încolo, deci o interogare greșită nu mai trece tăcut mai
departe. Conexiunea o cere oricum explicit, dar e bine știut.

## Fișierele

```
header.php              antetul, sursă unică pentru paginile PHP
footer.php              subsolul, la fel
inc/
  .htaccess             „Require all denied": nimic de aici nu se cere din browser
  config.php            DOAR pe server: gazdă, bază, utilizator, parolă
  db.php                conexiunea PDO
  auth.php              sesiune, autentificare, CSRF
  articole.php          interogările despre articole
  sablon.php            escape, slug, timp de citit, data în română
blog/
  index.php             lista articolelor publicate
  articol.php           un articol, după slug
  sitemap.php           harta blogului, servită ca /blog/sitemap.xml
admin/
  index.php             lista tuturor articolelor, cu editare și ștergere
  autentificare.php     formularul de intrare
  iesire.php            ieșirea din cont
  editor.php            creare și editare
  sterge.php            ștergerea, numai prin POST cu jeton
  setup.php             o singură dată, apoi se șterge de pe server
```

`header.php` scrie căi absolute, `/assets/...` și `/brand/...`, exact cum face
deja `404.html`. Așa merge neschimbat și de la rădăcină, și din `/blog/`, și
din `/admin/`, fără să numere nimeni câte niveluri trebuie urcate.

## Baza de date

Baza există deja, făcută de client din cPanel. Numele bazei, utilizatorul și
parola nu se scriu nicăieri în depozit, fiindcă **depozitul e public**: stau
numai în `inc/config.php`, pe server. Colația e `utf8mb4_unicode_ci`, altfel
diacriticele din titluri și din text se strică, iar site-ul e în română.

```sql
CREATE TABLE administratori (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190)  NOT NULL UNIQUE,
  parola_hash   VARCHAR(255)  NOT NULL,
  esecuri       INT           NOT NULL DEFAULT 0,
  blocat_pana   DATETIME      NULL,
  creat_la      DATETIME      NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articole (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  slug            VARCHAR(190)  NOT NULL UNIQUE,
  titlu           VARCHAR(255)  NOT NULL,
  eticheta        VARCHAR(60)   NOT NULL,
  rezumat         VARCHAR(400)  NOT NULL,
  text            MEDIUMTEXT    NOT NULL,
  data_publicare  DATE          NOT NULL,
  stare           ENUM('ciorna','publicat') NOT NULL DEFAULT 'ciorna',
  creat_la        DATETIME      NOT NULL,
  actualizat_la   DATETIME      NOT NULL,
  INDEX lista (stare, data_publicare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`esecuri` și `blocat_pana` sunt pentru încercările de intrare, explicate mai
jos. Indexul `lista` acoperă singura interogare publică: articolele publicate,
în ordinea datei.

## Configurarea și parola bazei

`inc/config.php` întoarce un tablou cu gazda, numele bazei, utilizatorul și
parola. Fișierul:

- **nu intră în git.** E în `.gitignore`. O parolă ajunsă în istoricul git nu
  se mai scoate de acolo.
- **nu se urcă prin deploy.** E în lista de excluderi din
  `.github/workflows/deploy.yml`, deci acțiunea nici nu-l trimite, nici nu-l
  suprascrie pe cel de pe server.
- **se pune o singură dată, de mână, prin FTP**, și rămâne acolo: acțiunea de
  deploy nu șterge niciodată fișiere de pe server.
- **nu se poate citi din browser.** `inc/.htaccess` refuză tot. Chiar și fără
  el, Apache ar executa fișierul și ar întoarce gol, dar două straturi costă
  o linie.

Ambele garduri sunt puse deja, înainte să existe fișierul, ca să nu existe
fereastra în care un commit grăbit îl ia cu el.

## Autentificare

Un singur cont. Parola se ține ca `password_hash` cu bcrypt și se verifică cu
`password_verify`; parola în clar nu se scrie nicăieri, nici în bază, nici în
jurnal.

- Sesiunea se regenerează la intrare, ca să nu se poată fixa dinainte.
- Cookie-ul e `HttpOnly`, `Secure` și `SameSite=Strict`. Site-ul e pe HTTPS,
  deci `Secure` nu strică nimic.
- Orice scriere, creare, modificare, ștergere, trece prin POST cu un jeton
  CSRF comparat cu `hash_equals`. Un GET nu schimbă nimic niciodată.
- Mesajul de eroare la intrare e același pentru e-mail greșit și pentru parolă
  greșită: altfel formularul spune dacă un e-mail există.
- După cinci încercări ratate contul se blochează zece minute, scris în
  `esecuri` și `blocat_pana`. În bază, nu în sesiune: altfel se ocolește
  ștergând un cookie.
- `/admin` primește antetul `X-Robots-Tag: noindex` și o linie `Disallow` în
  `robots.txt`.

Toate interogările sunt pregătite, cu `PDO::ATTR_EMULATE_PREPARES` pe `false`
și erorile pe excepții.

## Rutare

În `.htaccess` de la rădăcină, **sub** blocul pus de cPanel. Blocul acela e
generat de MultiPHP INI Editor, scrie „do not edit" și trimite erorile de PHP
în `/home/…/logs/php.error.log`; se păstrează cuvânt cu cuvânt, altfel cPanel
îl pune înapoi peste regulile noastre. Fișierul e împletit și pus pe server de
la 2026-09-16, cu partea care nu ține de blog: 404, cache, antete de
securitate. Regulile de mai jos se adaugă odată cu blogul, fiindcă un 301 de
la `/blog.html` înainte să existe `/blog/index.php` ar rupe pagina care merge
acum.

```
/blog/                  ->  blog/index.php
/blog/sitemap.xml       ->  blog/sitemap.php
/blog/<slug>            ->  blog/articol.php?slug=<slug>
/blog.html              ->  301 /blog/
/blog-articol.html      ->  301 /blog/
```

Slugul acceptă doar litere mici, cifre și cratime. Orice altceva nu se
potrivește cu regula și cade în 404.

## Randarea

**Textul e text.** Se scrie în pagină prin `htmlspecialchars` cu
`ENT_QUOTES | ENT_SUBSTITUTE` și `UTF-8`. Rândurile goale despart paragrafe.
Nimic din ce se scrie în editor nu poate ajunge marcaj în pagină, deci un
articol nu poate strica nici stilul, nici scriptul paginii.

**Timpul de citit** se calculează, nu se scrie: numărul de cuvinte împărțit la
200, rotunjit în sus, minimum un minut. Scris de mână, ar rămâne „6 min" pe un
articol care a ajuns între timp de două ori mai lung.

**Data** se scrie în română, „16 septembrie 2026", cu `datetime` mașinabil în
atributul `<time>`.

**Clasele rămân cele din paginile statice**, `.articol-card`, `.tag`,
`.articol-meta`, `.articol`, ca stilul să nu fie rescris. Întârzierile AOS din
listă se calculează din poziția cardului, ca acum: 0, 70, 140 și tot așa.

**SEO, pentru fiecare articol**: titlu în formatul „Titlul articolului ·
Academia", meta description din rezumat, `canonical`, Open Graph și JSON-LD
`BlogPosting`, toate din bază. Exact ce anticipa comentariul din
`tools/seo.js:294`.

**`/blog/sitemap.xml`** se generează din articolele publicate. `robots.txt`
trimite la ambele hărți, cea statică și cea a blogului.

## Ce se schimbă în ce există

| Fișier | Schimbarea |
|---|---|
| 7 fișiere HTML | linkul `Blog` din meniu și subsol, spre `/blog/` |
| `sitemap.xml` | `/blog.html` și `/blog-articol.html` scoase, `/blog/` pus |
| `llms.txt` | adresa blogului |
| `robots.txt` | `Disallow: /admin/`, plus harta blogului |
| `tools/seo.js` | cele două intrări de blog scoase: PHP-ul le face acum |
| `blog.html`, `blog-articol.html` | șterse din repo |
| `.htaccess` | regulile de rescriere și cele trei 301-uri |

**De ținut minte:** acțiunea de deploy nu șterge nimic de pe server. Cele două
fișiere statice trebuie șterse manual prin FTP, altfel rămân acolo. Regulile de
rescriere oricum trec înaintea lor, deci nu s-ar vedea, dar fișierele moarte pe
un server public sunt o datorie, nu o pagubă.

## Erori

- **Slug inexistent, sau articol în stare de ciornă:** cod 404 adevărat și
  pagina `404.html`, aceeași ca în restul site-ului.
- **Baza de date nu răspunde:** pagină simplă în română, cod 503, fără urmă de
  excepție în pagină. Detaliul se scrie în jurnalul serverului.
- **Unde se citesc:** erorile de PHP de pe server ajung în
  `/home/…/logs/php.error.log`, pus de blocul cPanel din `.htaccess`. Local,
  în ieșirea lui `php -S`.
- **Formular greșit completat:** erorile se arată lângă câmpuri și textul scris
  rămâne în formular. Un articol lung nu se pierde pentru o etichetă uitată.
- **Slug care se repetă:** se adaugă un sufix numeric, `titlu-2`.

## Testare

Local sunt PHP 8.5, MySQL 9.7 și Docker; portul 3306 al gazdei e închis din
afară, deci nu se lucrează pe baza de producție, ceea ce e și bine.

- O bază locală cu aceeași schemă și câteva articole de probă.
- `php -S` pentru pagini.
- Cazurile care contează: intrare cu parolă greșită, blocarea după cinci
  încercări, POST fără jeton CSRF, slug inexistent, articol în ciornă cerut
  direct după slug, text cu `<script>` în el, text cu diacritice, titlu care
  produce un slug deja luat.
- `tools/audit.sh` peste paginile de blog randate, ca să țină același prag ca
  restul site-ului: zero la contrast, la text tăiat, la ieșit din ecran și la
  suprapuneri.

## Punerea pe server, în ordine

1. Deploy normal, prin împingere în main.
2. `inc/config.php` urcat o dată, de mână, prin FTP.
3. `/admin/setup.php` deschis o dată: creează tabelele și contul.
4. `setup.php` șters de pe server.
5. `blog.html` și `blog-articol.html` șterse de pe server.

## Ce nu se face acum

Fără text îmbogățit, fără Markdown, fără poze în articole, fără categorii ca
tabel separat, fără căutare, fără paginare, fără comentarii, fără al doilea
cont, fără versiuni ale unui articol, fără previzualizare. Textul e text simplu,
cum s-a cerut. Fiecare dintre ele se poate adăuga peste schema asta fără s-o
rescrie.

## Ce mai lipsește

- **Parola de FTP** ar fi bine rotită, a trecut prin chat.
