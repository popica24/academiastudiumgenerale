/* ==========================================================================
   ACADEMIA · Studium Generale by Denisa
   DE COMPLETAT: singurul loc din tot site-ul cu date reale.
   Schimbă valorile de mai jos și toate paginile se actualizează singure.
   Nimic altundeva nu conține numere de telefon, linkuri sau ID-uri.
   ========================================================================== */
window.ACADEMIA = {
  /* --- WhatsApp -----------------------------------------------------------
     Format internațional, fără + și fără spații.  Ex: 40721004118           */
  whatsapp: "40735433720",

  /* Textul cu care se deschide conversația. Părintele îl poate șterge.      */
  whatsappMessage:
    "Bună ziua! Am găsit Academia pe site și aș vrea detalii despre pregătirea pentru ",

  /* --- Formularul de potrivire --------------------------------------------
     Cele trei întrebări de pe pagina principală ajung aici. Materia și
     profesorul ei: `de` și `pana` sunt vârstele între care se oferă, pentru
     materiile care se predau altfel la gimnaziu și altfel la liceu. O materie
     fără ele apare la orice vârstă. Prenumele sunt cele care ies în mesajul
     de WhatsApp, deci se scriu exact cum vreți să le citească părintele.
     Aceleași 16 materii sunt și în cardurile din `index.html`.             */
  materii: [
    { nume: "Română",      profesori: ["Luiza"] },
    { nume: "Matematică",  profesori: ["Andra"],              pana: 14 },
    { nume: "Matematică",  profesori: ["Andrei"],             de: 15 },
    { nume: "Istorie",     profesori: ["Ștefania"] },
    { nume: "Geografie",   profesori: ["Alexandra"] },
    { nume: "Logică",      profesori: ["Sara"] },
    { nume: "Biologie",    profesori: ["Andra", "Cătălin"] },
    { nume: "Chimie",      profesori: ["Ioana"] },
    { nume: "Engleză",     profesori: ["Denisa", "Ștefania"] },
    { nume: "Spaniolă",    profesori: ["Diana"] },
    { nume: "Franceză",    profesori: ["Sara", "Ilinca"] },
    { nume: "Chineză",     profesori: ["Alex", "Ana"] },
    { nume: "Coreeană",    profesori: ["Jun"] },
    { nume: "Maghiară",    profesori: [] },        /* DE COMPLETAT: cine predă */
    { nume: "Informatică", profesori: ["Andrei"] },
    { nume: "Greacă",      profesori: ["Denisa"] },
  ],

  /* --- Cursuri speciale ---------------------------------------------------
     Nu sunt materii de examen și nu se pregătește nimic cu ele: se iau
     separat, la orice vârstă. Aceeași formă ca `materii`, deci intră în
     același pas al formularului, doar sub alt titlu.
     DE COMPLETAT: cine predă fiecare. Eticheta roșie „de completat" nu se
     pune aici, ea e doar pentru WhatsApp, telefon, e-mail și adresă. Cât
     timp `profesori` e gol, rezultatul formularului scrie „La Excel vă
     spunem la telefon cine predă." în loc de un nume inventat.            */
  cursuriSpeciale: [
    { nume: "Excel",                profesori: [] },
    { nume: "Contabilitate",        profesori: [] },
    { nume: "Dicție",               profesori: [] },
    { nume: "Dezvoltare personală", profesori: [] },
    { nume: "Educație financiară",  profesori: [] },
  ],

  /* Mesajul cu care se deschide WhatsApp la capătul formularului.
     %MATERIE%, %VARSTA% și %MOD% se înlocuiesc cu ce a ales vizitatorul.   */
  formularMesaj:
    "Bună ziua, aș dori să particip la cursurile de %MATERIE%, îmi puteți da " +
    "mai multe detalii? Am %VARSTA% ani și aș vrea pregătire %MOD%.",

  /* --- Video de prezentare ------------------------------------------------
     Doar ID-ul din adresa YouTube (partea de după v=), sau lasă gol.        */
  youtubeId: "hc8JZRhXLo8",
  youtubeTitle: "Cum arată o ședință la Academia",

  /* --- Contact ------------------------------------------------------------ */
  phone: "+40 735 433 720",
  email: "contact@studiumgenerale.ro",
  address: "Str. Edgar Quinet 10, et. 1, ap. 4, 010018 București",
  schedule: "Luni–vineri, 08:00–21:00",

  /* --- Rețele (lasă gol ca să dispară din subsol) ------------------------- */
  facebook: "https://www.facebook.com/p/Academia-Studium-Generale-by-Denisa-61581913795617/",
  instagram: "https://www.instagram.com/academia_studium_generale/",

  /* Pagina de membru la Camera de Comerț.
     DE COMPLETAT: adresa o dă clientul. Cât timp scrie XXX, subsolul își pune
     singur eticheta roșie.                                                */
  cameraComert: "XXX",

  /* --- Date de firmă ------------------------------------------------------
     Obligatorii în subsolul unui site comercial din România.                */
  company: "STUDIUM GENERALE BY DENISA SRL",
  cui: "52829916",
  regCom: "J2025084335006",
};
