<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/articole.php';

cere_autentificare();
$articole = articole_toate();

$titlu = 'Articole · Administrare';
$noindex = true;
$adresa_canonica = 'https://studiumgenerale.ro/admin/';
$extra_head = '<link rel="stylesheet" href="/admin/admin.css">';
require dirname(__DIR__) . '/header.php';
?>
<section class="wrap admin-wrap">
  <div class="admin-bara">
    <h1 style="margin:0">Articole</h1>
    <div class="row" style="gap:var(--sp-3)">
      <a class="btn btn-sm btn-primary" href="/admin/editor.php">Articol nou</a>
      <form method="post" action="/admin/iesire.php" style="margin:0">
        <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
        <button class="btn btn-sm" type="submit">Ieși</button>
      </form>
    </div>
  </div>

  <?php if (count($articole) === 0): ?>
    <p class="admin-gol">Niciun articol încă. <a href="/admin/editor.php">Scrie primul</a>.</p>
  <?php else: ?>
  <table class="admin-tabel">
    <thead>
      <tr><th>Titlu</th><th>Etichetă</th><th>Data</th><th>Stare</th><th>Acțiuni</th></tr>
    </thead>
    <tbody>
    <?php foreach ($articole as $a): ?>
      <tr>
        <td><?= e($a['titlu']) ?></td>
        <td><?= e($a['eticheta']) ?></td>
        <td><?= e(data_ro($a['data_publicare'])) ?></td>
        <td><?= $a['stare'] === 'publicat' ? 'publicat' : 'ciornă' ?></td>
        <td>
          <div class="row" style="gap:var(--sp-2)">
            <?php if ($a['stare'] === 'publicat'): ?>
              <a class="navlink" href="/blog/<?= e($a['slug']) ?>" target="_blank" rel="noopener">Vezi</a>
            <?php endif; ?>
            <a class="navlink" href="/admin/editor.php?id=<?= (int) $a['id'] ?>">Modifică</a>
            <?php /* Confirmarea nu stă în onsubmit: browserul decodează entitățile HTML
               dintr-un atribut înainte ca JavaScript-ul de acolo să se interpreteze, deci
               un titlu cu apostrof, escapat de e() ca „&#039;", ar reveni la apostroful
               simplu chiar înainte de a rula și ar închide șirul JS mai devreme, un
               șir spart și exploatabil. Titlul stă într-un atribut de date, unde e()
               chiar e escaparea potrivită, iar confirmarea se leagă mai jos, o singură
               dată, dintr-un script separat. */ ?>
            <form method="post" action="/admin/sterge.php" class="admin-sterge"
                  data-titlu="<?= e($a['titlu']) ?>" style="margin:0">
              <input type="hidden" name="jeton" value="<?= e(jeton()) ?>">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button class="navlink" type="submit">Șterge</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
<script>
/* Vezi comentariul de lângă formularul de ștergere: confirmarea se leagă aici,
   nu în onsubmit, ca titlul cu apostrof să nu poată sparge șirul JS. */
document.querySelectorAll('form.admin-sterge').forEach(function (form) {
  form.addEventListener('submit', function (ev) {
    var titlu = form.dataset.titlu || '';
    if (!confirm('Ștergeți articolul „' + titlu + '”? Nu se mai poate aduce înapoi.')) {
      ev.preventDefault();
    }
  });
});
</script>
<?php require dirname(__DIR__) . '/footer.php';
