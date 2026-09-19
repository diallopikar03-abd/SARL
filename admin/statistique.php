<?php
include 'admin.php';

// Calcul du chiffre d'affaires des commandes livrées
$stmt = $pdo->query("
    SELECT SUM(total) 
    FROM commandes 
    WHERE statut = 'Livre'
");

$ca = $stmt->fetchColumn();

if ($ca === null) {
    $ca = 0;
}


// Produit le plus vendu
$stmt = $pdo->query("
    SELECT 
        d.id_produit,
        p.nom,
        SUM(d.quantite) AS ventes
    FROM details_commandes d
    JOIN produits p ON d.id_produit = p.id
    GROUP BY d.id_produit, p.nom
    ORDER BY ventes DESC
    LIMIT 1
");

$top = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Statistiques commerciales</title>

    <style>
        body{
            font-family:Arial, Helvetica, sans-serif;
            margin:20px;
        }

        .card{
            background:#f8f9fa;
            padding:20px;
            width:400px;
            border-radius:10px;
            margin-bottom:20px;
            box-shadow:0 3px 10px rgba(0,0,0,.15);
        }

        h1{
            color:#2c3e50;
        }

        b{
            color:#27ae60;
        }
    </style>

</head>

<body>


<h1>📊 Statistiques Commerciales</h1>


<div class="card">

<p>
<b>Chiffre d'Affaires Encaissé :</b>
<?= number_format($ca, 2) ?> €
</p>


</div>


<div class="card">

<p>
<b>Produit le plus vendu :</b>

<?php if ($top): ?>

<?= htmlspecialchars($top['nom']) ?>

<br>

Quantité vendue :
<?= htmlspecialchars($top['ventes']) ?>

fois

<?php else: ?>

Aucune vente enregistrée.

<?php endif; ?>

</p>


</div>


</body>

</html>