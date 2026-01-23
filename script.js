const ADMIN_CODE = "2004";

// Utilities
function getUsers() {
    return JSON.parse(localStorage.getItem('hbz_users')) || [];
}

function saveUsers(users) {
    localStorage.setItem('hbz_users', JSON.stringify(users));
}

function getCurrentUser() {
    return JSON.parse(sessionStorage.getItem('hbz_current_user'));
}

function setCurrentUser(user) {
    sessionStorage.setItem('hbz_current_user', JSON.stringify(user));
}

function logout() {
    sessionStorage.removeItem('hbz_current_user');
    window.location.href = 'page11.html';
}

function checkAdmin() {
    const code = prompt("Veuillez entrer le code administrateur :");
    if (code !== ADMIN_CODE) {
        alert("Code incorrect.");
        window.location.href = 'page11.html';
        return false;
    }
    return true;
}

// Page Specific Logic
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;
    const page = path.split("/").pop();

    // PAGE 11: Login
    if (page === 'page11.html') {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const adresse = document.getElementById('loginAdresse').value.trim();
                const mdp = document.getElementById('loginPass').value;

                const users = getUsers();
                const user = users.find(u => u.adresse === adresse && u.password === mdp);

                if (user) {
                    if (user.status === 'active') {
                        setCurrentUser(user);
                        window.location.href = 'page13.html';
                    } else if (user.status === 'pending') {
                        alert("Votre compte est en attente de validation par l'administrateur.");
                    }
                } else {
                    alert("Adresse ou mot de passe incorrect.");
                }
            });
        }
    }

    // PAGE 12: Register
    if (page === 'page12.html') {
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const titre = document.getElementById('regTitre').value;
                const nom = document.getElementById('regNom').value;
                const prenom = document.getElementById('regPrenom').value;
                const adresse = document.getElementById('regAdresse').value.trim();
                const password = document.getElementById('regPass').value;

                const users = getUsers();
                if (users.find(u => u.adresse === adresse)) {
                    alert("Cette adresse est déjà utilisée.");
                    return;
                }

                const newUser = {
                    id: Date.now(),
                    titre,
                    nom,
                    prenom,
                    adresse,
                    password,
                    status: 'pending' // Default status
                };

                users.push(newUser);
                saveUsers(users);

                alert("Compte créé avec succès ! En attente de validation.");
                window.location.href = 'page11.html';
            });
        }
    }

    // PAGE 13: User Dashboard
    if (page === 'page13.html') {
        const user = getCurrentUser();
        if (!user) {
            window.location.href = 'page11.html';
            return;
        }

        document.getElementById('displayNom').textContent = user.nom;
        document.getElementById('displayPrenom').textContent = user.prenom;

        // Detailed info
        const infoHTML = `
            <p><strong>Titre:</strong> ${user.titre}</p>
            <p><strong>Nom:</strong> ${user.nom}</p>
            <p><strong>Prénom:</strong> ${user.prenom}</p>
            <p><strong>Adresse:</strong> ${user.adresse}</p>
            <p><strong>Status:</strong> <span style="color:var(--success)">Actif</span></p>
        `;
        document.getElementById('userInfo').innerHTML = infoHTML;
    }

    // PAGE 21: Admin Approvals
    if (page === 'page21.html') {
        if (!checkAdmin()) return;

        const users = getUsers();
        const pendingUsers = users.filter(u => u.status === 'pending');
        const listContainer = document.getElementById('pendingList');

        if (pendingUsers.length === 0) {
            listContainer.innerHTML = "<tr><td colspan='4' style='text-align:center'>Aucune demande en attente.</td></tr>";
        } else {
            pendingUsers.forEach(u => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${u.nom} ${u.prenom}</td>
                    <td>${u.adresse}</td>
                    <td>${u.titre}</td>
                    <td>
                        <button onclick="changeStatus(${u.id}, 'active')" class="action-btn btn-approve">Accepter</button>
                        <button onclick="deleteUser(${u.id})" class="action-btn btn-reject">Refuser</button>
                    </td>
                `;
                listContainer.appendChild(tr);
            });
        }

        // Render Comments
        // Initialize Admin Chat
        renderAdminChatList();
    }

    // PAGE 22: Admin Registry
    if (page === 'page22.html') {
        if (!checkAdmin()) return;

        const users = getUsers();
        const activeUsers = users.filter(u => u.status === 'active');
        const listContainer = document.getElementById('fullList');

        if (activeUsers.length === 0) {
            listContainer.innerHTML = "<tr><td colspan='4' style='text-align:center'>Aucun utilisateur actif.</td></tr>";
        } else {
            activeUsers.forEach(u => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${u.titre}</td>
                    <td>${u.nom}</td>
                    <td>${u.prenom}</td>
                    <td>${u.adresse}</td>
                    <td>${u.password}</td>
                    <td>
                        <button onclick="deleteUser(${u.id})" class="action-btn btn-reject">Supprimer</button>
                    </td>
                `;
                listContainer.appendChild(tr);
            });
        }
    }
});

// Global Scope Functions (for onclick events)
window.changeStatus = function (id, newStatus) {
    let users = getUsers();
    const index = users.findIndex(u => u.id === id);
    if (index !== -1) {
        users[index].status = newStatus;
        saveUsers(users);
        location.reload();
    }
};

window.deleteUser = function (id) {
    if (!confirm("Êtes-vous sûr de vouloir supprimer cette demande ?")) return;
    let users = getUsers();
    users = users.filter(u => u.id !== id);
    saveUsers(users);
    location.reload();
};

