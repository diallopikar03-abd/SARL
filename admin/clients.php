<?php
include 'admin.php';

// Récupération des clients
$stmt = $pdo->query("SELECT * FROM commandes ORDER BY nom_client, prenom_client ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des clients</title>

    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            margin:20px;
        }

        h1{
            color:#2c3e50;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:10px;
            text-align:center;
        }

        table th{
            background:#2c3e50;
            color:white;
        }

        tr:nth-child(even){
            background:#f8f8f8;
        }

        tr:hover{
            background:#f1f1f1;
        }
    </style>
</head>
<body>

<h1>Liste des Clients</h1>

<table>

    <tr>
        <th>ID</th>
        <th>Nom_client</th>
        <th>Prenom_client</th>
        <th>Email</th>
        <th>Adresse</th>
    </tr>

    <?php while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>

    <tr>
        <td><?= htmlspecialchars($u['id_commande']) ?></td>
        <td><?= htmlspecialchars($u['nom_client']) ?></td>
        <td><?= htmlspecialchars($u['prenom_client']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['adresse_livraison']) ?></td>
    </tr>

    <?php endwhile; ?>

</table>

</body>
</html>