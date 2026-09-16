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
