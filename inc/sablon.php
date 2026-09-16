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
    /* Browserele normalizează rândurile dintr-un textarea la CRLF. Fără aceasta,
       \r rămâne orfelino în pagină, înainte de <br>. */
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    $bucati = preg_split("/\n\s*\n/u", trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $iesire = '';
    foreach ($bucati as $b) {
        $iesire .= '<p>' . preg_replace('/\n/', '<br>', e(trim($b))) . '</p>';
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