// Tab Logic
// Tab Logic
window.openTab = function (tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // Show current tab
    document.getElementById(tabName).classList.add('active');

    // Highlight button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    // Trigger Chat Refresh if opening Chat tab
    if (tabName === 'tabChat' && window.renderUserChat) {
        window.renderUserChat();
    }
};

window.openSubTab = function (parentId, subTabName) {
    // Parent context
    const parent = document.getElementById(parentId);

    // Hide all sub-contents in this parent
    parent.querySelectorAll('.sub-content').forEach(tab => tab.classList.remove('active'));
    parent.querySelectorAll('.sub-tab-btn').forEach(btn => btn.classList.remove('active'));

    // Show target sub-content
    document.getElementById(subTabName).classList.add('active');

    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
};

// Update Profile Logic
window.updateProfile = function (e) {
    e.preventDefault();

    // Get new values
    const nom = document.getElementById('editNom').value;
    const prenom = document.getElementById('editPrenom').value;
    const adresse = document.getElementById('editAdresse').value;
    const mdp = document.getElementById('editMdp').value;

    if (!confirm("Voulez-vous vraiment modifier vos informations ?")) return;

    let users = getUsers();
    let currentUser = getCurrentUser();

    // Check if new address is taken (if changed)
    if (adresse !== currentUser.adresse && users.find(u => u.adresse === adresse)) {
        alert("Cette adresse est déjà utilisée par un autre compte.");
        return;
    }

    // Update in Master List
    const index = users.findIndex(u => u.id === currentUser.id);
    if (index !== -1) {
        users[index].nom = nom;
        users[index].prenom = prenom;
        users[index].adresse = adresse;
        users[index].password = mdp;

        saveUsers(users);

        // Update Session
        currentUser = users[index];
        setCurrentUser(currentUser);

        alert("Profil mis à jour avec succès !");
        location.reload();
    } else {
        alert("Erreur: Utilisateur introuvable.");
    }
};

// --- CHAT SYSTEM ---
function getMessages() {
    return JSON.parse(localStorage.getItem('hbz_messages')) || [];
}

function saveMessages(msgs) {
    localStorage.setItem('hbz_messages', JSON.stringify(msgs));
}

// Render User Chat (Page 13)
window.renderUserChat = function () {
    const user = getCurrentUser();
    if (!user) return;

    const messages = getMessages();
    // Filter messages for this user (sent by them OR replies to them)
    const userMsgs = messages.filter(m => m.userId === user.id || m.toUserId === user.id);

    const chatBox = document.getElementById('chatBox');
    if (!chatBox) return;

    if (userMsgs.length === 0) {
        chatBox.innerHTML = '<div style="text-align: center; color: var(--text-muted); margin-top: 50%;">Démarrez une conversation avec l\'admin.</div>';
        return;
    }

    let html = '';
    userMsgs.sort((a, b) => a.id - b.id); // Oldest first

    userMsgs.forEach(m => {
        // If I sent it (userId matches) -> SENT. If Admin sent it (sender 'Admin') -> RECEIVED.
        const type = (m.userId === user.id) ? 'sent' : 'received';
        const senderName = (type === 'sent') ? 'Moi' : 'Admin';

        html += `
            <div class="message ${type}">
                <div class="message-bubble">
                    ${m.text}
                </div>
                <span class="message-meta">${senderName} • ${m.date}</span>
            </div>
        `;
    });

    chatBox.innerHTML = html;
    chatBox.scrollTop = chatBox.scrollHeight; // Auto scroll to bottom
};

window.sendUserMessage = function (e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    if (!text) return;

    const user = getCurrentUser();
    const msgs = getMessages();

    msgs.push({
        id: Date.now(),
        userId: user.id, // Sender
        userName: user.nom + ' ' + user.prenom,
        text: text,
        date: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        type: 'user_msg'
    });

    saveMessages(msgs);
    input.value = '';
    renderUserChat();
};

// Hook into openTab to refresh chat when opened
// --- ADMIN CHAT SYSTEM (Moved from page21.html) ---
function renderAdminChatList() {
    const msgs = getMessages();
    const container = document.getElementById('adminChatList');
    if (!container) return; // Guard clause

    // Filter: only USER messages (type 'user_msg') to show them as "Inbox"
    const userMsgs = msgs.filter(m => m.type === 'user_msg').reverse();

    if (userMsgs.length === 0) {
        container.innerHTML = "<p style='color:var(--text-muted);'>Aucun nouveau message.</p>";
        return;
    }

    let html = '';
    userMsgs.forEach(m => {
        html += `
            <div class="user-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong>${m.userName}</strong>
                    <span style="color:var(--text-muted); font-size:0.8rem;">${m.date}</span>
                </div>
                <p style="margin: 10px 0; color:white;">${m.text}</p>
                <button class="secondary" style="width:auto; padding:5px 15px; font-size:0.8rem;" onclick="replyToUser('${m.userId}', '${m.userName}')">Répondre</button>
            </div>
        `;
    });
    container.innerHTML = html;
}

window.replyToUser = function (userId, userName) {
    const replyText = prompt("Répondre à " + userName + " :");
    if (replyText) {
        const msgs = getMessages();
        msgs.push({
            id: Date.now(),
            userId: 'admin',      // Sender
            toUserId: userId,     // Recipient
            userName: 'Admin',
            text: replyText,
            date: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            type: 'admin_reply'
        });
        saveMessages(msgs);
        alert("Réponse envoyée !");
        renderAdminChatList();
    }
};
