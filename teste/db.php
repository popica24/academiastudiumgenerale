<?php
require_once dirname(__DIR__) . '/inc/db.php';

test('db() întoarce o conexiune PDO', function () {
    egal('PDO', get_class(db()));
});

test('db() întoarce mereu aceeași conexiune', function () {
    adevarat(db() === db(), 'a doua chemare nu deschide altă conexiune');
});

test('conexiunea aruncă excepții, nu întoarce false', function () {
    egal(PDO::ERRMODE_EXCEPTION, db()->getAttribute(PDO::ATTR_ERRMODE));
});

test('interogările nu sunt emulate', function () {
    egal(false, db()->getAttribute(PDO::ATTR_EMULATE_PREPARES));
});

test('conexiunea vorbește utf8mb4', function () {
    $r = db()->query("SELECT 'ăâîșț' AS d")->fetch();
    egal('ăâîșț', $r['d'], 'diacriticele trec nestricate');
});
