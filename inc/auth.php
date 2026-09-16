<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* zend.exception_ignore_args se pornește în db.php, nu aici: fiecare pagină
   ajunge la db.php (prin require_once), inclusiv cele publice ale blogului,
   care nu încarcă niciodată acest fișier. Rămâne adevărat și pentru
   auth.php: parola în clar trece prin autentifica() și admin_creeaza() ca
   argument, deci excepțiile de aici au nevoie de aceeași protecție, moștenită
   din db.php. */

const ESECURI_MAXIME = 5;
const MINUTE_BLOCARE = 10;

/* Cost 12 la fiecare hash bcrypt, atât la contul real cât și la hash-ul
   fantomă de mai jos: dacă ar avea costuri diferite, timpul de verificare ar
   deosebi din nou un e-mail existent de unul inexistent. */
const COST_BCRYPT = 12;

/* Hash bcrypt al unui text oarecare, cost 12, fără cont în spate. Folosit
   doar ca să coste la fel de mult calcul cât un password_verify() adevărat,
   niciodată ca să verifice ceva: pe calea cu e-mail inexistent nu există
   parolă de comparat, dar timpul trebuie să fie identic cu cel de pe calea
   cu parolă greșită, altfel un e-mail inexistent răspunde vizibil mai
   repede și diferența se poate măsura. */
const HASH_FANTOMA = '$2y$12$QTfoog/5dCIqzZwZByQgEu5.tvoB0JJGY4Gn1bfGCZQhiGw0ZfTka';

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
    try {
        $s = db()->prepare(
            'INSERT INTO administratori (email, parola_hash, creat_la) VALUES (?, ?, ?)'
        );
        $s->execute([$email, password_hash($parola, PASSWORD_BCRYPT, ['cost' => COST_BCRYPT]), date('Y-m-d H:i:s')]);
    } catch (PDOException $ex) {
        /* Fără argumente în excepția aruncată mai departe: $parola nu are ce
           căuta în jurnalul de erori. */
        throw new RuntimeException('Baza de date nu a răspuns la crearea contului.');
    }
}

/* Scrie mesajul de blocare, la singular când rămâne exact un minut. */
function mesaj_blocare(int $minute): string
{
    $cuvant = $minute === 1 ? '1 minut' : $minute . ' minute';
    return 'Prea multe încercări. Mai așteptați ' . $cuvant . '.';
}

/* Întoarce null dacă a intrat, altfel mesajul de arătat.

   Mesajul e același pentru e-mail inexistent și pentru parolă greșită:
   altfel formularul spune dacă un e-mail e cel bun, iar cine încearcă nu mai
   are de ghicit decât parola. Nu ne bazăm pe o pauză artificială ca să
   ascundem diferența: comparăm mereu cu un hash bcrypt, real sau fantomă, ca
   ambele căi să coste exact același calcul.

   Numărătoarea eșecurilor stă în bază, nu în sesiune: în sesiune s-ar ocoli
   ștergând un cookie. Mărirea ei se face direct în SQL (esecuri = esecuri +
   1), nu citind valoarea în PHP și scriind-o înapoi: două cereri paralele
   care citesc aceeași valoare veche ar scrie amândouă aceeași valoare nouă
   și un atac automatizat ar trece nesocotit de blocare. */
function autentifica(string $email, string $parola): ?string
{
    $gresit = 'E-mailul sau parola nu sunt bune.';

    /* „blocat" și „minute_ramase" sunt calculate de MySQL, nu de PHP: dacă
       ceasul serverului de bază și cel al PHP nu sunt pe același fus orar
       (aici, MySQL ține ora sistemului, PHP ține UTC), un strtotime() peste
       o dată scrisă de MySQL și comparată cu time() din PHP poate ieși
       greșit cu ore întregi, fără nicio eroare care să atragă atenția. */
    try {
        $s = db()->prepare(
            'SELECT id, parola_hash, esecuri, blocat_pana,
                    (blocat_pana IS NOT NULL AND blocat_pana > NOW()) AS blocat,
                    CEIL(TIMESTAMPDIFF(SECOND, NOW(), blocat_pana) / 60) AS minute_ramase
               FROM administratori WHERE email = ? LIMIT 1'
        );
        $s->execute([$email]);
        $a = $s->fetch();
    } catch (PDOException $ex) {
        throw new RuntimeException('Baza de date nu a răspuns la autentificare.');
    }

    if ($a === false) {
        /* Niciun cont, dar tot verificăm o parolă, cu hash-ul fantomă:
           calculul costă la fel ca password_verify() de pe calea cu cont
           real, ca timpul de răspuns să nu spună dacă e-mailul există. */
        password_verify($parola, HASH_FANTOMA);
        return $gresit;
    }

    $blocat = (bool) $a['blocat'];

    if ($a['blocat_pana'] !== null && !$blocat) {
        /* Blocarea a expirat. Se resetează acum, înainte de verificarea
           obișnuită: altfel numărătoarea rămâne la maxim și o singură parolă
           greșită următoare ar reporni blocarea la loc, la nesfârșit. */
        try {
            $u = db()->prepare('UPDATE administratori SET esecuri = 0, blocat_pana = NULL WHERE id = ?');
            $u->execute([$a['id']]);
        } catch (PDOException $ex) {
            throw new RuntimeException('Baza de date nu a răspuns la autentificare.');
        }
        $a['esecuri'] = 0;
        $a['blocat_pana'] = null;
    }

    if ($blocat) {
        /* Blocarea se dezvăluie doar cuiva care dovedește că știe parola.
           Altfel un cont blocat răspunde instantaneu, fără niciun hash de
           calculat, și devine el însuși un semnal că e-mailul există. */
        if (password_verify($parola, $a['parola_hash'])) {
            $minute = max(1, (int) $a['minute_ramase']);
            return mesaj_blocare($minute);
        }
        /* Nu se mărește numărătoarea cât timp e deja blocat: altfel un atac
           susținut ar putea împinge expirarea blocării tot mai departe. */
        return $gresit;
    }

    if (!password_verify($parola, $a['parola_hash'])) {
        try {
            /* MySQL evaluează atribuirile unui UPDATE în ordine, de la stânga
               la dreapta: până ajunge la a doua, „esecuri" din dreapta ei
               înseamnă deja valoarea mărită de prima atribuire, nu cea
               veche. De asta comparăm direct „esecuri >= ...", fără să mai
               adunăm încă un 1, altfel blocarea ar porni cu un eșec mai
               devreme decât ESECURI_MAXIME. */
            $u = db()->prepare(
                'UPDATE administratori
                    SET esecuri = esecuri + 1,
                        blocat_pana = IF(esecuri >= ' . ESECURI_MAXIME . ',
                                         DATE_ADD(NOW(), INTERVAL ' . MINUTE_BLOCARE . ' MINUTE),
                                         blocat_pana)
                  WHERE id = ?'
            );
            $u->execute([$a['id']]);
        } catch (PDOException $ex) {
            throw new RuntimeException('Baza de date nu a răspuns la autentificare.');
        }
        return $gresit;
    }

    try {
        $u = db()->prepare('UPDATE administratori SET esecuri = 0, blocat_pana = NULL WHERE id = ?');
        $u->execute([$a['id']]);
    } catch (PDOException $ex) {
        throw new RuntimeException('Baza de date nu a răspuns la autentificare.');
    }

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
