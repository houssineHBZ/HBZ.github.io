<?php
require_once 'config.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adresse = trim($_POST['adresse']);
    $password = $_POST['password'];

    if (!empty($adresse) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE adresse = ?");
        $stmt->execute([$adresse]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) { // Note: Simple comparison, consider password_verify for hashed
            if ($user['status'] === 'active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_titre'] = $user['titre'];
                $_SESSION['user_adresse'] = $user['adresse'];
                
                header("Location: page13.php");
                exit();
            } else {
                $error = "Votre compte est en attente de validation.";
            }
        } else {
            $error = "Adresse ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBZ Plateforme - Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="logo-container">
            <img src="logohbz.jpeg" alt="Logo HBZ">
        </div>
        <h1>Connexion</h1>
        <p class="subtitle">Accéder à la plateforme HBZ</p>

        <?php if ($error): ?>
            <p style="color: var(--danger); margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="page11.php">
            <div class="form-group">
                <label for="loginAdresse">Adresse</label>
                <input type="text" name="adresse" id="loginAdresse" required placeholder="Entrez votre adresse">
            </div>
            <div class="form-group">
                <label for="loginPass">Mot de passe</label>
                <input type="password" name="password" id="loginPass" required placeholder="Votre mot de passe">
            </div>
            <button type="submit">Entrer</button>
            <a href="page12.php"><button type="button" class="secondary">Créer un compte</button></a>
        </form>

        <!-- Link to Admin Pages -->
        <div style="margin-top: 20px; opacity: 0.3; font-size: 0.8rem;">
            Admin : <a href="page21.php" style="color: inherit;">Compte</a> | <a href="page22.php"
                style="color: inherit;">Liste</a>
        </div>
    </div>
</body>

</html>
