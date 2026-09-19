
<?php

include 'admin.php';

/*
|--------------------------------------------------------------------------
| Vérification administrateur
|--------------------------------------------------------------------------
*/




/*
|--------------------------------------------------------------------------
| Récupérer les avis
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            a.id_avis,
            a.id_utilisateur,
            a.id_boisson,
            a.note,
            a.commentaire,
            a.date_avis,

            u.nom,
            u.prenom,
            u.email,

            b.nom AS nom_boisson

        FROM avis a

        LEFT JOIN utilisateurs u
            ON a.id_utilisateur = u.id_utilisateur

        LEFT JOIN boissons b
            ON a.id_boisson = b.id_boisson

        ORDER BY a.date_avis DESC
    ";

    $stmt = $pdo->query($sql);

    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $avis = [];

    $erreur = $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

$totalAvis = count($avis);

$totalNotes = 0;

foreach ($avis as $a) {

    $totalNotes += (int)$a['note'];
}

$moyenne = $totalAvis > 0
    ? round($totalNotes / $totalAvis, 1)
    : 0;

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Avis clients - Administration</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f7fb;

    color: #222;
}

.container {

    max-width: 1250px;

    margin: 0 auto;

    padding: 30px;
}

.page-title {

    margin-bottom: 25px;
}

.page-title h1 {

    margin: 0;

    font-size: 30px;
}

.page-title p {

    color: #777;

    margin-top: 8px;
}


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

.stats {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}

.stat-card {

    background: white;

    padding: 25px;

    border-radius: 14px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.06);
}

.stat-card h3 {

    margin: 0 0 10px;

    color: #777;

    font-size: 15px;
}

.stat-value {

    font-size: 32px;

    font-weight: bold;
}

.star-color {

    color: #ffc107;
}


/*
|--------------------------------------------------------------------------
| Tableau
|--------------------------------------------------------------------------
*/

.table-card {

    background: white;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.06);
}

.table-header {

    padding: 20px;

    border-bottom: 1px solid #eee;

    font-size: 18px;

    font-weight: bold;
}

.table-responsive {

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;
}

th {

    background: #f8f9fa;

    padding: 15px;

    text-align: left;

    font-size: 14px;

    color: #555;
}

td {

    padding: 15px;

    border-top: 1px solid #eee;

    vertical-align: top;
}

tr:hover {

    background: #fafafa;
}


/*
|--------------------------------------------------------------------------
| Note
|--------------------------------------------------------------------------
*/

.note {

    color: #ffc107;

    font-size: 19px;

    white-space: nowrap;
}

.note-number {

    color: #555;

    font-size: 13px;

    margin-left: 5px;
}


/*
|--------------------------------------------------------------------------
| Client
|--------------------------------------------------------------------------
*/

.client-name {

    font-weight: bold;
}

.client-email {

    color: #777;

    font-size: 13px;

    margin-top: 4px;
}


/*
|--------------------------------------------------------------------------
| Commentaire
|--------------------------------------------------------------------------
*/

.commentaire {

    max-width: 350px;

    line-height: 1.5;

    color: #555;
}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

.date {

    color: #777;

    font-size: 13px;

}


/*
|--------------------------------------------------------------------------
| Aucun avis
|--------------------------------------------------------------------------
*/

.empty {

    padding: 50px;

    text-align: center;

    color: #777;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width: 700px) {

    .container {

        padding: 15px;
    }

    .stats {

        grid-template-columns: 1fr;
    }

    .page-title h1 {

        font-size: 25px;
    }
}

</style>

</head>

<body>


<div class="container">


    <!-- TITRE -->

    <div class="page-title">

        <h1>⭐ Avis des clients</h1>

        <p>
            Consultez les avis et les notes laissés par vos clients.
        </p>

    </div>


    <!-- STATISTIQUES -->

    <div class="stats">


        <div class="stat-card">

            <h3>Total des avis</h3>

            <div class="stat-value">

                <?= $totalAvis ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Note moyenne</h3>

            <div class="stat-value star-color">

                <?= number_format($moyenne, 1) ?>

                <span style="font-size:22px;">★</span>

            </div>

        </div>


    </div>


    <!-- TABLE -->

    <div class="table-card">


        <div class="table-header">

            Tous les avis clients

        </div>


        <?php if (!empty($erreur)): ?>

            <div class="empty">

                Erreur lors de la récupération des avis.

                <br><br>

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php elseif (empty($avis)): ?>

            <div class="empty">

                ⭐ Aucun avis client pour le moment.

            </div>

        <?php else: ?>


            <div class="table-responsive">

                <table>

                    <thead>

                        <tr>

                            <th>Client</th>

                            <th>Boisson</th>

                            <th>Note</th>

                            <th>Commentaire</th>

                            <th>Date</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($avis as $a): ?>

                        <tr>


                            <!-- CLIENT -->

                            <td>

                                <div class="client-name">

                                    <?= htmlspecialchars(
                                        trim(
                                            ($a['prenom'] ?? '') .
                                            ' ' .
                                            ($a['nom'] ?? '')
                                        )
                                    ) ?>

                                </div>

                                <div class="client-email">

                                    <?= htmlspecialchars(
                                        $a['email'] ?? ''
                                    ) ?>

                                </div>

                            </td>


                            <!-- BOISSON -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $a['nom_boisson']
                                        ?? 'Boisson supprimée'
                                    ) ?>

                                </strong>

                            </td>


                            <!-- NOTE -->

                            <td>

                                <div class="note">

                                    <?php

                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ) {

                                        echo $i <= (int)$a['note']
                                            ? '★'
                                            : '☆';
                                    }

                                    ?>

                                    <span class="note-number">

                                        <?= (int)$a['note'] ?>/5

                                    </span>

                                </div>

                            </td>


                            <!-- COMMENTAIRE -->

                            <td>

                                <div class="commentaire">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $a['commentaire']
                                        )
                                    ) ?>

                                </div>

                            </td>


                            <!-- DATE -->

                            <td>

                                <div class="date">

                                    <?= htmlspecialchars(
                                        date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $a['date_avis']
                                            )
                                        )
                                    ) ?>

                                </div>

                            </td>


                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>


    </div>

</div>

</body>

</html>

