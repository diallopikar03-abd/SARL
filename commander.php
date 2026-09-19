<?php

session_start();

require 'db.php';

header('Content-Type: text/html; charset=utf-8');


// =====================================================
// 1. VÉRIFIER LE PANIER
// =====================================================

if (empty($_SESSION['panier'])) {

    header("Location: panier.php");

    exit();
}


$panier = $_SESSION['panier'];

$produits = [];

$total_general = 0;


// =====================================================
// 2. RÉCUPÉRER LES PRODUITS DU PANIER
// =====================================================

foreach ($panier as $id_boisson => $quantite) {

    $id_boisson = (int)$id_boisson;

    $quantite = (int)$quantite;


    if ($quantite <= 0) {
        continue;
    }


    $stmt = $pdo->prepare("
        SELECT
            id_boisson,
            nom,
            prix,
            image,
            stock
        FROM boissons
        WHERE id_boisson = ?
        LIMIT 1
    ");

    $stmt->execute([$id_boisson]);

    $boisson = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($boisson) {

        // Vérifier le stock
        if ($quantite > (int)$boisson['stock']) {

            $quantite = (int)$boisson['stock'];

            $_SESSION['panier'][$id_boisson] = $quantite;
        }


        if ($quantite <= 0) {

            unset($_SESSION['panier'][$id_boisson]);

            continue;
        }


        $boisson['quantite'] = $quantite;


        $boisson['sous_total'] =
            (float)$boisson['prix'] * $quantite;


        $total_general +=
            $boisson['sous_total'];


        $produits[] = $boisson;
    }
}


// =====================================================
// 3. VÉRIFIER LE PANIER
// =====================================================

if (
    empty($produits) ||
    $total_general <= 0
) {

    $_SESSION['panier'] = [];

    header("Location: panier.php");

    exit();
}


// =====================================================
// 4. VARIABLES CLIENT
// =====================================================

$nom = '';

$prenom = '';

$telephone = '';

$email = '';

$adresse = '';

$erreurs = [];


// =====================================================
// 5. TRAITEMENT DU FORMULAIRE
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


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


    // -------------------------------------------------
    // Validation
    // -------------------------------------------------

    if ($prenom === '') {

        $erreurs[] =
            "Veuillez saisir votre prénom.";
    }


    if ($nom === '') {

        $erreurs[] =
            "Veuillez saisir votre nom.";
    }


    if ($telephone === '') {

        $erreurs[] =
            "Veuillez saisir votre numéro de téléphone.";
    }


    if ($adresse === '') {

        $erreurs[] =
            "Veuillez saisir votre adresse de livraison.";
    }


    if (
        $email !== '' &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $erreurs[] =
            "L'adresse email est invalide.";
    }


    // =================================================
    // 6. CRÉER LA COMMANDE
    // =================================================

    if (empty($erreurs)) {

        try {

            $pdo->beginTransaction();


            // -----------------------------------------
            // Numéro de commande unique
            // -----------------------------------------

            do {

                $numero_commande =
                    'CMD-' .
                    date('YmdHis') .
                    '-' .
                    random_int(1000, 9999);


                $check = $pdo->prepare("
                    SELECT id_commande
                    FROM commandes
                    WHERE numero_commande = ?
                    LIMIT 1
                ");


                $check->execute([
                    $numero_commande
                ]);


            } while ($check->fetch());


            // -----------------------------------------
            // Client connecté ou invité
            // -----------------------------------------

            $id_utilisateur = null;


            if (
                isset($_SESSION['user_id']) &&
                $_SESSION['user_id'] !== ''
            ) {

                $id_utilisateur =
                    (int)$_SESSION['user_id'];
            }


            // -----------------------------------------
            // Insérer la commande
            // -----------------------------------------

            $stmt = $pdo->prepare("
                INSERT INTO commandes
                (
                    id_utilisateur,
                    numero_commande,
                    montant_total,
                    statut,
                    date_commande,
                    nom_client,
                    prenom_client,
                    telephone,
                    email,
                    adresse_livraison
                )
                VALUES
                (
                    :id_utilisateur,
                    :numero_commande,
                    :montant_total,
                    'en_attente',
                    NOW(),
                    :nom_client,
                    :prenom_client,
                    :telephone,
                    :email,
                    :adresse_livraison
                )
            ");


            $stmt->execute([

                ':id_utilisateur' =>
                    $id_utilisateur,

                ':numero_commande' =>
                    $numero_commande,

                ':montant_total' =>
                    $total_general,

                ':nom_client' =>
                    $nom,

                ':prenom_client' =>
                    $prenom,

                ':telephone' =>
                    $telephone,

                ':email' =>
                    $email !== ''
                        ? $email
                        : null,

                ':adresse_livraison' =>
                    $adresse
            ]);


            // -----------------------------------------
            // ID commande
            // -----------------------------------------

            $id_commande =
                (int)$pdo->lastInsertId();


            // -----------------------------------------
            // Valider
            // -----------------------------------------

            $pdo->commit();


            // -----------------------------------------
            // Mémoriser
            // -----------------------------------------

            $_SESSION['id_commande'] =
                $id_commande;


            $_SESSION['numero_commande'] =
                $numero_commande;


            // -----------------------------------------
            // Vider le panier
            // -----------------------------------------

            $_SESSION['panier'] = [];


            // -----------------------------------------
            // Aller au paiement
            // -----------------------------------------

            header(
                "Location: payer.php?id_commande=" .
                $id_commande
            );

            exit();


        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }


            $erreurs[] =
                "Erreur lors de l'enregistrement de la commande : " .
                $e->getMessage();
        }
    }
}


include 'header.php';


// =====================================================
// NOMBRE D'ARTICLES
// =====================================================

$nombre_articles = 0;


foreach ($produits as $produit) {

    $nombre_articles +=
        (int)$produit['quantite'];
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
    Finaliser ma commande - DrinkShop
</title>

<style>

/* =====================================================
   RESET
===================================================== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}


/* =====================================================
   BODY
===================================================== */

body{

    font-family:
        Inter,
        Arial,
        Helvetica,
        sans-serif;

    background:#f5f7fb;

    color:#1f2937;

    min-height:100vh;
}


/* =====================================================
   CONTENEUR
===================================================== */

.checkout-container{

    max-width:1200px;

    margin:0 auto;

    padding:45px 20px 70px;
}


/* =====================================================
   HEADER PAGE
===================================================== */

.checkout-header{

    margin-bottom:30px;
}


.checkout-header h1{

    font-size:34px;

    color:#111827;

    font-weight:800;

    margin-bottom:8px;
}


.checkout-header p{

    color:#6b7280;

    font-size:15px;

    line-height:1.6;
}


/* =====================================================
   ÉTAPES
===================================================== */

.steps{

    display:flex;

    align-items:center;

    margin-bottom:30px;

    max-width:700px;
}


.step{

    display:flex;

    align-items:center;

    gap:9px;

    color:#9ca3af;

    font-size:13px;

    font-weight:700;

    white-space:nowrap;
}


.step-number{

    width:32px;

    height:32px;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#e5e7eb;

    color:#6b7280;

    font-weight:800;
}


.step.active{

    color:#2563eb;
}


.step.active .step-number{

    background:#2563eb;

    color:#ffffff;

    box-shadow:
        0 5px 15px rgba(37,99,235,.25);
}


.step-line{

    flex:1;

    height:2px;

    background:#e5e7eb;

    margin:0 12px;
}


/* =====================================================
   GRID PRINCIPAL
===================================================== */

.checkout-grid{

    display:grid;

    grid-template-columns:
        minmax(0,1fr)
        380px;

    gap:25px;

    align-items:start;
}


/* =====================================================
   CARTES
===================================================== */

.checkout-card{

    background:#ffffff;

    border:1px solid #e8ebf0;

    border-radius:20px;

    padding:30px;

    box-shadow:
        0 8px 30px rgba(15,23,42,.05);
}


/* =====================================================
   TITRE CARTE
===================================================== */

.card-title{

    display:flex;

    align-items:center;

    gap:12px;

    margin-bottom:25px;
}


.card-title-icon{

    width:40px;

    height:40px;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#eff6ff;

    border-radius:10px;

    font-size:20px;
}


.card-title h2{

    font-size:21px;

    color:#111827;

    font-weight:800;
}


.card-title p{

    color:#9ca3af;

    font-size:12px;

    margin-top:3px;
}


/* =====================================================
   FORMULAIRE
===================================================== */

.form-row{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:15px;
}


.form-group{

    margin-bottom:18px;
}


.form-group label{

    display:block;

    font-size:13px;

    font-weight:700;

    color:#374151;

    margin-bottom:8px;
}


.required{

    color:#dc2626;
}


.input-wrapper{

    position:relative;
}


.input-icon{

    position:absolute;

    left:14px;

    top:50%;

    transform:translateY(-50%);

    font-size:16px;

    color:#9ca3af;

    pointer-events:none;
}


.form-group input,
.form-group textarea{

    width:100%;

    padding:13px 14px 13px 42px;

    border:1px solid #dfe3e8;

    border-radius:10px;

    background:#ffffff;

    color:#111827;

    font-family:inherit;

    font-size:14px;

    outline:none;

    transition:
        border-color .2s,
        box-shadow .2s;
}


.form-group textarea{

    min-height:115px;

    resize:vertical;

    line-height:1.5;

    padding-left:14px;
}


.form-group input:focus,
.form-group textarea:focus{

    border-color:#2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,.10);
}


.form-group input::placeholder,
.form-group textarea::placeholder{

    color:#b0b5bd;
}


/* =====================================================
   MESSAGE ERREUR
===================================================== */

.erreurs{

    background:#fef2f2;

    border:1px solid #fecaca;

    color:#991b1b;

    padding:16px 18px;

    border-radius:12px;

    margin-bottom:22px;

    font-size:14px;
}


.erreurs-title{

    font-weight:800;

    margin-bottom:8px;
}


.erreurs ul{

    margin-left:20px;
}


.erreurs li{

    margin-bottom:4px;
}


/* =====================================================
   BOUTON
===================================================== */

.btn-payer{

    width:100%;

    height:54px;

    border:0;

    border-radius:11px;

    background:#2563eb;

    color:#ffffff;

    font-family:inherit;

    font-size:15px;

    font-weight:800;

    cursor:pointer;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:9px;

    margin-top:8px;

    transition:
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;
}


.btn-payer:hover{

    background:#1d4ed8;

    transform:translateY(-2px);

    box-shadow:
        0 9px 22px rgba(37,99,235,.25);
}


/* =====================================================
   RÉSUMÉ COMMANDE
===================================================== */

.resume-card{

    position:sticky;

    top:20px;
}


.resume-title{

    font-size:21px;

    color:#111827;

    font-weight:800;

    margin-bottom:5px;
}


.resume-subtitle{

    color:#9ca3af;

    font-size:13px;

    margin-bottom:22px;
}


/* =====================================================
   PRODUIT RÉSUMÉ
===================================================== */

.resume-product{

    display:grid;

    grid-template-columns:58px minmax(0,1fr) auto;

    gap:12px;

    align-items:center;

    padding:13px 0;

    border-bottom:1px solid #eef0f3;
}


.resume-image{

    width:58px;

    height:58px;

    background:#f8fafc;

    border-radius:10px;

    display:flex;

    justify-content:center;

    align-items:center;

    overflow:hidden;
}


.resume-image img{

    width:100%;

    height:100%;

    object-fit:contain;

    padding:5px;
}


.resume-product-name{

    font-size:14px;

    font-weight:700;

    color:#111827;

    overflow:hidden;

    white-space:nowrap;

    text-overflow:ellipsis;

    margin-bottom:5px;
}


.resume-product-quantity{

    color:#9ca3af;

    font-size:12px;
}


.resume-product-price{

    font-size:13px;

    font-weight:800;

    color:#374151;

    text-align:right;

    white-space:nowrap;
}


/* =====================================================
   RÉSUMÉ TOTAL
===================================================== */

.resume-details{

    margin-top:20px;
}


.resume-line{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:13px;

    color:#6b7280;

    font-size:14px;
}


.resume-line strong{

    color:#374151;
}


.resume-divider{

    height:1px;

    background:#e5e7eb;

    margin:20px 0;
}


.resume-total{

    display:flex;

    justify-content:space-between;

    align-items:end;

    gap:15px;
}


.resume-total span{

    font-size:16px;

    color:#374151;

    font-weight:700;
}


.resume-total strong{

    font-size:25px;

    color:#16a34a;

    font-weight:800;

    text-align:right;
}


/* =====================================================
   NOTE PAIEMENT
===================================================== */

.payment-info{

    margin-top:22px;

    padding:14px;

    background:#f0fdf4;

    border:1px solid #dcfce7;

    border-radius:10px;

    display:flex;

    gap:10px;

    align-items:flex-start;
}


.payment-info-icon{

    font-size:18px;
}


.payment-info-text{

    font-size:12px;

    line-height:1.5;

    color:#166534;
}


/* =====================================================
   GARANTIES
===================================================== */

.checkout-guarantees{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:10px;

    margin-top:22px;
}


.guarantee{

    padding:12px 8px;

    background:#f8fafc;

    border-radius:9px;

    text-align:center;

    font-size:11px;

    color:#6b7280;

    line-height:1.4;
}


.guarantee-icon{

    display:block;

    font-size:18px;

    margin-bottom:5px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:900px){

    .checkout-grid{

        grid-template-columns:1fr;
    }


    .resume-card{

        position:static;
    }

}


@media(max-width:650px){

    .checkout-container{

        padding:
            25px 12px
            50px;
    }


    .checkout-header h1{

        font-size:28px;
    }


    .steps{

        width:100%;
    }


    .step{

        font-size:11px;
    }


    .step-line{

        margin:0 7px;
    }


    .step-number{

        width:28px;

        height:28px;

        font-size:12px;
    }


    .checkout-card{

        padding:22px 18px;

        border-radius:16px;
    }


    .form-row{

        grid-template-columns:1fr;

        gap:0;
    }


    .checkout-guarantees{

        grid-template-columns:1fr;
    }

}


@media(max-width:400px){

    .step span:not(.step-number){

        display:none;
    }


    .step{

        flex:0 0 auto;
    }


    .step-line{

        flex:1;
    }

}

</style>

</head>

<body>

<div class="checkout-container">


<!-- =================================================
     EN-TÊTE
================================================== -->

<div class="checkout-header">

    <h1>
        Finaliser ma commande
    </h1>

    <p>
        Renseignez vos informations de livraison
        pour continuer vers le paiement.
    </p>

</div>


<!-- =================================================
     ÉTAPES
================================================== -->

<div class="steps">


    <div class="step active">

        <span class="step-number">
            1
        </span>

        <span>
            Livraison
        </span>

    </div>


    <div class="step-line"></div>


    <div class="step">

        <span class="step-number">
            2
        </span>

        <span>
            Paiement
        </span>

    </div>


    <div class="step-line"></div>


    <div class="step">

        <span class="step-number">
            3
        </span>

        <span>
            Confirmation
        </span>

    </div>


</div>


<!-- =================================================
     ERREURS
================================================== -->

<?php if (!empty($erreurs)): ?>

    <div class="erreurs">

        <div class="erreurs-title">

            ⚠️ Vérifiez les informations suivantes :

        </div>


        <ul>

            <?php foreach ($erreurs as $erreur): ?>

                <li>

                    <?= htmlspecialchars($erreur) ?>

                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>


<!-- =================================================
     GRID
================================================== -->

<div class="checkout-grid">


    <!-- =================================================
         INFORMATIONS CLIENT
    ================================================== -->

    <div class="checkout-card">


        <div class="card-title">


            <div class="card-title-icon">

                🚚

            </div>


            <div>

                <h2>
                    Informations de livraison
                </h2>

                <p>
                    Où devons-nous livrer votre commande ?
                </p>

            </div>


        </div>


        <form
            method="POST"
            action="commander.php"
        >


            <!-- PRÉNOM + NOM -->

            <div class="form-row">


                <div class="form-group">

                    <label for="prenom">

                        Prénom
                        <span class="required">*</span>

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>


                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            value="<?= htmlspecialchars($prenom) ?>"
                            placeholder="Votre prénom"
                            autocomplete="given-name"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="nom">

                        Nom
                        <span class="required">*</span>

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>


                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            value="<?= htmlspecialchars($nom) ?>"
                            placeholder="Votre nom"
                            autocomplete="family-name"
                            required
                        >

                    </div>

                </div>


            </div>


            <!-- TÉLÉPHONE -->

            <div class="form-group">

                <label for="telephone">

                    Numéro de téléphone
                    <span class="required">*</span>

                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        📱
                    </span>


                    <input
                        type="tel"
                        id="telephone"
                        name="telephone"
                        value="<?= htmlspecialchars($telephone) ?>"
                        placeholder="Exemple : 620 00 00 00"
                        autocomplete="tel"
                        required
                    >

                </div>

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">

                    Adresse email
                    <span style="color:#9ca3af;font-weight:400;">
                        (facultatif)
                    </span>

                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        ✉️
                    </span>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="exemple@email.com"
                        autocomplete="email"
                    >

                </div>

            </div>


            <!-- ADRESSE -->

            <div class="form-group">

                <label for="adresse">

                    Adresse de livraison
                    <span class="required">*</span>

                </label>


                <textarea
                    id="adresse"
                    name="adresse"
                    placeholder="Indiquez votre quartier, secteur, ville et toute précision utile pour le livreur..."
                    autocomplete="street-address"
                    required
                ><?= htmlspecialchars($adresse) ?></textarea>

            </div>


            <!-- BOUTON -->

            <button
                type="submit"
                class="btn-payer"
            >

                🔒

                Confirmer la commande et continuer vers le paiement

                →

            </button>


        </form>


    </div>


    <!-- =================================================
         RÉSUMÉ
    ================================================== -->

    <div class="checkout-card resume-card">


        <h2 class="resume-title">

            Votre commande

        </h2>


        <p class="resume-subtitle">

            <?= $nombre_articles ?>

            article<?= $nombre_articles > 1 ? 's' : '' ?>

            dans votre panier

        </p>


        <!-- PRODUITS -->

        <?php foreach ($produits as $produit): ?>


            <?php

            if (!empty($produit['image'])) {

                $image =
                    "image/" . $produit['image'];

            } else {

                $image =
                    "image/default.png";
            }

            ?>


            <div class="resume-product">


                <div class="resume-image">

                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($produit['nom']) ?>"
                        onerror="this.onerror=null;this.src='image/default.png';"
                    >

                </div>


                <div>

                    <div class="resume-product-name">

                        <?= htmlspecialchars(
                            $produit['nom']
                        ) ?>

                    </div>


                    <div class="resume-product-quantity">

                        Quantité :
                        <?= (int)$produit['quantite'] ?>

                        ×

                        <?= number_format(
                            $produit['prix'],
                            0,
                            ',',
                            ' '
                        ) ?>

                        GNF

                    </div>

                </div>


                <div class="resume-product-price">

                    <?= number_format(
                        $produit['sous_total'],
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </div>


            </div>


        <?php endforeach; ?>


        <!-- TOTAL -->

        <div class="resume-details">


            <div class="resume-line">

                <span>
                    Sous-total
                </span>

                <strong>

                    <?= number_format(
                        $total_general,
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </strong>

            </div>


            <div class="resume-line">

                <span>
                    Livraison
                </span>

                <strong>
                    À confirmer
                </strong>

            </div>


            <div class="resume-divider"></div>


            <div class="resume-total">

                <span>
                    Total à payer
                </span>


                <strong>

                    <?= number_format(
                        $total_general,
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </strong>

            </div>


        </div>


        <!-- INFORMATION PAIEMENT -->

        <div class="payment-info">


            <div class="payment-info-icon">

                🔐

            </div>


            <div class="payment-info-text">

                Après confirmation de votre commande,
                vous serez automatiquement redirigé vers
                la page de paiement.

            </div>


        </div>


        <!-- GARANTIES -->

        <div class="checkout-guarantees">


            <div class="guarantee">

                <span class="guarantee-icon">
                    🔒
                </span>

                Paiement sécurisé

            </div>


            <div class="guarantee">

                <span class="guarantee-icon">
                    🚚
                </span>

                Livraison

            </div>


            <div class="guarantee">

                <span class="guarantee-icon">
                    ✓
                </span>

                Commande suivie

            </div>


        </div>


    </div>


</div>

</div>

</body>

</html>
