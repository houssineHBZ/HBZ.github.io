<?php
require_once 'config.php';
session_start();

// Admin Authentication Check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: page21.php");
    exit();
}

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: page22.php");
    exit();
}

// Fetch Active Users
$stmt = $pdo->query("SELECT * FROM users WHERE status = 'active'");
$activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBZ Admin - Registre</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container wide">
        <h1>Registre Utilisateurs</h1>
        <p class="subtitle">Liste des comptes actifs</p>

        <div style="text-align: right; margin-bottom: 10px;">
            <a href="page21.php" class="link-text" style="display:inline; margin-right:15px;">Retour validations</a>
            <a href="page11.php" class="link-text" style="display:inline;">Déconnexion</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Adresse</th>
                    <th>Mot de passe</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="fullList">
                <?php if (count($activeUsers) == 0): ?>
                    <tr><td colspan='6' style='text-align:center'>Aucun utilisateur actif.</td></tr>
                <?php else: ?>
                    <?php foreach($activeUsers as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['titre']); ?></td>
                        <td><?php echo htmlspecialchars($u['nom']); ?></td>
                        <td><?php echo htmlspecialchars($u['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($u['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($u['password']); ?></td>
                        <td>
                            <a href="page22.php?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Supprimer cet utilisateur ?')"><button class="action-btn btn-reject" style="background:var(--danger); width:auto; padding:5px 10px;">Supprimer</button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
