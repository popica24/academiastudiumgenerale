/* ==========================================================================
   ACADEMIA · comportamentul site-ului
   Leagă datele din config.js, formularul de potrivire, caruselul de
   recenzii și meniul pe mobil. Nimic altceva. Fără dependențe.
   ========================================================================== */
(function () {
  "use strict";
  var C = window.ACADEMIA || {};
  var PLACEHOLDER = /X{3,}|exemplu|ID_VIDEO/i;
  function isReal(v) { return v && !PLACEHOLDER.test(v); }

  /* --- 1. WhatsApp ------------------------------------------------------ */
  function waHref(subject) {
    var text = (C.whatsappMessage || "") + (subject || "");
    return "https://wa.me/" + C.whatsapp + "?text=" + encodeURIComponent(text.trim());
  }
  document.querySelectorAll("[data-wa]").forEach(function (el) {
    if (!isReal(C.whatsapp)) {
      el.setAttribute("data-nedefinit", "WhatsApp");
      el.addEventListener("click", warn("numărul de WhatsApp"));
      return;
    }
    el.href = waHref(el.getAttribute("data-wa"));
    el.target = "_blank";
    el.rel = "noopener";
  });

  /* --- 2. Formularul de potrivire ---------------------------------------
     Trei întrebări, un singur pas vizibil o dată, totul în aceeași pagină:
     nu se trimite nimic nicăieri și nu se reîncarcă nimic. Răspunsurile stau
     într-un obiect, pasul curent e un indice, iar la capăt se compune mesajul
     de WhatsApp din ce a ales vizitatorul. Materiile și profesorii vin din
     config.js, deci se schimbă acolo, nu aici.                            */
  var formular = document.querySelector("[data-formular]");
  if (formular) construiesteFormular(formular);

  function construiesteFormular(radacina) {
    var scena = radacina.querySelector("[data-scena]");
    var progres = radacina.querySelectorAll(".formular-progres span");
    var live = radacina.querySelector("[data-live-formular]");
    var materii = C.materii || [];
    var raspunsuri = {};
    var indice = 0;

    var VARSTE = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
    var PASI = [
      {
        cheie: "varsta",
        intrebare: "Câți ani ai?",
        optiuni: function () {
          return VARSTE.map(function (v) {
            return { eticheta: v === 19 ? "19 sau mai mult" : String(v), valoare: v };
          });
        }
      },
      {
        cheie: "materie",
        intrebare: "Ce materie te-ar interesa?",
        optiuni: function () {
          return materiiLaVarsta(raspunsuri.varsta).map(function (m) {
            return { eticheta: m.nume, valoare: m.nume };
          });
        }
      },
      {
        cheie: "mod",
        intrebare: "Vrei să înveți singur sau în grupă?",
        optiuni: function () {
          return [
            { eticheta: "Singur, unu la unu", valoare: "individuală" },
            { eticheta: "În grupă de maximum șase", valoare: "în grupă" }
          ];
        }
      }
    ];

    function materiiLaVarsta(varsta) {
      return materii.filter(function (m) {
        if (m.de !== undefined && varsta < m.de) return false;
        if (m.pana !== undefined && varsta > m.pana) return false;
        return true;
      });
    }
    function profesoriPentru() {
      var alese = materiiLaVarsta(raspunsuri.varsta).filter(function (m) {
        return m.nume === raspunsuri.materie;
      });
      var nume = [];
      alese.forEach(function (m) {
        (m.profesori || []).forEach(function (n) {
          if (nume.indexOf(n) === -1) nume.push(n);   // doi profesori cu același prenume
        });
      });
      return nume;
    }
    /* „sau", nu „și": materia cu doi profesori se face cu unul dintre ei,
       nu cu amândoi deodată.                                              */
    function insiruie(nume) {
      if (nume.length < 2) return nume[0] || "";
      return nume.slice(0, -1).join(", ") + " sau " + nume[nume.length - 1];
    }
    function mesajWhatsApp() {
      var sablon = C.formularMesaj || "";
      return sablon.replace("%MATERIE%", raspunsuri.materie)
                   .replace("%VARSTA%", raspunsuri.varsta)
                   .replace("%MOD%", raspunsuri.mod);
    }

    /* Schimbarea pasului: se măsoară înălțimea veche, se pune cea nouă, iar
       scena face drumul între ele. Fără asta, pagina sare sub deget.
       Prima desenare nu are de unde pleca: acolo pasul doar apare, altfel
       ar porni de la zero și ar da peste banda de dedesubt cât ține drumul. */
    function arata(nod) {
      var vechi = scena.firstElementChild;
      if (!vechi || !vechi.classList.contains("formular-pas")) {
        if (vechi) vechi.remove();          /* mesajul pentru JavaScript oprit */
        scena.appendChild(nod);
        requestAnimationFrame(function () { nod.classList.add("e-vizibil"); });
        return;
      }
      var inaltimeVeche = scena.offsetHeight;
      vechi.classList.add("e-iesit");

      scena.style.height = inaltimeVeche + "px";
      vechi.remove();
      scena.appendChild(nod);
      var inaltimeNoua = nod.offsetHeight;

      requestAnimationFrame(function () {
        scena.style.height = inaltimeNoua + "px";
        nod.classList.add("e-vizibil");
      });

      /* Înălțimea fixată se eliberează la capătul drumului, ca scena să
         respire din nou cu conținutul. Dacă doi pași au exact aceeași
         înălțime, tranziția nu pornește și `transitionend` nu vine
         niciodată: de asta există și ceasul de siguranță.                  */
      var ceas = setTimeout(elibereaza, 600);
      function elibereaza() {
        clearTimeout(ceas);
        scena.style.height = "";
        scena.removeEventListener("transitionend", laCapat);
        reimprospateazaAOS();
      }
      function laCapat(e) { if (e.propertyName === "height") elibereaza(); }
      scena.addEventListener("transitionend", laCapat);
    }

    function marcheazaProgres() {
      Array.prototype.forEach.call(progres, function (b, i) {
        b.classList.toggle("e-facut", i < indice || indice >= PASI.length);
      });
    }

    function butonInapoi() {
      var b = document.createElement("button");
      b.type = "button";
      b.className = "btn btn-ghost formular-inapoi";
      b.textContent = "← Înapoi";
      b.addEventListener("click", function () {
        indice = Math.max(0, indice - 1);
        delete raspunsuri[PASI[indice].cheie];
        deseneazaPas();
      });
      return b;
    }

    function deseneazaPas() {
      var pas = PASI[indice];
      var nod = document.createElement("div");
      nod.className = "formular-pas";

      var numar = document.createElement("p");
      numar.className = "formular-numar";
      numar.textContent = "Pasul " + (indice + 1) + " din " + PASI.length;
      nod.appendChild(numar);

      var titlu = document.createElement("h3");
      titlu.textContent = pas.intrebare;
      titlu.tabIndex = -1;
      nod.appendChild(titlu);

      var optiuni = pas.optiuni();
      var lista = document.createElement("div");
      /* Sub patru opțiuni textele sunt lungi („În grupă de maximum șase"),
         deci pe telefon stau una sub alta, nu două pe rând.               */
      lista.className = "formular-optiuni" + (optiuni.length < 4 ? " putine" : "");
      optiuni.forEach(function (o) {
        var b = document.createElement("button");
        b.type = "button";
        b.className = "optiune";
        b.textContent = o.eticheta;
        b.addEventListener("click", function () {
          b.classList.add("e-ales");
          raspunsuri[pas.cheie] = o.valoare;
          indice++;
          setTimeout(function () {
            if (indice >= PASI.length) deseneazaRezultat(); else deseneazaPas();
          }, 140);
        });
        lista.appendChild(b);
      });
      nod.appendChild(lista);

      if (indice > 0) nod.appendChild(butonInapoi());

      arata(nod);
      marcheazaProgres();
      if (live) live.textContent = numar.textContent + ". " + pas.intrebare;
      if (indice > 0) titlu.focus();
    }

    function deseneazaRezultat() {
      var profesori = profesoriPentru();
      var nod = document.createElement("div");
      nod.className = "formular-pas";

      var numar = document.createElement("p");
      numar.className = "formular-numar";
      numar.textContent = "Gata";
      nod.appendChild(numar);

      var titlu = document.createElement("h3");
      titlu.textContent = "Felicitări!";
      titlu.tabIndex = -1;
      nod.appendChild(titlu);

      var raspuns = document.createElement("p");
      raspuns.className = "formular-raspuns";
      raspuns.textContent = profesori.length
        ? "La " + raspunsuri.materie + " vei lucra cu " + insiruie(profesori) + "."
        : "La " + raspunsuri.materie + " vă spunem la telefon cine predă.";
      nod.appendChild(raspuns);

      var recap = document.createElement("p");
      recap.className = "formular-recapitulare";
      recap.textContent = raspunsuri.varsta + " ani · " + raspunsuri.materie +
                          " · pregătire " + raspunsuri.mod;
      nod.appendChild(recap);

      var final = document.createElement("div");
      final.className = "formular-final";

      var trimite = document.createElement("a");
      trimite.className = "btn btn-primary";
      trimite.textContent = "Apasă aici pentru a finaliza";
      if (isReal(C.whatsapp)) {
        trimite.href = "https://wa.me/" + C.whatsapp + "?text=" + encodeURIComponent(mesajWhatsApp());
        trimite.target = "_blank";
        trimite.rel = "noopener";
      } else {
        trimite.href = "#programare";
        trimite.setAttribute("data-nedefinit", "WhatsApp");
        trimite.addEventListener("click", warn("numărul de WhatsApp"));
      }
      final.appendChild(trimite);

      var dinNou = document.createElement("button");
      dinNou.type = "button";
      dinNou.className = "btn btn-ghost";
      dinNou.textContent = "Iau întrebările de la capăt";
      dinNou.addEventListener("click", function () {
        raspunsuri = {}; indice = 0; deseneazaPas();
      });
      final.appendChild(dinNou);

      nod.appendChild(final);
      arata(nod);
      marcheazaProgres();
      if (live) live.textContent = raspuns.textContent;
      titlu.focus();
    }

    deseneazaPas();
  }

  /* --- 3. Video: se încarcă la clic, nu la încărcarea paginii ----------- */
  document.querySelectorAll("[data-video]").forEach(function (el) {
    if (!isReal(C.youtubeId)) { el.classList.add("is-placeholder"); return; }
    var poster = el.querySelector(".video-poster");
    if (poster) poster.style.backgroundImage =
      "url(https://i.ytimg.com/vi/" + C.youtubeId + "/maxresdefault.jpg)";
    el.addEventListener("click", function () {
      var f = document.createElement("iframe");
      f.src = "https://www.youtube-nocookie.com/embed/" + C.youtubeId + "?autoplay=1&rel=0";
      f.title = C.youtubeTitle || "Video de prezentare";
      f.allow = "accelerometer; autoplay; encrypted-media; picture-in-picture";
      f.allowFullscreen = true;
      el.innerHTML = "";
      el.appendChild(f);
    });
  });

  /* --- 4. Datele de contact din subsol ---------------------------------- */
  [["phone", "tel:"], ["email", "mailto:"]].forEach(function (pair) {
    document.querySelectorAll("[data-" + pair[0] + "]").forEach(function (el) {
      var v = C[pair[0]];
      el.textContent = v;
      if (isReal(v)) {
        el.href = pair[1] + v.replace(/\s/g, "");
      } else {
        el.removeAttribute("href");
        el.setAttribute("data-nedefinit", pair[0] === "phone" ? "Telefon" : "E-mail");
      }
    });
  });
  ["address", "schedule", "company", "cui", "regCom"].forEach(function (k) {
    document.querySelectorAll("[data-" + k + "]").forEach(function (el) {
      el.textContent = C[k];
      if (!isReal(C[k])) el.setAttribute("data-nedefinit", k === "address" ? "Adresa" : k);
    });
  });
  ["facebook", "instagram"].forEach(function (k) {
    document.querySelectorAll("[data-" + k + "]").forEach(function (el) {
      if (isReal(C[k])) { el.href = C[k]; } else { el.remove(); }
    });
  });

  function warn(what) {
    return function (e) {
      e.preventDefault();
      alert("Încă nu e completat " + what + ".\n\nSe adaugă în assets/config.js, o singură dată, pentru tot site-ul.");
    };
  }

  /* --- 5. Caruselul de recenzii ------------------------------------------
     Derulare cu scroll-snap: degetul merge nativ pe telefon, butoanele și
     tastele săgeți fac restul. Poziția se citește din scrollLeft, deci nu
     există o stare paralelă care să se desincronizeze.

     Bucla e fără capăt și fără salt vizibil: recenziile se clonează o dată
     la coadă, iar când rularea automată ajunge pe prima clonă, derularea se
     mută instantaneu cu o lungime înapoi. Clona arată exact ca originalul,
     deci ochiul nu prinde momentul. Fără clone, întoarcerea de la ultima la
     prima ar fi o baleiere lungă înapoi, peste toate celelalte.            */
  document.querySelectorAll("[data-carusel]").forEach(function (root) {
    var track = root.querySelector(".carusel-track");
    var slides = Array.prototype.slice.call(track.children);
    var prev = root.querySelector("[data-prev]");
    var next = root.querySelector("[data-next]");
    var dots = root.querySelector(".carusel-dots");
    var live = root.querySelector("[data-live]");
    var n = slides.length;
    if (!n) return;

    function reduced() {
      return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    }

    /* Clonele nu sunt conținut: cititoarele de ecran le sar, iar tastatura
       nu ajunge în ele.                                                    */
    var clonat = false;
    if (n > 1 && !reduced()) {
      slides.forEach(function (s) {
        var c = s.cloneNode(true);
        c.setAttribute("aria-hidden", "true");
        c.querySelectorAll("a, button").forEach(function (f) { f.tabIndex = -1; });
        track.appendChild(c);
      });
      clonat = true;
    }
    var toate = Array.prototype.slice.call(track.children);
    function lungimeSet() {
      return clonat ? toate[n].offsetLeft - toate[0].offsetLeft : 0;
    }

    slides.forEach(function (s, i) {
      var b = document.createElement("button");
      b.type = "button";
      b.className = "carusel-dot";
      b.setAttribute("aria-label", "Recenzia " + (i + 1) + " din " + n);
      b.addEventListener("click", function () { pauza(); go(i); });
      dots.appendChild(b);
    });

    function current() {
      var best = 0, min = Infinity;
      toate.forEach(function (s, i) {
        var d = Math.abs(s.offsetLeft - track.scrollLeft);
        if (d < min) { min = d; best = i; }
      });
      return best;
    }
    function go(i, instant) {
      i = Math.max(0, Math.min(toate.length - 1, i));
      track.scrollTo({ left: toate[i].offsetLeft,
                       behavior: (instant || reduced()) ? "auto" : "smooth" });
    }
    function sync() {
      var i = current() % n;
      Array.prototype.forEach.call(dots.children, function (d, j) {
        d.setAttribute("aria-current", j === i ? "true" : "false");
      });
      if (live) live.textContent = "Recenzia " + (i + 1) + " din " + n;
    }

    /* Săgețile și tastele merg în cerc, ca și rularea automată. */
    function paseste(directie) {
      var i = current() + directie;
      if (i < 0) { go(clonat ? n - 1 : 0); return; }
      go(i);
      if (clonat && i >= n) setTimeout(normalizeaza, 480);
    }
    function normalizeaza() {
      var L = lungimeSet();
      if (L && track.scrollLeft >= L - 2) track.scrollLeft -= L;
    }

    if (prev) prev.addEventListener("click", function () { pauza(); paseste(-1); });
    if (next) next.addEventListener("click", function () { pauza(); paseste(1); });
    root.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") { e.preventDefault(); pauza(); paseste(-1); }
      if (e.key === "ArrowRight") { e.preventDefault(); pauza(); paseste(1); }
    });
    var t;
    track.addEventListener("scroll", function () { clearTimeout(t); t = setTimeout(sync, 90); });
    sync();

    /* Rularea automată nu se oprește de tot când intervine cineva, doar se
       dă la o parte pentru câteva secunde și repornește: caruselul trebuie
       să curgă mai departe.                                                */
    var PAS = 5000, REVENIRE = 6000;
    var ceas = null, ceasPauza = null;

    function poate() {
      return !reduced() && track.scrollWidth > track.clientWidth + 4;
    }
    function opreste() { clearInterval(ceas); ceas = null; }
    function porneste() {
      opreste();
      if (!poate()) return;
      ceas = setInterval(function () { paseste(1); }, PAS);
    }
    function pauza() {
      opreste();
      clearTimeout(ceasPauza);
      ceasPauza = setTimeout(porneste, REVENIRE);
    }

    ["pointerdown", "wheel", "touchstart"].forEach(function (ev) {
      root.addEventListener(ev, pauza, { passive: true });
    });
    root.addEventListener("mouseenter", opreste);
    root.addEventListener("mouseleave", porneste);
    root.addEventListener("focusin", opreste);
    root.addEventListener("focusout", porneste);
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) opreste(); else porneste();
    });
    window.addEventListener("resize", function () { normalizeaza(); porneste(); });
    porneste();
  });

  /* --- 6. Meniul pe mobil ------------------------------------------------ */
  var burger = document.querySelector("[data-burger]");
  var menu = document.getElementById("meniu");
  if (burger && menu) {
    var setOpen = function (open) {
      burger.setAttribute("aria-expanded", String(open));
      menu.classList.toggle("is-open", open);
      /* Meniul acoperă tot ecranul pe telefon: fără asta, degetul derulează
         pagina de sub el în loc să deruleze meniul.                        */
      document.documentElement.classList.toggle("meniu-deschis", open);
    };
    burger.addEventListener("click", function () {
      setOpen(burger.getAttribute("aria-expanded") !== "true");
    });
    menu.addEventListener("click", function (e) {
      if (e.target.closest("a") && window.innerWidth <= 860) setOpen(false);
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && burger.getAttribute("aria-expanded") === "true") {
        setOpen(false); burger.focus();
      }
    });
  }

  /* --- 7. AOS, animația la derulare ---------------------------------------
     Singura dependență externă a proiectului. Vine de pe CDN, cu hash de
     integritate în pagină. Dacă lipsește, `fara-aos` readuce la vedere tot
     ce urma să fie animat: pagina rămâne întreagă, doar fără mișcare.
     `disable` întoarce true la `prefers-reduced-motion`, iar AOS șterge
     atunci singur atributele, deci nu rămâne nimic ascuns.               */
  if (window.AOS) {
    window.AOS.init({
      duration: 550,
      easing: "ease-out-cubic",
      offset: 60,
      once: true,                /* nu se reia la derulare înapoi */
      disable: function () {
        return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      }
    });
  } else {
    document.documentElement.classList.add("fara-aos");
  }

  /* Formularul își schimbă înălțimea de la un pas la altul, deci tot ce e sub
     el se mută. AOS ține minte pozițiile de la pornire; fără reîmprospătare,
     secțiunile de dedesubt s-ar aprinde prea devreme sau deloc.           */
  function reimprospateazaAOS() {
    if (window.AOS) window.AOS.refresh();
  }

  /* --- 8. Numerele care urcă ----------------------------------------------
     Valoarea finală e scrisă în HTML, nu aici: dacă JavaScript-ul nu pornește
     sau cineva cere mai puțină mișcare, numărul e deja acolo, corect. Ce face
     codul e doar să-l coboare la zero pentru câteva sute de milisecunde, și
     numai când chiar ajunge în ecran. Forma se păstrează din text: „8.40"
     rămâne cu două zecimale, „92%" își păstrează procentul.               */
  var numere = document.querySelectorAll("[data-numar]");
  if (numere.length && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    var DURATA = 1100;

    var citeste = function (el) {
      var text = el.textContent.trim();
      var m = text.match(/^([^\d-]*)(-?[\d.,]+)(.*)$/);
      if (!m) return null;
      var brut = m[2];
      var zecimale = (brut.split(".")[1] || "").length;
      var valoare = parseFloat(brut.replace(",", "."));
      if (isNaN(valoare)) return null;
      return { prefix: m[1], sufix: m[3], valoare: valoare, zecimale: zecimale, text: text };
    };

    var urca = function (el, d) {
      var start = null, gata = false;
      var termina = function () {
        if (gata) return;
        gata = true;
        el.textContent = d.text;                      /* exact textul din HTML */
      };
      var pas = function (acum) {
        if (gata) return;
        if (start === null) start = acum;
        var t = Math.min((acum - start) / DURATA, 1);
        var eased = 1 - Math.pow(1 - t, 3);           /* frânează la capăt */
        el.textContent = d.prefix + (d.valoare * eased).toFixed(d.zecimale) + d.sufix;
        if (t < 1) requestAnimationFrame(pas); else termina();
      };
      requestAnimationFrame(pas);
      /* Dacă cadrele se opresc pe drum, fila trece în fundal sau bateria intră
         în economie, numărul nu are voie să rămână la jumătate. */
      setTimeout(termina, DURATA + 400);
    };

    var porneste = function (el) {
      var d = citeste(el);
      if (!d || el.hasAttribute("data-numarat")) return;
      el.setAttribute("data-numarat", "");
      el.textContent = d.prefix + (0).toFixed(d.zecimale) + d.sufix;
      urca(el, d);
    };

    if (typeof IntersectionObserver === "function") {
      var ochi = new IntersectionObserver(function (intrari) {
        intrari.forEach(function (i) {
          if (!i.isIntersecting) return;
          ochi.unobserve(i.target);
          porneste(i.target);
        });
      }, { threshold: 0.6 });
      numere.forEach(function (el) { ochi.observe(el); });
    } else {
      numere.forEach(porneste);
    }
  }

  /* --- 9. Linkul paginii curente ---------------------------------------- */
  var here = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll("[data-nav] a").forEach(function (a) {
    if (a.getAttribute("href") === here) a.setAttribute("aria-current", "page");
  });
})();
