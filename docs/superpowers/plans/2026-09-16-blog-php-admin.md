# Blog în PHP și tablou de administrare · plan de implementare

> **Pentru cine execută cu agenți:** SUB-SKILL OBLIGATORIU: folosiți
> superpowers:subagent-driven-development (recomandat) sau
> superpowers:executing-plans ca să duceți planul sarcină cu sarcină. Pașii
> au căsuțe (`- [ ]`) ca să se poată bifa.

**Scopul:** articolele de blog ajung în MySQL, se scriu dintr-un tablou de
administrare la `/admin` cu un singur cont, iar `/blog` le citește din bază și
le toarnă în șablonul care există deja.

**Arhitectura:** PHP simplu, fără framework și fără composer, cum e tot restul
site-ului. Funcții în `inc/`, pagini în `blog/` și `admin/`, antet și subsol
comune în `header.php` și `footer.php`. Rutarea se face din `.htaccess`, sub
blocul pus de cPanel. Cele șapte pagini HTML statice nu se ating, doar linkul
lor spre blog.

**Uneltele:** PHP 8.1 pe gazdă (8.5 local), MySQL, PDO. Zero dependențe noi.
Testele rulează cu un hămuleț propriu de vreo 40 de rânduri, fiindcă CLAUDE.md
spune „fără build și fără framework" și un PHPUnit adus cu composer ar fi a
doua dependență a proiectului, după AOS.

**Specificația:** `docs/superpowers/specs/2026-09-16-blog-php-admin-design.md`

## Constrângeri globale

Se aplică la fiecare sarcină, nu se repetă în ele:

- **PHP 8.1** e versiunea gazdei. Nimic introdus după ea.
- **Totul în română:** nume de funcții, de variabile, de tabele, de coloane,
  comentarii, texte din pagină, mesaje de eroare.
