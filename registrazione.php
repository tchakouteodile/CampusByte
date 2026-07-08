<?php
require_once 'config/db.php';
session_start();

$errore ="";

    if ($_SERVER["REQUEST_METHOD"]=="POST"){
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        if(!empty($nome) && !empty($cognome) && !empty($email) && !empty($password)){

            //Regex Unibo :accetta studenti (@studio.unibo.it) o staff(@unibo.it)
            if(!preg_match("/^[a-zA-Z0-9.-%+-]+@(?:studio\.)?unibo\.it$", $email)){
                $errore = "Accesso negato. E richiesto un'email istituizionale Unibo.";
            }
            else{
                $stmt = $pdo->prepare("SELECT id FROM utenti WHERE EMAIL = ?");
                $stmt -> execute([$email]);
                
                if($stmt->fetch()){
                    $errore = "Questa email è gia registrata.";
                }
                else{
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $codice_verifica = strval(rand(100000,999999));

                    $ruolo = 'user';
                    if(str_ends_with($email, '@unibo.it')){
                        $ruolo = 'admin';
                    }

                $stmt = $pdo->prepare("INSERT INTO utenti (nome, cognome, email, password, ruolo, gettoni_karma, codice_conferma, stato_account) VALUES (?, ?, ?, ?, ?, 5, ?, 'Non_Confermato')");
            
                if($stmt->execute([$nome,$cognome,$email,$password_hash,$ruolo,$codice_verifica,])){
                $stmt_login = $pdo->prepare("SELECT id, nome ,ruolo, stato_account FROM utenti WHERE email = ?") ;
                $stmt_login ->execute([email]);
                $nuovo_utente = $stmt_login->fetch();

                $_SESSION['user_id'] = $nuovo_utente['id'];
                $_SESSION['nome'] = $nuovo_utente['nome'];
                $_SESSION['ruolo'] = $nuovo_utente['ruolo'];
                $_SESSION['stato_account'] = $nuovo_utente['stato_account'];

                header("Location: dashboard.php");
                exit();

                }else{
                    $errore = "Errore durante la regitrazione.";
                }
            }
        }
    }else{
        $errore = "Tutti i campi sono obligatori.";
    }
}
?>

<!DOCTYPE html>
<htm>
    <!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>CampusByte - Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="container" style="max-width: 500px;">
    <?php if (isset($_GET['errore']) && $_GET['errore'] === 'tempo_scaduto'): ?>
        <div class="alert alert-danger text-center shadow-sm"><strong>Tempo Scaduto!</strong> Non hai inserito il codice entro 15 minuti. L'account temporaneo è stato eliminato. Riprova con dati validi.</div>
    <?php endif; ?>
    <div class="card shadow p-4">
        <h2 class="text-center text-success mb-4">Crea Account CampusByte</h2>
        <?php if(!empty($errore)): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errore); ?></div> <?php endif; ?>
        <form action="registrazione.php" method="POST">
            <div class="row mb-3"><div class="col"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div><div class="col"><label class="form-label">Cognome</label><input type="text" name="cognome" class="form-control" required></div></div>
            <div class="mb-3"><label class="form-label">Email Istituzionale</label><input type="email" name="email" class="form-control" placeholder="nome.cognome@studio.unibo.it" required></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
            <button type="submit" class="btn btn-success w-100">Registrati ed Entra</button>
        </form>
    </div>
</div>
</body>
</html>
