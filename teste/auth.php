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

test('două eșecuri la rând măresc numărătoarea corect', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    autentifica('a@b.ro', 'gresita');
    autentifica('a@b.ro', 'gresita');
    $r = db()->query('SELECT esecuri FROM administratori')->fetch();
    egal(2, (int) $r['esecuri'], 'mărirea se face în SQL, nu citind și scriind din PHP');
});

test('blocarea se activează exact la al cincilea eșec', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    for ($i = 0; $i < ESECURI_MAXIME; $i++) { autentifica('a@b.ro', 'gresita'); }
    $r = db()->query('SELECT esecuri, blocat_pana FROM administratori')->fetch();
    egal(ESECURI_MAXIME, (int) $r['esecuri']);
    adevarat($r['blocat_pana'] !== null, 'contul trebuie să fie blocat după al cincilea eșec');
});

test('după ce blocarea expiră, o parolă greșită nu reblochează imediat', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    $id = db()->query('SELECT id FROM administratori')->fetch()['id'];
    $u = db()->prepare('UPDATE administratori SET esecuri = ?, blocat_pana = DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE id = ?');
    $u->execute([ESECURI_MAXIME, $id]);

    $m = autentifica('a@b.ro', 'gresita');
    adevarat(is_string($m));
    adevarat(!str_contains($m, 'Prea multe'), 'blocarea expirată nu trebuie să reapară imediat');

    $r = db()->query('SELECT esecuri FROM administratori')->fetch();
    egal(1, (int) $r['esecuri'], 'eșecurile trebuiau resetate la 0 înainte de acest eșec nou');
});

test('cât timp e blocat, parola greșită dă mesajul generic, iar cea bună dă mesajul de blocare', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    $id = db()->query('SELECT id FROM administratori')->fetch()['id'];
    $u = db()->prepare('UPDATE administratori SET esecuri = ?, blocat_pana = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = ?');
    $u->execute([ESECURI_MAXIME, $id]);

    egal('E-mailul sau parola nu sunt bune.', autentifica('a@b.ro', 'gresita'));

    $mb = autentifica('a@b.ro', 'parola-buna-123');
    adevarat(is_string($mb));
    adevarat(str_contains($mb, 'Prea multe'), 'parola bună, cât e blocat, trebuie să arate mesajul de blocare');
});

test('mesajul de blocare e la singular când rămâne un minut', function () {
    db()->exec('DELETE FROM administratori');
    admin_creeaza('a@b.ro', 'parola-buna-123');
    $id = db()->query('SELECT id FROM administratori')->fetch()['id'];
    $u = db()->prepare('UPDATE administratori SET esecuri = ?, blocat_pana = DATE_ADD(NOW(), INTERVAL 30 SECOND) WHERE id = ?');
    $u->execute([ESECURI_MAXIME, $id]);

    $m = autentifica('a@b.ro', 'parola-buna-123');
    adevarat(is_string($m));
    adevarat(str_contains($m, '1 minut.'), 'un minut rămas se scrie la singular');
    adevarat(!str_contains($m, '1 minute'), 'nu trebuie să apară „1 minute”');
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