- **Fără cratime lungi** („em dash") nicăieri: nici în cod, nici în
  comentarii, nici în textele din pagină. Virgulă, punct, două puncte, `·`.
- **Fără dependențe noi.** Fără composer, fără pachete.
- **Clasele CSS rămân exact cele din paginile statice.** `.card`,
  `.articol-card`, `.tag`, `.articol-meta`, `.articol`, `.articole`. Nu se
  scrie CSS nou decât pentru `/admin`, care n-are corespondent static.
- **`utf8mb4_unicode_ci`** peste tot în bază. Site-ul e în română.
- **Nimic din `inc/config.php` nu intră în git.** E deja în `.gitignore` și în
  excluderile din `.github/workflows/deploy.yml`.
- **Depozitul e public.** Nici numele bazei, nici utilizatorul, nici parola nu
  se scriu în niciun fișier urmărit de git.
- **Fiecare sarcină se termină cu un commit**, în română, în stilul
  istoricului: prima linie spune ce s-a schimbat, corpul spune de ce.

## Structura fișierelor

| Fișier | De ce răspunde |
|---|---|
| `inc/config.exemplu.php` | șablonul datelor de acces, urmărit de git |
| `inc/config.php` | datele adevărate, doar local și pe server, ignorat de git |
| `inc/db.php` | o singură funcție: conexiunea PDO |
| `inc/sablon.php` | funcții pure: escape, slug, timp de citit, data în română, paragrafe, tentă |
| `inc/articole.php` | toate interogările despre articole, nimic altceva |
| `inc/auth.php` | sesiune, autentificare, jeton CSRF, blocare după eșecuri |
| `inc/.htaccess` | refuză orice cerere din browser către `inc/` |
| `header.php`, `footer.php` | antetul și subsolul, pentru paginile PHP |
| `blog/index.php` | lista articolelor publicate |
| `blog/articol.php` | un articol, după slug |
| `blog/sitemap.php` | harta blogului |
| `admin/setup.php` | creează tabelele și contul, o singură dată |
| `admin/autentificare.php`, `admin/iesire.php` | intrare și ieșire |
| `admin/index.php` | lista tuturor articolelor |
| `admin/editor.php` | creare și modificare |
| `admin/sterge.php` | ștergere, doar prin POST cu jeton |
| `admin/admin.css` | singurul CSS nou, doar pentru tablou |
| `tools/test.php` | hămulețul de teste |
| `teste/*.php` | testele |
| `schema.sql` | cele două tabele, pentru referință și pentru testare |

`inc/` ține numai funcții, `blog/` și `admin/` numai pagini. O pagină nu scrie
niciodată SQL direct.

---

### Sarcina 1: Hămulețul de teste, configurarea și conexiunea

**Fișiere:**
- Creează: `tools/test.php`, `teste/proba.php`, `inc/config.exemplu.php`,
  `inc/db.php`, `inc/.htaccess`, `schema.sql`
- Creează local, neurmărit: `inc/config.php`

**Interfețe:**
- Produce: `db(): PDO`, funcțiile de test `test(string $nume, callable $f)`,
  `egal($asteptat, $obtinut, string $ce = '')`,
  `adevarat($valoare, string $ce = '')`,
  `arunca(callable $f, string $ce = '')`

- [ ] **Pasul 1: Scrie hămulețul de teste**

`tools/test.php`:

```php
<?php
/* Hămuleț de teste, cât să nu aducem composer în casă pentru două tabele.
   Rulează: php tools/test.php            toate testele
            php tools/test.php sablon     doar teste/sablon.php
   Un test picat oprește fișierul lui, nu toată rularea: vrem lista întreagă
   de eșecuri dintr-o dată, nu primul dintre ele. */
declare(strict_types=1);

$GLOBALS['treceri'] = 0;
$GLOBALS['esecuri'] = [];
$GLOBALS['fisier_curent'] = '';

function test(string $nume, callable $f): void {
    try {
        $f();
        $GLOBALS['treceri']++;
        echo "  ok   $nume\n";
    } catch (Throwable $ex) {
        $GLOBALS['esecuri'][] = $GLOBALS['fisier_curent'] . ' · ' . $nume
            . "\n       " . $ex->getMessage();
        echo "  PICAT $nume\n       " . $ex->getMessage() . "\n";
    }
}

function egal($asteptat, $obtinut, string $ce = ''): void {
    if ($asteptat !== $obtinut) {
        throw new RuntimeException(
            ($ce !== '' ? $ce . ': ' : '') .
            'așteptat ' . var_export($asteptat, true) .
            ', obținut ' . var_export($obtinut, true)
        );
    }
}

function adevarat($valoare, string $ce = ''): void {
    if ($valoare !== true) {
        throw new RuntimeException(
            ($ce !== '' ? $ce . ': ' : '') . 'așteptat true, obținut '
            . var_export($valoare, true)
        );
    }
}

function arunca(callable $f, string $ce = ''): void {
    try {
        $f();
    } catch (Throwable $ex) {
        return;
    }
    throw new RuntimeException(($ce !== '' ? $ce . ': ' : '') . 'nu a aruncat nimic');
}

$radacina = dirname(__DIR__);
$filtru = $argv[1] ?? '';
$fisiere = glob($radacina . '/teste/*.php');
sort($fisiere);

foreach ($fisiere as $f) {
    $nume = basename($f, '.php');
    if ($filtru !== '' && $nume !== $filtru) { continue; }
    $GLOBALS['fisier_curent'] = $nume;
    echo "\n$nume\n";
    require $f;
}

$n = count($GLOBALS['esecuri']);
echo "\n" . str_repeat('-', 60) . "\n";
echo $GLOBALS['treceri'] . " trecute, " . $n . " picate\n";
exit($n === 0 ? 0 : 1);
```

- [ ] **Pasul 2: Scrie un test de probă, ca să vezi că hămulețul picată corect**

`teste/proba.php`:

```php
<?php
test('hămulețul compară valori', function () {
    egal(4, 2 + 2);
});

test('hămulețul prinde tipul greșit', function () {
    arunca(function () { egal(4, '4'); }, 'int și string nu sunt egale');
});
```

- [ ] **Pasul 3: Rulează testele**

Rulează: `php tools/test.php proba`
Așteptat: `2 trecute, 0 picate`, cod de ieșire 0.

- [ ] **Pasul 4: Scrie schema**

`schema.sql`:

```sql
-- Cele două tabele ale blogului. Se aplică o singură dată, de admin/setup.php
-- pe server și de mână local. Colația e utf8mb4_unicode_ci fiindcă titlurile
-- și textele sunt în română: cu latin1, „învățare" se strică la scriere.

CREATE TABLE IF NOT EXISTS administratori (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190)  NOT NULL UNIQUE,
  parola_hash   VARCHAR(255)  NOT NULL,
  esecuri       INT           NOT NULL DEFAULT 0,
  blocat_pana   DATETIME      NULL,
  creat_la      DATETIME      NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articole (
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

- [ ] **Pasul 5: Fă baza locală și aplică schema**

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS asd_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root asd_blog < schema.sql
mysql -u root asd_blog -e "SHOW TABLES;"
```

Așteptat: `administratori` și `articole`.

- [ ] **Pasul 6: Scrie șablonul de configurare și configurarea locală**

`inc/config.exemplu.php` (intră în git):

```php
<?php
/* Copiază fișierul ăsta în inc/config.php și pune datele adevărate.
   inc/config.php nu intră niciodată în git și nu se urcă prin deploy: pe
   server se pune o dată, de mână, prin FTP, și rămâne acolo, fiindcă
   acțiunea de deploy nu șterge niciodată fișiere.
   Datele bazei de pe gazdă se iau din cPanel > MySQL Databases. */
return [
    'gazda'      => 'localhost',
    'baza'       => 'numele_bazei',
    'utilizator' => 'utilizatorul',
    'parola'     => 'parola',
];
```

`inc/config.php` (NU intră în git, doar local):

```php
<?php
return [
    'gazda'      => '127.0.0.1',
    'baza'       => 'asd_blog',
    'utilizator' => 'root',
    'parola'     => '',
];
```

- [ ] **Pasul 7: Închide `inc/` față de browser**

`inc/.htaccess`:

```apache
# Aici stau funcții și datele de acces la MySQL, nu pagini. Nimic de aici nu
# se cere vreodată din browser. Al doilea gard, peste regula din .htaccess-ul
# de la rădăcină: dacă unul e șters din greșeală, celălalt ține.
Require all denied
```

- [ ] **Pasul 8: Scrie testul conexiunii**

`teste/db.php`:

```php
<?php
require_once dirname(__DIR__) . '/inc/db.php';

test('db() întoarce o conexiune PDO', function () {
    egal('PDO', get_class(db()));
});

test('db() întoarce mereu aceeași conexiune', function () {
    adevarat(db() === db(), 'a doua chemare nu deschide altă conexiune');
});

test('conexiunea aruncă excepții, nu întoarce false', function () {
    egal(PDO::ERRMODE_EXCEPTION, db()->getAttribute(PDO::ATTR_ERRMODE));
});

test('interogările nu sunt emulate', function () {
    egal(false, db()->getAttribute(PDO::ATTR_EMULATE_PREPARES));
});

test('conexiunea vorbește utf8mb4', function () {
    $r = db()->query("SELECT 'ăâîșț' AS d")->fetch();
    egal('ăâîșț', $r['d'], 'diacriticele trec nestricate');
});
```

- [ ] **Pasul 9: Rulează testul, ca să pice**

Rulează: `php tools/test.php db`
Așteptat: PICAT, cu „Failed opening required .../inc/db.php".

- [ ] **Pasul 10: Scrie conexiunea**

`inc/db.php`:

```php
<?php
declare(strict_types=1);

/* Conexiunea la MySQL, una singură pe cerere.

   ATTR_EMULATE_PREPARES pe false înseamnă că interogările pregătite se fac
   chiar de MySQL, nu de driver prin lipire de șiruri. Cu emularea pornită,
   un parametru ajunge tot în textul interogării, doar escapat, iar asta e
   exact felul de siguranță pe care nu vrem s-o depindem de o funcție.

   Fără charset=utf8mb4 în DSN, conexiunea vorbește latin1 și „învățare" se
   scrie strâmb în bază, oricât de bine ar fi declarat tabelul. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cale = __DIR__ . '/config.php';
    if (!is_file($cale)) {
        throw new RuntimeException(
            'Lipsește inc/config.php. Copiază inc/config.exemplu.php și pune datele bazei.'
        );
    }
    $c = require $cale;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $c['gazda'], $c['baza']);
    $pdo = new PDO($dsn, $c['utilizator'], $c['parola'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}
```

- [ ] **Pasul 11: Rulează testele, ca să treacă**

Rulează: `php tools/test.php`
Așteptat: `7 trecute, 0 picate`.

- [ ] **Pasul 12: Verifică faptul care contează cel mai mult**

```bash
git status --porcelain inc/config.php
git check-ignore -v inc/config.php
```

Așteptat: prima comandă nu scrie nimic, a doua arată regula din `.gitignore`.
Dacă `inc/config.php` apare la `git status`, oprește-te și repară `.gitignore`
înainte de commit.

- [ ] **Pasul 13: Commit**

```bash
git add tools/test.php teste/ inc/db.php inc/config.exemplu.php inc/.htaccess schema.sql
git commit -m "$(cat <<'MSG'
Hămuleț de teste, schema blogului și conexiunea la MySQL

Testele rulează cu vreo 40 de rânduri de PHP, nu cu PHPUnit adus de composer:
proiectul are o singură dependență, AOS, și n-are build. Pentru două tabele,
un hămuleț propriu costă mai puțin decât un director vendor.

Conexiunea cere explicit excepții, interogări nepregătite de driver și
utf8mb4 în DSN. Fără ultimul, „învățare" se scrie strâmb în bază oricât de
bine ar fi declarat tabelul.

inc/config.php nu intră în git, doar exemplul de lângă el.
MSG
)"
```

---

### Sarcina 2: Funcțiile de șablon

**Fișiere:**
- Creează: `inc/sablon.php`, `teste/sablon.php`

**Interfețe:**
- Produce: `e(?string $t): string`, `slug(string $titlu): string`,
  `timp_citit(string $text): int`, `data_ro(string $iso): string`,
  `paragrafe(string $text): string`, `tenta(string $eticheta): string`

Sunt funcții pure, fără bază de date. Se testează întâi fiindcă tot restul
site-ului public le folosește, iar o greșeală în `e()` e o gaură de securitate
în fiecare pagină deodată.

- [ ] **Pasul 1: Scrie testele**

`teste/sablon.php`:

```php
<?php
require_once dirname(__DIR__) . '/inc/sablon.php';

test('e() taie colții marcajului', function () {
    egal('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
});

test('e() escapează și ghilimelele, nu doar parantezele unghiulare', function () {
    egal('&quot;a&quot; &amp; &#039;b&#039;', e('"a" & \'b\''));
});

test('e() lasă diacriticele în pace', function () {
    egal('învățare ășțâî', e('învățare ășțâî'));
});

test('e() primește null fără să crape', function () {
    egal('', e(null));
});

test('slug() scrie cu litere mici și cratime', function () {
    egal('ce-cere-baremul', slug('Ce cere baremul'));
});

test('slug() rade diacriticele, cu virgulă și cu sedilă', function () {
    egal('invatare-serioasa', slug('Învățare serioasă'));
    egal('invatare-serioasa', slug("\u{00CE}nv\u{0163}are serioas\u{015F}a"));
});

test('slug() nu lasă cratime la capete sau duble', function () {
    egal('a-b', slug('  ...a???b!!!  '));
});

test('slug() nu trece de 190 de caractere', function () {
    adevarat(strlen(slug(str_repeat('titlu lung ', 60))) <= 190);
});

test('slug() nu întoarce gol pentru un titlu numai din semne', function () {
    egal('articol', slug('!!!???'));
});

test('timp_citit() socotește 200 de cuvinte pe minut, rotunjit în sus', function () {
    egal(1, timp_citit(str_repeat('cuvant ', 200)));
    egal(2, timp_citit(str_repeat('cuvant ', 201)));
    egal(3, timp_citit(str_repeat('cuvant ', 500)));
});

test('timp_citit() nu întoarce niciodată zero', function () {
    egal(1, timp_citit('două cuvinte'));
    egal(1, timp_citit(''));
});

test('data_ro() scrie luna în română', function () {
    egal('14 august 2026', data_ro('2026-08-14'));
    egal('1 ianuarie 2027', data_ro('2027-01-01'));
    egal('11 iunie 2026', data_ro('2026-06-11'));
});

test('paragrafe() rupe la rând gol', function () {
    egal('<p>unu</p><p>doi</p>', paragrafe("unu\n\ndoi"));
});

test('paragrafe() face rândul simplu un <br>, nu un paragraf nou', function () {
    egal('<p>unu<br>doi</p>', paragrafe("unu\ndoi"));
});

test('paragrafe() escapează, deci un articol nu poate injecta marcaj', function () {
    egal('<p>&lt;b&gt;bold&lt;/b&gt;</p>', paragrafe('<b>bold</b>'));
});

test('paragrafe() sare peste rândurile goale de la capete', function () {
    egal('<p>unu</p>', paragrafe("\n\n  unu  \n\n"));
});

test('tenta() dă aceeași culoare aceleiași etichete, mereu', function () {
    egal(tenta('Bacalaureat'), tenta('Bacalaureat'));
});

test('tenta() dă doar tente din paletă', function () {
    foreach (['Bacalaureat', 'Evaluare Națională', 'Metodă', 'Altceva', 'X'] as $et) {
        adevarat(in_array(tenta($et), ['p-butter', 'p-sky', 'p-sage'], true), $et);
    }
});
```

- [ ] **Pasul 2: Rulează testele, ca să pice**

Rulează: `php tools/test.php sablon`
Așteptat: PICAT, „Failed opening required .../inc/sablon.php".

- [ ] **Pasul 3: Scrie funcțiile**

`inc/sablon.php`:

```php
<?php
declare(strict_types=1);

/* Funcții pure, folosite de paginile publice și de tablou. Nicio interogare
   aici: dacă o funcție de aici are nevoie de baza de date, e în fișierul
   greșit. */

/* Singura poartă prin care textul ajunge în pagină. ENT_QUOTES prinde și
   apostroful, nu doar ghilimelele, fiindcă atributele din șablon se scriu
   uneori cu apostrof. ENT_SUBSTITUTE pune semnul de întrebare în locul unui
   octet stricat, în loc să întoarcă șir gol: un text prost codat trebuie să
   se vadă ciudat, nu să dispară. */
function e(?string $t): string
{
    return htmlspecialchars($t ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* Adresa unui articol, făcută din titlu.

   Româna se scrie cu ș și ț cu virgulă dedesubt (U+0219, U+021B), dar multe
   tastaturi și texte vechi trimit variantele cu sedilă (U+015F, U+0163).
   Arată aproape la fel și sunt caractere diferite, deci se traduc amândouă,
   altfel același titlu ar da două adrese. */
function slug(string $titlu): string
{
    $harta = [
        'ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ț'=>'t','ş'=>'s','ţ'=>'t',
        'Ă'=>'a','Â'=>'a','Î'=>'i','Ș'=>'s','Ț'=>'t','Ş'=>'s','Ţ'=>'t',
    ];
    $s = strtr($titlu, $harta);
    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
    $s = trim($s, '-');
    if (strlen($s) > 190) {
        $s = rtrim(substr($s, 0, 190), '-');
    }
    /* Un titlu numai din semne ar da șir gol, iar o adresă goală ar însemna
       /blog/ în loc de /blog/ceva. */
    return $s !== '' ? $s : 'articol';
}

/* Timpul de citit se calculează, nu se scrie de mână: altfel rămâne „6 min"
   pe un articol ajuns între timp de două ori mai lung. 200 de cuvinte pe
   minut e ritmul obișnuit de citire a unui text de proză.
   preg_split cu /u, nu str_word_count: al doilea nu știe UTF-8 și rupe
   cuvintele cu diacritice în bucăți. */
function timp_citit(string $text): int
{
    $cuvinte = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return max(1, (int) ceil(count($cuvinte) / 200));
}

function data_ro(string $iso): string
{
    static $luni = [
        1=>'ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie',
        'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie',
    ];
    $d = new DateTimeImmutable($iso);
    return (int) $d->format('j') . ' ' . $luni[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/* Textul articolului e text simplu. Rândurile goale despart paragrafe, un
   rând simplu e o trecere la rând nou în același paragraf. Escapăm înainte
   de a adăuga marcajul nostru, deci nimic din ce scrie autorul nu poate
   ajunge etichetă în pagină. */
function paragrafe(string $text): string
{
    $bucati = preg_split("/\n\s*\n/u", trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $iesire = '';
    foreach ($bucati as $b) {
        $iesire .= '<p>' . nl2br(e(trim($b)), false) . '</p>';
    }
    return $iesire;
}

/* Tenta pastilei de etichetă. Aceeași etichetă primește mereu aceeași
   culoare, fiindcă vine din numele ei, nu din poziția în listă: altfel
   „Bacalaureat" ar fi galben azi și albastru mâine, după câte articole sunt
   deasupra lui. */
function tenta(string $eticheta): string
{
    $tente = ['p-butter', 'p-sky', 'p-sage'];
    return $tente[crc32(mb_strtolower(trim($eticheta), 'UTF-8')) % count($tente)];
}
```

- [ ] **Pasul 4: Rulează testele, ca să treacă**

Rulează: `php tools/test.php sablon`
Așteptat: `18 trecute, 0 picate`.

- [ ] **Pasul 5: Commit**

```bash
git add inc/sablon.php teste/sablon.php
git commit -m "$(cat <<'MSG'
Funcțiile de șablon: escape, slug, timp de citit, data, paragrafe

Se scriu primele fiindcă le folosește fiecare pagină publică, iar o greșeală
în e() ar fi o gaură în toate deodată.

Slugul traduce ș și ț și cu virgulă, și cu sedilă. Arată aproape la fel, sunt
caractere diferite, iar fără amândouă același titlu ar da două adrese.

Timpul de citit se calculează din numărul de cuvinte, nu se scrie de mână:
scris de mână, rămâne „6 min" pe un articol ajuns de două ori mai lung.

Tenta etichetei vine din numele ei, nu din poziția în listă, ca „Bacalaureat"
să nu fie galben azi și albastru mâine.
MSG
)"
```

---

### Sarcina 3: Interogările despre articole

**Fișiere:**
- Creează: `inc/articole.php`, `teste/articole.php`

**Interfețe:**
- Consumă: `db()` din Sarcina 1, `slug()` din Sarcina 2
- Produce:
  `articole_publicate(): array`,
  `articol_dupa_slug(string $slug): ?array`,
  `articole_toate(): array`,
  `articol_dupa_id(int $id): ?array`,
  `articol_salveaza(array $date, ?int $id = null): int`,
  `articol_sterge(int $id): void`,
  `slug_liber(string $dorit, ?int $exceptand = null): string`

- [ ] **Pasul 1: Scrie testele**

`teste/articole.php`:

```php
<?php
require_once dirname(__DIR__) . '/inc/articole.php';

/* Fiecare rulare pleacă de la masa goală: altfel testele depind de ordinea
   în care au rulat data trecută. */
db()->exec('DELETE FROM articole');

function articol_de_proba(array $peste = []): array {
    return array_merge([
        'titlu'          => 'Ce cere baremul',
        'eticheta'       => 'Bacalaureat',
        'rezumat'        => 'Un rezumat scurt.',
        'text'           => "Primul paragraf.\n\nAl doilea paragraf.",
        'data_publicare' => '2026-08-14',
        'stare'          => 'publicat',
    ], $peste);
}

test('articol_salveaza() creează și întoarce id-ul', function () {
    $id = articol_salveaza(articol_de_proba());
    adevarat($id > 0);
});

test('articolul salvat se citește după slug', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Trei capitole']));
    $a = articol_dupa_slug('trei-capitole');
    egal('Trei capitole', $a['titlu']);
    egal('Bacalaureat', $a['eticheta']);
});

test('slugul se face din titlu dacă nu e dat', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba(['titlu' => 'Învățare serioasă']));
    egal('invatare-serioasa', articol_dupa_id($id)['slug']);
});

test('un slug luat primește sufix numeric', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Acelasi titlu']));
    $id2 = articol_salveaza(articol_de_proba(['titlu' => 'Acelasi titlu']));
    egal('acelasi-titlu-2', articol_dupa_id($id2)['slug']);
});

test('articolele în ciornă nu apar în lista publică', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Publicat', 'stare' => 'publicat']));
    articol_salveaza(articol_de_proba(['titlu' => 'Ciorna', 'stare' => 'ciorna']));
    egal(1, count(articole_publicate()));
    egal('Publicat', articole_publicate()[0]['titlu']);
});

test('un articol în ciornă nu se găsește după slug pe partea publică', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Ciorna', 'stare' => 'ciorna']));
    egal(null, articol_dupa_slug('ciorna'));
});

test('tabloul le vede pe amândouă', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Unu', 'stare' => 'publicat']));
    articol_salveaza(articol_de_proba(['titlu' => 'Doi', 'stare' => 'ciorna']));
    egal(2, count(articole_toate()));
});

test('lista publică e în ordinea datei, cel mai nou întâi', function () {
    db()->exec('DELETE FROM articole');
    articol_salveaza(articol_de_proba(['titlu' => 'Vechi', 'data_publicare' => '2026-01-01']));
    articol_salveaza(articol_de_proba(['titlu' => 'Nou',   'data_publicare' => '2026-09-01']));
    egal('Nou', articole_publicate()[0]['titlu']);
});

test('salvarea cu id modifică, nu adaugă', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba(['titlu' => 'Înainte']));
    articol_salveaza(articol_de_proba(['titlu' => 'După']), $id);
    egal(1, count(articole_toate()));
    egal('După', articol_dupa_id($id)['titlu']);
});

test('modificarea nu se lovește de propriul slug', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba(['titlu' => 'Titlu stabil']));
    articol_salveaza(articol_de_proba(['titlu' => 'Titlu stabil', 'rezumat' => 'alt rezumat']), $id);
    egal('titlu-stabil', articol_dupa_id($id)['slug'], 'nu devine titlu-stabil-2');
});

test('ștergerea chiar șterge', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba());
    articol_sterge($id);
    egal(null, articol_dupa_id($id));
});

test('diacriticele se întorc din bază nestricate', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba(['text' => 'Învățarea așezată în timp.']));
    egal('Învățarea așezată în timp.', articol_dupa_id($id)['text']);
});

test('un titlu cu apostrof nu rupe interogarea', function () {
    db()->exec('DELETE FROM articole');
    $id = articol_salveaza(articol_de_proba(['titlu' => "O'zi \"grea\"; DROP TABLE articole;--"]));
    adevarat($id > 0);
    egal(1, count(articole_toate()), 'tabelul există încă');
});
```

- [ ] **Pasul 2: Rulează testele, ca să pice**

Rulează: `php tools/test.php articole`
Așteptat: PICAT, „Failed opening required .../inc/articole.php".

- [ ] **Pasul 3: Scrie interogările**

`inc/articole.php`:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sablon.php';

/* Toate interogările despre articole. Paginile nu scriu SQL: dacă o pagină
   are nevoie de altă interogare, se adaugă aici, nu acolo.

   Toate sunt pregătite, cu parametrii legați. Niciun text venit din formular
   sau din adresă nu ajunge în interogare prin lipire de șiruri. */

const CAMPURI = 'id, slug, titlu, eticheta, rezumat, text, data_publicare, stare, creat_la, actualizat_la';

function articole_publicate(): array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole
          WHERE stare = "publicat"
          ORDER BY data_publicare DESC, id DESC'
    );
    $s->execute();
    return $s->fetchAll();
}

