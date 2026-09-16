<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

$a = articol_dupa_slug((string) ($_GET['slug'] ?? ''));

/* Slug inexistent și articol în ciornă dau același răspuns: 404 adevărat,
   cu pagina site-ului. Nu „nu aveți voie", fiindcă asta ar spune că
   articolul există. */
if ($a === null) {
    http_response_code(404);
    readfile(dirname(__DIR__) . '/404.html');
    exit;
}

$adresa = 'https://studiumgenerale.ro/blog/' . $a['slug'];
$titlu           = $a['titlu'] . ' · Academia';
$descriere       = $a['rezumat'];
$adresa_canonica = $adresa;

/* JSON_HEX_TAG transformă „<” și „>” în < și >. Fără el, un titlu
   care conține literalmente „</script>” ar închide eticheta de mai jos
   înainte de vreme și tot ce urmează ar rula ca HTML în pagină, nu ca text
   în JSON. Diacriticele rămân neescapate, JSON_HEX_TAG nu le atinge. */
$jsonld = json_encode([
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $a['titlu'],
    'description'      => $a['rezumat'],
    'datePublished'    => $a['data_publicare'],
    'dateModified'     => substr($a['actualizat_la'], 0, 10),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $adresa],
    'author'           => ['@type' => 'Organization', 'name' => 'Academia · Studium Generale by Denisa'],
    'publisher'        => ['@type' => 'Organization', 'name' => 'STUDIUM GENERALE BY DENISA SRL'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT);

$extra_head = '
<meta property="og:type" content="article">
<meta property="og:site_name" content="Academia · Studium Generale by Denisa">
<meta property="og:locale" content="ro_RO">
<meta property="og:url" content="' . e($adresa) . '">
<meta property="og:title" content="' . e($a['titlu']) . '">
<meta property="og:description" content="' . e($a['rezumat']) . '">
<meta property="og:image" content="https://studiumgenerale.ro/assets/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">' . $jsonld . '</script>';

require dirname(__DIR__) . '/header.php';
?>
  <section class="wrap" style="padding-bottom:var(--sp-10)">
    <article class="articol">
      <a class="btn btn-sm" href="/blog/" style="margin-bottom:var(--sp-6)">← Toate articolele</a>

      <span class="tag <?= e(tenta($a['eticheta'])) ?>"><?= e($a['eticheta']) ?></span>
      <h1 style="font-size:var(--fs-2xl);margin-top:var(--sp-4)"><?= e($a['titlu']) ?></h1>

      <p class="articol-meta" style="margin-top:var(--sp-3)">
        <time datetime="<?= e($a['data_publicare']) ?>"><?= e(data_ro($a['data_publicare'])) ?></time> · <span><?= timp_citit($a['text']) ?> min</span>
      </p>

      <?= paragrafe($a['text']) ?>
    </article>
  </section>
<?php require dirname(__DIR__) . '/footer.php';
