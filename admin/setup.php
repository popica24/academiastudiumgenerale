<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/sablon.php';

/* Se deschide o singură dată, la prima punere pe server: face tabelele și
   contul. După ce există un cont, refuză: altfel oricine ar putea să-și facă
   unul și să intre. Se șterge oricum de pe server după ce a fost folosită,
   dar refuzul e plasa: un fișier uitat nu trebuie să fie o ușă. */

$eroare = '';
$gata = false;

try {
    $exista = admin_exista();
} catch (PDOException $ex) {
    /* Prima vizită pe o bază complet goală: nici tabelul administratori nu
       există încă, fiindcă schema n-a rulat niciodată. Nu e o eroare, e
       chiar starea de dinaintea instalării, deci tratăm întrebarea ca „nu,
       nu există cont" și lăsăm formularul să apară. */
    $exista = false;
}

/* Schema se citește și se rulează DOAR cât timp nu există încă niciun cont,
   nu la fiecare cerere: odată contul creat, tabelele sunt deja acolo, iar
   a mai executa CREATE TABLE la fiecare vizită ar fi lucru degeaba pe o
   pagină care oricum stă pe server doar temporar. */
if (!$exista && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cale_schema = dirname(__DIR__) . '/schema.sql';
    if (!is_file($cale_schema)) {
        /* admin/setup.php și schema.sql se urcă separat, de mână, prin FTP:
           deploy-ul automat le exclude pe amândouă (vezi deploy.yml). Dacă
           operatorul a uitat schema.sql alături de setup.php, spunem exact
           asta, în română, nu lăsăm file_get_contents() să dea un TypeError
           neinteligibil. */
        $eroare = 'Lipsește fișierul schema.sql. Urcați-l prin FTP alături de setup.php, apoi reîncărcați pagina.';
    } else {
        $schema = file_get_contents($cale_schema);
        /* schema.sql are două CREATE TABLE plus comentarii, iar PDO_MySQL nu
           garantează că exec() rulează mai multe instrucțiuni date dintr-o
           dată: dacă driverul ar tăcea la a doua, tocmai pasul care rulează
           pe serverul adevărat, unde e cel mai greu de depanat, ar eșua pe
           jumătate. De aceea fișierul se taie pe ';' și fiecare bucată se
           execută separat, sărind peste ce rămâne gol sau e numai
           comentariu. */
        foreach (explode(';', $schema) as $instructiune) {
            $fara_comentarii = preg_replace('/--.*$/m', '', $instructiune);
            if (trim($fara_comentarii) === '') {
                continue;
            }
            db()->exec($instructiune);
        }

        $email  = trim((string) ($_POST['email'] ?? ''));
        $parola = (string) ($_POST['parola'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $eroare = 'E-mailul nu pare a fi un e-mail.';
        } elseif (mb_strlen($parola, 'UTF-8') < 12) {
            /* mb_strlen, nu strlen: pe un site românesc, „țâșîăâ" are 6 litere
               dar 12 octeți în UTF-8. strlen() ar număra octeții și ar lăsa să
               treacă o parolă de jumătate din lungimea promisă în mesaj. */
            $eroare = 'Parola trebuie să aibă cel puțin 12 caractere.';
        } else {
            admin_creeaza($email, $parola);
            $gata = true;
        }
    }
}

$titlu = 'Instalare · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/setup.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <h1>Instalare</h1>
  <?php if ($exista): ?>
    <p>Există deja un cont de administrare. Pagina asta nu mai face nimic.</p>
    <p><strong>Ștergeți fișierul <code>admin/setup.php</code> de pe server.</strong></p>
    <p><a class="btn btn-sm" href="/admin/autentificare.php">Intră în cont</a></p>
  <?php elseif ($gata): ?>
    <p>Contul a fost creat și tabelele sunt la locul lor.</p>
    <p><strong>Ștergeți acum fișierul <code>admin/setup.php</code> de pe server.</strong></p>
    <p><a class="btn btn-sm" href="/admin/autentificare.php">Intră în cont</a></p>
  <?php else: ?>
    <p>Se face o singură dată: contul cu care se scriu articolele.</p>
    <?php if ($eroare !== ''): ?><p class="admin-eroare"><?= e($eroare) ?></p><?php endif; ?>
    <form class="admin-form" method="post" action="/admin/setup.php">
      <label>E-mail
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </label>
      <label>Parolă, cel puțin 12 caractere
        <input type="password" name="parola" required minlength="12" autocomplete="new-password">
      </label>
      <div><button class="btn btn-primary" type="submit">Creează contul</button></div>
    </form>
  <?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/footer.php';
