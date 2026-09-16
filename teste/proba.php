<?php
test('hămulețul compară valori', function () {
    egal(4, 2 + 2);
});

test('hămulețul prinde tipul greșit', function () {
    arunca(function () { egal(4, '4'); }, 'int și string nu sunt egale');
});
