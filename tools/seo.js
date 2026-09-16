#!/usr/bin/env node
/* ==========================================================================
   tools/seo.js · scrie în HTML ce trebuie să se vadă fără JavaScript

   `assets/config.js` rămâne singurul loc în care se editează datele de
   contact. Scriptul ăsta le copiază în pagini, ca să apară în view-source:
   crawlerele de AI și aplicațiile de previzualizare nu rulează JavaScript,
   deci un telefon pus doar din `site.js` pentru ele nu există.

   Ce face, pe fiecare pagină din PAGINI:
     1. umple elementele `data-phone`, `data-email`, `data-address`,
        `data-schedule`, `data-company`, `data-cui`, `data-regCom`, anul din
        subsol și linkurile `data-wa`, `data-facebook`, `data-instagram`,
        `data-camera`, `data-colegiu`. `site.js` face la fel la încărcare, deci cele
        două nu
        se contrazic: HTML-ul e doar punctul de plecare.
     2. rescrie JSON-LD-ul dintre marcajele DATE STRUCTURATE, la capătul lui <body>,
        construit din config.js și din textul paginii (FAQ, profesori,
        articol), ca un text să nu fie scris de două ori de mână.

   Se rulează după orice schimbare în config.js, în FAQ, la profesori sau în
   titlul și descrierea unei pagini:

       node tools/seo.js

   Rulat de două ori la rând nu schimbă nimic a doua oară.
   ========================================================================== */
"use strict";
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const RADACINA = path.join(__dirname, "..");
const BAZA = "https://studiumgenerale.ro";

const ctx = { window: {} };
vm.runInNewContext(fs.readFileSync(path.join(RADACINA, "assets/config.js"), "utf8"), ctx);
const C = ctx.window.ACADEMIA;
const PLACEHOLDER = /X{3,}|exemplu|ID_VIDEO/i;
const real = (v) => Boolean(v) && !PLACEHOLDER.test(v);

/* `tip` e tipul schema.org al paginii; `fir` e firul Ariadnei, fără Acasă,
   care se adaugă singur. 404 nu are JSON-LD: nu e o pagină de indexat.    */
const PAGINI = [
  { fisier: "index.html",        adresa: "/",                  tip: "WebPage" },
  { fisier: "despre.html",       adresa: "/despre.html",       tip: "AboutPage",      fir: [["Despre", "/despre.html"]] },
  { fisier: "en/index.html",     adresa: "/en/",               tip: "WebPage" },
  { fisier: "en/despre.html",    adresa: "/en/despre.html",    tip: "AboutPage",      fir: [["About", "/en/despre.html"]] },
  { fisier: "404.html",          fara_jsonld: true },
];

