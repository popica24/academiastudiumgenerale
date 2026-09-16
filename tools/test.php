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

/* teste/articole.php și teste/auth.php încep cu DELETE FROM la scop de
   fișier, împotriva bazei din inc/config.php, oricare ar fi ea. Dacă cineva
   copiază local configurația de pe server, ca să depaneze o problemă vie,
   o rulare obișnuită a suitei ar șterge fiecare articol și fiecare cont de
   pe site. Se verifică numele bazei ÎNAINTE de a atinge orice fișier de
   teste, nu după. */
$cale_config = $radacina . '/inc/config.php';
if (!is_file($cale_config)) {
    fwrite(STDERR, "Lipsește inc/config.php. Copiază inc/config.exemplu.php și pune datele bazei de dezvoltare.\n");
    exit(1);
}
$config = require $cale_config;
$baza = (string) ($config['baza'] ?? '');
$are_voie = getenv('TESTE') === 'da'
    || str_contains(strtolower($baza), 'test')
    || $baza === 'asd_blog';
if (!$are_voie) {
    fwrite(STDERR, "Suita golește tabelele înaintea fiecărui test și nu pune nimic la loc.\n");
    fwrite(STDERR, "Baza configurată în inc/config.php e „{$baza}”, care nu pare o bază de dezvoltare\n");
    fwrite(STDERR, "(un nume cu „test” în el, sau baza locală „asd_blog”).\n");
    fwrite(STDERR, "Refuz să rulez, ca să nu șterg date adevărate.\n");
    fwrite(STDERR, "Dacă e chiar o bază de test, forțează cu: TESTE=da php tools/test.php\n");
    exit(1);
}

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
