# CampusHelp: Il mercatino anti-spreco della Schiscetta & Co.

**Progetto di Tecnologie Web - Corso di Laurea in Ingegneria e Scienze Informatiche** **Alma Mater Studiorum - Università di Bologna (Campus di Cesena)**

---

## Gruppo a una sola persona
* **Nome Cognome : Tchakoute Odile Bertoise
* - Matricola: 0001131717
 * -  Email: tchakoute.odile@studio.unibo.it

---

##Introduzione e Idea del Progetto

**CampusHelp** è una piattaforma web responsive e accessibile pensata per facilitare la vita quotidiana degli studenti all'interno del Campus di Cesena dell'Università di Bologna, affrontando due problematiche comuni e complementari: lo spreco alimentare e le emergenze improvvise legate allo studio di tutti i giorni.

Il servizio si divide in due flussi logici speculari, ottimizzati sulla reale esperienza di vita universitaria:
1. **Food Sharing (Gestione dell'Offerta):** Dedicato a chi ha cibo in eccedenza (es. pranzi al sacco sigillati, schiscette preparate la sera prima) e desidera "farlo sparire" rapidamente per evitare sprechi inutili, regalandolo ai colleghi presenti in facoltà.
2. **Tool Emergency (Gestione della Richiesta):** Dedicato a chi si trova in una situazione di bisogno o emergenza logistica (es. dimenticanza di caricatori per PC/smartphone, ombrelli in caso di pioggia improvvisa, calcolatrici scientifiche prima di un esame). In questa sezione lo studente inserisce una richiesta d'aiuto visibile a tutta la comunità, attendendo che qualcuno si proponga per un prestito rapido.

---

##Il Problema della Fiducia e della Sicurezza (Risolto con Smart Contract)

Per garantire l'assoluta affidabilità degli scambi senza ricorrere a sistemi di pagamento commerciali, CampusHelp implementa un'architettura multilivello basata su tracciabilità istituzionale e clausole finanziarie vincolanti accettate preventivamente tramite il sistema:

* **Autenticazione Istituzionale Blindata:** La registrazione accetta esclusivamente domini ufficiali dell'Ateneo (`@studio.unibo.it` per studenti, `@unibo.it` per admin/personale). La convalida formale è gestita tramite espressioni regolari (Regex) lato server. L'eliminazione totale dell'anonimato rende gli utenti rintracciabili e direttamente responsabili di fronte all'istituto.

* **Sistema "Soft Lock" Temporizzato (15 Minuti):** Per garantire una navigazione fluida (UX), i nuovi utenti accedono subito alla Dashboard in modalità di sola consultazione. Se l'utente ha inserito una mail falsa o inesistente, scatta un timer automatico di 15 minuti: in assenza del codice OTP di sblocco a 6 cifre, il server distrugge la sessione e cancella l'utente dal database per non accumulare dati spazzatura. L'obbligo psicologico di dover ripetere l'inserimento spinge i furbi a usare subito le credenziali reali.

* **Flusso Invertito dell'Offerta di Prestito:** Quando un utente risponde a un grido d'aiuto per un oggetto, è l'aiutante (proprietario dell'oggetto) a stabilire i vincoli di sicurezza contrattuale che l'altro dovrà accettare formalmente prima dell'incontro.

* **Sanzioni Progressive e Clausola di Riscatto Forzoso (5 Ore):** Se l'oggetto non viene restituito entro il limite di tempo stabilito dall'aiutante, il sistema applica una penalità automatica a tre livelli:
  * *Entro le prime 2 ore di ritardo:* Penalità "morbida" con decrescita e decurtazione oraria dei punti Karma dell'utente.
  * *Oltre le 2 ore di ritardo:* Blocco dell'account e sanzione finanziaria minima fissa in denaro impostata originariamente dal proprietario.
  * *Superate le 5 ore di ritardo:* Scatta il **Riscatto Forzoso Completo**. Il sistema calcola la differenza economica per raggiungere il valore di stima totale dell'oggetto (Stima Totale - Penale minima già applicata) e addebita l'intero debito finanziario residuo all'utente ritardatario. L'oggetto passa ufficialmente di proprietà, l'account viene congelato e vengono mostrati i dati di pagamento (IBAN) del proprietario per saldare la transazione.
