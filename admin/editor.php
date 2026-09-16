<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$a = $id !== null ? articol_dupa_id($id) : null;
if ($id !== null && $a === null) {
    http_response_code(404);
    readfile(dirname(__DIR__) . '/404.html');
    exit;
}

$erori = [];
/* Valorile din formular, luate din POST dacă s-a trimis, din bază dacă se
   modifică, goale dacă e articol nou. Așa un formular respins se întoarce cu
   tot ce s-a scris în el: un text de o mie de cuvinte nu se pierde pentru o
   etichetă uitată. */
$v = [
    'titlu'          => $_POST['titlu']          ?? $a['titlu']          ?? '',
    'eticheta'       => $_POST['eticheta']       ?? $a['eticheta']       ?? '',
    'rezumat'        => $_POST['rezumat']        ?? $a['rezumat']        ?? '',
    'text'           => $_POST['text']           ?? $a['text']           ?? '',
    'data_publicare' => $_POST['data_publicare'] ?? $a['data_publicare'] ?? date('Y-m-d'),
    'stare'          => $_POST['stare']          ?? $a['stare']          ?? 'ciorna',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifica_jeton($_POST['jeton'] ?? null)) {
        $erori[] = 'Formularul a expirat. Trimiteți din nou.';
    }
    if (trim($v['titlu']) === '')    { $erori[] = 'Titlul nu poate lipsi.'; }
    if (mb_strlen($v['titlu']) > 255) { $erori[] = 'Titlul e prea lung, maximum 255 de caractere.'; }
    if (trim($v['eticheta']) === '') { $erori[] = 'Eticheta nu poate lipsi.'; }
    if (trim($v['rezumat']) === '')  { $erori[] = 'Rezumatul nu poate lipsi.'; }
    if (mb_strlen($v['rezumat']) > 400) { $erori[] = 'Rezumatul e prea lung, maximum 400 de caractere.'; }
    if (trim($v['text']) === '')     { $erori[] = 'Textul nu poate lipsi.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['data_publicare'])) { $erori[] = 'Data nu e bună.'; }
    if (!in_array($v['stare'], ['ciorna', 'publicat'], true)) { $erori[] = 'Starea nu e bună.'; }

    if (count($erori) === 0) {
        $id = articol_salveaza($v, $id);
        header('Location: /admin/?salvat=' . $id);
        exit;
    }
}

$titlu = ($a === null ? 'Articol nou' : 'Modifică articolul') . ' · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/editor.php';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <div class="admin-bara">
    <h1 style="margin:0"><?= $a === null ? 'Articol nou' : 'Modifică articolul' ?></h1>
    <a class="btn btn-sm" href="/admin/">Înapoi la listă</a>
  </div>

  <?php foreach ($erori as $er): ?>
    <p class="admin-eroare"><?= e($er) ?></p>
  <?php endforeach; ?>

  <form class="admin-form" method="post">
    <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
    <label>Titlu
      <input type="text" name="titlu" required maxlength="255" value="<?= e($v['titlu']) ?>">
    </label>
    <label>Etichetă
      <input type="text" name="eticheta" required maxlength="60" value="<?= e($v['eticheta']) ?>"
             placeholder="Bacalaureat, Evaluare Națională, Metodă">
    </label>
    <label>Rezumat, se vede pe card și în previzualizarea linkului
      <textarea name="rezumat" required maxlength="400" style="min-height:90px"><?= e($v['rezumat']) ?></textarea>
    </label>
    <label>Text, simplu. Un rând gol începe un paragraf nou.
      <textarea name="text" required><?= e($v['text']) ?></textarea>
    </label>
    <label>Data publicării
      <input type="date" name="data_publicare" required value="<?= e($v['data_publicare']) ?>">
    </label>
    <label>Stare
      <select name="stare">
        <option value="ciorna"   <?= $v['stare'] === 'ciorna'   ? 'selected' : '' ?>>Ciornă, nu se vede pe site</option>
        <option value="publicat" <?= $v['stare'] === 'publicat' ? 'selected' : '' ?>>Publicat</option>
      </select>
    </label>
    <div><button class="btn btn-primary" type="submit">Salvează</button></div>
  </form>
</section>
<?php require dirname(__DIR__) . '/footer.php';
