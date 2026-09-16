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