* **Handshake OTP Digitale per la Restituzione Sicura:** Al posto di complessi e aggirabili sistemi di verifica fotografica, la restituzione effettiva dell'oggetto (che annulla all'istante la programmazione dei pagamenti delle indennità) avviene tramite verifica a due fattori in tempo reale. Quando i due studenti si incontrano, lo schermo del proprietario genera un codice segreto monouso a 4 cifre. Il destinatario lo digita sul proprio dispositivo per confermare la consegna fisica simultanea e chiudere il prestito nel database.
* **Punti di Incontro Certificati del Campus:** Per azzerare i rischi ambientali, la piattaforma impedisce l'inserimento di testo libero, obbligando a scegliere i luoghi di incontro fisici tra le opzioni reali della facoltà di Cesena: *Aule (2.1, 2.2, ecc.)*, *Laboratori (2.2, 3.3, 4.1, ecc.)*, *Caffè Club (dietro ricevimento)*, *Biblioteca*, o *Sala di studio di fronte alla scuola*.

---

## Architettura e Scelte Tecnologiche

Il progetto rispetta rigidamente i vincoli tecnologici e architetturali imposti dalle specifiche didattiche d'esame:

* **Lato Server:** **PHP 8.x (Nativo)** per l'elaborazione di tutta la logica di business, il monitoraggio dinamico delle scadenze temporali degli account (15 min) e dei prestiti (clausole delle 2 e 5 ore) e la gestione delle sessioni.
* **Database:** **MySQL (Engine InnoDB)** strutturato su **5 tabelle normalizzate** per garantire l'integrità referenziale referenziata (`FOREIGN KEY` con vincoli `ON DELETE CASCADE` e `SET NULL`). L'interfaccia di comunicazione usa esclusivamente **PDO (PHP Data Objects)** con prepared statements nativi per neutralizzare attacchi di tipo SQL Injection. Le password sono memorizzate tramite hashing forte `password_hash()` con algoritmo BCRYPT.
* **Lato Client:** **HTML5, CSS3, JavaScript Vanilla (Nativo)**. È stato evitato l'uso di qualsiasi framework o libreria esterna JS (no React, Angular o Vue).
* **Interfaccia e Layout:** **Bootstrap 5** per assicurare una progettazione *Mobile-First*, totalmente accessibile per i contrasti cromatici e fluida su schermi desktop e smartphone.

---

## Struttura delle Cartelle del Progetto

```text
CampusHelp/
│
├── config/
│   ├── db.php                  # Connessione protetta al database tramite PDO
│   └── controllo_scadenze.php  # Script di calcolo orario in tempo reale per penali e riscatto oggetti
│
├── css/
│   └── style.css               # Override CSS personalizzati per layout e accessibilità
│
├── js/
│   └── main.js                 # Logica asincrona e validazioni lato client
│
├── database/
│   └── schema.sql              # Dump strutturato e normalizzato delle 5 tabelle del database
│
├── uploads/                    # Cartella di archiviazione protetta delle foto degli oggetti in prestito
│
├── index.php                   # Landing Page principale e form di Login sicuro per gli utenti
├── registrazione.php           # Form di registrazione con Regex @unibo e avvio timer di Soft Lock 15m
├── dashboard.php               # Pannello principale (Countdown orario, bacheca cibo, grido d'aiuto oggetti)
├── crea_post.php               # Modulo di inserimento post con opzioni dinamiche per i luoghi del Campus
├── invia_offerta.php           # Modulo in cui l'aiutante fissa i suoi vincoli contrattuali (Karma, IBAN, Stima)
├── visualizza_offerte.php      # Schermata in cui il richiedente seleziona l'offerta e l'orario dell'incontro
├── gestisci_restituzione.php   # Chiusura prestito tramite generazione e inserimento del codice OTP a 4 cifre
├── admin.php                   # Pannello Amministratore (Operazioni CRUD su Categorie e Luoghi di incontro)
├── logout.php                  # Script per la distruzione sicura della sessione e dei dati temporanei
└── README.md                   # Relazione tecnica ufficiale del progetto d'esame
