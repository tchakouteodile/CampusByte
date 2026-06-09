<?php
// dashboard.php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: registrazione.php");
    exit();
}

$id_utente = $_SESSION['user_id'];
$nome_utente = $_SESSION['nome'];
$stato_account = $_SESSION['stato_account'];

// TIMER DI PUNIZIONE E SICUREZZA (15 MINUTI)
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
        // SCADUTO: Eliminiamo l'utente fake dal database per non accumulare spazzatura
        $delete_stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
        $delete_stmt->execute([$id_utente]);

        session_unset();
        session_destroy();
        header("Location: registrazione.php?errore=tempo_scaduto");
        exit();
    }
    $minuti_rimanenti = ceil(15 - $differenza_minuti);
    $codice_simulato_debug = $utente_dati['codice_conferma']; // Per debug d'esame
}

// SBLOCCO ACCOUNT TRAMITE CODICE
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
        $messaggio_verifica = "<div class='alert alert-success shadow-sm'>✔️ Account verificato! Tutte le funzioni di scambio sono sbloccate.</div>";
    } else {
        $messaggio_verifica = "<div class='alert alert-danger shadow-sm'>❌ Codice errato. Riprova.</div>";
    }
}

// Recupero dati Karma
$stmt_karma = $pdo->prepare("SELECT gettoni_karma FROM utenti WHERE id = ?");
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
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">CampusHelp</a>
        <span class="navbar-text text-white">Studente: <strong><?php echo htmlspecialchars($nome_utente); ?></strong> | Karma: ⭐<?php echo $karma; ?></span>
    </div>
</nav>

<div class="container mt-4">
    <?php echo $messaggio_verifica; ?>

    <?php if ($stato_account === 'non_confermato'): ?>
        <div class="card border-danger bg-danger bg-opacity-10 mb-4 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div>
                    <h5 class="text-danger mb-1">Sessione Temporanea (Scadenza: <?php echo $minuti_rimanenti; ?> minuti)</h5>
                    <p class="mb-md-0 text-muted">Se hai inserito una mail falsa non riceverai il codice. Tra <?php echo $minuti_rimanenti; ?> minuti l'account verrà <strong>cancellato definitivamente</strong>.</p>
                    <small class="text-info"> Codice simulato arrivato sulla mail: <strong><?php echo $codice_simulato_debug; ?></strong></small>
                </div>
                <form action="dashboard.php" method="POST" class="d-flex gap-2 mt-2 mt-md-0">
                    <input type="hidden" name="azione_verifica" value="1">
                    <input type="text" name="codice" class="form-control" placeholder="6 cifre" required maxlength="6" style="width: 110px;">
                    <button type="submit" class="btn btn-danger">Sblocca</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm text-center">
                <h5>Azioni Scambio</h5>
                <?php if ($stato_account === 'non_confermato'): ?>
                    <button class="btn btn-secondary w-100 my-2" disabled>Pubblica Annuncio (Richiede Verifica)</button>
                <?php else: ?>
                    <a href="crea_annuncio.php" class="btn btn-success w-100 my-2">Crea Nuovo Annuncio Protetto</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4 shadow-sm">
                <h3> Bacheca Annunci Campus</h3>
                <p class="text-muted">Qui verranno mostrati gli oggetti per il prestito e le schiscette disponibili.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>