/* ── utilitare de text ─────────────────────────────────────────────────── */
const escHtml = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const escAttr = (s) => escHtml(s).replace(/"/g, "&quot;");
const ENTITATI = { amp: "&", lt: "<", gt: ">", quot: '"', rsquo: "’", lsquo: "‘", rdquo: "”", ldquo: "„", nbsp: " ", hellip: "…", ndash: "–" };
const faraTaguri = (s) => s
  .replace(/<[^>]+>/g, "")
  .replace(/&(#\d+|\w+);/g, (m, e) => e[0] === "#" ? String.fromCodePoint(+e.slice(1)) : (ENTITATI[e] ?? m))
  .replace(/\s+/g, " ").trim();

/* Elementele fără copii cu atributul `data-<cheie>`: li se înlocuiește
   conținutul și, dacă e cazul, `href`. Toate țintele din pagini sunt simple
   (`<a data-phone href="#"></a>`, `<span data-address></span>`), deci o
   expresie regulată e suficientă și nu trebuie un parser întreg.          */
function umple(html, cheie, { text, href, extern }) {
  const re = new RegExp(`<(a|span)((?:\\s[^>]*)?\\sdata-${cheie}(?:="[^"]*")?(?:\\s[^>]*)?)>([^<]*)</\\1>`, "g");
  return html.replace(re, (tot, tag, atr, continut) => {
    let a = atr;
    if (href !== undefined) {
      a = / href="[^"]*"/.test(a) ? a.replace(/ href="[^"]*"/, ` href="${escAttr(href)}"`) : `${a} href="${escAttr(href)}"`;
      if (extern) {
        if (!/ target=/.test(a)) a += ' target="_blank"';
        if (!/ rel=/.test(a)) a += ' rel="noopener"';
      }
    }
    return `<${tag}${a}>${text !== undefined ? escHtml(text) : continut}</${tag}>`;
  });
}

function textePentru(limba) {
  return (C.texte && C.texte[limba]) || C.texte.ro;
}

/* ── 1. datele de contact, scrise în pagină ─────────────────────────────── */
function datele(html, limba) {
  const T = textePentru(limba);
  if (real(C.phone)) html = umple(html, "phone", { text: C.phone, href: "tel:" + C.phone.replace(/\s/g, "") });
  if (real(C.email)) html = umple(html, "email", { text: C.email, href: "mailto:" + C.email });
  if (real(C.address)) html = umple(html, "address", { text: C.address });
  if (real(T.schedule)) html = umple(html, "schedule", { text: T.schedule });
  for (const k of ["company", "cui", "regCom"]) if (real(C[k])) html = umple(html, k, { text: C[k] });
  for (const k of ["facebook", "instagram", "tiktok"]) if (real(C[k])) html = umple(html, k, { href: C[k], extern: true });
  if (real(C.cameraComert)) html = umple(html, "camera", { href: C.cameraComert, extern: true });
  if (real(C.mediterraneanCollege)) html = umple(html, "colegiu", { href: C.mediterraneanCollege, extern: true });
  if (real(C.whatsapp)) {
    html = html.replace(/<a((?:\s[^>]*)?)\sdata-wa="([^"]*)"((?:\s[^>]*)?)>/g, (tot, inainte, subiect, dupa) => {
      const mesaj = ((T.whatsappMessage || "") + subiect).trim();
      const href = `https://wa.me/${C.whatsapp}?text=${encodeURIComponent(mesaj)}`;
      let rest = (inainte + dupa).replace(/ href="[^"]*"| target="[^"]*"| rel="[^"]*"/g, "");
      return `<a${rest} data-wa="${subiect}" href="${escAttr(href)}" target="_blank" rel="noopener">`;
    });
  }
  html = html.replace(/<span id="an">[^<]*<\/span>/, `<span id="an">${new Date().getFullYear()}</span>`);
  return html;
}

/* ── 2. JSON-LD ─────────────────────────────────────────────────────────── */
function citeste(html, re) {
  const m = html.match(re);
  return m ? faraTaguri(m[1]) : "";
}

function scoala(limba) {
  const T = textePentru(limba);
  const org = {
    "@type": ["EducationalOrganization", "LocalBusiness"],
    "@id": BAZA + "/#scoala",
    name: "Academia Studium Generale by Denisa",
    alternateName: ["Academia", "ASG", "Studium Generale by Denisa"],
    legalName: C.company,
    url: BAZA + "/",
    logo: { "@type": "ImageObject", url: BAZA + "/assets/logo.webp", width: 200, height: 200 },
    image: BAZA + "/assets/og.jpg",
    slogan: "Construim viitorul prin educație!",
    foundingDate: "2025-11-03",
    founder: { "@type": "Person", name: "Denisa" },
    areaServed: { "@type": "City", name: "București" },
    knowsLanguage: ["ro", "en", "fr", "es", "zh", "ko", "hu", "el", "ar"],
  };
  if (real(C.cui)) org.taxID = C.cui;
  if (real(C.regCom)) org.identifier = { "@type": "PropertyValue", propertyID: "Registrul Comerțului", value: C.regCom };
  if (real(C.phone)) org.telephone = C.phone.replace(/\s/g, "");
  if (real(C.email)) org.email = C.email;
  if (real(C.address)) {
    const cod = C.address.match(/\b(\d{6})\b/);
    org.address = {
      "@type": "PostalAddress",
      /* Orașul are câmpul lui, deci nu se repetă în stradă. */
      streetAddress: C.address.replace(/,?\s*\d{6}.*$/, "").replace(/^București,\s*/, "").trim(),
      addressLocality: "București",
      postalCode: cod ? cod[1] : undefined,
      addressCountry: "RO",
    };
  }
  const orar = (T.schedule || "").match(/(\d{2}):(\d{2})[^\d]+(\d{2}):(\d{2})/);
  if (orar) {
    org.openingHoursSpecification = [{
      "@type": "OpeningHoursSpecification",
      dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      opens: `${orar[1]}:${orar[2]}`,
      closes: `${orar[3]}:${orar[4]}`,
    }];
  }
  if (real(C.whatsapp)) {
    org.contactPoint = {
      "@type": "ContactPoint",
      contactType: limba === "en" ? "enrollment" : "înscrieri",
      telephone: "+" + C.whatsapp,
      availableLanguage: ["ro", "en"],
    };
  }
  const retele = [C.facebook, C.instagram, C.tiktok].filter(real);
  if (retele.length) org.sameAs = retele;
  return org;
}

