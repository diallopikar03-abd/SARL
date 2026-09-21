<?php

$host = 'iriguchi.proxy.rlwy.net';
$port = '30477';
$dbname = 'railway';
$user = 'root';
$password = 'PvWnlqdTgjrgrbqBXMuEkafaUeAngYKS';

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Erreur : " . $e->getMessage());
}