/* Partea publică cere articolul doar dacă e publicat. Ciornele nu se văd
   nici dacă cineva ghicește adresa: întoarcem null, iar pagina face 404. */
function articol_dupa_slug(string $slug): ?array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole WHERE slug = ? AND stare = "publicat" LIMIT 1'
    );
    $s->execute([$slug]);
    $r = $s->fetch();
    return $r === false ? null : $r;
}

function articole_toate(): array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole ORDER BY data_publicare DESC, id DESC'
    );
    $s->execute();
    return $s->fetchAll();
}

function articol_dupa_id(int $id): ?array
{
    $s = db()->prepare('SELECT ' . CAMPURI . ' FROM articole WHERE id = ? LIMIT 1');
    $s->execute([$id]);
    $r = $s->fetch();
    return $r === false ? null : $r;
}

/* Găsește o adresă liberă. La modificare, articolul nu trebuie să se
   lovească de propriul slug, de aceea se poate exclude un id: fără asta,
   salvarea unui articol fără schimbarea titlului l-ar muta la titlu-2, apoi
   titlu-3, la fiecare salvare. */
function slug_liber(string $dorit, ?int $exceptand = null): string
{
    $baza = slug($dorit);
    $incercare = $baza;
    $n = 1;
    while (true) {
        $sql = 'SELECT id FROM articole WHERE slug = ?';
        $param = [$incercare];
        if ($exceptand !== null) {
            $sql .= ' AND id <> ?';
            $param[] = $exceptand;
        }
        $s = db()->prepare($sql . ' LIMIT 1');
        $s->execute($param);
        if ($s->fetch() === false) {
            return $incercare;
        }
        $n++;
        $incercare = $baza . '-' . $n;
    }
}

/* Una singură pentru creare și modificare: câmpurile sunt aceleași, iar două
   funcții aproape identice ar însemna două locuri de ținut la fel. */
function articol_salveaza(array $date, ?int $id = null): int
{
    $slug = $date['slug'] ?? '';
    $slug = slug_liber($slug !== '' ? $slug : $date['titlu'], $id);
    $acum = date('Y-m-d H:i:s');

    if ($id === null) {
        $s = db()->prepare(
            'INSERT INTO articole
             (slug, titlu, eticheta, rezumat, text, data_publicare, stare, creat_la, actualizat_la)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $s->execute([
            $slug, $date['titlu'], $date['eticheta'], $date['rezumat'],
            $date['text'], $date['data_publicare'], $date['stare'], $acum, $acum,
        ]);
        return (int) db()->lastInsertId();
    }

    $s = db()->prepare(
        'UPDATE articole
            SET slug = ?, titlu = ?, eticheta = ?, rezumat = ?, text = ?,
                data_publicare = ?, stare = ?, actualizat_la = ?
          WHERE id = ?'
    );
    $s->execute([
        $slug, $date['titlu'], $date['eticheta'], $date['rezumat'],
        $date['text'], $date['data_publicare'], $date['stare'], $acum, $id,
    ]);
    return $id;
}

function articol_sterge(int $id): void
{
    $s = db()->prepare('DELETE FROM articole WHERE id = ?');
    $s->execute([$id]);
}
```

- [ ] **Pasul 4: Rulează testele, ca să treacă**

Rulează: `php tools/test.php articole`
Așteptat: `13 trecute, 0 picate`.

- [ ] **Pasul 5: Rulează tot, ca să vezi că n-ai stricat nimic**

Rulează: `php tools/test.php`
Așteptat: `38 trecute, 0 picate`.

- [ ] **Pasul 6: Commit**

```bash
git add inc/articole.php teste/articole.php
git commit -m "$(cat <<'MSG'
Interogările despre articole, într-un singur loc

Paginile nu scriu SQL. Tot ce se cere de la baza de date trece pe aici, cu
interogări pregătite și parametri legați: niciun text din formular sau din
adresă nu ajunge în interogare prin lipire de șiruri. Un test verifică exact
asta, cu un titlu care conține DROP TABLE.

articol_dupa_slug() cere „publicat" în interogare, deci o ciornă nu se vede
nici dacă cineva ghicește adresa. Tabloul folosește altă funcție.

slug_liber() poate exclude un id, altfel salvarea unui articol fără
schimbarea titlului l-ar muta la titlu-2, apoi titlu-3, la fiecare salvare.
MSG
)"
```

---

### Sarcina 4: Sesiune, autentificare și jeton CSRF

**Fișiere:**
- Creează: `inc/auth.php`, `teste/auth.php`

**Interfețe:**
- Consumă: `db()`
- Produce:
  `sesiune_porneste(): void`,
  `admin_exista(): bool`,
  `admin_creeaza(string $email, string $parola): void`,
  `autentifica(string $email, string $parola): ?string` (null la reușită,
  mesajul de eroare la eșec),
  `e_autentificat(): bool`,
  `cere_autentificare(): void`,
  `deconecteaza(): void`,
  `jeton(): string`,
  `verifica_jeton(?string $primit): bool`

- [ ] **Pasul 1: Scrie testele**

`teste/auth.php`:

```php
<?php
require_once dirname(__DIR__) . '/inc/auth.php';

db()->exec('DELETE FROM administratori');

test('la început nu există niciun cont', function () {
    db()->exec('DELETE FROM administratori');
    egal(false, admin_exista());
});

test('contul creat există', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    egal(true, admin_exista());
});

test('parola nu se ține în clar', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    $r = db()->query('SELECT parola_hash FROM administratori')->fetch();
    adevarat($r['parola_hash'] !== 'parola-buna-123', 'hash, nu text');
    adevarat(password_verify('parola-buna-123', $r['parola_hash']));
});

test('autentificarea reușește cu parola bună', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    egal(null, autentifica('a@b.ro', 'parola-buna-123'));
});

test('autentificarea eșuează cu parola greșită', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    adevarat(is_string(autentifica('a@b.ro', 'gresita')));
});

test('mesajul e același pentru e-mail inexistent și parolă greșită', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    egal(autentifica('a@b.ro', 'gresita'), autentifica('nimeni@b.ro', 'orice'));
});

