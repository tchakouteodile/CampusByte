<?php
// visualizza_offerte.php
require_once 'config/db.php';
session_start();

$id_post = intval($_GET['id_post']);
$id_utente = $_SESSION['user_id'];
$successo = "";

// Recuperiamo tutte le proposte ricevute per questo post
$stmt = $pdo->prepare("
    SELECT t.*, u.nome AS nome_aiutante 
    FROM transazioni t 
    JOIN utenti u ON t.id_utente_aiutante = u.id 
    WHERE t.id_post = ? AND t.stato_transazione = 'proposta'
");
$stmt->execute([$id_post]);
$proposte = $stmt->fetchAll();

// Se il richiedente accetta una proposta specifica
if (isset($_POST['accetta_proposta'])) {
    $id_transazione = intval($_POST['id_transazione']);
    $minuti_incontro = intval($_POST['minuti_incontro']);
    
    // Aggiorna lo stato della transazione e imposta il tempo di arrivo programmato
    $stmt_accetta = $pdo->prepare("
        UPDATE transazioni 
        SET stato_transazione = 'accettata', minuti_arrivo_proprietario = ? 
        WHERE id = ?
    ");
    $stmt_accetta->execute([$minuti_incontro, $id_transazione]);
    
    // Disattiva il post in bacheca così altri non possono fare offerte
    $pdo->prepare("UPDATE bacheca SET stato = 'assegnato' WHERE id = ?")->execute([$id_post]);
    
    $successo = "Offerta accettata! Dirigiti al punto di incontro entro il tempo selezionato.";
    header("Refresh: 3; url=dashboard.php");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Proposte di Aiuto Ricevute</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 800px;">
    <h3 class="mb-4">📋 Offerte ricevute per la tua richiesta</h3>
    <?php if(!empty($successo)): ?> <div class="alert alert-success"><?php echo $successo; ?></div> <?php endif; ?>

    <?php if(empty($proposte)): ?>
        <div class="alert alert-info">Nessuna proposta ricevuta per adesso. Attendi che un collega legga la bacheca.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach($proposte as $p): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm">
                        <?php if($p['foto_oggetto']): ?>
                            <img src="<?php echo $p['foto_oggetto']; ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">Offerta da: <?php echo htmlspecialchars($p['nome_aiutante']); ?></h5>
                            <p class="mb-1 small">⏱️ <strong>Disponibilità:</strong> <?php echo $p['durata_ore_max']; ?> ore max</p>
                            <p class="mb-1 small">⭐ <strong>Penalità iniziale:</strong> -<?php echo $p['penalita_ora_karma']; ?> Karma/ora</p>
                            <p class="mb-1 small">💰 <strong>Penalità dopo 2 ore:</strong> <?php echo $p['penale_denaro_min']; ?> €</p>
                            <p class="mb-3 small">⚠️ <strong>Stima valore totale oggetto:</strong> <?php echo $p['valore_stima_oggetto']; ?> €</p>
                            
                            <form action="" method="POST" class="border-top pt-2">
                                <input type="hidden" name="id_transazione" value="<?php echo $p['id']; ?>">
                                <label class="form-label small font-weight-bold">⏰ Tra quanto ci incontriamo sul posto?</label>
                                <select name="minuti_incontro" class="form-select form-select-sm mb-3" required>
                                    <option value="5">Tra 5 Minuti</option>
                                    <option value="10" selected>Tra 10 Minuti</option>
                                    <option value="15">Tra 15 Minuti</option>
                                    <option value="20">Tra 20 Minuti</option>
                                </select>
                                <button type="submit" name="accetta_proposta" class="btn btn-success btn-sm w-100">Accetta Condizioni e Fissa Incontro</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <br><a href="dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
</div>
</body>
</html>