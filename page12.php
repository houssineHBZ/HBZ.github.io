<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = trim($_POST['titre']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresse = trim($_POST['adresse']);
    $password = $_POST['password'];

    if (!empty($titre) && !empty($nom) && !empty($prenom) && !empty($adresse) && !empty($password)) {
        // Check if address exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE adresse = ?");
        $stmt->execute([$adresse]);
        if ($stmt->fetch()) {
            $error = "Cette adresse est déjà utilisée.";
        } else {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (titre, nom, prenom, adresse, password, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            if ($stmt->execute([$titre, $nom, $prenom, $adresse, $password])) {
                header("Location: page11.php?registered=1");
                exit();
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBZ Plateforme - Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="logo-container">
            <img src="logohbz.jpeg" alt="Logo HBZ">
        </div>
        <h1>Inscription</h1>
        <p class="subtitle">Rejoindre la communauté HBZ</p>

        <?php if ($error): ?>
            <p style="color: var(--danger); margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="page12.php">
            <div class="form-group">
                <label for="regTitre">Titre de compte</label>
                <input type="text" name="titre" id="regTitre" required placeholder="Ex: Personnel, Pro...">
            </div>
            <div class="form-group">
                <label for="regNom">Nom</label>
                <input type="text" name="nom" id="regNom" required placeholder="Votre nom">
            </div>
            <div class="form-group">
                <label for="regPrenom">Prénom</label>
                <input type="text" name="prenom" id="regPrenom" required placeholder="Votre prénom">
            </div>
            <div class="form-group">
                <label for="regAdresse">Adresse (Identifiant)</label>
                <input type="text" name="adresse" id="regAdresse" required placeholder="Votre adresse unique">
            </div>
            <div class="form-group">
                <label for="regPass">Mot de passe</label>
                <input type="password" name="password" id="regPass" required placeholder="Choisissez un mot de passe">
            </div>
            <button type="submit">S'inscrire</button>
            <a href="page11.php" class="link-text">Retour connexion</a>
        </form>
    </div>
</body>

</html>
