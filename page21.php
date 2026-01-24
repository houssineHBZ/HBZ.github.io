<?php
require_once 'config.php';
session_start();

// Admin Authentication (Simple Code Check)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_code'])) {
        if ($_POST['admin_code'] === '2004') {
            $_SESSION['is_admin'] = true;
        } else {
            $error = "Code administrateur incorrect.";
        }
    }
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HBZ Admin - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Administration</h1>
        <p>Veuillez entrer le code administrateur.</p>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="admin_code" required placeholder="Code Admin">
            <button type="submit">Entrer</button>
            <a href="page11.php" class="link-text">Retour</a>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}

// Handle User Actions (Approve/Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] == 'reject') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: page21.php");
    exit();
}

// Handle Reply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
    $to_user_id = $_POST['to_user_id'];
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, to_user_id, user_name, message, type) VALUES (0, ?, 'Admin', ?, 'admin_reply')");
        $stmt->execute([$to_user_id, $message]);
        header("Location: page21.php");
        exit();
    }
}

// Fetch Pending Users
$stmt = $pdo->query("SELECT * FROM users WHERE status = 'pending'");
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch User Messages (Latest first) from Users only (to show inbox style)
// We group by user to show the latest conversations or just list requests.
// For simplicity matching the existing design, we list individual user messages.
$stmt = $pdo->query("SELECT * FROM messages WHERE type = 'user_msg' ORDER BY created_at DESC");
$userMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBZ Admin - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container wide">
        <h1>Administration</h1>

        <div style="text-align: right; margin-bottom: 2rem;">
            <a href="page22.php" style="text-decoration:none;"><button class="secondary"
                    style="width: auto; margin-right: 15px;">Voir Registre (Tout)</button></a>
            <a href="page11.php" class="link-text" style="display:inline;">Déconnexion</a>
        </div>

        <!-- Section 1: Validation -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color:var(--primary); font-size:1.5rem; margin-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
                Validations en attente</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Adresse</th>
                        <th>Titre</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pendingList">
                    <?php if (count($pendingUsers) == 0): ?>
                        <tr><td colspan='4' style='text-align:center'>Aucune demande en attente.</td></tr>
                    <?php else: ?>
                        <?php foreach($pendingUsers as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($u['adresse']); ?></td>
                            <td><?php echo htmlspecialchars($u['titre']); ?></td>
                            <td>
                                <a href="page21.php?action=approve&id=<?php echo $u['id']; ?>"><button class="action-btn btn-approve" style="width:auto; padding:5px 10px; display:inline-block; margin:0 5px; background:var(--success);">Accepter</button></a>
                                <a href="page21.php?action=reject&id=<?php echo $u['id']; ?>" onclick="return confirm('Refuser cette demande ?')"><button class="action-btn btn-reject" style="width:auto; padding:5px 10px; display:inline-block; margin:0 5px; background:var(--danger);">Refuser</button></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Section 2: Communication -->
        <div>
            <h2 style="color:var(--primary); font-size:1.5rem; margin-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
                Espace Communication</h2>
            <div id="adminChatList">
                <?php if (count($userMessages) == 0): ?>
                    <p style='color:var(--text-muted);'>Aucun nouveau message.</p>
                <?php else: ?>
                    <?php foreach($userMessages as $m): ?>
                    <div class="user-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong><?php echo htmlspecialchars($m['user_name']); ?></strong>
                            <span style="color:var(--text-muted); font-size:0.8rem;"><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></span>
                        </div>
                        <p style="margin: 10px 0; color:white;"><?php echo htmlspecialchars($m['message']); ?></p>
                        
                        <!-- Reply Form Inline -->
                        <form method="POST" action="page21.php" style="margin-top:10px;">
                            <input type="hidden" name="to_user_id" value="<?php echo $m['user_id']; ?>">
                            <input type="text" name="message" placeholder="Votre réponse..." required style="width: 70%; display:inline-block; padding:8px;">
                            <button type="submit" name="reply_message" class="secondary" style="width:auto; padding:8px 15px; font-size:0.8rem; display:inline-block;">Répondre</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>
