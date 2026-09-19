<?php
session_start();

// Vérification de la connexion et du rôle administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php");
    exit();
}

// Connexion à la base de données
require_once "../db.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>

    <style>
        body{
            margin:0;
            font-family:Arial, Helvetica, sans-serif;
            background:#f5f5f5;
        }

        nav{
            background:#2c3e50;
            padding:15px;
        }

        nav a{
            color:white;
            text-decoration:none;
            margin-right:20px;
            font-weight:bold;
        }

        nav a:hover{
            color:#f1c40f;
        }

        hr{
            margin:0;
            border:1px solid #ddd;
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="boissons.php">🥤 Gestion Boissons</a>
    <a href="categories.php">📂 Catégories</a>
    <a href="commandes.php">🛒 Commandes</a>
    <a href="clients.php">👥 Clients</a>
    <a href="paiements.php">💳 Paiements</a>
    <a href="statistiques.php">📊 Statistiques</a>
    <a href="../index.php">🌐 Retour au site</a>
    <a href="../deconnexion.php" style="float:right;">🚪 Déconnexion</a>
</nav>

<hr>