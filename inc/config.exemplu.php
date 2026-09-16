<?php
/* Copiază fișierul ăsta în inc/config.php și pune datele adevărate.
   inc/config.php nu intră niciodată în git și nu se urcă prin deploy: pe
   server se pune o dată, de mână, prin FTP, și rămâne acolo, fiindcă
   acțiunea de deploy nu șterge niciodată fișiere.
   Datele bazei de pe gazdă se iau din cPanel > MySQL Databases. */
return [
    'gazda'      => 'localhost',
    'baza'       => 'numele_bazei',
    'utilizator' => 'utilizatorul',
    'parola'     => 'parola',
];