function oferta(limba) {
  const vazute = new Set();
  const toate = (C.materii || []).concat(C.cursuriSpeciale || []).filter((m) => !vazute.has(m.nume) && vazute.add(m.nume));
  return {
    "@type": "OfferCatalog",
    name: limba === "en" ? "Subjects and special courses" : "Materii și cursuri speciale",
    itemListElement: toate.map((m) => {
      const nume = (limba === "en" && m.numeEn) || m.nume;
      return {
        "@type": "Offer",
        itemOffered: {
          "@type": "Course",
          name: nume,
          description: (limba === "en"
            ? "Preparation in %M%, one to one or in groups of no more than three students."
            : "Pregătire la %M%, individual sau în grupe de maximum trei elevi.").replace("%M%", nume),
          provider: { "@id": BAZA + "/#scoala" },
        },
      };
    }),
  };
}

function jsonld(html, p) {
  const limba = (html.match(/<html lang="([a-z]{2})/) || [, "ro"])[1];
  const url = BAZA + p.adresa;
  const titlu = citeste(html, /<title>([\s\S]*?)<\/title>/);
  const descriere = (html.match(/<meta name="description" content="([^"]*)"/) || [, ""])[1];

  const org = scoala(limba);
  const noduri = [org, {
    "@type": "WebSite",
    "@id": BAZA + "/#site",
    url: BAZA + "/",
    name: "Academia Studium Generale by Denisa",
    inLanguage: ["ro", "en"],
    publisher: { "@id": BAZA + "/#scoala" },
  }];

  const pagina = {
    "@type": p.tip,
    "@id": url + "#pagina",
    url,
    name: titlu,
    description: faraTaguri(descriere),
    inLanguage: limba === "en" ? "en" : "ro-RO",
    isPartOf: { "@id": BAZA + "/#site" },
    about: { "@id": BAZA + "/#scoala" },
    primaryImageOfPage: { "@type": "ImageObject", url: BAZA + "/assets/og.jpg", width: 1200, height: 630 },
  };
  noduri.push(pagina);

  if (p.fir) {
    const acasa = limba === "en" ? [["Home", "/en/"]] : [["Acasă", "/"]];
    const h1 = citeste(html, /<h1[^>]*>([\s\S]*?)<\/h1>/);
    pagina.breadcrumb = { "@id": url + "#fir" };
    noduri.push({
      "@type": "BreadcrumbList",
      "@id": url + "#fir",
      itemListElement: acasa.concat(p.fir).map(([nume, adresa], i) => ({
        "@type": "ListItem", position: i + 1, name: nume || h1, item: BAZA + adresa,
      })),
    });
  }

  /* Pagina principală: ce se predă, cine predă, videoul și întrebările. */
  if (/<section[^>]*id="materii"/.test(html)) {
    org.hasOfferCatalog = oferta(limba);

    /* Un card poate avea doar bio, doar citat sau amândouă: unii profesori
       s-au prezentat numai la persoana întâi, fără date de parcurs.       */
    const profesori = [...html.matchAll(/<article class="person person-foto"[\s\S]*?<\/article>/g)].map(([card]) => [
      null,
      (card.match(/<img src="([^"]+)"/) || [])[1],
      (card.match(/<h3>([\s\S]*?)<\/h3>/) || [])[1],
      (card.match(/<p class="tag">([\s\S]*?)<\/p>/) || [])[1],
      [(card.match(/<p class="person-cv">([\s\S]*?)<\/p>/) || [])[1],
       (card.match(/<blockquote class="person-vorba">([\s\S]*?)<\/blockquote>/) || [])[1]].filter(Boolean).join(" "),
    ]);
    if (profesori.length) {
      org.employee = profesori.map(([, img, nume, materie, cv]) => ({
        "@type": "Person",
        name: faraTaguri(nume),
        jobTitle: (limba === "en" ? "Teacher · " : "Profesor · ") + faraTaguri(materie),
        description: faraTaguri(cv),
        image: BAZA + "/" + img.replace(/^\.\.\//, ""),
        worksFor: { "@id": BAZA + "/#scoala" },
      }));
    }

    const video = html.match(/<section[^>]*id="video"[\s\S]*?<h2>([\s\S]*?)<\/h2>\s*<p>([\s\S]*?)<\/p>/);
    if (video) {
      noduri.push({
        "@type": "VideoObject",
        "@id": url + "#video",
        name: faraTaguri(video[1]),
        description: faraTaguri(video[2]),
        thumbnailUrl: BAZA + "/assets/video/prezentare.webp",
        contentUrl: BAZA + "/assets/video/prezentare.mp4",
        uploadDate: "2026-09-14T00:00:00+03:00",
        duration: "PT44S",
        inLanguage: "ro-RO",
        publisher: { "@id": BAZA + "/#scoala" },
      });
    }

    const faq = html.match(/data-faq[\s\S]*?<\/section>/);
    if (faq) {
      const intrebari = [...faq[0].matchAll(/<summary>([\s\S]*?)<\/summary>\s*<(p|div) class="faq-raspuns">([\s\S]*?)<\/\2>/g)];
      if (intrebari.length) {
        noduri.push({
          "@type": "FAQPage",
          "@id": url + "#intrebari",
          isPartOf: { "@id": url + "#pagina" },
          inLanguage: pagina.inLanguage,
          mainEntity: intrebari.map(([, q, , a]) => ({
            "@type": "Question",
            name: faraTaguri(q),
            acceptedAnswer: { "@type": "Answer", text: faraTaguri(a) },
          })),
        });
      }
    }
  }

  /* Articolul de blog are acum propriul JSON-LD, scris de blog/articol.php
     din baza de date; funcția de aici nu mai are ce face.                 */

  const json = JSON.stringify({ "@context": "https://schema.org", "@graph": noduri }, null, 2)
    .replace(/<\//g, "<\\/");
  const bloc = `<!-- DATE STRUCTURATE: generate de tools/seo.js din config.js și din textul paginii. Nu se editează de mână. -->
<script type="application/ld+json">
${json}
</script>
<!-- /DATE STRUCTURATE -->`;
  /* La capătul lui <body>, nu în <head>: sunt câteva sute de rânduri, iar
     în <head> ar sta înaintea foilor de stil. Google le citește oriunde.  */
  const re = /<!-- DATE STRUCTURATE:[\s\S]*?<!-- \/DATE STRUCTURATE -->\n/;
  return html.replace(re, "").replace("</body>", bloc + "\n</body>");
}

/* ── rulare ─────────────────────────────────────────────────────────────── */
let schimbate = 0;
for (const p of PAGINI) {
  const f = path.join(RADACINA, p.fisier);
  if (!fs.existsSync(f)) { console.warn("lipsește", p.fisier); continue; }
  const inainte = fs.readFileSync(f, "utf8");
  const limba = (inainte.match(/<html lang="([a-z]{2})/) || [, "ro"])[1];
  let html = datele(inainte, limba);
  if (!p.fara_jsonld) html = jsonld(html, p);
  if (html !== inainte) { fs.writeFileSync(f, html); schimbate++; }
  console.log((html !== inainte ? "scris   " : "neschimbat ") + p.fisier);
}
console.log(schimbate ? `${schimbate} fișiere actualizate` : "totul era deja la zi");
