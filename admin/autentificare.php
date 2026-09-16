<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/sablon.php';

if (e_autentificat()) {
    header('Location: /admin/');
    exit;
}

$eroare = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifica_jeton($_POST['jeton'] ?? null)) {
        $eroare = 'Formularul a expirat. Încercați din nou.';
    } else {
        $eroare = autentifica(
            trim((string) ($_POST['email'] ?? '')),
            (string) ($_POST['parola'] ?? '')
        ) ?? '';
        if ($eroare === '') {
            header('Location: /admin/');
            exit;
        }
    }
}

$titlu = 'Intrare · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/autentificare.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <h1>Intrare</h1>
  <?php if ($eroare !== ''): ?><p class="admin-eroare"><?= e($eroare) ?></p><?php endif; ?>
  <form class="admin-form" method="post" action="/admin/autentificare.php">
    <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
    <label>E-mail
      <input type="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
    </label>
    <label>Parolă
      <input type="password" name="parola" required autocomplete="current-password">
    </label>
    <div><button class="btn btn-primary" type="submit">Intră</button></div>
  </form>
</section>
<?php require dirname(__DIR__) . '/footer.php';
