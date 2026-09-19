<?php
try {
    // REMPLACEZ LES TEXTES CI-DESSOUS PAR VOS INFOS DE L'ONGLET VARIABLES DE RAILWAY
    $host = 'METTRE_ICI_LA_VALEUR_DE_MYSQLHOST'; 
    $port = 'METTRE_ICI_LA_VALEUR_DE_MYSQLPORT';
    $dbname = 'METTRE_ICI_LA_VALEUR_DE_MYSQLDATABASE';
    $user = 'METTRE_ICI_LA_VALEUR_DE_MYSQLUSER';
    $password = 'METTRE_ICI_LA_VALEUR_DE_MYSQLPASSWORD';

    // Connexion forcée en TCP/IP avec les accès en clair
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) { 
    die('Erreur : ' . $e->getMessage()); 
}
?>