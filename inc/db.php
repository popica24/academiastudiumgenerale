<?php
declare(strict_types=1);

/* O excepție necaptată își arată argumentele apelurilor din urmă în mesaj și
   în jurnalul de erori. Pe PHP 8.1, cel de pe gazdă, PDO::__construct nu are
   încă #[\SensitiveParameter] (a apărut abia în 8.2), deci un eșec de
   conectare ar scrie parola bazei, trunchiată la 15 caractere, direct în
   urma de excepție. Fiecare pagină ajunge la db.php prin lanțul de
   require_once, spre deosebire de inc/auth.php, pe care paginile publice ale
   blogului nu-l încarcă niciodată, așa că protecția stă aici, nu doar acolo. */
ini_set('zend.exception_ignore_args', '1');

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
    try {
        $pdo = new PDO($dsn, $c['utilizator'], $c['parola'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $ex) {
        /* Aici se prinde exact excepția care ar fi purtat gazda, baza,
           utilizatorul și parola în mesajul ei. Cea aruncată mai departe nu
           numește niciun acreditiv. */
        throw new RuntimeException('Baza de date nu a răspuns la conectare.');
    }
    return $pdo;
}