test('după cinci eșecuri contul se blochează', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    for ($i = 0; $i < 5; $i++) { autentifica('a@b.ro', 'gresita'); }
    $m = autentifica('a@b.ro', 'parola-buna-123');
    adevarat(is_string($m), 'parola bună nu mai intră cât e blocat');
    adevarat(str_contains($m, 'minute'), 'mesajul spune cât are de așteptat');
});

test('o autentificare reușită șterge eșecurile', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    autentifica('a@b.ro', 'gresita');
    autentifica('a@b.ro', 'gresita');
    autentifica('a@b.ro', 'parola-buna-123');
    $r = db()->query('SELECT esecuri FROM administratori')->fetch();
    egal(0, (int) $r['esecuri']);
});

test('jetonul e destul de lung ca să nu se ghicească', function () {
    sesiune_porneste();
    adevarat(strlen(jeton()) >= 32);
});

test('jetonul rămâne același în aceeași sesiune', function () {
    sesiune_porneste();
    egal(jeton(), jeton());
});

test('verifica_jeton() acceptă jetonul bun și refuză restul', function () {
    sesiune_porneste();
    egal(true, verifica_jeton(jeton()));
    egal(false, verifica_jeton('altceva'));
    egal(false, verifica_jeton(null));
    egal(false, verifica_jeton(''));
});
```

- [ ] **Pasul 2: Rulează testele, ca să pice**

Rulează: `php tools/test.php auth`
Așteptat: PICAT, „Failed opening required .../inc/auth.php".

- [ ] **Pasul 3: Scrie autentificarea**

`inc/auth.php`:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const ESECURI_MAXIME = 5;
const MINUTE_BLOCARE = 10;

/* Sesiunea se pornește cu cookie-ul strâns din toate șuruburile:
   HttpOnly, ca JavaScript să nu-l poată citi; Secure, fiindcă site-ul e pe
   HTTPS; SameSite=Strict, ca un alt site să nu poată trimite cereri în
   numele tău. La linia de comandă, în teste, nu există anteturi, deci
   pornim sesiunea fără cookie. */
function sesiune_porneste(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    if (PHP_SAPI === 'cli') {
        if (session_status() === PHP_SESSION_NONE) { @session_start(); }
        return;
    }
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Strict',
        'path'     => '/',
    ]);
    session_start();
}

function admin_exista(): bool
{
    return (bool) db()->query('SELECT 1 FROM administratori LIMIT 1')->fetch();
}

function admin_creeaza(string $email, string $parola): void
{
    $s = db()->prepare(
        'INSERT INTO administratori (email, parola_hash, creat_la) VALUES (?, ?, ?)'
    );
    $s->execute([$email, password_hash($parola, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
}

/* Întoarce null dacă a intrat, altfel mesajul de arătat.

   Mesajul e același pentru e-mail inexistent și pentru parolă greșită:
   altfel formularul spune dacă un e-mail e cel bun, iar cine încearcă nu mai
   are de ghicit decât parola.

   Numărătoarea eșecurilor stă în bază, nu în sesiune: în sesiune s-ar ocoli
   ștergând un cookie. */
function autentifica(string $email, string $parola): ?string
{
    $gresit = 'E-mailul sau parola nu sunt bune.';

    $s = db()->prepare('SELECT id, parola_hash, esecuri, blocat_pana FROM administratori WHERE email = ? LIMIT 1');
    $s->execute([$email]);
    $a = $s->fetch();

    if ($a === false) {
        /* Aceeași întârziere ca la o parolă greșită: fără ea, un e-mail
           inexistent răspunde vizibil mai repede și diferența se poate
           măsura. */
        usleep(300000);
        return $gresit;
    }

    if ($a['blocat_pana'] !== null && strtotime($a['blocat_pana']) > time()) {
        $minute = max(1, (int) ceil((strtotime($a['blocat_pana']) - time()) / 60));
        return 'Prea multe încercări. Mai așteptați ' . $minute . ' minute.';
    }

    if (!password_verify($parola, $a['parola_hash'])) {
        usleep(300000);
        $esecuri = (int) $a['esecuri'] + 1;
        $pana = $esecuri >= ESECURI_MAXIME
            ? date('Y-m-d H:i:s', time() + MINUTE_BLOCARE * 60)
            : null;
        $u = db()->prepare('UPDATE administratori SET esecuri = ?, blocat_pana = ? WHERE id = ?');
        $u->execute([$esecuri, $pana, $a['id']]);
        return $gresit;
    }

    $u = db()->prepare('UPDATE administratori SET esecuri = 0, blocat_pana = NULL WHERE id = ?');
    $u->execute([$a['id']]);

    sesiune_porneste();
    /* Identificatorul se schimbă la intrare, ca o sesiune pregătită dinainte
       de altcineva să nu devină una autentificată. */
    if (PHP_SAPI !== 'cli') { session_regenerate_id(true); }
    $_SESSION['admin_id'] = (int) $a['id'];
    return null;
}

function e_autentificat(): bool
{
    sesiune_porneste();
    return isset($_SESSION['admin_id']);
}

function cere_autentificare(): void
{
    if (!e_autentificat()) {
        header('Location: /admin/autentificare.php');
        exit;
    }
}

function deconecteaza(): void
{
    sesiune_porneste();
    $_SESSION = [];
    if (PHP_SAPI !== 'cli') { session_destroy(); }
}

function jeton(): string
{
    sesiune_porneste();
    if (empty($_SESSION['jeton'])) {
        $_SESSION['jeton'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['jeton'];
}

/* hash_equals, nu ===: compararea obișnuită se oprește la prima diferență,
   deci timpul ei spune câte caractere de la început erau bune. */
function verifica_jeton(?string $primit): bool
{
    sesiune_porneste();
    if ($primit === null || $primit === '' || empty($_SESSION['jeton'])) {
        return false;
    }
    return hash_equals($_SESSION['jeton'], $primit);
}
```

- [ ] **Pasul 4: Rulează testele, ca să treacă**

Rulează: `php tools/test.php auth`
Așteptat: `11 trecute, 0 picate`.

- [ ] **Pasul 5: Commit**

```bash
git add inc/auth.php teste/auth.php
git commit -m "$(cat <<'MSG'
Autentificare: bcrypt, sesiune strânsă, jeton CSRF, blocare

Un singur cont, cu parola ținută ca hash bcrypt. Mesajul de eroare e același
pentru e-mail inexistent și pentru parolă greșită, cu aceeași întârziere la
amândouă: altfel formularul spune care e-mail e cel bun, iar cine încearcă
nu mai are de ghicit decât parola.

Numărătoarea eșecurilor stă în bază, nu în sesiune. În sesiune s-ar ocoli
ștergând un cookie. Cinci eșecuri, zece minute de așteptare.

Jetonul CSRF se compară cu hash_equals: compararea obișnuită se oprește la
prima diferență, deci timpul ei spune câte caractere de la început erau bune.
MSG
)"
```

---

### Sarcina 5: Antetul și subsolul comune

**Fișiere:**
- Creează: `header.php`, `footer.php`
- Referință: `blog.html:1-77` pentru `<head>` și antet, `blog.html:135-185`
  pentru subsol

**Interfețe:**
- Produce: `header.php` așteaptă variabilele `$titlu`, `$descriere`,
  `$adresa_canonica` și, opțional, `$extra_head` și `$noindex` definite
  înainte de includere. `footer.php` nu așteaptă nimic.

Antetul și subsolul se copiază **cuvânt cu cuvânt** din `blog.html`, cu două
schimbări: căile devin absolute (`/assets/...`, `/index.html`), fiindcă
fișierul se include și din `/blog/` și din `/admin/`; iar titlul, descrierea
și adresa canonică vin din variabile.

- [ ] **Pasul 1: Scrie `header.php`**

```php
<?php
/* Antetul, o singură dată pentru paginile PHP.

   Căile sunt absolute, „/assets/...", nu „assets/...", exact cum face deja
   404.html: fișierul se include și de la /blog/, și de la /admin/, iar cu
   căi relative fiecare ar trebui să numere câte niveluri are de urcat.

   Cele șapte pagini HTML statice au încă propria copie a antetului. Când vor
   fi mutate pe PHP, o includ pe asta și copiile dispar.

   Variabilele așteptate, definite înainte de include:
     $titlu            textul din <title>
     $descriere        meta description
     $adresa_canonica  adresa completă, cu https://studiumgenerale.ro
     $extra_head       marcaj în plus în <head>, de pildă JSON-LD  (opțional)
     $noindex          true pentru paginile care nu se indexează   (opțional) */
$titlu           = $titlu           ?? 'Academia · Studium Generale by Denisa';
$descriere       = $descriere       ?? '';
$adresa_canonica = $adresa_canonica ?? 'https://studiumgenerale.ro/';
$extra_head      = $extra_head      ?? '';
$noindex         = $noindex         ?? false;
require_once __DIR__ . '/inc/sablon.php';
?><!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titlu) ?></title>
<meta name="description" content="<?= e($descriere) ?>">
<?php if ($noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php endif; ?>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="canonical" href="<?= e($adresa_canonica) ?>">
<meta name="theme-color" content="#121B52">
<meta name="author" content="Studium Generale by Denisa SRL">
<?= $extra_head ?>
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/assets/icons/favicon-32.png" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/poiret-one-400-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/poiret-one-400-latin-ext.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-400-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-400-latin-ext.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/gwendolyn-400-latin.woff2" crossorigin>
<link rel="stylesheet" href="/brand/tokens.css">
<link rel="stylesheet" href="/assets/site.css">
</head>
<body>

<a href="#continut" class="btn btn-sm sr-only">Sari la conținut</a>

<header class="site-head">
  <div class="wrap head-inner">
    <a class="brand" href="/index.html" aria-label="Academia · Studium Generale by Denisa">
      <span class="brand-mark"><img src="/assets/logo.webp" alt=""></span>
      <span class="brand-text">
        <span class="brand-name">Academia</span>
        <span class="brand-sub">Studium Generale by Denisa</span>
      </span>
    </a>
    <nav id="meniu" class="nav" data-nav aria-label="Navigație principală">
      <a class="navlink" href="/index.html">Acasă</a>
      <a class="navlink" href="/despre.html">Despre</a>
      <a class="navlink" href="/index.html#materii">Materii</a>
      <a class="navlink" href="/index.html#cursuri">Cursuri</a>
      <a class="navlink" href="/index.html#profesori">Profesori</a>
      <a class="navlink" href="/blog/">Blog</a>
      <a class="navlink lang-meniu" href="/en/index.html" hreflang="en" lang="en">English</a>
    </nav>
    <div class="row head-actions" style="gap:var(--sp-3)">
      <a class="navlink lang-comutator" href="/en/index.html" hreflang="en" lang="en">EN</a>
      <a class="btn btn-sm btn-wa" data-wa="Bacalaureat." href="https://wa.me/40735433720?text=Bun%C4%83%20ziua!%20Am%20g%C4%83sit%20Academia%20pe%20site%20%C8%99i%20a%C8%99%20vrea%20detalii%20despre%20preg%C4%83tirea%20pentru%20Bacalaureat." target="_blank" rel="noopener">WhatsApp</a>
      <button class="btn btn-sm burger" type="button" data-burger aria-expanded="false" aria-controls="meniu" aria-label="Meniu"><span class="burger-linii" aria-hidden="true"></span></button>
    </div>
  </div>
</header>

<main id="continut">
```

- [ ] **Pasul 2: Scrie `footer.php`**

