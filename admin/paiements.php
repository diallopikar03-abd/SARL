
<?php

include 'admin.php';

header('Content-Type: text/html; charset=utf-8');

/*
|--------------------------------------------------------------------------
| 1. VÉRIFICATION DE LA CONNEXION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| 2. VÉRIFICATION DE LA CONNEXION BDD
|--------------------------------------------------------------------------
*/

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Erreur : connexion à la base de données impossible.');
}

/*
|--------------------------------------------------------------------------
| 3. TRAITEMENT DES ACTIONS
|--------------------------------------------------------------------------
*/

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_paiement = filter_input(
        INPUT_POST,
        'id_paiement',
        FILTER_VALIDATE_INT
    );

    $action = $_POST['action'] ?? '';

    if (!$id_paiement) {

        $message = 'Identifiant du paiement invalide.';
        $type_message = 'error';

    } elseif (!in_array($action, ['valider', 'refuser'], true)) {

        $message = 'Action invalide.';
        $type_message = 'error';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | RÉCUPÉRER LE PAIEMENT
            |--------------------------------------------------------------------------
            */

            $sql = "
                SELECT
                    p.id_paiement,
                    p.id_commande,
                    p.reference_commande,
                    p.reference_transaction,
                    p.montant,
                    p.methode,
                    p.telephone_client,
                    p.statut,
                    p.date_paiement
                FROM paiements p
                WHERE p.id_paiement = :id_paiement
                LIMIT 1
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':id_paiement' => $id_paiement
            ]);

            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paiement) {

                $message = 'Paiement introuvable.';
                $type_message = 'error';

            } else {

                /*
                |--------------------------------------------------------------------------
                | VALIDER LE PAIEMENT
                |--------------------------------------------------------------------------
                */

                if ($action === 'valider') {

                    $pdo->beginTransaction();

                    /*
                    | On récupère les valeurs acceptées par la colonne statut
                    | de paiements.
                    */

                    $stmtEnum = $pdo->query("
                        SHOW COLUMNS FROM paiements LIKE 'statut'
                    ");

                    $colonne = $stmtEnum->fetch(PDO::FETCH_ASSOC);

                    $statut_paiement = null;

                    if ($colonne && isset($colonne['Type'])) {

                        if (
                            preg_match(
                                "/enum\\((.*)\\)/i",
                                $colonne['Type'],
                                $matches
                            )
                        ) {

                            $valeurs = str_getcsv(
                                $matches[1],
                                ',',
                                "'"
                            );

                            foreach ($valeurs as $valeur) {

                                $valeur = trim($valeur);

                                if (
                                    strtolower($valeur) === 'payee' ||
                                    strtolower($valeur) === 'payé' ||
                                    strtolower($valeur) === 'paye' ||
                                    strtolower($valeur) === 'confirmee' ||
                                    strtolower($valeur) === 'confirmée' ||
                                    strtolower($valeur) === 'confirmee'
                                ) {
                                    $statut_paiement = $valeur;
                                    break;
                                }
                            }
                        }
                    }

                    /*
                    | Si aucun statut "payé/confirmé" n'est trouvé,
                    | on utilise "payee" comme valeur habituelle.
                    */

                    if ($statut_paiement === null) {
                        $statut_paiement = 'payee';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | METTRE À JOUR LE PAIEMENT
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE paiements
                        SET statut = :statut
                        WHERE id_paiement = :id_paiement
                    ");

                    $stmt->execute([
                        ':statut' => $statut_paiement,
                        ':id_paiement' => $id_paiement
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | METTRE À JOUR LA COMMANDE
                    |--------------------------------------------------------------------------
                    |
                    | On regarde d'abord les valeurs réellement disponibles
                    | dans la colonne commandes.statut.
                    |
                    */

                    $stmtEnumCommande = $pdo->query("
                        SHOW COLUMNS FROM commandes LIKE 'statut'
                    ");

                    $colonneCommande = $stmtEnumCommande->fetch(PDO::FETCH_ASSOC);

                    $statut_commande = null;

                    if (
                        $colonneCommande &&
                        isset($colonneCommande['Type'])
                    ) {

                        if (
                            preg_match(
                                "/enum\\((.*)\\)/i",
                                $colonneCommande['Type'],
                                $matchesCommande
                            )
                        ) {

                            $valeursCommande = str_getcsv(
                                $matchesCommande[1],
                                ',',
                                "'"
                            );

                            foreach ($valeursCommande as $valeur) {

                                $valeur = trim($valeur);

                                if (
                                    strtolower($valeur) === 'payee' ||
                                    strtolower($valeur) === 'payé' ||
                                    strtolower($valeur) === 'paye'
                                ) {
                                    $statut_commande = $valeur;
                                    break;
                                }
                            }
                        }
                    }

                    /*
                    | Si "payee" n'existe pas dans l'ENUM,
                    | on ne modifie pas la commande.
                    */

                    if ($statut_commande !== null) {

                        $stmt = $pdo->prepare("
                            UPDATE commandes
                            SET statut = :statut
                            WHERE id_commande = :id_commande
                        ");

                        $stmt->execute([
                            ':statut' => $statut_commande,
                            ':id_commande' => $paiement['id_commande']
                        ]);
                    }

                    $pdo->commit();

                    $message = 'Paiement validé avec succès.';
                    $type_message = 'success';
                }

                /*
                |--------------------------------------------------------------------------
                | REFUSER LE PAIEMENT
                |--------------------------------------------------------------------------
                */

                elseif ($action === 'refuser') {

                    /*
                    | Chercher automatiquement une valeur "refuse/refusée"
                    | dans l'ENUM.
                    */

                    $stmtEnum = $pdo->query("
                        SHOW COLUMNS FROM paiements LIKE 'statut'
                    ");

                    $colonne = $stmtEnum->fetch(PDO::FETCH_ASSOC);

                    $statut_refuse = null;

                    if ($colonne && isset($colonne['Type'])) {

                        if (
                            preg_match(
                                "/enum\\((.*)\\)/i",
                                $colonne['Type'],
                                $matches
                            )
                        ) {

                            $valeurs = str_getcsv(
                                $matches[1],
                                ',',
                                "'"
                            );

                            foreach ($valeurs as $valeur) {

                                $valeur = trim($valeur);

                                $test = strtolower($valeur);

                                if (
                                    $test === 'refuse' ||
                                    $test === 'refusé' ||
                                    $test === 'refusee' ||
                                    $test === 'refusée' ||
                                    $test === 'annule' ||
                                    $test === 'annulée' ||
                                    $test === 'annulee'
                                ) {
                                    $statut_refuse = $valeur;
                                    break;
                                }
                            }
                        }
                    }

                    if ($statut_refuse === null) {

                        $message =
                            'Impossible de trouver un statut de refus accepté par la base de données.';

                        $type_message = 'error';

                    } else {

                        $stmt = $pdo->prepare("
                            UPDATE paiements
                            SET statut = :statut
                            WHERE id_paiement = :id_paiement
                        ");

                        $stmt->execute([
                            ':statut' => $statut_refuse,
                            ':id_paiement' => $id_paiement
                        ]);

                        $message = 'Paiement refusé.';
                        $type_message = 'success';
                    }
                }
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message =
                'Erreur lors du traitement du paiement : ' .
                $e->getMessage();

            $type_message = 'error';
        }
    }
}

/*
|--------------------------------------------------------------------------
| 4. RÉCUPÉRER LES PAIEMENTS
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            p.id_paiement,
            p.id_commande,
            p.reference_commande,
            p.reference_transaction,
            p.montant,
            p.methode,
            p.telephone_client,
            p.statut,
            p.date_paiement,

            c.nom_client,
            c.prenom_client,
            c.email,
            c.adresse_livraison,
            c.numero_commande

        FROM paiements p

        LEFT JOIN commandes c
            ON c.id_commande = p.id_commande

        ORDER BY p.date_paiement DESC,
                 p.id_paiement DESC
    ";

    $stmt = $pdo->query($sql);

    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    die(
        'Erreur lors de la récupération des paiements : ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

/*
|--------------------------------------------------------------------------
| 5. STATISTIQUES
|--------------------------------------------------------------------------
*/

$total_paiements = count($paiements);

$total_montant = 0;
$total_attente = 0;
$total_valides = 0;
$total_refuses = 0;

foreach ($paiements as $paiement) {

    $total_montant += (float) $paiement['montant'];

    $statut = strtolower(
        trim((string) $paiement['statut'])
    );

    if (
        $statut === 'en_attente' ||
        $statut === 'en attente'
    ) {

        $total_attente++;

    } elseif (
        $statut === 'payee' ||
        $statut === 'payé' ||
        $statut === 'paye' ||
        $statut === 'confirmee' ||
        $statut === 'confirmée'
    ) {

        $total_valides++;

    } elseif (
        $statut === 'refuse' ||
        $statut === 'refusé' ||
        $statut === 'refusee' ||
        $statut === 'refusée' ||
        $statut === 'annule' ||
        $statut === 'annulée'
    ) {

        $total_refuses++;
    }
}

/*
|--------------------------------------------------------------------------
| 6. FONCTION AFFICHAGE STATUT
|--------------------------------------------------------------------------
*/

function afficherStatut($statut)
{
    $statut_original = (string) $statut;

    $test = strtolower(
        trim($statut_original)
    );

    if (
        $test === 'en_attente' ||
        $test === 'en attente'
    ) {
        return [
            'classe' => 'attente',
            'texte' => $statut_original
        ];
    }

    if (
        $test === 'payee' ||
        $test === 'payé' ||
        $test === 'paye' ||
        $test === 'confirmee' ||
        $test === 'confirmée'
    ) {
        return [
            'classe' => 'valide',
            'texte' => $statut_original
        ];
    }

    if (
        $test === 'refuse' ||
        $test === 'refusé' ||
        $test === 'refusee' ||
        $test === 'refusée' ||
        $test === 'annule' ||
        $test === 'annulée'
    ) {
        return [
            'classe' => 'refuse',
            'texte' => $statut_original
        ];
    }

    return [
        'classe' => 'autre',
        'texte' => $statut_original
    ];
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Paiements Orange Money - Administration</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }

        .topbar {
            height: 70px;
            background: #111827;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }

        .logo {
            font-size: 23px;
            font-weight: bold;
        }

        .admin {
            font-size: 14px;
        }

        .container {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .page-title p {
            color: #6b7280;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.07);
        }

        .card-title {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 27px;
            font-weight: bold;
        }

        .orange {
            color: #f97316;
        }

        .green {
            color: #16a34a;
        }

        .yellow {
            color: #ca8a04;
        }

        .red {
            color: #dc2626;
        }

        .message {
            padding: 15px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h2 {
            font-size: 19px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1150px;
        }

        th {
            background: #f9fafb;
            color: #374151;
            font-size: 13px;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            vertical-align: middle;
        }

        tr:hover {
            background: #fafafa;
        }

        .client {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .small {
            font-size: 12px;
            color: #6b7280;
        }

        .reference {
            background: #f3f4f6;
            padding: 7px 9px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: bold;
            display: inline-block;
        }

        .amount {
            font-weight: bold;
            color: #111827;
        }

        .method {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #fff7ed;
            color: #c2410c;
            font-weight: bold;
            font-size: 12px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge.attente {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.valide {
            background: #dcfce7;
            color: #166534;
        }

        .badge.refuse {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.autre {
            background: #e5e7eb;
            color: #374151;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            border: none;
            padding: 8px 11px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }

        .btn-valider {
            background: #16a34a;
            color: white;
        }

        .btn-valider:hover {
            background: #15803d;
        }

        .btn-refuser {
            background: #dc2626;
            color: white;
        }

        .btn-refuser:hover {
            background: #b91c1c;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        @media (max-width: 1000px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .container {
                padding: 15px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 0 15px;
            }

        }

    </style>

</head>

<body>

    <header class="topbar">

        <div class="logo">
            DrinkShop — Administration
        </div>

        <div class="admin">
            👤 Administrateur
        </div>

    </header>

    <main class="container">

        <div class="page-title">

            <h1>💰 Paiements Orange Money</h1>

            <p>
                Consultez les références de transactions envoyées par les clients
                et vérifiez les paiements.
            </p>

        </div>

        <?php if ($message !== ''): ?>

            <div class="message <?= htmlspecialchars($type_message, ENT_QUOTES, 'UTF-8') ?>">

                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <!-- STATISTIQUES -->

        <div class="stats">

            <div class="card">

                <div class="card-title">
                    Total paiements
                </div>

                <div class="card-value">
                    <?= $total_paiements ?>
                </div>

            </div>

            <div class="card">

                <div class="card-title">
                    En attente
                </div>

                <div class="card-value yellow">
                    <?= $total_attente ?>
                </div>

            </div>

            <div class="card">

                <div class="card-title">
                    Validés
                </div>

                <div class="card-value green">
                    <?= $total_valides ?>
                </div>

            </div>

            <div class="card">

                <div class="card-title">
                    Montant total déclaré
                </div>

                <div class="card-value orange">

                    <?= number_format(
                        $total_montant,
                        0,
                        ',',
                        ' '
                    ) ?>

                    FCFA

                </div>

            </div>

        </div>

        <!-- TABLEAU -->

        <div class="table-card">

            <div class="table-header">

                <h2>
                    Liste des transactions
                </h2>

            </div>

            <?php if (empty($paiements)): ?>

                <div class="empty">

                    <div class="empty-icon">
                        💳
                    </div>

                    <p>
                        Aucun paiement n'a encore été déclaré.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Commande
                                </th>

                                <th>
                                    Client
                                </th>

                                <th>
                                    Téléphone
                                </th>

                                <th>
                                    Montant
                                </th>

                                <th>
                                    Référence transaction
                                </th>

                                <th>
                                    Méthode
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Statut
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($paiements as $paiement): ?>

                                <?php

                                $statut = afficherStatut(
                                    $paiement['statut']
                                );

                                $nom_complet = trim(
                                    ($paiement['prenom_client'] ?? '') .
                                    ' ' .
                                    ($paiement['nom_client'] ?? '')
                                );

                                if ($nom_complet === '') {
                                    $nom_complet = 'Client non renseigné';
                                }

                                ?>

                                <tr>

                                    <!-- COMMANDE -->

                                    <td>

                                        <strong>
                                            #<?= htmlspecialchars(
                                                $paiement['id_commande'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                        <?php if (!empty($paiement['numero_commande'])): ?>

                                            <div class="small">

                                                <?= htmlspecialchars(
                                                    $paiement['numero_commande'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </div>

                                        <?php endif; ?>

                                    </td>

                                    <!-- CLIENT -->

                                    <td>

                                        <div class="client">

                                            <?= htmlspecialchars(
                                                $nom_complet,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>

                                        <?php if (!empty($paiement['email'])): ?>

                                            <div class="small">

                                                <?= htmlspecialchars(
                                                    $paiement['email'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </div>

                                        <?php endif; ?>

                                    </td>

                                    <!-- TELEPHONE -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $paiement['telephone_client'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>

                                    <!-- MONTANT -->

                                    <td>

                                        <span class="amount">

                                            <?= number_format(
                                                (float) $paiement['montant'],
                                                0,
                                                ',',
                                                ' '
                                            ) ?>

                                            FCFA

                                        </span>

                                    </td>

                                    <!-- REFERENCE -->

                                    <td>

                                        <span class="reference">

                                            <?= htmlspecialchars(
                                                $paiement['reference_transaction'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>

                                    <!-- METHODE -->

                                    <td>

                                        <span class="method">

                                            <?= htmlspecialchars(
                                                $paiement['methode'] ?? 'Orange Money',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>

                                    <!-- DATE -->

                                    <td>

                                        <?php

                                        if (!empty($paiement['date_paiement'])) {

                                            echo htmlspecialchars(
                                                date(
                                                    'd/m/Y H:i',
                                                    strtotime(
                                                        $paiement['date_paiement']
                                                    )
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                        } else {

                                            echo '-';
                                        }

                                        ?>

                                    </td>

                                    <!-- STATUT -->

                                    <td>

                                        <span class="badge <?= htmlspecialchars(
                                            $statut['classe'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">

                                            <?= htmlspecialchars(
                                                $statut['texte'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>

                                    <!-- ACTIONS -->

                                    <td>

                                        <?php

                                        $statut_test = strtolower(
                                            trim(
                                                (string) $paiement['statut']
                                            )
                                        );

                                        $est_attente =
                                            $statut_test === 'en_attente' ||
                                            $statut_test === 'en attente';

                                        ?>

                                        <?php if ($est_attente): ?>

                                            <div class="actions">

                                                <!-- VALIDER -->

                                                <form
                                                    method="POST"
                                                    onsubmit="return confirm('Confirmer la validation de ce paiement ?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id_paiement"
                                                        value="<?= (int) $paiement['id_paiement'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="valider"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-valider"
                                                    >
                                                        ✓ Valider
                                                    </button>

                                                </form>

                                                <!-- REFUSER -->

                                                <form
                                                    method="POST"
                                                    onsubmit="return confirm('Voulez-vous vraiment refuser ce paiement ?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id_paiement"
                                                        value="<?= (int) $paiement['id_paiement'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="refuser"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-refuser"
                                                    >
                                                        ✕ Refuser
                                                    </button>

                                                </form>

                                            </div>

                                        <?php else: ?>

                                            <span class="small">
                                                Aucun traitement
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </main>

</body>

</html>
```
