<?php
try {
    // Récupération des informations de Railway (ou valeurs par défaut en local)
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $port = getenv('MYSQLPORT') ?: '3306';
    $dbname = getenv('MYSQLDATABASE') ?: 'Site_web';
    $user = getenv('MYSQLUSER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';

    // Connexion dynamique
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) { 
    die('Erreur : ' . $e->getMessage()); 
}
?>