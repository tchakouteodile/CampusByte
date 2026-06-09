<?php
// index.php
require_once 'config/db.php';
session_start();

// Se l'utente è già loggato, lo reindirizziamo direttamente alla sua pagina corretta
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['ruolo'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        try {
            // Cerchiamo l'utente nel database tramite Prepared Statement (Sicurezza anti SQL Injection)
            $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            $utente = $stmt->fetch();

            // Verifichiamo se l'utente esiste e se la password corrisponde a quella cifrata (BCRYPT)
            if ($utente && password_verify($password, $utente['password'])) {
                
                // Salviamo i dati principali dell'utente all'interno della sessione sicura
                $_SESSION['user_id'] = $utente['id'];
                $_SESSION['nome'] = $utente['nome'];
                $_SESSION['ruolo'] = $utente['ruolo'];
                $_SESSION['stato_account'] = $utente['stato_account'];

                // REINDIRIZZAMENTO IN BASE AL RUOLO (Security Control)
                if ($utente['ruolo'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $errore = "Credenziali errate. Riprova o controlla i dati inseriti.";
            }
        } catch (PDOException $e) {
            $errore = "Errore durante il login: " . $e->getMessage();
        }
    } else {
        $errore = "Tutti i campi sono obbligatori.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHelp - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-gradient-unibo {
            background: linear-gradient(135deg, #003366 0%, #006633 100%);
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="container" style="max-width: 900px;">
    <div class="card shadow-lg border-0 overflow-hidden">
        <div class="row g-0">
            
            <div class="col-md-6 bg-gradient-unibo d-none d-md-flex flex-column justify-content-center p-5 text-white">
                <h1 class="display-5 font-weight-bold mb-3">🤝 CampusHelp</h1>
                <p class="lead">La piattaforma di mutuo aiuto per gli studenti del Campus di Cesena.</p>
                <hr class="border-white opacity-25 my-4">
                <ul class="list-unstyled">
                    <li class="mb-2">🍏 <strong>Food Sharing:</strong> Regala il cibo in più ed evita gli sprechi.</li>
                    <li class="mb-2">🎒 <strong>Tool Emergency:</strong> Chiedi in prestito oggetti e attrezzi da studio in tempo reale.</li>
                    <li class="mb-2">🛡️ <strong>Smart Contract:</strong> Scambi protetti e penali regolate ad Handshake OTP.</li>
                </ul>
            </div>

            <div class="col-md-6 p-4 p-md-5 bg-white d-flex flex-column justify-content-center">
                <div class="text-center mb-4 d-md-none">
                    <h2 class="text-success font-weight-bold">CampusHelp</h2>
                    <p class="text-muted small">Condivisione e supporto tra universitari</p>
                </div>

                <h3 class="mb-3 text-dark font-weight-bold">Accedi alla Bacheca</h3>
                <p class="text-muted small mb-4">Inserisci le tue credenziali istituzionali registrate per entrare nel sistema.</p>

                <?php if (!empty($errore)): ?>
                    <div class="alert alert-danger shadow-sm py-2 small" role="alert">
                        ❌ <?php echo htmlspecialchars($errore); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label small font-weight-bold text-secondary">Email Istituzionale</label>
                        <input type="email" class="form-control form-control-lg text-lowercase" id="email" name="email" placeholder="nome.cognome@studio.unibo.it" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label small font-weight-bold text-secondary">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm font-weight-bold mb-3">Accedi ed Entra</button>
                </form>

                <div class="text-center mt-3 border-top pt-3">
                    <p class="text-muted small mb-0">Non hai ancora un account?</p>
                    <a href="registrazione.php" class="text-success font-weight-bold text-decoration-none small">Crea un Account Istituzionale qui &rarr;</a>
                </div>
            </div>

        </div>
    </div>
    
    <div class="text-center mt-3 text-muted" style="font-size: 0.8rem;">
        Alma Mater Studiorum – Università di Bologna • Progetto di Tecnologie Web • Cesena
    </div>
</div>

</body>
</html>