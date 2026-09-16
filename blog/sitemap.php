<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/articole.php';

/* sitemap.xml al blogului. Cel de la rădăcină e scris de mână și ține
   paginile statice; ăsta se face din bază, fiindcă articolele apar și dispar
   fără ca nimeni să atingă un fișier. Amândouă sunt trecute în robots.txt. */
header('Content-Type: application/xml; charset=utf-8');
$articole = articole_publicate();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://studiumgenerale.ro/blog/</loc>
    <lastmod><?= e(date('Y-m-d')) ?></lastmod>
  </url>
<?php foreach ($articole as $a): ?>
  <url>
    <loc>https://studiumgenerale.ro/blog/<?= e($a['slug']) ?></loc>
    <lastmod><?= e(substr($a['actualizat_la'], 0, 10)) ?></lastmod>
  </url>
<?php endforeach; ?>
</urlset>
