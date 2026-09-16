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
