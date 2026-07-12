<?php
// dashboard.php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id_utente = $_SESSION['user_id'];
$nome_utente = $_SESSION['nome'];
$stato_account = $_SESSION['stato_account'];

// MONITORAGGIO DINAMICO DEL TIMER DI SICUREZZA (15 MINUTI)
$minuti_rimanenti = 15;
if ($stato_account === 'non_confermato') {
    $stmt_time = $pdo->prepare("SELECT creato_il, codice_conferma FROM utenti WHERE id = ?");
    $stmt_time->execute([$id_utente]);
    $utente_dati = $stmt_time->fetch();

    if (!$utente_dati) {
        session_destroy();
        header("Location: registrazione.php");
        exit();
    }

    $tempo_registrazione = strtotime($utente_dati['creato_il']);
    $tempo_attuale = time();
    $differenza_minuti = ($tempo_attuale - $tempo_registrazione) / 60;

    if ($differenza_minuti > 15) {
        // SCADUTO: Cancellazione formale dell'account temporaneo incompleto
        $delete_stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
        $delete_stmt->execute([$id_utente]);

        session_unset();
        session_destroy();
        header("Location: registrazione.php?errore=tempo_scaduto");
        exit();
    }
    $minuti_rimanenti = ceil(15 - $differenza_minuti);
    $codice_simulato_debug = $utente_dati['codice_conferma']; // Mantenuto per la fase di debug in sede d'esame
}

// PROCESSO DI VERIFICA ED ATTIVAZIONE ACCOUNT
$messaggio_verifica = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione_verifica'])) {
    $codice_inserito = trim($_POST['codice']);
    
    $stmt_cod = $pdo->prepare("SELECT codice_conferma FROM utenti WHERE id = ?");
    $stmt_cod->execute([$id_utente]);
    $codice_vero = $stmt_cod->fetchColumn();

    if ($codice_vero && $codice_vero === $codice_inserito) {
        $update = $pdo->prepare("UPDATE utenti SET stato_account = 'attivo', codice_conferma = NULL WHERE id = ?");
        $update->execute([$id_utente]);
        $_SESSION['stato_account'] = 'attivo';
        $stato_account = 'attivo';
        $messaggio_verifica = "<div class='alert alert-success border-0 shadow-sm small'>Account attivato. Le funzionalità di pubblicazione e scambio sono ora disponibili.</div>";
    } else {
        $messaggio_verifica = "<div class='alert alert-danger border-0 shadow-sm small'>Il codice inserito non è corretto. Riprova.</div>";
    }
}

// RECUPERO DEI PUNTI KARMA AGGIORNATI (Allineato alla colonna esatta di phpMyAdmin)
$stmt_karma = $pdo->prepare("SELECT punti_karma FROM utenti WHERE id = ?");
$stmt_karma->execute([$id_utente]);
$karma = $stmt_karma->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHelp - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="dashboard.php">CampusHelp</a>
        <div class="ms-auto navbar-text text-white small">
            Studente: <strong class="text-white"><?php echo htmlspecialchars($nome_utente); ?></strong> | 
            Saldo Karma: <span class="badge bg-light text-success"><?php echo $karma; ?> Punti</span>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-3 py-0">Esci</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php echo $messaggio_verifica; ?>

    <?php if ($stato_account === 'non_confermato'): ?>
        <div class="card border-0 border-start border-4 border-danger bg-danger bg-opacity-10 mb-4 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="text-danger font-weight-bold mb-1">Attivazione profilo richiesta (Scadenza: <?php echo $minuti_rimanenti; ?> minuti)</h5>
                    <p class="mb-1 text-muted small">Inserisci il codice ricevuto per confermare l'indirizzo istituzionale. Al termine del tempo l'account provvisorio verrà rimosso.</p>
                    <small class="text-dark bg-white border px-2 py-0.5 rounded shadow-xs" style="font-size: 0.75rem;">Simulazione email universitaria, codice: <strong><?php echo $codice_simulato_debug; ?></strong></small>
                </div>
                <form action="dashboard.php" method="POST" class="d-flex gap-2 align-self-start align-self-md-center">
                    <input type="hidden" name="azione_verifica" value="1">
                    <input type="text" name="codice" class="form-control form-control-sm text-center font-weight-bold" placeholder="Codice" required maxlength="6" style="width: 100px;">
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3">Sblocca</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="font-weight-bold text-secondary border-bottom pb-2 mb-3">Opzioni Utente</h5>
                <?php if ($stato_account === 'non_confermato'): ?>
                    <button class="btn btn-secondary w-100 btn-sm font-weight-bold text-white-50" disabled>Pubblica Annuncio</button>
                    <div class="form-text text-center small text-danger mt-2">Conferma il profilo per pubblicare.</div>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <a href="crea_post.php?tipo=cibo" class="btn btn-success btn-sm font-weight-bold">Regala Cibo in Eccedenza</a>
                        <a href="crea_post.php?tipo=oggetto" class="btn btn-warning btn-sm font-weight-bold text-dark">Chiedi Oggetto in Prestito</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-8 mb-3">
            <div class="card border-0 shadow-sm p-4 text-center py-5">
                <h4 class="font-weight-bold text-dark mb-2">Bacheca del Campus</h4>
                <p class="text-muted small mb-0">La visualizzazione asincrona e il caricamento dinamico delle offerte attive e dei prestiti logistici approvati verranno mappati in questa sezione.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
