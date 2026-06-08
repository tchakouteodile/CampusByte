# CampusByte
#  CampusByte: Il mercatino anti-spreco della Schiscetta & Co.
**Progetto di Tecnologie Web - Corso di Laurea in Ingegneria e Scienze Informatiche** **Alma Mater Studiorum - Università di Bologna (Campus di Cesena)**

---

## Gruppo a una sola persona
* **Nome Cognome : Tchakoute Odile Bertoise
* - Matricola: 0001131717
 * -  Email: tchakoute.odile@studio.unibo.it

---

## Introduzione e Idea del Progetto
**CampusByte** è una piattaforma web responsive e accessibile pensata per facilitare la vita quotidiana degli studenti all'interno del Campus di Cesena, affrontando due problematiche comuni: lo spreco alimentare e la gestione delle piccole emergenze quotidiane da studio.

Il servizio si divide in due macro-aree:
1. **Food Sharing (Anti-Spreco):** Consente agli studenti di condividere porzioni di cibo in eccedenza (es. pranzi al sacco sigillati, cibo preparato la sera prima e non consumato) con altri colleghi presenti nella facoltà.
2. **Tool Emergency (Il Kit del Fuorisede):** Una bacheca per il prestito rapido di oggetti di cancelleria o tecnologici dimenticati a casa (es. caricatori USB-C, ombrelli in caso di pioggia improvvisa, calcolatrici scientifiche).

---

## Il Problema della Fiducia e della Sicurezza (Risolto)
Per garantire l'assoluta affidabilità degli utenti e la restituzione degli oggetti prestati in perfetto stato, CampusByte non si affida a sistemi di pagamento commerciali, ma implementa un'architettura multilivello di **sicurezza comportamentale e finanziaria**:

* **Autenticazione Istituzionale Blindata:** La registrazione accetta **esclusivamente** email ufficiali dell'Ateneo (`@studio.unibo.it` per studenti, `@unibo.it` per admin/personale). La convalida formale avviene tramite espressioni regolari (Regex). La tracciabilità totale elimina l'anonimato e rende gli utenti responsabili delle proprie azioni di fronte all'istituto.
* **Sistema "Soft Lock" con Scadenza (Deterrente Anti-Falsificazione):** Per non rallentare il servizio, un utente appena registrato può accedere subito alla Dashboard in modalità di sola consultazione (UX fluida). Tuttavia, se l'utente ha inserito una mail falsa nel tentativo di aggirare il sistema, scatta un **timer di controllo di 15 minuti**. Se entro questo limite non viene inserito il codice di sblocco inviato alla mail istituzionale, il sistema distrugge la sessione e **cancella automaticamente l'account dal database**. Questo meccanismo scoraggia i tentativi di frode: l'obbligo di attendere ed eseguire una nuova registrazione spinge l'utente ad utilizzare immediatamente le credenziali reali.
* **Sistema di Gamification "Gettoni Karma":** Ogni utente parte con 5 Gettoni Karma. Quando si richiede un prestito o si riceve cibo, un gettone viene temporaneamente "bloccato". Viene restituito (e incrementato come feedback positivo) solo quando il proprietario conferma la chiusura dello scambio. Se i gettoni scendono a zero, il sistema applica un blocco totale sulle funzionalità di scambio.
* **Protocollo Tutela Integrità (Prova Video Obbligatoria):** Per azzerare le controversie sullo stato degli oggetti ("era già rotto" / "lo hai rotto tu"), il proponente è **obbligato a caricare un video/foto di controllo dello stato visivo** prima di pubblicare l'annuncio. Al momento della restituzione, viene registrato un secondo video comparativo ("Prima" e "Dopo").
* **Deposito Cauzionale e Piano Penali Simulato:** Chi propone il prestito stabilisce una tabella di rimborsi economici in caso di danno (suddivisa in tre livelli: *Lieve*, *Medio*, *Grave*). Il destinatario, per poter prenotare e ritirare l'oggetto, deve accettare formalmente il vincolo finanziario. In caso di danneggiamento o rifiuto, l'Amministratore interviene come moderatore analizzando le prove video archiviate nel server per decretare la penale da risarcire.
* **Punti di Incontro Certificati:** Gli annunci possono essere impostati solo in aree pubbliche, popolate e protette del Campus (es. "Zona Microonde", "Biblioteca", "Portineria").

---

## Architettura e Scelte Tecnologiche
Il progetto rispetta rigidamente i vincoli tecnologici imposti dalle specifiche d'esame:

* **Lato Server:** **PHP 8.x (Nativo)** per l'elaborazione della logica di business, il monitoraggio in tempo reale dei timestamp dei 15 minuti di scadenza dell'account, la gestione delle sessioni e l'upload sicuro dei file multimediali.
* **Database:** **MySQL** gestito tramite l'interfaccia sicura **PDO (PHP Data Objects)** con Prepared Statements nativi per neutralizzare qualsiasi minaccia di SQL Injection. Le password degli utenti sono cifrate nel database tramite l'algoritmo di hashing `password_hash()` (BCRYPT).
* **Lato Client:** **HTML5, CSS3, JavaScript Vanilla (Nativo)**. È stato evitato l'uso di qualsiasi framework JS (no React, Vue o Angular).
* **Interfaccia e Layout:** **Bootstrap 5** per garantire una progettazione *Mobile-First*, fluida, responsive e conforme alle linee guida sull'accessibilità dei contrasti cromatici.

---

## Struttura delle Cartelle del Progetto
```text
CampusByte/
│
├── config/
│   └── db.php              # Connessione protetta al database tramite PDO
│
├── css/
│   └── style.css           # Override CSS personalizzati per l'accessibilità
│
├── js/
│   └── main.js             # Logica asincrona lato client (Validazioni e AJAX)
│
├── database/
│   └── schema.sql          # Dump del database (Tabelle utenti, categorie, annunci)
│
├── uploads/                # Cartella del server che archivia i video delle prove di integrità
│
├── index.php               # Landing Page e Form di Login sicuro
├── registrazione.php       # Form di registrazione con controllo Regex e avvio timer 15m
├── dashboard.php           # Pannello Utente (Soft Lock con countdown, bacheca annunci, Gettoni Karma)
├── crea_annuncio.php       # Modulo di upload video e configurazione penali danni (Lieve/Medio/Grave)
├── accetta_scambio.php     # Schermata di accettazione vincoli monetari e visualizzazione video "Prima"
├── admin.php               # Pannello Amministratore (Operazioni CRUD, risoluzione controversie danni)
└── logout.php              # Script per la distruzione sicura della sessione
├── logout.php              # Script per la distruzione sicura della sessione
└── README.md               # Questa relazione di progetto
