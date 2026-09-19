
<?php

include 'admin.php';

// =====================================================
// RÉCUPÉRER LES PAIEMENTS
// Les informations du client sont dans la table commandes
// =====================================================

$sql = "
    SELECT 
        p.id_paiement,
        p.id_commande,
        p.montant,
        p.mode_paiement,
        p.date_paiement,

        c.numero_commande,
        c.nom_client,
        c.prenom_client,
        c.telephone,
        c.email,
        c.adresse_livraison

    FROM paiements p

    INNER JOIN commandes c
        ON p.id_commande = c.id_commande

    ORDER BY p.date_paiement DESC
";

try {

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erreur lors de la récupération des paiements : "
        . htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Historique des paiements</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 30px;
            background: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 1500px;
            margin: auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0 0 10px;
            color: #2c3e50;
        }

        .header p {
            margin: 0;
            color: #666;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        th,
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 13px 12px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
            white-space: nowrap;
        }

        tr:hover {
            background: #f8fafc;
        }

        .montant {
            font-weight: bold;
            color: #16a34a;
            white-space: nowrap;
        }

        .mode {
            font-weight: bold;
            color: #2c3e50;
        }

        .client {
            text-align: left;
            min-width: 230px;
        }

        .client strong {
            display: block;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .client small {
            display: block;
            color: #777;
            margin-top: 3px;
        }

        .telephone {
            color: #374151;
        }

        .adresse {
            text-align: left;
            max-width: 250px;
            color: #555;
        }

        .commande {
            font-weight: bold;
            color: #2563eb;
            white-space: nowrap;
        }

        .aucun {
            padding: 30px;
            text-align: center;
            color: #777;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #e8f5e9;
            color: #15803d;
            font-size: 13px;
            font-weight: bold;
        }

        .date {
            white-space: nowrap;
            color: #555;
        }

        .non-renseigne {
            color: #999;
            font-style: italic;
        }

    </style>

</head>

<body>

<div class="container">

    <!-- =====================================================
         EN-TÊTE
    ====================================================== -->

    <div class="header">

        <h1>💳 Historique des paiements</h1>

        <p>
            Liste des transactions enregistrées dans la plateforme.
        </p>

    </div>


    <!-- =====================================================
         TABLE DES PAIEMENTS
    ====================================================== -->

    <div class="table-container">

        <?php if (empty($paiements)): ?>

            <div class="aucun">
                Aucun paiement enregistré pour le moment.
            </div>

        <?php else: ?>

            <table>

                <thead>

                    <tr>

                        <th>ID Paiement</th>

                        <th>Commande</th>

                        <th>Client</th>

                        <th>Téléphone</th>

                        <th>Email</th>

                        <th>Adresse de livraison</th>

                        <th>Montant</th>

                        <th>Mode de paiement</th>

                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($paiements as $p): ?>

                    <tr>

                        <!-- =================================================
                             ID PAIEMENT
                        ================================================== -->

                        <td>
                            #<?= htmlspecialchars(
                                $p['id_paiement'] ?? ''
                            ) ?>
                        </td>


                        <!-- =================================================
                             COMMANDE
                        ================================================== -->

                        <td class="commande">

                            <?php if (!empty($p['numero_commande'])): ?>

                                <?= htmlspecialchars(
                                    $p['numero_commande']
                                ) ?>

                            <?php else: ?>

                                #<?= htmlspecialchars(
                                    $p['id_commande'] ?? ''
                                ) ?>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             CLIENT
                             Les informations viennent maintenant
                             directement de commandes
                        ================================================== -->

                        <td class="client">

                            <?php

                            $nomComplet = trim(
                                ($p['prenom_client'] ?? '') . ' ' .
                                ($p['nom_client'] ?? '')
                            );

                            ?>

                            <?php if (!empty($nomComplet)): ?>

                                <strong>
                                    <?= htmlspecialchars($nomComplet) ?>
                                </strong>

                            <?php else: ?>

                                <span class="non-renseigne">
                                    Client non renseigné
                                </span>

                            <?php endif; ?>


                            <!-- Email sous le nom -->

                            <?php if (!empty($p['email'])): ?>

                                <small>
                                    📧 <?= htmlspecialchars($p['email']) ?>
                                </small>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             TÉLÉPHONE
                        ================================================== -->

                        <td class="telephone">

                            <?php if (!empty($p['telephone'])): ?>

                                📞 <?= htmlspecialchars(
                                    $p['telephone']
                                ) ?>

                            <?php else: ?>

                                <span class="non-renseigne">
                                    Non renseigné
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             EMAIL
                        ================================================== -->

                        <td>

                            <?php if (!empty($p['email'])): ?>

                                <?= htmlspecialchars(
                                    $p['email']
                                ) ?>

                            <?php else: ?>

                                <span class="non-renseigne">
                                    Non renseigné
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             ADRESSE DE LIVRAISON
                        ================================================== -->

                        <td class="adresse">

                            <?php if (!empty($p['adresse_livraison'])): ?>

                                📍 <?= htmlspecialchars(
                                    $p['adresse_livraison']
                                ) ?>

                            <?php else: ?>

                                <span class="non-renseigne">
                                    Adresse non renseignée
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             MONTANT
                        ================================================== -->

                        <td class="montant">

                            <?= number_format(
                                (float) $p['montant'],
                                0,
                                ',',
                                ' '
                            ) ?>

                            FCFA

                        </td>


                        <!-- =================================================
                             MODE DE PAIEMENT
                        ================================================== -->

                        <td class="mode">

                            <span class="badge">

                                <?= htmlspecialchars(
                                    $p['mode_paiement']
                                    ?? 'Non renseigné'
                                ) ?>

                            </span>

                        </td>


                        <!-- =================================================
                             DATE
                        ================================================== -->

                        <td class="date">

                            <?php

                            if (!empty($p['date_paiement'])) {

                                echo htmlspecialchars(
                                    date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $p['date_paiement']
                                        )
                                    )
                                );

                            } else {

                                echo '-';

                            }

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