Copiază subsolul din `blog.html`, de la `<footer class="site-foot">` până la
sfârșitul fișierului, și schimbă doar căile în absolute și linkul blogului în
`/blog/`. Obține exact structura de mai jos, cu conținutul real luat din
fișier:

```php
</main>

<footer class="site-foot">
  <div class="wrap">
    <p class="foot-afiliere">
      <span class="tag tag-brand">Membru oficial al <a data-camera href="https://www.hrcc.ro/members/" target="_blank" rel="noopener">Camerei de Comerț Eleno-Române</a></span>
      <span class="tag tag-brand">Partener oficial al <a data-colegiu href="https://www.medcollege.edu.gr/" target="_blank" rel="noopener">Mediterranean College</a></span>
    </p>
    <!-- restul subsolului, copiat din blog.html, cu căile făcute absolute
         și linkul Blog dus la /blog/ -->
  </div>
</footer>

<script>document.getElementById("an").textContent = new Date().getFullYear();</script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha384-n1AULnKdMJlK1oQCLNDL9qZsDgXtH6jRYFCpBtWFc+a9Yve0KSoMn575rk755NJZ"
        crossorigin="anonymous" referrerpolicy="no-referrer"
        onerror="document.documentElement.classList.add('fara-aos')"></script>
<script defer src="/assets/config.js"></script>
<script defer src="/assets/site.js"></script>
</body>
</html>
```

- [ ] **Pasul 3: Verifică prin diferență că n-ai schimbat nimic în plus**

```bash
php -r 'ob_start(); $titlu="x"; require "header.php"; require "footer.php"; file_put_contents("/tmp/php-chrome.html", ob_get_clean());'
diff <(sed -n '/<header class="site-head">/,/<\/header>/p' blog.html | sed 's|href="|href="/|g; s|src="|src="/|g') \
     <(sed -n '/<header class="site-head">/,/<\/header>/p' /tmp/php-chrome.html)
```

Așteptat: singurele diferențe sunt căile duble `//` din partea stângă, care
vin din `sed`-ul grosolan, și linkul `Blog`. Dacă apar diferențe de structură
sau de clase, antetul PHP nu e o copie fidelă și trebuie corectat.

- [ ] **Pasul 4: Commit**

```bash
git add header.php footer.php
git commit -m "$(cat <<'MSG'
Antet și subsol, o singură dată, pentru paginile PHP

Copiate cuvânt cu cuvânt din blog.html, cu căile făcute absolute, ca în
404.html: fișierele se includ și din /blog/, și din /admin/, iar cu căi
relative fiecare ar trebui să numere câte niveluri are de urcat.

Cele șapte pagini HTML își păstrează deocamdată copia lor, cum s-a hotărât.
Când vor trece pe PHP, o includ pe asta și copiile dispar.
MSG
)"
```

---

### Sarcina 6: Paginile publice ale blogului

**Fișiere:**
- Creează: `blog/index.php`, `blog/articol.php`
- Referință: `blog.html:84-130` pentru cardurile listei,
  `blog-articol.html:86-120` pentru șablonul articolului

**Interfețe:**
- Consumă: `articole_publicate()`, `articol_dupa_slug()`, `e()`, `slug()`,
  `data_ro()`, `timp_citit()`, `paragrafe()`, `tenta()`, `header.php`,
  `footer.php`

- [ ] **Pasul 1: Scrie lista**

`blog/index.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

$articole = articole_publicate();

$titlu           = 'Blog · Academia · Studium Generale by Denisa';
$descriere       = 'Articole despre pregătirea pentru Bacalaureat și Evaluare Națională: metode de învățat, greșeli frecvente și ce cere baremul.';
$adresa_canonica = 'https://studiumgenerale.ro/blog/';
$extra_head = '
<meta property="og:type" content="website">
<meta property="og:site_name" content="Academia · Studium Generale by Denisa">
<meta property="og:locale" content="ro_RO">
<meta property="og:url" content="https://studiumgenerale.ro/blog/">
<meta property="og:title" content="Blog · Academia Studium Generale">
<meta property="og:description" content="' . e($descriere) . '">
<meta property="og:image" content="https://studiumgenerale.ro/assets/og.jpg">
<meta name="twitter:card" content="summary_large_image">';
require dirname(__DIR__) . '/header.php';
?>
  <section class="hero wrap">
    <h1>Blog</h1>
    <p class="hero-lede">Scriem rar și doar când avem ce spune: ce cere baremul, ce greșeli se repetă an de an și cum se învață un capitol în loc să fie memorat.</p>
  </section>

  <section class="wrap">
    <h2 class="sr-only">Articole recente</h2>
    <?php if (count($articole) === 0): ?>
      <p class="note">Încă nu e publicat niciun articol. Revine curând.</p>
    <?php else: ?>
    <div class="articole">
      <?php foreach ($articole as $i => $a): ?>
      <article>
        <a class="card articol-card" href="/blog/<?= e($a['slug']) ?>" data-aos="fade-up" data-aos-delay="<?= min($i, 4) * 70 ?>">
          <span class="tag <?= e(tenta($a['eticheta'])) ?>"><?= e($a['eticheta']) ?></span>
          <h3><?= e($a['titlu']) ?></h3>
          <p><?= e($a['rezumat']) ?></p>
          <span class="articol-meta">
            <time datetime="<?= e($a['data_publicare']) ?>"><?= e(data_ro($a['data_publicare'])) ?></time> · <span><?= timp_citit($a['text']) ?> min</span>
          </span>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
<?php require dirname(__DIR__) . '/footer.php';
```

Întârzierea AOS se oprește la al cincilea card: `min($i, 4) * 70`. Fără plafon,
al cincizecilea card ar aștepta trei secunde și jumătate după ce intră în
ecran, adică ar părea rupt.

- [ ] **Pasul 2: Scrie articolul**

`blog/articol.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

$a = articol_dupa_slug((string) ($_GET['slug'] ?? ''));

/* Slug inexistent și articol în ciornă dau același răspuns: 404 adevărat,
   cu pagina site-ului. Nu „nu aveți voie", fiindcă asta ar spune că
   articolul există. */
if ($a === null) {
    http_response_code(404);
    readfile(dirname(__DIR__) . '/404.html');
    exit;
}

$adresa = 'https://studiumgenerale.ro/blog/' . $a['slug'];
$titlu           = $a['titlu'] . ' · Academia';
$descriere       = $a['rezumat'];
$adresa_canonica = $adresa;

$jsonld = json_encode([
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $a['titlu'],
    'description'      => $a['rezumat'],
    'datePublished'    => $a['data_publicare'],
    'dateModified'     => substr($a['actualizat_la'], 0, 10),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $adresa],
    'author'           => ['@type' => 'Organization', 'name' => 'Academia · Studium Generale by Denisa'],
    'publisher'        => ['@type' => 'Organization', 'name' => 'STUDIUM GENERALE BY DENISA SRL'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

$extra_head = '
<meta property="og:type" content="article">
<meta property="og:site_name" content="Academia · Studium Generale by Denisa">
<meta property="og:locale" content="ro_RO">
<meta property="og:url" content="' . e($adresa) . '">
<meta property="og:title" content="' . e($a['titlu']) . '">
<meta property="og:description" content="' . e($a['rezumat']) . '">
<meta property="og:image" content="https://studiumgenerale.ro/assets/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">' . $jsonld . '</script>';

require dirname(__DIR__) . '/header.php';
?>
  <section class="wrap" style="padding-bottom:var(--sp-10)">
    <article class="articol">
      <a class="btn btn-sm" href="/blog/" style="margin-bottom:var(--sp-6)">← Toate articolele</a>

      <span class="tag <?= e(tenta($a['eticheta'])) ?>"><?= e($a['eticheta']) ?></span>
      <h1 style="font-size:var(--fs-2xl);margin-top:var(--sp-4)"><?= e($a['titlu']) ?></h1>

      <p class="articol-meta" style="margin-top:var(--sp-3)">
        <time datetime="<?= e($a['data_publicare']) ?>"><?= e(data_ro($a['data_publicare'])) ?></time> · <span><?= timp_citit($a['text']) ?> min</span>
      </p>

      <?= paragrafe($a['text']) ?>
    </article>
  </section>
<?php require dirname(__DIR__) . '/footer.php';
```

Rândul „de Denisa V." din șablonul static nu se mai scrie: e un singur cont,
numele nu vine de nicăieri, iar CLAUDE.md spune limpede că numele inventate
au ieșit din site.

- [ ] **Pasul 3: Pune trei articole de probă și pornește serverul**

```bash
php -r '
require "inc/articole.php";
db()->exec("DELETE FROM articole");
articol_salveaza(["titlu"=>"Ce cere de fapt baremul la subiectul III","eticheta"=>"Bacalaureat","rezumat"=>"Cei mai mulți elevi pierd puncte nu pentru că greșesc rezolvarea, ci pentru că sar pașii pe care corectorul îi caută explicit.","text"=>"Primul paragraf, cu diacritice: învățare așezată.\n\nAl doilea paragraf.","data_publicare"=>"2026-08-14","stare"=>"publicat"]);
articol_salveaza(["titlu"=>"Trei capitole care decid nota la Evaluare","eticheta"=>"Evaluare Națională","rezumat"=>"Din toată programa, trei capitole apar în aproape fiecare variantă.","text"=>"Text de probă.","data_publicare"=>"2026-07-23","stare"=>"publicat"]);
articol_salveaza(["titlu"=>"Ciorna care nu trebuie sa apara","eticheta"=>"Metodă","rezumat"=>"Nu se vede.","text"=>"Nu se vede.","data_publicare"=>"2026-09-01","stare"=>"ciorna"]);
'
php -S localhost:8000 &
```

- [ ] **Pasul 4: Verifică paginile**

```bash
curl -s "http://localhost:8000/blog/index.php" | grep -c "articol-card"
curl -s "http://localhost:8000/blog/articol.php?slug=ce-cere-de-fapt-baremul-la-subiectul-iii" | grep -o "<h1[^>]*>[^<]*</h1>"
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8000/blog/articol.php?slug=nu-exista"
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8000/blog/articol.php?slug=ciorna-care-nu-trebuie-sa-apara"
curl -s "http://localhost:8000/blog/index.php" | grep -c "Ciorna care nu trebuie"
```

Așteptat: `2` carduri (ciorna nu apare), titlul articolului, `404`, `404`, `0`.

- [ ] **Pasul 5: Verifică așezarea în pagină cu auditul**

```bash
curl -s "http://localhost:8000/blog/index.php" > /tmp/blog-lista.html
curl -s "http://localhost:8000/blog/articol.php?slug=ce-cere-de-fapt-baremul-la-subiectul-iii" > /tmp/blog-articol.html
```

Deschide amândouă în browser la 1440, 900 și 560 și compară cu `blog.html`
și `blog-articol.html` cum arătau. Așteptat: aceeași așezare, aceleași
carduri, aceleași pastile de etichetă.

- [ ] **Pasul 6: Commit**

