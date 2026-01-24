<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: page11.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresse = trim($_POST['adresse']);
    $password = $_POST['password'];

    if (!empty($nom) && !empty($prenom) && !empty($adresse) && !empty($password)) {
        // Check if address is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE adresse = ? AND id != ?");
        $stmt->execute([$adresse, $user_id]);
        if ($stmt->fetch()) {
            $error = "Cette adresse est déjà utilisée.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, adresse = ?, password = ? WHERE id = ?");
            if ($stmt->execute([$nom, $prenom, $adresse, $password, $user_id])) {
                // Update session
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_adresse'] = $adresse;
                $success = "Profil mis à jour avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
    }
}

// Handle New Message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, user_name, message, type) VALUES (?, ?, ?, 'user_msg')");
        $user_name = $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom'];
        $stmt->execute([$user_id, $user_name, $message]);
        // Refresh to avoid resubmission
        header("Location: page13.php#tabChat"); 
        exit();
    }
}

// Fetch Messages
$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? OR to_user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBZ Plateforme - Accueil</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeSlideUp 0.4s ease; }
    </style>
</head>

<body>
    <div class="container wide">
        <div class="logo-container">
            <img src="logohbz.jpeg" alt="Logo HBZ">
        </div>
        <h1>Bienvenue chez HBZ</h1>
        <p class="subtitle">Bonjour, <?php echo htmlspecialchars($_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom']); ?></p>

        <?php if ($success): ?>
            <p style="color: var(--success); margin-bottom: 1rem;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: var(--danger); margin-bottom: 1rem;"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Navigation Menu -->
        <div class="tab-menu">
            <button class="tab-btn active" onclick="openTab('tabCompte', event)">Mon Compte</button>
            <button class="tab-btn" onclick="openTab('tabCours', event)">Les Cours</button>
            <button class="tab-btn" onclick="openTab('tabExercices', event)">Exercices</button>
            <button class="tab-btn" onclick="openTab('tabChat', event)">Communication</button>
        </div>

        <!-- Tab 1: Mon Compte -->
        <div id="tabCompte" class="tab-content active">
            <div id="userInfo" class="user-card">
                <p><strong>Titre:</strong> <?php echo htmlspecialchars($_SESSION['user_titre']); ?></p>
                <p><strong>Nom:</strong> <?php echo htmlspecialchars($_SESSION['user_nom']); ?></p>
                <p><strong>Prénom:</strong> <?php echo htmlspecialchars($_SESSION['user_prenom']); ?></p>
                <p><strong>Adresse:</strong> <?php echo htmlspecialchars($_SESSION['user_adresse']); ?></p>
                <p><strong>Status:</strong> <span style="color:var(--success)">Actif</span></p>
            </div>

            <div id="editSection" class="user-card" style="margin-top: 2rem; display: none;">
                <h3 style="color:var(--primary); margin-bottom:1rem;">Modifier mes informations</h3>
                <form method="POST" action="page13.php">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?php echo htmlspecialchars($_SESSION['user_prenom']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Adresse (Identifiant)</label>
                        <input type="text" name="adresse" value="<?php echo htmlspecialchars($_SESSION['user_adresse']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="text" name="password" required placeholder="Nouveau mot de passe">
                    </div>
                    <button type="submit" class="primary">Sauvegarder</button>
                    <button type="button" class="secondary" onclick="toggleEdit()" style="margin-top: 5px;">Annuler</button>
                </form>
            </div>

             <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 15px;">
                <button onclick="toggleEdit()" class="secondary" style="width: auto; padding: 10px 30px;">Modifier infos</button>
                <a href="page11.php" style="text-decoration:none;"><button class="primary" style="background:var(--danger); color:white; width: auto; padding: 10px 30px;">Déconnexion</button></a>
            </div>
        </div>

        <!-- Tab 2: Les Cours -->
        <div id="tabCours" class="tab-content">
            <div class="user-card">
                <h3>Mes Cours</h3>
                <p style="margin-bottom: 1.5rem;">Sélectionnez une matière pour voir le contenu.</p>
                <div class="sub-tab-menu">
                    <button class="sub-tab-btn" onclick="window.location.href='page14.html'">Python</button>
                    <button class="sub-tab-btn" onclick="window.location.href='page15.html'">G. Électrique</button>
                    <button class="sub-tab-btn" onclick="window.location.href='page16.html'">G. Mécanique</button>
                </div>
            </div>
        </div>

        <!-- Tab 3: Exercices -->
        <div id="tabExercices" class="tab-content">
            <div class="user-card">
                <h3>Exercices</h3>
                <p style="margin-bottom: 1.5rem;">Pratiquez vos connaissances.</p>
                <div class="sub-tab-menu">
                    <button class="sub-tab-btn" onclick="window.location.href='page14.html#exercices'">Python</button>
                    <button class="sub-tab-btn" onclick="window.location.href='page15.html#exercices'">G. Électrique</button>
                    <button class="sub-tab-btn" onclick="window.location.href='page16.html#exercices'">G. Mécanique</button>
                </div>
            </div>
        </div>

        <!-- Tab 4: Communication -->
        <div id="tabChat" class="tab-content">
            <div class="user-card" style="height: 500px; display: flex; flex-direction: column;">
                <h3>Espace Communication</h3>
                <div id="chatBox" style="flex: 1; overflow-y: auto; background: rgba(0,0,0,0.3); border-radius: 8px; padding: 15px; margin-bottom: 15px; border: 1px solid var(--glass-border);">
                    <?php if (count($messages) == 0): ?>
                        <div style="text-align: center; color: var(--text-muted); margin-top: 20%;">Démarrez une conversation avec l'admin.</div>
                    <?php else: ?>
                        <?php foreach($messages as $msg): 
                            $type = ($msg['user_id'] == $user_id && $msg['type'] == 'user_msg') ? 'sent' : 'received';
                            $sender = ($type == 'sent') ? 'Moi' : 'Admin';
                        ?>
                            <div class="message <?php echo $type; ?>">
                                <div class="message-bubble">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
                                <span class="message-meta"><?php echo $sender; ?> • <?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form method="POST" action="page13.php" style="display: flex; gap: 10px;">
                    <input type="hidden" name="send_message" value="1">
                    <input type="text" name="message" placeholder="Écrivez votre message..." required style="flex: 1;">
                    <button type="submit" style="width: auto; margin-top: 0;">Envoyer</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function openTab(tabName, event) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            if(event) event.currentTarget.classList.add('active');
            
            // Auto scroll chat
            if(tabName === 'tabChat') {
                const chatBox = document.getElementById('chatBox');
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function toggleEdit() {
            const form = document.getElementById('editSection');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }

        // Check hash to open correct tab on load
        if(window.location.hash === '#tabChat') {
             // Basic implementation to select tab if redirected
             document.addEventListener('DOMContentLoaded', () => {
                 const btn = document.querySelector("button[onclick*='tabChat']");
                 if(btn) btn.click();
             });
        }
    </script>
</body>
</html>
