<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

$articole = articole_publicate();

$titlu           = 'Blog · Academia · Studium Generale by Denisa';
$descriere       = 'Articole despre pregătirea pentru Bacalaureat și Evaluare Națională: metode de învățat, greșeli frecvente și ce cere baremul.';
$adresa_canonica = 'https://studiumgenerale.ro/blog/';
$extra_head = '
<meta property="og:type" content="website">
<meta property="og:site_name" content="Academia · Studium Generale by Denisa">
<meta property="og:locale" content="ro_RO">
<meta property="og:url" content="https://studiumgenerale.ro/blog/">
<meta property="og:title" content="Blog · Academia Studium Generale">
<meta property="og:description" content="' . e($descriere) . '">
<meta property="og:image" content="https://studiumgenerale.ro/assets/og.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Blazonul Academiei Studium Generale by Denisa">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Blog · Academia Studium Generale">
<meta name="twitter:description" content="' . e($descriere) . '">
<meta name="twitter:image" content="https://studiumgenerale.ro/assets/og.jpg">';
require dirname(__DIR__) . '/header.php';
?>
  <section class="hero wrap">
    <h1>Blog</h1>
    <p class="hero-lede">Scriem rar și doar când avem ce spune: ce cere baremul, ce greșeli se repetă an de an și cum se învață un capitol în loc să fie memorat.</p>
  </section>

  <section class="wrap">
    <h2 class="sr-only">Articole recente</h2>
    <?php if (count($articole) === 0): ?>
      <p class="note">Încă nu e publicat niciun articol. Revine curând.</p>
    <?php else: ?>
    <div class="articole">
      <?php foreach ($articole as $i => $a): ?>
      <article>
        <a class="card articol-card" href="/blog/<?= e($a['slug']) ?>" data-aos="fade-up" data-aos-delay="<?= min($i, 4) * 70 ?>">
          <span class="tag <?= e(tenta($a['eticheta'])) ?>"><?= e($a['eticheta']) ?></span>
          <h3><?= e($a['titlu']) ?></h3>
          <p><?= e($a['rezumat']) ?></p>
          <span class="articol-meta">
            <time datetime="<?= e($a['data_publicare']) ?>"><?= e(data_ro($a['data_publicare'])) ?></time> · <span><?= timp_citit($a['text']) ?> min</span>
          </span>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
<?php require dirname(__DIR__) . '/footer.php';