```bash
git add blog/index.php blog/articol.php
git commit -m "$(cat <<'MSG'
Paginile publice ale blogului, din baza de date

Lista și articolul, cu aceleași clase ca paginile statice pe care le
înlocuiesc: stilul nu se rescrie deloc.

Un slug inexistent și un articol în ciornă dau același 404. Nu „nu aveți
voie": asta ar spune că articolul există.

Textul trece prin paragrafe(), care escapează înainte să adauge marcaj, deci
un articol nu poate strica pagina în care intră.

Întârzierea AOS se oprește la al cincilea card. Fără plafon, al cincizecilea
ar aștepta trei secunde și jumătate după ce intră în ecran, adică ar părea
rupt.

Rândul „de Denisa V." din șablonul static nu se mai scrie: e un singur cont,
numele nu vine de nicăieri, iar numele inventate au ieșit din site.
MSG
)"
```

---

### Sarcina 7: Harta blogului

**Fișiere:**
- Creează: `blog/sitemap.php`

- [ ] **Pasul 1: Scrie harta**

`blog/sitemap.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

/* sitemap.xml al blogului. Cel de la rădăcină e scris de mână și ține
   paginile statice; ăsta se face din bază, fiindcă articolele apar și dispar
   fără ca nimeni să atingă un fișier. Amândouă sunt trecute în robots.txt. */
header('Content-Type: application/xml; charset=utf-8');
$articole = articole_publicate();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://studiumgenerale.ro/blog/</loc>
    <lastmod><?= e(date('Y-m-d')) ?></lastmod>
  </url>
<?php foreach ($articole as $a): ?>
  <url>
    <loc>https://studiumgenerale.ro/blog/<?= e($a['slug']) ?></loc>
    <lastmod><?= e(substr($a['actualizat_la'], 0, 10)) ?></lastmod>
  </url>
<?php endforeach; ?>
</urlset>
```

- [ ] **Pasul 2: Verifică**

```bash
curl -s "http://localhost:8000/blog/sitemap.php" | head -20
curl -s "http://localhost:8000/blog/sitemap.php" | python3 -c "import sys,xml.dom.minidom; xml.dom.minidom.parseString(sys.stdin.read()); print('XML valid')"
curl -s "http://localhost:8000/blog/sitemap.php" | grep -c "<loc>"
```

Așteptat: `XML valid` și `3` adrese (lista plus cele două articole publicate,
ciorna lipsește).

- [ ] **Pasul 3: Commit**

```bash
git add blog/sitemap.php
git commit -m "$(cat <<'MSG'
Harta blogului, făcută din baza de date

Cea de la rădăcină e scrisă de mână și ține paginile statice, care se schimbă
rar. Articolele apar și dispar fără ca nimeni să atingă un fișier, deci harta
lor se face din bază. Ciornele nu intră în ea.
MSG
)"
```

---

### Sarcina 8: Pagina de instalare

**Fișiere:**
- Creează: `admin/setup.php`, `admin/admin.css`

- [ ] **Pasul 1: Scrie foaia de stil a tabloului**

`admin/admin.css`:

```css
/* Singurul CSS scris pentru tablou. Restul vine din tokens.css și site.css,
   deci tabloul arată ca site-ul, nu ca un panou străin lipit lângă el.
   Aici stau numai lucrurile care n-au corespondent în paginile publice:
   tabelul de articole și câmpurile late ale editorului. */
.admin-wrap { max-width: 900px; margin-inline: auto; padding-block: var(--sp-10); }
.admin-bara { display: flex; flex-wrap: wrap; gap: var(--sp-4);
  align-items: center; justify-content: space-between; margin-bottom: var(--sp-8); }
.admin-tabel { width: 100%; border-collapse: collapse; }
.admin-tabel th, .admin-tabel td { text-align: left; padding: var(--sp-3);
  border-bottom: 1px solid var(--border); font-size: var(--fs-sm); vertical-align: middle; }
.admin-tabel th { color: var(--text-muted); font-weight: var(--fw-semibold);
  text-transform: uppercase; letter-spacing: .06em; font-size: var(--fs-xs); }
.admin-form { display: flex; flex-direction: column; gap: var(--sp-5); max-width: 700px; }
.admin-form label { display: flex; flex-direction: column; gap: var(--sp-2);
  font-size: var(--fs-sm); font-weight: var(--fw-semibold); }
.admin-form input, .admin-form textarea, .admin-form select {
  font: inherit; font-size: var(--fs-base); padding: var(--sp-3);
  border: var(--stroke-thin); border-radius: var(--r-md); background: var(--surface);
  color: var(--text); width: 100%; }
.admin-form textarea { min-height: 320px; resize: vertical; line-height: var(--lh-normal); }
.admin-eroare { color: #8B1A1A; font-size: var(--fs-sm); font-weight: var(--fw-medium); }
.admin-gol { color: var(--text-muted); padding-block: var(--sp-10); }
@media (max-width: 560px) {
  .admin-tabel th:nth-child(2), .admin-tabel td:nth-child(2) { display: none; }
}
```

- [ ] **Pasul 2: Scrie pagina de instalare**

`admin/setup.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/sablon.php';

/* Se deschide o singură dată, la prima punere pe server: face tabelele și
   contul. După ce există un cont, refuză: altfel oricine ar putea să-și facă
   unul și să intre. Se șterge oricum de pe server după ce a fost folosită,
   dar refuzul e plasa: un fișier uitat nu trebuie să fie o ușă. */

$eroare = '';
$gata = false;

db()->exec(file_get_contents(dirname(__DIR__) . '/schema.sql'));

$exista = admin_exista();

if (!$exista && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim((string) ($_POST['email'] ?? ''));
    $parola = (string) ($_POST['parola'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $eroare = 'E-mailul nu pare a fi un e-mail.';
    } elseif (strlen($parola) < 12) {
        $eroare = 'Parola trebuie să aibă cel puțin 12 caractere.';
    } else {
        admin_creeaza($email, $parola);
        $gata = true;
    }
}

$titlu = 'Instalare · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/setup.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <h1>Instalare</h1>
  <?php if ($exista): ?>
    <p>Există deja un cont de administrare. Pagina asta nu mai face nimic.</p>
    <p><strong>Ștergeți fișierul <code>admin/setup.php</code> de pe server.</strong></p>
    <p><a class="btn btn-sm" href="/admin/autentificare.php">Intră în cont</a></p>
  <?php elseif ($gata): ?>
    <p>Contul a fost creat și tabelele sunt la locul lor.</p>
    <p><strong>Ștergeți acum fișierul <code>admin/setup.php</code> de pe server.</strong></p>
    <p><a class="btn btn-sm" href="/admin/autentificare.php">Intră în cont</a></p>
  <?php else: ?>
    <p>Se face o singură dată: contul cu care se scriu articolele.</p>
    <?php if ($eroare !== ''): ?><p class="admin-eroare"><?= e($eroare) ?></p><?php endif; ?>
    <form class="admin-form" method="post" action="/admin/setup.php">
      <label>E-mail
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </label>
      <label>Parolă, cel puțin 12 caractere
        <input type="password" name="parola" required minlength="12" autocomplete="new-password">
      </label>
      <div><button class="btn btn-primary" type="submit">Creează contul</button></div>
    </form>
  <?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/footer.php';
```

- [ ] **Pasul 3: Verifică refuzul, care e partea care contează**

```bash
mysql -u root asd_blog -e "DELETE FROM administratori;"
curl -s "http://localhost:8000/admin/setup.php" | grep -c "Creează contul"
curl -s -X POST -d "email=a@b.ro&parola=parola-de-proba-123" "http://localhost:8000/admin/setup.php" | grep -c "Contul a fost creat"
curl -s "http://localhost:8000/admin/setup.php" | grep -c "Există deja un cont"
curl -s -X POST -d "email=hot@rau.ro&parola=parola-de-proba-999" "http://localhost:8000/admin/setup.php" | grep -c "Există deja un cont"
mysql -u root asd_blog -e "SELECT COUNT(*) FROM administratori;"
```

Așteptat: `1`, `1`, `1`, `1`, și un singur rând în tabel. Al doilea POST nu
trebuie să creeze al doilea cont.

- [ ] **Pasul 4: Commit**

```bash
git add admin/setup.php admin/admin.css
git commit -m "$(cat <<'MSG'
Pagina de instalare, care refuză să ruleze a doua oară

Face tabelele și contul, o singură dată. După ce există un cont, refuză:
altfel oricine ar putea să-și facă unul și să intre. Se șterge oricum de pe
server după folosire, dar refuzul e plasa, fiindcă un fișier uitat nu trebuie
să fie o ușă. Un test cu al doilea POST verifică exact asta.

Tabloul împrumută stilul site-ului; admin.css are numai ce n-are corespondent
în paginile publice, tabelul și câmpurile late ale editorului.
MSG
)"
```

---

### Sarcina 9: Intrarea și ieșirea din cont

**Fișiere:**
- Creează: `admin/autentificare.php`, `admin/iesire.php`

- [ ] **Pasul 1: Scrie formularul de intrare**

`admin/autentificare.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/sablon.php';

if (e_autentificat()) {
    header('Location: /admin/');
    exit;
}

$eroare = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifica_jeton($_POST['jeton'] ?? null)) {
        $eroare = 'Formularul a expirat. Încercați din nou.';
    } else {
        $eroare = autentifica(
            trim((string) ($_POST['email'] ?? '')),
            (string) ($_POST['parola'] ?? '')
        ) ?? '';
        if ($eroare === '') {
            header('Location: /admin/');
            exit;
        }
    }
}

$titlu = 'Intrare · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/autentificare.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <h1>Intrare</h1>
  <?php if ($eroare !== ''): ?><p class="admin-eroare"><?= e($eroare) ?></p><?php endif; ?>
  <form class="admin-form" method="post" action="/admin/autentificare.php">
    <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
    <label>E-mail
      <input type="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
    </label>
    <label>Parolă
      <input type="password" name="parola" required autocomplete="current-password">
    </label>
    <div><button class="btn btn-primary" type="submit">Intră</button></div>
  </form>
</section>
<?php require dirname(__DIR__) . '/footer.php';
```

- [ ] **Pasul 2: Scrie ieșirea**

`admin/iesire.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';

/* Ieșirea se face prin POST cu jeton, ca orice altă schimbare de stare: cu
   GET, o imagine cu src="/admin/iesire.php" pusă în altă pagină ar scoate
   omul din cont de fiecare dată când o vede. E o glumă, nu o spargere, dar
   regula e aceeași pentru toate. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifica_jeton($_POST['jeton'] ?? null)) {
    deconecteaza();
}
header('Location: /admin/autentificare.php');
exit;
```

- [ ] **Pasul 3: Verifică**

```bash
J=$(curl -s -c /tmp/c.txt "http://localhost:8000/admin/autentificare.php" | grep -o 'name="jeton" value="[^"]*"' | cut -d'"' -f4)
curl -s -b /tmp/c.txt -c /tmp/c.txt -X POST -d "jeton=$J&email=a@b.ro&parola=gresita" "http://localhost:8000/admin/autentificare.php" | grep -c "nu sunt bune"
curl -s -b /tmp/c.txt -c /tmp/c.txt -X POST -d "jeton=RAU&email=a@b.ro&parola=parola-de-proba-123" "http://localhost:8000/admin/autentificare.php" | grep -c "a expirat"
curl -s -b /tmp/c.txt -c /tmp/c.txt -o /dev/null -w "%{http_code} %{redirect_url}\n" -X POST -d "jeton=$J&email=a@b.ro&parola=parola-de-proba-123" "http://localhost:8000/admin/autentificare.php"
```

Așteptat: `1`, `1`, apoi `302` spre `/admin/`.

- [ ] **Pasul 4: Commit**

