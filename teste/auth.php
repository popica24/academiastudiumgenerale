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
