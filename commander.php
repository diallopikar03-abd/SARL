<?php

session_start();

require 'db.php';

header('Content-Type: text/html; charset=utf-8');


/*
|--------------------------------------------------------------------------
| CONFIGURATION PDO
|--------------------------------------------------------------------------
*/

try {

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception(
            "La connexion à la base de données est indisponible."
        );
    }

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (Throwable $e) {

    die(
        '<div style="
            max-width:700px;
            margin:50px auto;
            padding:20px;
            background:#fee2e2;
            color:#991b1b;
            border-radius:10px;
            font-family:Arial,sans-serif;
        ">
            <h3>Erreur de connexion</h3>
            <p>' .
            htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            ) .
            '</p>
        </div>'
    );
}


/*
|--------------------------------------------------------------------------
| 1. VÉRIFIER LE PANIER
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['panier']) ||
    empty($_SESSION['panier'])
) {

    header('Location: produits.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$erreurs = [];

$nom = '';
$prenom = '';
$telephone = '';
$email = '';
$adresse = '';

$produits_panier = [];
$montant_total = 0;


/*
|--------------------------------------------------------------------------
| 2. RÉCUPÉRER LES PRODUITS DU PANIER
|--------------------------------------------------------------------------
*/

try {

    foreach ($_SESSION['panier'] as $id_boisson => $quantite) {

        $id_boisson = (int) $id_boisson;
        $quantite = (int) $quantite;

        if (
            $id_boisson <= 0 ||
            $quantite <= 0
        ) {
            continue;
        }


        $stmt = $pdo->prepare("
            SELECT
                id_boisson,
                nom,
                prix,
                stock,
                image
            FROM boissons
            WHERE id_boisson = :id_boisson
            LIMIT 1
        ");

        $stmt->execute([
            ':id_boisson' => $id_boisson
        ]);

        $produit = $stmt->fetch();


        /*
        | Produit inexistant
        */

        if (!$produit) {

            $erreurs[] =
                "Le produit numéro " .
                $id_boisson .
                " n'existe plus.";

            continue;
        }


        /*
        | Vérification du stock
        */

        if (
            (int) $produit['stock'] <
            $quantite
        ) {

            $erreurs[] =
                "Le produit « " .
                $produit['nom'] .
                " » ne possède pas suffisamment de stock.";

            continue;
        }


        /*
        | Calcul du sous-total
        */

        $prix = (float) $produit['prix'];

        $sous_total =
            $prix * $quantite;

        $produit['quantite'] =
            $quantite;

        $produit['sous_total'] =
            $sous_total;

        $produits_panier[] =
            $produit;

        $montant_total +=
            $sous_total;
    }


    /*
    |--------------------------------------------------------------------------
    | PANIER INVALIDE
    |--------------------------------------------------------------------------
    */

    if (empty($produits_panier)) {

        $erreurs[] =
            "Votre panier ne contient aucun produit valide.";
    }


    /*
    |--------------------------------------------------------------------------
    | 3. TRAITEMENT DU FORMULAIRE
    |--------------------------------------------------------------------------
    */

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
    ) {

        /*
        | Récupération des informations client
        */

        $nom =
            trim($_POST['nom'] ?? '');

        $prenom =
            trim($_POST['prenom'] ?? '');

        $telephone =
            trim($_POST['telephone'] ?? '');

        $email =
            trim($_POST['email'] ?? '');

        $adresse =
            trim($_POST['adresse'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | VALIDATION DU NOM
        |--------------------------------------------------------------------------
        */

        if ($nom === '') {

            $erreurs[] =
                "Le nom est obligatoire.";

        } elseif (
            mb_strlen($nom) > 100
        ) {

            $erreurs[] =
                "Le nom ne doit pas dépasser 100 caractères.";
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATION DU PRÉNOM
        |--------------------------------------------------------------------------
        */

        if ($prenom === '') {

            $erreurs[] =
                "Le prénom est obligatoire.";

        } elseif (
            mb_strlen($prenom) > 100
        ) {

            $erreurs[] =
                "Le prénom ne doit pas dépasser 100 caractères.";
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATION DU TÉLÉPHONE
        |--------------------------------------------------------------------------
        */

        if ($telephone === '') {

            $erreurs[] =
                "Le numéro de téléphone est obligatoire.";

        } elseif (
            mb_strlen($telephone) < 6
        ) {

            $erreurs[] =
                "Le numéro de téléphone est trop court.";

        } elseif (
            mb_strlen($telephone) > 30
        ) {

            $erreurs[] =
                "Le numéro de téléphone ne doit pas dépasser 30 caractères.";
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATION EMAIL
        |--------------------------------------------------------------------------
        */

        if ($email === '') {

            $erreurs[] =
                "L'adresse email est obligatoire.";

        } elseif (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $erreurs[] =
                "Veuillez saisir une adresse email valide.";

        } elseif (
            mb_strlen($email) > 150
        ) {

            $erreurs[] =
                "L'adresse email ne doit pas dépasser 150 caractères.";
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATION ADRESSE
        |--------------------------------------------------------------------------
        */

        if ($adresse === '') {

            $erreurs[] =
                "L'adresse de livraison est obligatoire.";
        }


        /*
        |--------------------------------------------------------------------------
        | 4. ENREGISTRER LA COMMANDE
        |--------------------------------------------------------------------------
        */

        if (empty($erreurs)) {

            try {

                /*
                | Début transaction
                */

                $pdo->beginTransaction();


                /*
                |--------------------------------------------------------------------------
                | 5. GÉNÉRER UN NUMÉRO DE COMMANDE UNIQUE
                |--------------------------------------------------------------------------
                */

                do {

                    $numero_commande =
                        'CMD-' .
                        date('YmdHis') .
                        '-' .
                        strtoupper(
                            bin2hex(
                                random_bytes(3)
                            )
                        );


                    $verification =
                        $pdo->prepare("
                            SELECT id_commande
                            FROM commandes
                            WHERE numero_commande = :numero_commande
                            LIMIT 1
                        ");

                    $verification->execute([
                        ':numero_commande' =>
                            $numero_commande
                    ]);

                    $numero_existe =
                        $verification->fetch();

                } while ($numero_existe);


                /*
                |--------------------------------------------------------------------------
                | 6. INSERTION DE LA COMMANDE
                |
                | IMPORTANT :
                | statut n'est pas envoyé.
                | MySQL utilise automatiquement sa valeur par défaut.
                |
                | id_utilisateur n'est pas envoyé car
                | le client peut commander sans compte.
                |--------------------------------------------------------------------------
                */

                $sql = "
                    INSERT INTO commandes
                    (
                        numero_commande,
                        montant_total,
                        nom_client,
                        prenom_client,
                        telephone,
                        email,
                        adresse_livraison
                    )
                    VALUES
                    (
                        :numero_commande,
                        :montant_total,
                        :nom_client,
                        :prenom_client,
                        :telephone,
                        :email,
                        :adresse_livraison
                    )
                ";


                $stmt =
                    $pdo->prepare($sql);


                $stmt->execute([

                    ':numero_commande' =>
                        $numero_commande,

                    ':montant_total' =>
                        number_format(
                            $montant_total,
                            2,
                            '.',
                            ''
                        ),

                    ':nom_client' =>
                        $nom,

                    ':prenom_client' =>
                        $prenom,

                    ':telephone' =>
                        $telephone,

                    ':email' =>
                        $email,

                    ':adresse_livraison' =>
                        $adresse
                ]);


                /*
                |--------------------------------------------------------------------------
                | 7. RÉCUPÉRER L'ID
                |--------------------------------------------------------------------------
                */

                $id_commande =
                    (int) $pdo->lastInsertId();


                if (
                    $id_commande <= 0
                ) {

                    throw new Exception(
                        "Impossible de récupérer l'identifiant de la commande."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | 8. VÉRIFIER QUE LA COMMANDE EXISTE
                |--------------------------------------------------------------------------
                */

                $verification_commande =
                    $pdo->prepare("
                        SELECT
                            id_commande,
                            numero_commande,
                            montant_total,
                            statut
                        FROM commandes
                        WHERE id_commande = :id_commande
                        LIMIT 1
                    ");


                $verification_commande->execute([
                    ':id_commande' =>
                        $id_commande
                ]);


                $commande =
                    $verification_commande->fetch();


                if (!$commande) {

                    throw new Exception(
                        "La commande n'a pas pu être retrouvée après son enregistrement."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | 9. VÉRIFIER ET DIMINUER LE STOCK
                |--------------------------------------------------------------------------
                */

                foreach (
                    $produits_panier
                    as $produit
                ) {

                    $id_boisson =
                        (int) $produit['id_boisson'];

                    $quantite =
                        (int) $produit['quantite'];


                    /*
                    | Verrouillage du produit
                    */

                    $stock_stmt =
                        $pdo->prepare("
                            SELECT stock
                            FROM boissons
                            WHERE id_boisson = :id_boisson
                            FOR UPDATE
                        ");


                    $stock_stmt->execute([
                        ':id_boisson' =>
                            $id_boisson
                    ]);


                    $stock_actuel =
                        $stock_stmt->fetchColumn();


                    if (
                        $stock_actuel === false
                    ) {

                        throw new Exception(
                            "Le produit « " .
                            $produit['nom'] .
                            " » n'existe plus."
                        );
                    }


                    /*
                    | Vérification finale
                    */

                    if (
                        (int) $stock_actuel <
                        $quantite
                    ) {

                        throw new Exception(
                            "Le stock du produit « " .
                            $produit['nom'] .
                            " » est insuffisant."
                        );
                    }


                    /*
                    | Diminution du stock
                    */

                    $update_stock =
                        $pdo->prepare("
                            UPDATE boissons
                            SET stock = stock - :quantite
                            WHERE id_boisson = :id_boisson
                        ");


                    $update_stock->execute([

                        ':quantite' =>
                            $quantite,

                        ':id_boisson' =>
                            $id_boisson
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | 10. VALIDER LA TRANSACTION
                |--------------------------------------------------------------------------
                */

                $pdo->commit();


                /*
                |--------------------------------------------------------------------------
                | 11. ENREGISTRER LA COMMANDE EN SESSION
                |--------------------------------------------------------------------------
                */

                $_SESSION['id_commande'] =
                    $id_commande;

                $_SESSION['numero_commande'] =
                    $numero_commande;


                /*
                |--------------------------------------------------------------------------
                | 12. VIDER LE PANIER
                |--------------------------------------------------------------------------
                */

                $_SESSION['panier'] = [];


                /*
                |--------------------------------------------------------------------------
                | 13. REDIRECTION VERS PAIEMENT
                |--------------------------------------------------------------------------
                */

                header(
                    'Location: payer.php?id_commande=' .
                    $id_commande
                );

                exit;


            } catch (Throwable $e) {

                if (
                    $pdo->inTransaction()
                ) {

                    $pdo->rollBack();
                }


                $erreurs[] =
                    "Erreur lors de l'enregistrement de la commande : " .
                    $e->getMessage();
            }
        }
    }

} catch (Throwable $e) {

    $erreurs[] =
        "Une erreur est survenue : " .
        $e->getMessage();
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

    <title>
        Finaliser la commande - DrinkShop
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Inter,
                Arial,
                Helvetica,
                sans-serif;

            background:
                #f6f7fb;

            color:
                #111827;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .top-header {

            background:
                #ffffff;

            border-bottom:
                1px solid #e5e7eb;

            height:
                72px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            padding:
                0 5%;

            position:
                sticky;

            top:
                0;

            z-index:
                100;
        }


        .logo {

            font-size:
                24px;

            font-weight:
                800;

            color:
                #111827;
        }


        .logo span {

            color:
                #f97316;
        }


        .secure {

            color:
                #6b7280;

            font-size:
                14px;

            display:
                flex;

            align-items:
                center;

            gap:
                7px;
        }


        /* =====================================================
           CONTENEUR
        ===================================================== */

        .container {

            width:
                92%;

            max-width:
                1180px;

            margin:
                45px auto;
        }


        .page-heading {

            margin-bottom:
                30px;
        }


        .page-heading h1 {

            margin:
                0 0 8px;

            font-size:
                32px;

            font-weight:
                800;

            color:
                #111827;
        }


        .page-heading p {

            margin:
                0;

            color:
                #6b7280;

            font-size:
                15px;
        }


        /* =====================================================
           ERREURS
        ===================================================== */

        .erreurs {

            background:
                #fff1f2;

            border:
                1px solid #fecdd3;

            color:
                #9f1239;

            border-radius:
                12px;

            padding:
                18px 20px;

            margin-bottom:
                25px;
        }


        .erreurs strong {

            display:
                block;

            margin-bottom:
                8px;
        }


        .erreurs ul {

            margin:
                0;

            padding-left:
                20px;
        }


        .erreurs li {

            margin-bottom:
                5px;
        }


        /* =====================================================
           LAYOUT
        ===================================================== */

        .checkout {

            display:
                grid;

            grid-template-columns:
                minmax(0, 1fr) 410px;

            gap:
                28px;

            align-items:
                start;
        }


        .card {

            background:
                #ffffff;

            border:
                1px solid #e5e7eb;

            border-radius:
                16px;

            box-shadow:
                0 8px 30px rgba(
                    15,
                    23,
                    42,
                    0.06
                );

            overflow:
                hidden;
        }


        .card-header {

            padding:
                24px 26px;

            border-bottom:
                1px solid #eef0f3;
        }


        .card-header h2 {

            margin:
                0 0 5px;

            font-size:
                20px;
        }


        .card-header p {

            margin:
                0;

            color:
                #6b7280;

            font-size:
                13px;
        }


        .card-body {

            padding:
                26px;
        }


        /* =====================================================
           FORMULAIRE
        ===================================================== */

        .form-grid {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                18px;
        }


        .form-group {

            margin-bottom:
                18px;
        }


        .form-group.full {

            grid-column:
                1 / -1;
        }


        label {

            display:
                block;

            margin-bottom:
                8px;

            font-size:
                14px;

            font-weight:
                700;

            color:
                #374151;
        }


        .required {

            color:
                #ef4444;
        }


        input,
        textarea {

            width:
                100%;

            border:
                1px solid #d1d5db;

            border-radius:
                9px;

            padding:
                13px 14px;

            font-size:
                15px;

            font-family:
                inherit;

            color:
                #111827;

            background:
                #ffffff;

            outline:
                none;

            transition:
                0.2s;
        }


        input {

            height:
                48px;
        }


        textarea {

            min-height:
                115px;

            resize:
                vertical;
        }


        input:focus,
        textarea:focus {

            border-color:
                #f97316;

            box-shadow:
                0 0 0 3px
                rgba(
                    249,
                    115,
                    22,
                    0.10
                );
        }


        /* =====================================================
           BOUTON
        ===================================================== */

        .btn {

            width:
                100%;

            height:
                52px;

            border:
                none;

            border-radius:
                10px;

            background:
                #f97316;

            color:
                #ffffff;

            font-size:
                15px;

            font-weight:
                700;

            cursor:
                pointer;

            transition:
                0.2s;

            margin-top:
                5px;
        }


        .btn:hover {

            background:
                #ea580c;

            transform:
                translateY(-1px);
        }


        /* =====================================================
           RÉSUMÉ PANIER
        ===================================================== */

        .summary-card {

            position:
                sticky;

            top:
                95px;
        }


        .summary-body {

            padding:
                10px 22px 22px;
        }


        .produit {

            display:
                flex;

            align-items:
                center;

            gap:
                14px;

            padding:
                17px 0;

            border-bottom:
                1px solid #eef0f3;
        }


        .produit:last-child {

            border-bottom:
                none;
        }


        /* =====================================================
           IMAGE PRODUIT
        ===================================================== */

        .image-wrapper {

            width:
                78px;

            height:
                78px;

            flex-shrink:
                0;

            border-radius:
                12px;

            overflow:
                hidden;

            background:
                #f3f4f6;

            border:
                1px solid #e5e7eb;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;
        }


        .image-wrapper img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

            display:
                block;
        }


        .image-placeholder {

            font-size:
                28px;

            color:
                #9ca3af;
        }


        /* =====================================================
           INFORMATIONS PRODUIT
        ===================================================== */

        .produit-info {

            flex:
                1;

            min-width:
                0;
        }


        .produit-nom {

            font-size:
                15px;

            font-weight:
                700;

            color:
                #111827;

            margin-bottom:
                7px;

            overflow:
                hidden;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }


        .produit-details {

            color:
                #6b7280;

            font-size:
                13px;

            line-height:
                1.5;
        }


        .produit-total {

            font-size:
                14px;

            font-weight:
                800;

            color:
                #111827;

            white-space:
                nowrap;
        }


        /* =====================================================
           TOTAL
        ===================================================== */

        .total-box {

            margin-top:
                8px;

            padding:
                20px 0 4px;

            border-top:
                2px solid #111827;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;
        }


        .total-label {

            font-size:
                16px;

            font-weight:
                700;
        }


        .total-price {

            font-size:
                22px;

            font-weight:
                800;

            color:
                #f97316;
        }


        /* =====================================================
           INFO PAIEMENT
        ===================================================== */

        .info-paiement {

            margin:
                20px 0 0;

            padding:
                15px;

            border-radius:
                10px;

            background:
                #fff7ed;

            border:
                1px solid #fed7aa;

            color:
                #9a3412;

            font-size:
                13px;

            line-height:
                1.6;
        }


        .info-paiement strong {

            display:
                block;

            margin-bottom:
                4px;
        }


        /* =====================================================
           RESPONSIVE TABLETTE
        ===================================================== */

        @media (max-width: 950px) {

            .checkout {

                grid-template-columns:
                    1fr;
            }


            .summary-card {

                position:
                    static;
            }

        }


        /* =====================================================
           RESPONSIVE MOBILE
        ===================================================== */

        @media (max-width: 600px) {

            .top-header {

                padding:
                    0 4%;

                height:
                    64px;
            }


            .logo {

                font-size:
                    20px;
            }


            .secure {

                font-size:
                    12px;
            }


            .container {

                width:
                    94%;

                margin:
                    25px auto;
            }


            .page-heading h1 {

                font-size:
                    25px;
            }


            .form-grid {

                grid-template-columns:
                    1fr;

                gap:
                    0;
            }


            .form-group.full {

                grid-column:
                    auto;
            }


            .card-body {

                padding:
                    20px;
            }


            .card-header {

                padding:
                    20px;
            }


            .image-wrapper {

                width:
                    65px;

                height:
                    65px;
            }


            .produit {

                gap:
                    10px;
            }


            .produit-nom {

                font-size:
                    14px;
            }


            .produit-total {

                font-size:
                    13px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<header class="top-header">

    <div class="logo">
        Drink<span>Shop</span>
    </div>

    <div class="secure">
        🔒 Commande sécurisée
    </div>

</header>


<!-- =========================================================
     CONTENU
========================================================= -->

<div class="container">


    <div class="page-heading">

        <h1>
            Finaliser votre commande
        </h1>

        <p>
            Renseignez vos informations de livraison
            pour continuer vers le paiement.
        </p>

    </div>


    <!-- =====================================================
         ERREURS
    ====================================================== -->

    <?php if (!empty($erreurs)): ?>

        <div class="erreurs">

            <strong>
                ⚠️ Vérifiez les informations suivantes :
            </strong>

            <ul>

                <?php foreach ($erreurs as $erreur): ?>

                    <li>

                        <?= htmlspecialchars(
                            $erreur,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <div class="checkout">


        <!-- =================================================
             FORMULAIRE CLIENT
        ================================================== -->

        <div class="card">

            <div class="card-header">

                <h2>
                    Informations de livraison
                </h2>

                <p>
                    Ces informations seront enregistrées
                    avec votre commande.
                </p>

            </div>


            <div class="card-body">

                <form
                    method="POST"
                    action=""
                    autocomplete="on"
                >


                    <div class="form-grid">


                        <!-- NOM -->

                        <div class="form-group">

                            <label for="nom">

                                Nom
                                <span class="required">*</span>

                            </label>

                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                maxlength="100"
                                placeholder="Votre nom"
                                value="<?= htmlspecialchars(
                                    $nom,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>


                        <!-- PRÉNOM -->

                        <div class="form-group">

                            <label for="prenom">

                                Prénom
                                <span class="required">*</span>

                            </label>

                            <input
                                type="text"
                                id="prenom"
                                name="prenom"
                                maxlength="100"
                                placeholder="Votre prénom"
                                value="<?= htmlspecialchars(
                                    $prenom,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>


                        <!-- TELEPHONE -->

                        <div class="form-group">

                            <label for="telephone">

                                Téléphone
                                <span class="required">*</span>

                            </label>

                            <input
                                type="tel"
                                id="telephone"
                                name="telephone"
                                maxlength="30"
                                placeholder="+224 6XX XX XX XX"
                                value="<?= htmlspecialchars(
                                    $telephone,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>


                        <!-- EMAIL -->

                        <div class="form-group">

                            <label for="email">

                                Adresse email
                                <span class="required">*</span>

                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                maxlength="150"
                                placeholder="exemple@email.com"
                                value="<?= htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>


                        <!-- ADRESSE -->

                        <div class="form-group full">

                            <label for="adresse">

                                Adresse de livraison
                                <span class="required">*</span>

                            </label>

                            <textarea
                                id="adresse"
                                name="adresse"
                                placeholder="Quartier, rue, commune, ville..."
                                required
                            ><?= htmlspecialchars(
                                $adresse,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>

                        </div>


                    </div>


                    <!-- BOUTON -->

                    <button
                        type="submit"
                        class="btn"
                    >

                        Continuer vers le paiement
                        →
                        
                    </button>


                </form>

            </div>

        </div>


        <!-- =================================================
             RÉSUMÉ COMMANDE
        ================================================== -->

        <div class="card summary-card">


            <div class="card-header">

                <h2>
                    Votre commande
                </h2>

                <p>

                    <?= count($produits_panier) ?>

                    produit<?= count($produits_panier) > 1 ? 's' : '' ?>

                </p>

            </div>


            <div class="summary-body">


                <?php foreach (
                    $produits_panier
                    as $produit
                ): ?>


                    <?php

                    /*
                    |--------------------------------------------------------------------------
                    | GESTION DE L'IMAGE
                    |--------------------------------------------------------------------------
                    |
                    | Si la base contient :
                    | images/boisson.jpg
                    | le chemin est utilisé directement.
                    |
                    | Si aucun chemin n'est fourni,
                    | image.png est utilisé.
                    |--------------------------------------------------------------------------
                    */

                    $image =
                        trim(
                            (string)
                            ($produit['image'] ?? '')
                        );


                    if ($image === '') {

                        $image =
                            'image.png';
                    }

                    ?>


                    <div class="produit">


                        <!-- IMAGE -->

                        <div class="image-wrapper">

                            <img
                                src="<?= htmlspecialchars(
                                    $image,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $produit['nom'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >

                            <div
                                class="image-placeholder"
                                style="display:none;"
                            >
                                🥤
                            </div>

                        </div>


                        <!-- INFORMATIONS -->

                        <div class="produit-info">


                            <div class="produit-nom">

                                <?= htmlspecialchars(
                                    $produit['nom'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </div>


                            <div class="produit-details">

                                <?= (int)
                                    $produit['quantite']
                                ?>

                                ×

                                <?= number_format(
                                    (float)
                                    $produit['prix'],
                                    0,
                                    ',',
                                    ' '
                                ) ?>

                                FCFA

                            </div>


                        </div>


                        <!-- SOUS-TOTAL -->

                        <div class="produit-total">

                            <?= number_format(
                                (float)
                                $produit['sous_total'],
                                0,
                                ',',
                                ' '
                            ) ?>

                            FCFA

                        </div>


                    </div>


                <?php endforeach; ?>


                <!-- TOTAL -->

                <div class="total-box">

                    <span class="total-label">

                        Total

                    </span>

                    <span class="total-price">

                        <?= number_format(
                            (float)
                            $montant_total,
                            0,
                            ',',
                            ' '
                        ) ?>

                        FCFA

                    </span>

                </div>


                <!-- INFORMATION PAIEMENT -->

                <div class="info-paiement">

                    <strong>
                        🔒 Paiement sécurisé
                    </strong>

                    Après validation de vos informations,
                    vous serez redirigé vers la page de paiement
                    pour régler votre commande.

                </div>


            </div>

        </div>


    </div>

</div>


</body>

</html>