```bash
git add admin/autentificare.php admin/iesire.php
git commit -m "$(cat <<'MSG'
Intrarea și ieșirea din contul de administrare

Formularul poartă jeton CSRF, iar ieșirea se face tot prin POST cu jeton: cu
GET, o imagine cu src="/admin/iesire.php" pusă în altă pagină ar scoate omul
din cont ori de câte ori o vede.

Mesajul de eroare vine din autentifica(), deci e același pentru e-mail
inexistent și pentru parolă greșită.
MSG
)"
```

---

### Sarcina 10: Tabloul, editorul și ștergerea

**Fișiere:**
- Creează: `admin/index.php`, `admin/editor.php`, `admin/sterge.php`

- [ ] **Pasul 1: Scrie lista din tablou**

`admin/index.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();
$articole = articole_toate();

$titlu = 'Articole · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <div class="admin-bara">
    <h1 style="margin:0">Articole</h1>
    <div class="row" style="gap:var(--sp-3)">
      <a class="btn btn-sm btn-primary" href="/admin/editor.php">Articol nou</a>
      <form method="post" action="/admin/iesire.php" style="margin:0">
        <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
        <button class="btn btn-sm" type="submit">Ieși</button>
      </form>
    </div>
  </div>

  <?php if (count($articole) === 0): ?>
    <p class="admin-gol">Niciun articol încă. <a href="/admin/editor.php">Scrie primul</a>.</p>
  <?php else: ?>
  <table class="admin-tabel">
    <thead>
      <tr><th>Titlu</th><th>Etichetă</th><th>Data</th><th>Stare</th><th>Acțiuni</th></tr>
    </thead>
    <tbody>
    <?php foreach ($articole as $a): ?>
      <tr>
        <td><?= e($a['titlu']) ?></td>
        <td><?= e($a['eticheta']) ?></td>
        <td><?= e(data_ro($a['data_publicare'])) ?></td>
        <td><?= $a['stare'] === 'publicat' ? 'publicat' : 'ciornă' ?></td>
        <td>
          <div class="row" style="gap:var(--sp-2)">
            <?php if ($a['stare'] === 'publicat'): ?>
              <a class="navlink" href="/blog/<?= e($a['slug']) ?>" target="_blank" rel="noopener">Vezi</a>
            <?php endif; ?>
            <a class="navlink" href="/admin/editor.php?id=<?= (int) $a['id'] ?>">Modifică</a>
            <form method="post" action="/admin/sterge.php" style="margin:0"
                  onsubmit="return confirm('Ștergeți articolul „<?= e($a['titlu']) ?>”? Nu se mai poate aduce înapoi.')">
              <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button class="navlink" type="submit">Șterge</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/footer.php';
```

- [ ] **Pasul 2: Scrie editorul**

`admin/editor.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$a = $id !== null ? articol_dupa_id($id) : null;
if ($id !== null && $a === null) {
    http_response_code(404);
    readfile(dirname(__DIR__) . '/404.html');
    exit;
}

$erori = [];
/* Valorile din formular, luate din POST dacă s-a trimis, din bază dacă se
   modifică, goale dacă e articol nou. Așa un formular respins se întoarce cu
   tot ce s-a scris în el: un text de o mie de cuvinte nu se pierde pentru o
   etichetă uitată. */
$v = [
    'titlu'          => $_POST['titlu']          ?? $a['titlu']          ?? '',
    'eticheta'       => $_POST['eticheta']       ?? $a['eticheta']       ?? '',
    'rezumat'        => $_POST['rezumat']        ?? $a['rezumat']        ?? '',
    'text'           => $_POST['text']           ?? $a['text']           ?? '',
    'data_publicare' => $_POST['data_publicare'] ?? $a['data_publicare'] ?? date('Y-m-d'),
    'stare'          => $_POST['stare']          ?? $a['stare']          ?? 'ciorna',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifica_jeton($_POST['jeton'] ?? null)) {
        $erori[] = 'Formularul a expirat. Trimiteți din nou.';
    }
    if (trim($v['titlu']) === '')    { $erori[] = 'Titlul nu poate lipsi.'; }
    if (mb_strlen($v['titlu']) > 255) { $erori[] = 'Titlul e prea lung, maximum 255 de caractere.'; }
    if (trim($v['eticheta']) === '') { $erori[] = 'Eticheta nu poate lipsi.'; }
    if (trim($v['rezumat']) === '')  { $erori[] = 'Rezumatul nu poate lipsi.'; }
    if (mb_strlen($v['rezumat']) > 400) { $erori[] = 'Rezumatul e prea lung, maximum 400 de caractere.'; }
    if (trim($v['text']) === '')     { $erori[] = 'Textul nu poate lipsi.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['data_publicare'])) { $erori[] = 'Data nu e bună.'; }
    if (!in_array($v['stare'], ['ciorna', 'publicat'], true)) { $erori[] = 'Starea nu e bună.'; }

    if (count($erori) === 0) {
        $id = articol_salveaza($v, $id);
        header('Location: /admin/?salvat=' . $id);
        exit;
    }
}

$titlu = ($a === null ? 'Articol nou' : 'Modifică articolul') . ' · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/editor.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <div class="admin-bara">
    <h1 style="margin:0"><?= $a === null ? 'Articol nou' : 'Modifică articolul' ?></h1>
    <a class="btn btn-sm" href="/admin/">Înapoi la listă</a>
  </div>

  <?php foreach ($erori as $er): ?>
    <p class="admin-eroare"><?= e($er) ?></p>
  <?php endforeach; ?>

  <form class="admin-form" method="post">
    <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
    <label>Titlu
      <input type="text" name="titlu" required maxlength="255" value="<?= e($v['titlu']) ?>">
    </label>
    <label>Etichetă
      <input type="text" name="eticheta" required maxlength="60" value="<?= e($v['eticheta']) ?>"
             placeholder="Bacalaureat, Evaluare Națională, Metodă">
    </label>
    <label>Rezumat, se vede pe card și în previzualizarea linkului
      <textarea name="rezumat" required maxlength="400" style="min-height:90px"><?= e($v['rezumat']) ?></textarea>
    </label>
    <label>Text, simplu. Un rând gol începe un paragraf nou.
      <textarea name="text" required><?= e($v['text']) ?></textarea>
    </label>
    <label>Data publicării
      <input type="date" name="data_publicare" required value="<?= e($v['data_publicare']) ?>">
    </label>
    <label>Stare
      <select name="stare">
        <option value="ciorna"   <?= $v['stare'] === 'ciorna'   ? 'selected' : '' ?>>Ciornă, nu se vede pe site</option>
        <option value="publicat" <?= $v['stare'] === 'publicat' ? 'selected' : '' ?>>Publicat</option>
      </select>
    </label>
    <div><button class="btn btn-primary" type="submit">Salvează</button></div>
  </form>
</section>
<?php require dirname(__DIR__) . '/footer.php';
```

- [ ] **Pasul 3: Scrie ștergerea**

`admin/sterge.php`:

```php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();

/* Numai POST, și numai cu jeton. Un GET nu șterge nimic niciodată: altfel
   un link trimis pe chat, sau un crawler care urmărește linkuri, ar putea
   goli blogul. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifica_jeton($_POST['jeton'] ?? null)) {
    http_response_code(400);
    header('Location: /admin/');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    articol_sterge($id);
}
header('Location: /admin/?sters=1');
exit;
```

- [ ] **Pasul 4: Verifică drumul întreg, plus refuzurile**

```bash
# fără cont, tabloul trimite la intrare
curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" "http://localhost:8000/admin/index.php"

# intră
rm -f /tmp/c.txt
J=$(curl -s -c /tmp/c.txt "http://localhost:8000/admin/autentificare.php" | grep -o 'name="jeton" value="[^"]*"' | cut -d'"' -f4)
curl -s -b /tmp/c.txt -c /tmp/c.txt -o /dev/null -X POST -d "jeton=$J&email=a@b.ro&parola=parola-de-proba-123" "http://localhost:8000/admin/autentificare.php"

# creează un articol
J2=$(curl -s -b /tmp/c.txt -c /tmp/c.txt "http://localhost:8000/admin/editor.php" | grep -o 'name="jeton" value="[^"]*"' | cut -d'"' -f4)
curl -s -b /tmp/c.txt -o /dev/null -w "%{http_code} %{redirect_url}\n" -X POST \
  --data-urlencode "jeton=$J2" --data-urlencode "titlu=Articol scris din tablou" \
  --data-urlencode "eticheta=Metodă" --data-urlencode "rezumat=Rezumat." \
  --data-urlencode "text=Un paragraf.

Al doilea." --data-urlencode "data_publicare=2026-09-16" --data-urlencode "stare=publicat" \
  "http://localhost:8000/admin/editor.php"

# se vede pe site
curl -s "http://localhost:8000/blog/articol.php?slug=articol-scris-din-tablou" | grep -c "Al doilea"

# ștergerea prin GET nu face nimic
curl -s -b /tmp/c.txt -o /dev/null -w "%{http_code}\n" "http://localhost:8000/admin/sterge.php?id=1"

# ștergerea cu jeton greșit nu face nimic
curl -s -b /tmp/c.txt -o /dev/null -X POST -d "jeton=RAU&id=1" "http://localhost:8000/admin/sterge.php"
mysql -u root asd_blog -e "SELECT COUNT(*) FROM articole;"
```

Așteptat: `302` spre autentificare; salvarea dă `302` spre `/admin/`; `1`
pentru textul din articol; `400` la GET; numărul de articole neschimbat după
cele două încercări de ștergere.

- [ ] **Pasul 5: Rulează toate testele**

Rulează: `php tools/test.php`
Așteptat: `49 trecute, 0 picate`.

- [ ] **Pasul 6: Commit**

```bash
git add admin/index.php admin/editor.php admin/sterge.php
git commit -m "$(cat <<'MSG'
Tabloul: listă, editor, ștergere

Cele patru lucruri cerute, cu jeton CSRF pe fiecare scriere. Ștergerea merge
numai prin POST cu jeton: cu GET, un link trimis pe chat sau un crawler care
urmărește linkuri ar putea goli blogul.

Formularul respins se întoarce cu tot ce s-a scris în el. Un text de o mie de
cuvinte nu se pierde fiindcă a lipsit eticheta.

Tabloul vede și ciornele, site-ul nu: sunt două funcții diferite, nu un
parametru uitat.
MSG
)"
```

---

### Sarcina 11: Rutarea

**Fișiere:**
- Modifică: `.htaccess` (adaugă la sfârșit, sub regulile existente)

- [ ] **Pasul 1: Adaugă regulile la sfârșitul lui `.htaccess`**

