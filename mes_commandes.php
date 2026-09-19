<?php
session_start();

require 'db.php';
include 'header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Récupérer les commandes de l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT * 
    FROM commandes 
    WHERE id_utilisateur = ?
    ORDER BY date_creation DESC
");

$stmt->execute([$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Commandes</title>

    <style>
        body{
            font-family:Arial, Helvetica, sans-serif;
            margin:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        th, td{
            border:1px solid #ddd;
            padding:12px;
            text-align:center;
        }

        th{
            background:#2c3e50;
            color:white;
        }

        tr:nth-child(even){
            background:#f8f8f8;
        }

        .statut{
            font-weight:bold;
        }
    </style>
</head>

<body>

<h1>📦 Mes Commandes</h1>

<table>

<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Total</th>
    <th>Statut</th>
</tr>


<?php while ($c = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr>

    <td>
        #<?= htmlspecialchars($c['id']) ?>
    </td>

    <td>
        <?= htmlspecialchars($c['date_creation']) ?>
    </td>

    <td>
        <?= htmlspecialchars($c['total']) ?> €
    </td>

    <td class="statut">
        <?= htmlspecialchars($c['statut']) ?>
    </td>

</tr>

<?php endwhile; ?>


</table>


</body>
</html>