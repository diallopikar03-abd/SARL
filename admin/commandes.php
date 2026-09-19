<?php

include 'admin.php';

// =====================================================
// MARQUER UNE COMMANDE COMME LIVRÉE
// =====================================================

if (isset($_GET['livrer'])) {

    $idCommande = (int) $_GET['livrer'];

    if ($idCommande > 0) {

        try {

            $stmt = $pdo->prepare("
                UPDATE commandes
                SET statut = 'Livre'
                WHERE id_commande = ?
            ");

            $stmt->execute([$idCommande]);

        } catch (PDOException $e) {

            die(
                "Erreur lors de la mise à jour de la commande : "
                . htmlspecialchars($e->getMessage())
            );
        }
    }

    // Éviter de refaire l'action si la page est actualisée
    header("Location: commandes.php");
    exit;
}


// =====================================================
// RÉCUPÉRER LES COMMANDES
// =====================================================

try {

    $stmt = $pdo->query("
        SELECT
            id_commande,
            numero_commande,

            nom_client,
            prenom_client,
            telephone,
            email,
            adresse_livraison,

            montant_total,
            statut,
            date_commande

        FROM commandes

        ORDER BY date_commande DESC
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erreur lors de la récupération des commandes : "
        . htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des commandes</title>

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
            max-width: 1600px;
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
            min-width: 1300px;
        }

        th,
        td {
            padding: 13px 12px;
            border-bottom: 1px solid #e5e7eb;
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

        .commande {
            color: #2563eb;
            font-weight: bold;
            white-space: nowrap;
        }

        .client {
            text-align: left;
            min-width: 200px;
        }

        .client strong {
            display: block;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .client small {
            display: block;
            color: #777;
            margin-top: 3px;
        }

        .telephone {
            white-space: nowrap;
        }

        .email {
            color: #555;
        }

        .adresse {
            text-align: left;
            max-width: 250px;
        }

        .montant {
            color: #16a34a;
            font-weight: bold;
            white-space: nowrap;
        }

        .statut {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .statut-attente {
            background: #fff7ed;
            color: #c2410c;
        }

        .statut-livre {
            background: #dcfce7;
            color: #15803d;
        }

        .statut-autre {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-livrer {
            display: inline-block;
            padding: 8px 14px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-livrer:hover {
            background: #15803d;
        }

        .deja-livree {
            color: #16a34a;
            font-weight: bold;
        }

        .aucune {
            padding: 30px;
            text-align: center;
            color: #777;
        }

    </style>

</head>

<body>

<div class="container">

    <!-- =====================================================
         EN-TÊTE
    ====================================================== -->

    <div class="header">

        <h1>📦 Gestion des commandes</h1>

        <p>
            Consultez les commandes et les informations des clients.
        </p>

    </div>


    <!-- =====================================================
         TABLEAU
    ====================================================== -->

    <div class="table-container">

        <?php if (empty($commandes)): ?>

            <div class="aucune">
                Aucune commande enregistrée pour le moment.
            </div>

        <?php else: ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Commande</th>

                        <th>Client</th>

                        <th>Téléphone</th>

                        <th>Email</th>

                        <th>Adresse de livraison</th>

                        <th>Total</th>

                        <th>Statut</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($commandes as $c): ?>

                    <tr>

                        <!-- =================================================
                             ID
                        ================================================== -->

                        <td>

                            #<?= htmlspecialchars(
                                $c['id_commande']
                            ) ?>

                        </td>


                        <!-- =================================================
                             NUMÉRO DE COMMANDE
                        ================================================== -->

                        <td class="commande">

                            <?php if (!empty($c['numero_commande'])): ?>

                                <?= htmlspecialchars(
                                    $c['numero_commande']
                                ) ?>

                            <?php else: ?>

                                #<?= htmlspecialchars(
                                    $c['id_commande']
                                ) ?>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             NOM DU CLIENT
                        ================================================== -->

                        <td class="client">

                            <?php

                            $nomComplet = trim(
                                ($c['prenom_client'] ?? '') . ' ' .
                                ($c['nom_client'] ?? '')
                            );

                            ?>

                            <?php if (!empty($nomComplet)): ?>

                                <strong>
                                    👤 <?= htmlspecialchars(
                                        $nomComplet
                                    ) ?>
                                </strong>

                            <?php else: ?>

                                <strong>
                                    Client non renseigné
                                </strong>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             TÉLÉPHONE
                        ================================================== -->

                        <td class="telephone">

                            <?php if (!empty($c['telephone'])): ?>

                                📞 <?= htmlspecialchars(
                                    $c['telephone']
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             EMAIL
                        ================================================== -->

                        <td class="email">

                            <?php if (!empty($c['email'])): ?>

                                <?= htmlspecialchars(
                                    $c['email']
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             ADRESSE DE LIVRAISON
                        ================================================== -->

                        <td class="adresse">

                            <?php if (!empty($c['adresse_livraison'])): ?>

                                📍 <?= htmlspecialchars(
                                    $c['adresse_livraison']
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             MONTANT TOTAL
                        ================================================== -->

                        <td class="montant">

                            <?= number_format(
                                (float) $c['montant_total'],
                                0,
                                ',',
                                ' '
                            ) ?>

                            GNF

                        </td>


                        <!-- =================================================
                             STATUT
                        ================================================== -->

                        <td>

                            <?php

                            $statut = strtolower(
                                trim($c['statut'] ?? '')
                            );

                            if (
                                $statut === 'en attente' ||
                                $statut === 'en_attente'
                            ) {

                                $classeStatut = 'statut-attente';

                            } elseif (
                                $statut === 'livre' ||
                                $statut === 'livrée' ||
                                $statut === 'livree'
                            ) {

                                $classeStatut = 'statut-livre';

                            } else {

                                $classeStatut = 'statut-autre';

                            }

                            ?>

                            <span class="statut <?= $classeStatut ?>">

                                <?= htmlspecialchars(
                                    $c['statut'] ?? 'Non renseigné'
                                ) ?>

                            </span>

                        </td>


                        <!-- =================================================
                             DATE
                        ================================================== -->

                        <td>

                            <?php if (!empty($c['date_commande'])): ?>

                                <?= htmlspecialchars(
                                    date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $c['date_commande']
                                        )
                                    )
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             ACTION
                        ================================================== -->

                        <td>

                            <?php

                            $statutActuel = strtolower(
                                trim($c['statut'] ?? '')
                            );

                            ?>

                            <?php if (
                                $statutActuel === 'en attente' ||
                                $statutActuel === 'en_attente'
                            ): ?>

                                <a
                                    href="commandes.php?livrer=<?= urlencode($c['id_commande']) ?>"
                                    class="btn-livrer"
                                    onclick="return confirm('Confirmer la livraison de cette commande ?')"
                                >
                                    ✓ Marquer comme livrée
                                </a>

                            <?php elseif (
                                $statutActuel === 'livre' ||
                                $statutActuel === 'livrée' ||
                                $statutActuel === 'livree'
                            ): ?>

                                <span class="deja-livree">
                                    ✓ Livrée
                                </span>

                            <?php else: ?>

                                -

                            <?php endif; ?>

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
