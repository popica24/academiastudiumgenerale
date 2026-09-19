<?php
/* Antetul, o singură dată pentru paginile PHP.

   Căile sunt absolute, „/assets/...", nu „assets/...", exact cum face deja
   404.html: fișierul se include și de la /blog/, și de la /admin/, iar cu
   căi relative fiecare ar trebui să numere câte niveluri are de urcat.

   Cele șapte pagini HTML statice au încă propria copie a antetului. Când vor
   fi mutate pe PHP, o includ pe asta și copiile dispar.

   Variabilele așteptate, definite înainte de include:
     $titlu            textul din <title>
     $descriere        meta description
     $adresa_canonica  adresa completă, cu https://studiumgenerale.ro
     $extra_head       marcaj în plus în <head>, de pildă JSON-LD  (opțional)
     $noindex          true pentru paginile care nu se indexează   (opțional) */
$titlu           = $titlu           ?? 'Academia · Studium Generale by Denisa';
$descriere       = $descriere       ?? '';
$adresa_canonica = $adresa_canonica ?? 'https://studiumgenerale.ro/';
$extra_head      = $extra_head      ?? '';
$noindex         = $noindex         ?? false;
require_once __DIR__ . '/inc/sablon.php';
?><!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titlu) ?></title>
<meta name="description" content="<?= e($descriere) ?>">
<?php if ($noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php endif; ?>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="canonical" href="<?= e($adresa_canonica) ?>">
<meta name="theme-color" content="#121B52">
<meta name="author" content="Studium Generale by Denisa SRL">
<?= $extra_head ?>
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/assets/icons/favicon-32.png" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/poiret-one-400-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/poiret-one-400-latin-ext.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-400-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-400-latin-ext.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/montserrat-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonturi/gwendolyn-400-latin.woff2" crossorigin>
<link rel="stylesheet" href="/brand/tokens.css">
<link rel="stylesheet" href="/assets/site.css">
</head>
<body>

<a href="#continut" class="btn btn-sm sr-only">Sari la conținut</a>

<header class="site-head">
  <div class="wrap head-inner">
    <a class="brand" href="/index.html" aria-label="Academia · Studium Generale by Denisa">
      <span class="brand-mark"><img src="/assets/logo.webp" alt=""></span>
      <span class="brand-text">
        <span class="brand-name">Academia</span>
        <span class="brand-sub">Studium Generale by Denisa</span>
      </span>
    </a>
    <nav id="meniu" class="nav" data-nav aria-label="Navigație principală">
      <a class="navlink acasa-meniu" href="/index.html">Acasă</a>
      <a class="navlink" href="/despre.html">Despre</a>
      <a class="navlink" href="/index.html#materii">Materii</a>
      <a class="navlink" href="/index.html#cursuri">Cursuri</a>
      <a class="navlink" href="/index.html#preturi">Prețuri</a>
      <a class="navlink" href="/index.html#profesori">Profesori</a>
      <a class="navlink" href="/blog/">Blog</a>
      <a class="navlink lang-meniu" href="/en/index.html" hreflang="en" lang="en">English</a>
    </nav>
    <div class="row head-actions" style="gap:var(--sp-3)">
      <a class="navlink lang-comutator" href="/en/index.html" hreflang="en" lang="en">EN</a>
      <a class="btn btn-sm btn-wa" data-wa="Bacalaureat." href="https://wa.me/40735433720?text=Bun%C4%83%20ziua!%20Am%20g%C4%83sit%20Academia%20pe%20site%20%C8%99i%20a%C8%99%20vrea%20detalii%20despre%20preg%C4%83tirea%20pentru%20Bacalaureat." target="_blank" rel="noopener">WhatsApp</a>
      <button class="btn btn-sm cauta-btn" type="button" data-cauta aria-expanded="false" aria-controls="cautare" aria-label="Caută în pagină"><span class="lupa" aria-hidden="true"></span></button>
      <button class="btn btn-sm burger" type="button" data-burger aria-expanded="false" aria-controls="meniu" aria-label="Meniu"><span class="burger-linii" aria-hidden="true"></span></button>
    </div>
  </div>
  <div class="cautare" id="cautare" hidden>
    <form class="wrap cautare-inner" role="search" data-cautare>
      <label class="sr-only" for="cautare-text">Caută în pagină</label>
      <input id="cautare-text" class="cautare-camp" type="search" placeholder="Caută în pagină" autocomplete="off" spellcheck="false" enterkeyhint="search">
      <span class="cautare-numar" aria-live="polite"></span>
      <button class="cautare-pas" type="button" data-pas="-1" aria-label="Rezultatul anterior"><span class="sageata sageata-sus" aria-hidden="true"></span></button>
      <button class="cautare-pas" type="button" data-pas="1" aria-label="Rezultatul următor"><span class="sageata" aria-hidden="true"></span></button>
      <button class="cautare-pas" type="button" data-inchide aria-label="Închide căutarea"><span class="inchide" aria-hidden="true"></span></button>
    </form>
  </div>
</header>

<main id="continut">
