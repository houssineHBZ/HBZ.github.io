<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'soulutionhbz';
$username = 'root';
$password = ''; // Laissez vide par défaut pour XAMPP/WAMP, ou mettez 'root' pour MAMP

// URL du site (Hébergement GitHub)
// Note: GitHub Pages ne supporte pas PHP ni MySQL. Ce code est stocké ici pour sauvegarde.
$site_url = 'https://github.com/houssineHBZ/HBZ.github.io';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurer PDO pour lancer des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
