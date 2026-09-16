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
