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
    egal('invatare-serioasa', slug("\u{00CE}nv\u{0103}\u{0163}are serioas\u{0103}"));
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

test('paragrafe() curăță și sfârșiturile de rând trimise de browser', function () {
    egal('<p>unu<br>doi</p>', paragrafe("unu\r\ndoi"));
});

test('paragrafe() rupe paragrafele și când rândul gol e trimis de browser', function () {
    egal('<p>unu</p><p>doi</p>', paragrafe("unu\r\n\r\ndoi"));
});

test('tenta() dă aceeași culoare aceleiași etichete, mereu', function () {
    egal(tenta('Bacalaureat'), tenta('Bacalaureat'));
});

test('tenta() dă doar tente din paletă', function () {
    foreach (['Bacalaureat', 'Evaluare Națională', 'Metodă', 'Altceva', 'X'] as $et) {
        adevarat(in_array(tenta($et), ['p-butter', 'p-sky', 'p-sage'], true), $et);
    }
});
