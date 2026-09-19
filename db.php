<?php
try {
// =====================================================
// CONNEXION À LA BASE DE DONNÉES
// Railway OU XAMPP
// =====================================================

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'Site_web';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

  // Connexion forcée en TCP/IP avec les accès en clair
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) { 
    die('Erreur : ' . $e->getMessage()); 
}
?>