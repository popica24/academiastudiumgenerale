<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';

/* Ieșirea se face prin POST cu jeton, ca orice altă schimbare de stare: cu
   GET, o imagine cu src="/admin/iesire.php" pusă în altă pagină ar scoate
   omul din cont de fiecare dată când o vede. E o glumă, nu o spargere, dar
   regula e aceeași pentru toate. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifica_jeton($_POST['jeton'] ?? null)) {
    deconecteaza();
}
header('Location: /admin/autentificare.php');
exit;
