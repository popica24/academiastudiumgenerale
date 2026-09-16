<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();

/* Numai POST, și numai cu jeton. Un GET nu șterge nimic niciodată: altfel
   un link trimis pe chat, sau un crawler care urmărește linkuri, ar putea
   goli blogul. Fără „Location" aici: antetul acela forțează singur starea
   302, iar 400 chiar trebuie să rămână 400, nu un redirect deghizat. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifica_jeton($_POST['jeton'] ?? null)) {
    http_response_code(400);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    articol_sterge($id);
}
header('Location: /admin/?sters=1');
exit;
