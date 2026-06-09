<?php
// admin.php
require_once 'config/db.php';
session_start();

// PROTEZIONE BASE: Accesso consentito SOLO agli utenti loggati con ruolo 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$nome_admin = $_SESSION['nome'];
$errore = "";
$successo = "";

// --------------------------------------------------------
// GESTIONE OPERAZIONI CRUD (POST)
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. CREATE: Aggiungi Nuova Categoria
    if (isset($_POST['add_categoria'])) {
        $nome_cat = trim($_POST['nome_categoria']);
        if (!empty($nome_cat)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categorie (nome) VALUES (?)");
                $stmt->execute([$nome_cat]);
                $successo = "Categoria '$nome_cat' aggiunta con successo!";
            } catch (PDOException $e) {
                $errore = "Errore: Categoria già esistente o non valida.";
            }
        }
    }

    // 2. CREATE: Aggiungi Nuovo Luogo del Campus
    if (isset($_POST['add_luogo'])) {
        $nome_luogo = trim($_POST['nome_luogo']);
        if (!empty($nome_luogo)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO punti_incontro (nome_luogo) VALUES (?)");
                $stmt->execute([$nome_luogo]);
                $successo = "Luogo '$nome_luogo' aggiunto con successo!";
            } catch (PDOException $e) {
                $errore = "Errore: Luogo già esistente nel sistema.";
            }
        }
    }
}

// 3. DELETE: Eliminazione Categoria (Tramite parametro GET)
if (isset($_GET['del_cat'])) {
    $id_cat = intval($_GET['del_cat']);
    $stmt = $pdo->prepare("DELETE FROM categorie WHERE id = ?");
    $stmt->execute([$id_cat]);
    $successo = "Categoria eliminata con successo (e tutti i post associati in cascata).";
}

// 4. DELETE: Eliminazione Luogo (Tramite parametro GET)
if (isset($_GET['del_luogo'])) {
    $id_luogo = intval($_GET['del_luogo']);
    $stmt = $pdo->prepare("DELETE FROM punti_incontro WHERE id = ?");
    $stmt->execute([$id_luogo]);
    $successo = "Luogo rimosso dal Campus con successo.";
}

// --------------------------------------------------------
// READ: Recupero dati aggiornati per riempire le tabelle
// --------------------------------------------------------
$categorie = $pdo->query("SELECT * FROM categorie ORDER BY nome ASC")->fetchAll();
$luoghi = $pdo->query("SELECT * FROM punti_incontro ORDER BY nome_luogo ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHelp - Pannello Amministratore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark p-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">⚙️ CampusHelp - Pannello di Controllo Generale</a>
        <span class="navbar-text text-white-50">Admin: <strong><?php echo htmlspecialchars($nome_admin); ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Disconnetti</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="p-4 bg-white rounded shadow-sm mb-4">
        <h2>Benvenuto nel centro decisionale del Campus</h2>
        <p class="text-muted mb-0">Qui hai il pieno controllo delle impostazioni strutturali dell'applicazione. Le modifiche effettuate modificheranno istantaneamente i menu a tendina degli studenti durante la creazione dei post.</p>
    </div>

    <?php if(!empty($errore)): ?> <div class="alert alert-danger shadow-sm"><?php echo $errore; ?></div> <?php endif; ?>
    <?php if(!empty($successo)): ?> <div class="alert alert-success shadow-sm"><?php echo $successo; ?></div> <?php endif; ?>

    <div class="row">
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white font-weight-bold">📁 Gestione Categorie (Food/Tool)</div>
                <div class="card-body">
                    
                    <form action="admin.php" method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="nome_categoria" class="form-control" placeholder="Nuova categoria (es. Libri, Snack...)" required>
                        <button type="submit" name="add_categoria" class="btn btn-primary">Aggiungi</button>
                    </form>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped table-hover align-middle small mb-0">
                            <thead>
                                <tr><th>ID</th><th>Nome Categoria</th><th class="text-end">Azione</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($categorie as $cat): ?>
                                    <tr>
                                        <td>#<?php echo $cat['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($cat['nome']); ?></strong></td>
                                        <td class="text-end">
                                            <a href="admin.php?del_cat=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm py-0" onclick="return confirm('Sicuro di voler eliminare questa categoria e tutti i post collegati?');">Elimina</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white font-weight-bold">📍 Gestione Luoghi di Incontro Ufficiali</div>
                <div class="card-body">
                    
                    <form action="admin.php" method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="nome_luogo" class="form-control" placeholder="Nuovo luogo (es. Aula 3.1, Lab...)" required>
                        <button type="submit" name="add_luogo" class="btn btn-success">Aggiungi</button>
                    </form>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped table-hover align-middle small mb-0">
                            <thead>
                                <tr><th>ID</th><th>Luogo del Campus</th><th class="text-end">Azione</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($luoghi as $l): ?>
                                    <tr>
                                        <td>#<?php echo $l['id']; ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($l['nome_luogo']); ?></span></td>
                                        <td class="text-end">
                                            <a href="admin.php?del_luogo=<?php echo $l['id']; ?>" class="btn btn-danger btn-sm py-0" onclick="return confirm('Rimuovere questo punto di incontro?');">Rimuovi</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>