```apache
# --- Blogul ----------------------------------------------------------------
# Adresele articolelor sunt /blog/titlul-articolului, nu /blog/?id=7: se
# citesc, se rețin și spun despre ce e vorba, și oamenilor, și motoarelor.
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Vechile fișiere statice. Se păstrează redirectul chiar dacă fișierele se
  # șterg de pe server: un link trimis cuiva înainte de mutare trebuie să
  # ajungă undeva, nu în 404.
  RewriteRule ^blog\.html$          /blog/ [R=301,L]
  RewriteRule ^blog-articol\.html$  /blog/ [R=301,L]

  # Harta blogului, înaintea regulii de articol: altfel „sitemap.xml" ar fi
  # citit ca slug de articol.
  RewriteRule ^blog/sitemap\.xml$   blog/sitemap.php [L]

  # Lista.
  RewriteRule ^blog/?$              blog/index.php [L]

  # Un articol. Slugul e numai litere mici, cifre și cratime, exact ce scoate
  # slug(); orice altceva nu se potrivește aici și cade în 404.
  RewriteRule ^blog/([a-z0-9-]+)/?$ blog/articol.php?slug=$1 [L,QSA]
</IfModule>

# --- Tabloul nu se indexează -----------------------------------------------
<IfModule mod_headers.c>
  <FilesMatch "^(index|editor|autentificare|setup|sterge|iesire)\.php$">
    Header set X-Robots-Tag "noindex, nofollow"
  </FilesMatch>
</IfModule>
```

Atenție: regula pentru `FilesMatch` de mai sus prinde și `blog/index.php`.
Pune blocul într-un `admin/.htaccess` separat, cu numai
`Header set X-Robots-Tag "noindex, nofollow"`, ca să nu scoată din index
lista blogului.

- [ ] **Pasul 2: Scrie `admin/.htaccess` în locul blocului greșit**

`admin/.htaccess`:

```apache
# Tabloul nu are ce căuta în rezultatele căutării. Regula stă aici, nu în
# .htaccess-ul de la rădăcină: acolo un FilesMatch pe „index.php" ar prinde
# și blog/index.php, adică ar scoate din index chiar lista articolelor.
<IfModule mod_headers.c>
  Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
```

Și scoate blocul „Tabloul nu se indexează" din `.htaccess`-ul de la rădăcină.

- [ ] **Pasul 3: Verifică regulile local, cât se poate**

Serverul încorporat al lui PHP nu citește `.htaccess`, deci regulile se
verifică pe server, după deploy, în Sarcina 13. Local se verifică doar că
fișierul n-are greșeli de sintaxă evidente și că paginile răspund pe căile
lor directe, ceea ce s-a făcut deja.

- [ ] **Pasul 4: Commit**

```bash
git add .htaccess admin/.htaccess
git commit -m "$(cat <<'MSG'
Rutarea blogului, sub blocul cPanel

/blog/ pentru listă, /blog/titlul-articolului pentru articol, plus cele două
301-uri de la fișierele statice. Redirectul se păstrează și după ce
fișierele se șterg de pe server: un link trimis cuiva înainte de mutare
trebuie să ajungă undeva.

Harta e pusă înaintea regulii de articol, altfel „sitemap.xml" ar fi citit ca
slug.

„noindex" pentru tablou stă în admin/.htaccess, nu la rădăcină: acolo un
FilesMatch pe index.php ar fi prins și blog/index.php, adică ar fi scos din
index chiar lista articolelor.
MSG
)"
```

---

### Sarcina 12: Restul site-ului

**Fișiere:**
- Modifică: `index.html`, `despre.html`, `404.html`, `en/index.html`,
  `en/despre.html` (linkul Blog, în meniu și în subsol), `sitemap.xml`,
  `llms.txt`, `robots.txt`, `tools/seo.js:47-48`
- Șterge: `blog.html`, `blog-articol.html`

- [ ] **Pasul 1: Mută linkurile spre `/blog/`**

```bash
python3 - <<'PY'
import pathlib, re
for f in ["index.html","despre.html","404.html","en/index.html","en/despre.html"]:
    p = pathlib.Path(f); s = p.read_text(encoding="utf-8")
    inainte = s
    s = s.replace('href="blog.html"', 'href="/blog/"')
    s = s.replace('href="../blog.html"', 'href="/blog/"')
    s = s.replace('href="/blog.html"', 'href="/blog/"')
    assert s != inainte, f
    p.write_text(s, encoding="utf-8")
    print("ok", f)
PY
grep -rn "blog\.html\|blog-articol\.html" --include='*.html' . | grep -v node_modules
```

Așteptat: ultima comandă nu mai găsește nimic în afară de `blog.html` și
`blog-articol.html` însele, care se șterg în pasul următor.

- [ ] **Pasul 2: Șterge paginile statice și scoate-le din `tools/seo.js`**

```bash
git rm blog.html blog-articol.html
```

În `tools/seo.js`, șterge cele două rânduri din lista de pagini:

```js
  { fisier: "blog.html",         adresa: "/blog.html",         tip: "CollectionPage", fir: [["Blog", "/blog.html"]] },
  { fisier: "blog-articol.html", adresa: "/blog-articol.html", tip: "WebPage",        fir: [["Blog", "/blog.html"], [null, "/blog-articol.html"]] },
```

Și în comentariul de la linia 294, unde scrie că JSON-LD-ul articolului se va
scrie din bază „când vine PHP-ul", schimbă în: articolul are acum propriul
JSON-LD, scris de `blog/articol.php`; funcția de aici nu mai are ce face.
Șterge blocul care caută `<article class="articol">`.

- [ ] **Pasul 3: Adu la zi harta, robots și llms**

În `sitemap.xml`, înlocuiește cele două `<url>` de blog cu una singură:

```xml
  <url>
    <loc>https://studiumgenerale.ro/blog/</loc>
    <lastmod>2026-09-16</lastmod>
  </url>
```

În `robots.txt`, adaugă `Disallow: /admin/` și `Disallow: /inc/` la amândouă
grupurile de `User-agent`, și a doua hartă la sfârșit:

```
Sitemap: https://studiumgenerale.ro/sitemap.xml
Sitemap: https://studiumgenerale.ro/blog/sitemap.xml
```

În `llms.txt`, schimbă adresa blogului:

```
- [Blog](https://studiumgenerale.ro/blog/): articole despre pregătirea pentru examene
```

- [ ] **Pasul 4: Rulează seo.js și auditul**

```bash
node tools/seo.js
tools/audit.sh
```

Așteptat: `seo.js` trece fără erori peste cele cinci pagini rămase; auditul
iese zero peste tot la 1440, 900 și 560.

- [ ] **Pasul 5: Verifică faptul că nu mai există linkuri moarte**

```bash
grep -rn "blog\.html\|blog-articol\.html" --include='*.html' --include='*.xml' --include='*.txt' --include='*.js' . | grep -v node_modules | grep -v '\.htaccess'
```

Așteptat: nimic.

- [ ] **Pasul 6: Commit**

```bash
git add -A
git commit -m "$(cat <<'MSG'
Blogul se mută la /blog/, fișierele statice ies

Linkul din meniu și din subsol, în cele cinci pagini rămase, harta, llms.txt
și tools/seo.js, care nu mai are de scris JSON-LD pentru articol: îl scrie
blog/articol.php, din bază, exact cum spunea comentariul lăsat acolo.

robots.txt închide /admin/ și /inc/ și trimite la a doua hartă, cea a
blogului.

blog.html și blog-articol.html erau trei carduri și un articol scrise de
mână, ca să se vadă așezarea. Acum așezarea vine din bază.
MSG
)"
```

---

### Sarcina 13: Punerea pe server

**Fișiere:** niciunul. Pași pe server, în ordine.

- [ ] **Pasul 1: Împinge și urmărește deploy-ul**

```bash
git push origin main
gh run watch "$(gh run list --limit 1 --json databaseId --jq '.[0].databaseId')" --exit-status
```

- [ ] **Pasul 2: Pune `inc/config.php` pe server, prin FTP**

Un singur fișier, scris după `inc/config.exemplu.php`, cu datele din cPanel.
Nu intră în git și nu se urcă prin deploy, deci se pune o singură dată de
mână. Rămâne acolo: acțiunea nu șterge niciodată fișiere.

- [ ] **Pasul 3: Deschide `/admin/setup.php`, fă contul, apoi șterge fișierul**

Prin FTP, după ce contul e creat. Verifică întâi că a doua deschidere spune
„Există deja un cont".

- [ ] **Pasul 4: Șterge de pe server fișierele statice rămase**

Prin FTP: `blog.html`, `blog-articol.html`, și `default.html`, pagina de
întâmpinare a gazdei. Acțiunea de deploy nu șterge nimic, deci fișierele
scoase din repo rămân pe server până le scoate cineva cu mâna.

- [ ] **Pasul 5: Verifică pe adresa adevărată**

```bash
for u in blog/ blog.html blog-articol.html blog/sitemap.xml admin/ inc/config.php; do
  printf "  %-24s " "$u"
  curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" "https://studiumgenerale.ro/$u"
done
curl -sI https://studiumgenerale.ro/admin/ | grep -i x-robots-tag
curl -s https://studiumgenerale.ro/blog/ | grep -c articol-card
```

Așteptat:
- `/blog/` dă `200`
- `/blog.html` și `/blog-articol.html` dau `301` spre `/blog/`
- `/blog/sitemap.xml` dă `200`
- `/admin/` dă `302` spre autentificare, cu `X-Robots-Tag: noindex`
- `/inc/config.php` dă `403`

- [ ] **Pasul 6: Scrie un articol adevărat din tablou și vezi-l pe site**

Ultimul test, cel care contează: intră la `/admin/`, scrie un articol, publică-l,
deschide `/blog/` și apoi articolul. Verifică diacriticele, data în română și
timpul de citit.

- [ ] **Pasul 7: Adu CLAUDE.md la zi**

Secțiunea „Zonele pentru PHP" nu mai descrie ceva viitor. Scrie ce există:
`/blog` și `/admin` în PHP, `header.php` și `footer.php` folosite de ele,
cele cinci pagini HTML încă având propria copie a antetului, unde stau datele
de acces și de ce nu sunt în git.

- [ ] **Pasul 8: Commit**

```bash
git add CLAUDE.md
git commit -m "$(cat <<'MSG'
CLAUDE.md: blogul nu mai e plan, e cod

„Zonele pentru PHP" descriau ceva ce urma să existe. Acum există: /blog și
/admin, cu header.php și footer.php ale lor. Cele cinci pagini HTML își
păstrează copia antetului până vor fi și ele mutate.
MSG
)"
```

---

## Autoverificarea planului

**Acoperirea specificației.** Fiecare secțiune din spec are o sarcină:
fișierele (1, 5), baza de date (1), configurarea și parola (1), autentificarea
(4), rutarea (11), randarea (2, 6), SEO și harta (6, 7), ce se schimbă în ce
există (12), erorile (3, 6, 10), testarea (1 și fiecare sarcină), punerea pe
server (13).

**Fără substituenți.** Fiecare pas are comanda sau codul lui. Singurul loc
care spune „copiază din fișier" e subsolul din Sarcina 5, unde conținutul e
lung și trebuie să fie identic cu originalul, iar pasul 3 al acelei sarcini
verifică prin diferență că este.

**Potrivirea numelor.** `articol_salveaza($date, $id)` e folosită la fel în
Sarcinile 3, 10 și 6. `articol_dupa_slug()` cere „publicat" în interogare și
de asta `blog/articol.php` nu mai verifică starea. `jeton()` și
`verifica_jeton()` se cheamă la fel în 4, 9 și 10. `tenta()`, `data_ro()`,
`timp_citit()`, `paragrafe()` din 2 se folosesc în 6 și 10 cu aceleași
semnături.

**O capcană prinsă la revizuire:** blocul `X-Robots-Tag` din Sarcina 11
prindea și `blog/index.php`, adică ar fi scos din index chiar lista
articolelor. De aceea a fost mutat în `admin/.htaccess`.
