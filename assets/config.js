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

  /* --- Formularul de potrivire --------------------------------------------
     Cele trei întrebări de pe pagina principală ajung aici. Materia și
     profesorul ei: `de` și `pana` sunt vârstele între care se oferă, pentru
     materiile care se predau altfel la gimnaziu și altfel la liceu. O materie
     fără ele apare la orice vârstă. Prenumele sunt cele care ies în mesajul
     de WhatsApp, deci se scriu exact cum vreți să le citească părintele.
     Aceleași 18 materii sunt și în cardurile din `index.html`.             */
  materii: [
    { nume: "Română",      numeEn: "Romanian",   profesori: ["Luiza"] },
    { nume: "Matematică",  numeEn: "Mathematics", profesori: ["Andra"],  pana: 14 },
    { nume: "Matematică",  numeEn: "Mathematics", profesori: ["Andrei"], de: 15 },
    { nume: "Istorie",     numeEn: "History",     profesori: ["Ștefania"] },
    { nume: "Geografie",   numeEn: "Geography",   profesori: ["Alexandra"] },
    { nume: "Logică",      numeEn: "Logic",       profesori: ["Sara"] },
    { nume: "Biologie",    numeEn: "Biology",     profesori: ["Andra", "Cătălin"] },
    { nume: "Chimie",      numeEn: "Chemistry",   profesori: ["Ioana"] },
    { nume: "Engleză",     numeEn: "English",     profesori: ["Denisa", "Ștefania"] },
    { nume: "Spaniolă",    numeEn: "Spanish",     profesori: ["Diana"] },
    { nume: "Franceză",    numeEn: "French",      profesori: ["Sara", "Ilinca"] },
    { nume: "Chineză",     numeEn: "Chinese",     profesori: ["Alexandru", "Ana"] },
    { nume: "Coreeană",    numeEn: "Korean",      profesori: ["Jun"] },
    { nume: "Maghiară",    numeEn: "Hungarian",   profesori: [] },        /* DE COMPLETAT: cine predă */
    { nume: "Informatică", numeEn: "Computer science", profesori: ["Andrei"] },
    { nume: "Greacă",      numeEn: "Greek",       profesori: ["Denisa"] },
    { nume: "Turcă",       numeEn: "Turkish",     profesori: ["Denisa"] },
    { nume: "Română pentru străini", numeEn: "Romanian for foreigners", profesori: ["Denisa"] },
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
    { nume: "Contabilitate",        numeEn: "Accounting",         profesori: [] },
    { nume: "Dicție",               numeEn: "Diction",            profesori: [] },
    { nume: "Dezvoltare personală", numeEn: "Personal development", profesori: [] },
    { nume: "Educație financiară",  numeEn: "Financial literacy", profesori: [] },
  ],

  /* --- Textele care depind de limbă ---------------------------------------
     Aici intră doar ce se schimbă între română și engleză: întrebările
     formularului, mesajele de WhatsApp și programul. Telefonul, adresa și
     restul datelor de firmă NU se dublează: sunt aceleași în orice limbă și
     rămân mai sus, scrise o singură dată.
     `whatsappMessage` și `formularMesaj` există DOAR aici, nu și mai sus:
     un text scris în două locuri se desincronizează la prima corectură,
     exact cum s-a întâmplat cu `schedule`. Sursa de adevăr e `texte`;
     `site.js` citește mereu prin `T()`, cu ramura după `lang` de pe <html>,
     deci o pagină nouă nu are nevoie de nicio linie de cod, doar de
     atributul corect.                                                     */
  texte: {
    ro: {
      whatsappMessage:
        "Bună ziua! Am găsit Academia pe site și aș vrea detalii despre pregătirea pentru ",
      formularMesaj:
        "Bună ziua, aș dori să particip la cursurile de %MATERIE%, îmi puteți da " +
        "mai multe detalii? Am %VARSTA% ani și aș vrea pregătire %MOD%.",
      /* Descrierea ofertei din datele structurate (JSON-LD), citită de
         `hasOfferCatalog` din site.js, secțiunea 9. %MATERIE% se înlocuiește
         cu numele materiei, deja tradus de `numeMaterie()`.               */
      ofertaDescriere:
        "Pregătire la %MATERIE%, individual sau în grupe de maximum trei elevi.",
      varsta: "Câți ani ai?",
      materie: "Ce materie te-ar interesa?",
      mod: "Vrei să înveți singur sau în grupă?",
      grupMaterii: "Materii",
      grupCursuri: "Cursuri speciale",
      individual: "Singur, unu la unu",
      inGrupa: "În grupă de maximum trei",
      valIndividual: "individuală",
      valInGrupa: "în grupă",
      saiMult: "19 sau mai mult",
      pasul: "Pasul %N% din %TOTAL%",
      inapoi: "← Înapoi",
      gata: "Gata",
      felicitari: "Felicitări!",
      cuProfesor: "La %MATERIE% vei lucra cu %PROFESORI%.",
      faraProfesor: "La %MATERIE% vă spunem la telefon cine predă.",
      recapitulare: "%VARSTA% ani · %MATERIE% · pregătire %MOD%",
      separatorSau: " sau ",
      apasaAici: "Apasă aici pentru a finaliza",
      delaCapat: "Iau întrebările de la capăt",
      /* Programul, ca propoziție: nu e o dată de contact, e text și se
         traduce. `site.js` îl citește prin `T("schedule", ...)`, nu direct
         din obiectul de mai jos.                                          */
      schedule: "Luni–vineri, 08:00–21:00",
    },
    en: {
      whatsappMessage:
        "Hello! I found the Academy online and I would like details about preparation for ",
      formularMesaj:
        "Hello, I would like to join the %MATERIE% classes, could you send me " +
        "more details? I am %VARSTA% years old and I would prefer %MOD% lessons.",
      ofertaDescriere:
        "Preparation for %MATERIE%, one to one or in groups of up to three students.",
      varsta: "How old are you?",
      materie: "Which subject are you interested in?",
      mod: "Would you rather study alone or in a small group?",
      grupMaterii: "Subjects",
      grupCursuri: "Special courses",
      individual: "Alone, one to one",
      inGrupa: "In a group of no more than three",
      valIndividual: "one to one",
      valInGrupa: "small group",
      saiMult: "19 or older",
      pasul: "Step %N% of %TOTAL%",
      inapoi: "← Back",
      gata: "Done",
      felicitari: "Congratulations!",
      cuProfesor: "For %MATERIE% you will work with %PROFESORI%.",
      faraProfesor: "For %MATERIE% we will tell you by phone who teaches it.",
      recapitulare: "%VARSTA% years old · %MATERIE% · %MOD% preparation",
      separatorSau: " or ",
      apasaAici: "Click here to finish",
      delaCapat: "Start the questions over",
      schedule: "Monday–Friday, 08:00–21:00",
    },
  },

  /* --- Contact ------------------------------------------------------------
     Programul nu mai e aici: e o propoziție, nu o dată de contact, deci s-a
     mutat în `texte.ro.schedule` / `texte.en.schedule`, mai sus. `site.js`
     îl citește de acolo prin `T("schedule", ...)`.                        */
  phone: "+40 735 433 720",
  email: "academiastudiumgeneraledenisa@gmail.com",
  /* Fără stradă, la cererea clientului (2026-09-14). */
  address: "București, Sectorul 1, lângă metrou",

  /* --- Rețele (lasă gol ca să dispară din subsol) ------------------------- */
  facebook: "https://www.facebook.com/p/Academia-Studium-Generale-by-Denisa-61581913795617/",
  instagram: "https://www.instagram.com/academia_studium_generale/",
  /* DE COMPLETAT: adresa contului. Cât e gol, linkul dispare din subsol. */
  tiktok: "",

  /* Pagina de membru la Camera de Comerț, dată de client. */
  cameraComert: "https://www.hrcc.ro/members/",

  /* --- Date de firmă ------------------------------------------------------
     Obligatorii în subsolul unui site comercial din România.                */
  company: "STUDIUM GENERALE BY DENISA SRL",
  cui: "52829916",
  regCom: "J2025084335006",
};
