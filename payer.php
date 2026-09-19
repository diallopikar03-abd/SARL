<?php

session_start();

header('Content-Type: text/html; charset=utf-8');


/*
|--------------------------------------------------------------------------
| 1. CONNEXION À LA BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        "mysql:host=localhost;dbname=Site_web;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die(
        "Erreur de connexion à la base de données : "
        . htmlspecialchars($e->getMessage())
    );
}


/*
|--------------------------------------------------------------------------
| 2. RÉCUPÉRER L'ID DE LA COMMANDE
|--------------------------------------------------------------------------
*/

$id_commande = filter_input(
    INPUT_GET,
    'id_commande',
    FILTER_VALIDATE_INT
);


/*
|--------------------------------------------------------------------------
| Si l'ID n'est pas dans l'URL, chercher dans la session
|--------------------------------------------------------------------------
*/

if (!$id_commande) {

    if (isset($_SESSION['id_commande'])) {

        $id_commande = (int) $_SESSION['id_commande'];

    } else {

        $id_commande = 0;
    }
}


/*
|--------------------------------------------------------------------------
| Vérifier l'ID
|--------------------------------------------------------------------------
*/

if ($id_commande <= 0) {

    die('
        <div style="
            max-width:600px;
            margin:50px auto;
            padding:25px;
            font-family:Arial;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
        ">

            <h2>❌ Commande introuvable</h2>

            <p>
                Aucun identifiant de commande valide
                n\'a été fourni.
            </p>

        </div>
    ');
}


/*
|--------------------------------------------------------------------------
| 3. RÉCUPÉRER LA COMMANDE
|--------------------------------------------------------------------------
*/

try {

    $stmtCommande = $pdo->prepare("
        SELECT
            id_commande,
            numero_commande,
            montant_total,
            statut
        FROM commandes
        WHERE id_commande = ?
        LIMIT 1
    ");

    $stmtCommande->execute([
        $id_commande
    ]);

    $commande = $stmtCommande->fetch();

} catch (PDOException $e) {

    die('
        <div style="
            max-width:600px;
            margin:50px auto;
            padding:25px;
            font-family:Arial;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
        ">

            <h2>❌ Erreur</h2>

            <p>
                Impossible de récupérer la commande.
            </p>

        </div>
    ');
}


/*
|--------------------------------------------------------------------------
| Commande inexistante
|--------------------------------------------------------------------------
*/

if (!$commande) {

    die('
        <div style="
            max-width:600px;
            margin:50px auto;
            padding:25px;
            font-family:Arial;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
        ">

            <h2>❌ Commande introuvable</h2>

            <p>
                Cette commande n\'existe pas.
            </p>

        </div>
    ');
}


/*
|--------------------------------------------------------------------------
| 4. INFORMATIONS DE LA COMMANDE
|--------------------------------------------------------------------------
*/

$montant = (float) $commande['montant_total'];


$numero_commande = !empty($commande['numero_commande'])
    ? $commande['numero_commande']
    : 'CMD-' . $id_commande;


$statut_commande = $commande['statut'];


/*
|--------------------------------------------------------------------------
| 5. VÉRIFIER LE MONTANT
|--------------------------------------------------------------------------
*/

if ($montant <= 0) {

    die('
        <div style="
            max-width:600px;
            margin:50px auto;
            padding:25px;
            font-family:Arial;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
        ">

            <h2>❌ Montant invalide</h2>

            <p>
                Le montant de cette commande est invalide.
            </p>

        </div>
    ');
}


/*
|--------------------------------------------------------------------------
| 6. VARIABLES
|--------------------------------------------------------------------------
*/

$erreur = '';

$succes = '';

$reference_transaction = '';


/*
|--------------------------------------------------------------------------
| 7. NUMÉRO ORANGE MONEY
|--------------------------------------------------------------------------
*/

$numero_orange = '+224 628 536 273';


/*
|--------------------------------------------------------------------------
| 8. VÉRIFIER SI UN PAIEMENT EXISTE DÉJÀ
|--------------------------------------------------------------------------
|
| IMPORTANT :
| On utilise uniquement les colonnes réellement utilisées :
|
| id_paiement
| reference_transaction
| reference_commande
| methode
| telephone_client
| montant
| statut
| date_paiement
|
| On ne demande PAS "mode_paiement".
|--------------------------------------------------------------------------
*/

try {

    $stmtPaiement = $pdo->prepare("
        SELECT
            id_paiement,
            reference_transaction,
            reference_commande,
            methode,
            telephone_client,
            montant,
            statut,
            date_paiement
        FROM paiements
        WHERE id_commande = ?
        ORDER BY id_paiement DESC
        LIMIT 1
    ");

    $stmtPaiement->execute([
        $id_commande
    ]);

    $paiement_existant = $stmtPaiement->fetch();

} catch (PDOException $e) {

    die('
        <div style="
            max-width:600px;
            margin:50px auto;
            padding:25px;
            font-family:Arial;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
        ">

            <h2>❌ Erreur de paiement</h2>

            <p>
                Impossible de vérifier les paiements existants.
            </p>

        </div>
    ');
}


/*
|--------------------------------------------------------------------------
| 9. RÉFÉRENCE DE COMMANDE
|--------------------------------------------------------------------------
*/

$reference_commande = 'CMD_'
    . $id_commande
    . '_'
    . date('YmdHis')
    . '_'
    . random_int(1000, 9999);


/*
|--------------------------------------------------------------------------
| 10. TRAITEMENT DU FORMULAIRE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !$paiement_existant
) {


    /*
    |--------------------------------------------------------------------------
    | ID commande envoyé par le formulaire
    |--------------------------------------------------------------------------
    */

    $id_commande_post = filter_input(
        INPUT_POST,
        'id_commande',
        FILTER_VALIDATE_INT
    );


    /*
    |--------------------------------------------------------------------------
    | Téléphone
    |--------------------------------------------------------------------------
    */

    $telephone_client = trim(
        $_POST['telephone_client'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Référence transaction
    |--------------------------------------------------------------------------
    */

    $reference_transaction = trim(
        $_POST['reference_transaction'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | 10.1 Vérifier l'ID de commande
    |--------------------------------------------------------------------------
    */

    if (
        !$id_commande_post
        || (int) $id_commande_post !== (int) $id_commande
    ) {

        $erreur = "Commande invalide.";

    }


    /*
    |--------------------------------------------------------------------------
    | 10.2 Vérifier le téléphone
    |--------------------------------------------------------------------------
    */

    elseif ($telephone_client === '') {

        $erreur =
            "Veuillez saisir votre numéro Orange Money.";

    }


    /*
    |--------------------------------------------------------------------------
    | 10.3 Vérifier la référence
    |--------------------------------------------------------------------------
    */

    elseif ($reference_transaction === '') {

        $erreur =
            "Veuillez saisir la référence de votre transaction Orange Money.";

    }


    /*
    |--------------------------------------------------------------------------
    | 10.4 Vérifier la longueur du téléphone
    |--------------------------------------------------------------------------
    */

    elseif (
        strlen($telephone_client) < 8
        || strlen($telephone_client) > 20
    ) {

        $erreur =
            "Le numéro Orange Money semble incorrect.";

    }


    /*
    |--------------------------------------------------------------------------
    | 10.5 Vérifier la longueur de la référence
    |--------------------------------------------------------------------------
    */

    elseif (strlen($reference_transaction) > 100) {

        $erreur =
            "La référence de transaction est trop longue.";

    }


    /*
    |--------------------------------------------------------------------------
    | 10.6 ENREGISTRER LE PAIEMENT
    |--------------------------------------------------------------------------
    */

    else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Vérifier une nouvelle fois si une référence existe
            |--------------------------------------------------------------------------
            */

            $verification = $pdo->prepare("
                SELECT id_paiement
                FROM paiements
                WHERE reference_transaction = ?
                LIMIT 1
            ");

            $verification->execute([
                $reference_transaction
            ]);


            if ($verification->fetch()) {

                $pdo->rollBack();

                $erreur =
                    "Cette référence de transaction a déjà été utilisée.";

            } else {


                /*
                |--------------------------------------------------------------------------
                | Vérifier qu'aucun paiement n'a été enregistré
                |--------------------------------------------------------------------------
                */

                $verificationCommande = $pdo->prepare("
                    SELECT id_paiement
                    FROM paiements
                    WHERE id_commande = ?
                    LIMIT 1
                ");

                $verificationCommande->execute([
                    $id_commande
                ]);


                if ($verificationCommande->fetch()) {

                    $pdo->rollBack();

                    $erreur =
                        "Un paiement existe déjà pour cette commande.";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | INSERTION DU PAIEMENT
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        INSERT INTO paiements
                        (
                            id_commande,
                            reference_commande,
                            reference_transaction,
                            montant,
                            methode,
                            telephone_client,
                            statut,
                            date_paiement
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            NOW()
                        )
                    ");


                    $stmt->execute([

                        $id_commande,

                        $reference_commande,

                        $reference_transaction,

                        $montant,

                        'orange_money',

                        $telephone_client,

                        'en_attente'

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Mettre la commande en attente
                    |--------------------------------------------------------------------------
                    */

                    $updateCommande = $pdo->prepare("
                        UPDATE commandes
                        SET statut = ?
                        WHERE id_commande = ?
                    ");

                    $updateCommande->execute([
                        'en_attente',
                        $id_commande
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Valider la transaction
                    |--------------------------------------------------------------------------
                    */

                    $pdo->commit();


                    /*
                    |--------------------------------------------------------------------------
                    | Message de succès
                    |--------------------------------------------------------------------------
                    */

                    $succes =
                        "Votre paiement a bien été enregistré.";


                    /*
                    |--------------------------------------------------------------------------
                    | Récupérer le paiement enregistré
                    |--------------------------------------------------------------------------
                    */

                    $stmtPaiement = $pdo->prepare("
                        SELECT
                            id_paiement,
                            reference_transaction,
                            reference_commande,
                            methode,
                            telephone_client,
                            montant,
                            statut,
                            date_paiement
                        FROM paiements
                        WHERE id_commande = ?
                        ORDER BY id_paiement DESC
                        LIMIT 1
                    ");

                    $stmtPaiement->execute([
                        $id_commande
                    ]);

                    $paiement_existant =
                        $stmtPaiement->fetch();
                }
            }


        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | Message d'erreur
            |--------------------------------------------------------------------------
            */

            $erreur =
                "Erreur lors de l'enregistrement du paiement.";

            /*
            | Pour le développement, tu peux temporairement utiliser :
            |
            | $erreur .= " " . $e->getMessage();
            |
            */
        }
    }
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

    <title>Paiement Orange Money</title>


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

            color: #1f2937;
        }


        .container {

            width: 100%;

            max-width: 650px;

            margin: 50px auto;

            padding: 20px;
        }


        .card {

            background: white;

            border-radius: 16px;

            padding: 30px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, .08);
        }


        h1 {

            text-align: center;

            margin-top: 0;

            font-size: 28px;
        }


        .commande {

            background: #f3f4f6;

            padding: 15px;

            border-radius: 10px;

            text-align: center;

            margin-bottom: 20px;
        }


        .commande strong {

            font-size: 18px;
        }


        .orange-box {

            background: #ff7900;

            color: white;

            padding: 25px;

            border-radius: 14px;

            text-align: center;

            margin: 25px 0;
        }


        .orange-box .title {

            font-size: 18px;

            margin-bottom: 10px;
        }


        .numero {

            font-size: 28px;

            font-weight: bold;

            letter-spacing: 1px;
        }


        .montant {

            background: #fff7ed;

            border: 1px solid #fed7aa;

            border-radius: 12px;

            padding: 20px;

            text-align: center;

            margin-bottom: 25px;
        }


        .montant-label {

            color: #6b7280;

            margin-bottom: 8px;
        }


        .montant-value {

            font-size: 30px;

            font-weight: bold;

            color: #ea580c;
        }


        .instructions {

            background: #f9fafb;

            border: 1px solid #e5e7eb;

            border-radius: 12px;

            padding: 20px;

            margin-bottom: 25px;
        }


        .instructions h2 {

            margin-top: 0;

            font-size: 18px;
        }


        .instructions li {

            margin-bottom: 10px;

            line-height: 1.5;
        }


        .reference {

            background: #f3f4f6;

            padding: 12px;

            border-radius: 8px;

            text-align: center;

            margin-bottom: 25px;

            font-weight: bold;

            word-break: break-word;
        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;
        }


        input {

            width: 100%;

            padding: 13px 15px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 16px;

            margin-bottom: 18px;

            outline: none;
        }


        input:focus {

            border-color: #ff7900;

            box-shadow:
                0 0 0 3px
                rgba(255, 121, 0, .12);
        }


        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 9px;

            background: #ff7900;

            color: white;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;
        }


        button:hover {

            background: #e86d00;
        }


        .erreur {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;

            line-height: 1.5;
        }


        .succes {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;

            padding: 18px;

            border-radius: 8px;

            margin-bottom: 20px;

            line-height: 1.6;
        }


        .warning {

            background: #fffbeb;

            color: #92400e;

            border: 1px solid #fde68a;

            padding: 15px;

            border-radius: 8px;

            margin-top: 20px;

            line-height: 1.5;
        }


        .deja-paye {

            background: #dcfce7;

            color: #166534;

            padding: 20px;

            border-radius: 10px;

            margin-top: 20px;

            line-height: 1.6;
        }


        .info-paiement {

            background: #f3f4f6;

            padding: 15px;

            border-radius: 10px;

            margin-top: 15px;
        }


        .statut {

            display: inline-block;

            padding: 6px 12px;

            background: #fef3c7;

            color: #92400e;

            border-radius: 20px;

            font-weight: bold;

            margin-top: 5px;
        }


        @media (max-width: 600px) {

            .container {

                margin: 20px auto;

                padding: 10px;
            }


            .card {

                padding: 20px;
            }


            h1 {

                font-size: 24px;
            }


            .numero {

                font-size: 23px;
            }


            .montant-value {

                font-size: 25px;
            }

        }

    </style>

</head>


<body>


<div class="container">

    <div class="card">


        <h1>
            💳 Paiement Orange Money
        </h1>


        <!-- =====================================================
             COMMANDE
        ====================================================== -->

        <div class="commande">

            Commande :

            <br>

            <strong>
                <?= htmlspecialchars(
                    $numero_commande,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

        </div>


        <!-- =====================================================
             MESSAGE ERREUR
        ====================================================== -->

        <?php if ($erreur): ?>

            <div class="erreur">

                <?= htmlspecialchars(
                    $erreur,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             MESSAGE SUCCÈS
        ====================================================== -->

        <?php if ($succes): ?>

            <div class="succes">

                <strong>
                    ✅ Paiement enregistré
                </strong>

                <br><br>

                Votre demande de paiement a bien été
                enregistrée.

                <br><br>

                Montant :

                <strong>

                    <?= number_format(
                        $montant,
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </strong>

                <br><br>

                Votre paiement est maintenant :

                <br>

                <span class="statut">
                    ⏳ En attente de vérification
                </span>

                <br><br>

                Référence de transaction :

                <strong>

                    <?= htmlspecialchars(
                        $reference_transaction,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </strong>

            </div>


        <!-- =====================================================
             PAIEMENT EXISTANT
        ====================================================== -->

        <?php elseif ($paiement_existant): ?>

            <div class="deja-paye">

                <strong>
                    ✅ Paiement déjà enregistré
                </strong>

                <br><br>

                Cette commande possède déjà une demande
                de paiement.

                <br><br>

                Montant :

                <strong>

                    <?= number_format(
                        (float) $paiement_existant['montant'],
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </strong>

                <br><br>

                Statut :

                <br>

                <span class="statut">

                    <?= htmlspecialchars(
                        $paiement_existant['statut'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </span>

                <br><br>

                Référence de transaction :

                <strong>

                    <?= htmlspecialchars(
                        $paiement_existant[
                            'reference_transaction'
                        ],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </strong>

                <?php if (
                    !empty(
                        $paiement_existant['telephone_client']
                    )
                ): ?>

                    <br><br>

                    Numéro Orange Money :

                    <strong>

                        <?= htmlspecialchars(
                            $paiement_existant[
                                'telephone_client'
                            ],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </strong>

                <?php endif; ?>

            </div>


        <!-- =====================================================
             FORMULAIRE DE PAIEMENT
        ====================================================== -->

        <?php else: ?>


            <!-- NUMÉRO ORANGE MONEY -->

            <div class="orange-box">

                <div class="title">

                    Envoyez le paiement à ce numéro

                </div>


                <div class="numero">

                    <?= htmlspecialchars(
                        $numero_orange,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            </div>


            <!-- MONTANT -->

            <div class="montant">

                <div class="montant-label">

                    Montant de votre commande

                </div>


                <div class="montant-value">

                    <?= number_format(
                        $montant,
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </div>

            </div>


            <!-- INSTRUCTIONS -->

            <div class="instructions">

                <h2>
                    📱 Comment payer ?
                </h2>


                <ol>

                    <li>
                        Ouvrez votre compte Orange Money.
                    </li>


                    <li>

                        Envoyez exactement

                        <strong>

                            <?= number_format(
                                $montant,
                                0,
                                ',',
                                ' '
                            ) ?>

                            GNF

                        </strong>

                        au numéro :

                        <strong>

                            <?= htmlspecialchars(
                                $numero_orange,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </strong>

                    </li>


                    <li>

                        Après le transfert,
                        récupérez la référence
                        de la transaction.

                    </li>


                    <li>

                        Saisissez votre numéro Orange Money
                        et la référence de transaction
                        ci-dessous.

                    </li>

                </ol>

            </div>


            <!-- RÉFÉRENCE COMMANDE -->

            <div class="reference">

                Référence de commande :

                <br><br>

                <?= htmlspecialchars(
                    $reference_commande,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>


            <!-- FORMULAIRE -->

            <form method="POST">


                <input
                    type="hidden"
                    name="id_commande"
                    value="<?= (int) $id_commande ?>"
                >


                <label for="telephone_client">

                    📱 Votre numéro Orange Money

                </label>


                <input
                    type="tel"
                    id="telephone_client"
                    name="telephone_client"
                    placeholder="+224 6XXXXXXXX"
                    maxlength="20"
                    autocomplete="tel"
                    required
                >


                <label for="reference_transaction">

                    🔢 Référence de la transaction

                </label>


                <input
                    type="text"
                    id="reference_transaction"
                    name="reference_transaction"
                    placeholder="Exemple : TXN123456789"
                    maxlength="100"
                    required
                >


                <button type="submit">

                    ✅ Confirmer mon paiement

                </button>

            </form>


            <!-- AVERTISSEMENT -->

            <div class="warning">

                <strong>
                    ⚠️ Important
                </strong>

                <br><br>

                Votre paiement sera vérifié avant que
                la commande soit définitivement validée.

                <br><br>

                Ne communiquez jamais votre code secret
                Orange Money.

            </div>


        <?php endif; ?>


    </div>

</div>


</body>

</html>

