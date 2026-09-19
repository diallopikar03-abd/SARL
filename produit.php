<?php

session_start();

require 'db.php';


// =====================================================
// VÉRIFIER L'ID DU PRODUIT
// =====================================================

if (!isset($_GET['id_boisson'])) {
    die("Produit non spécifié.");
}

$id = (int) $_GET['id_boisson'];


// =====================================================
// RÉCUPÉRER LE PRODUIT
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM boissons
    WHERE id_boisson = ?
");

$stmt->execute([$id]);

$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die("Produit introuvable.");
}


// =====================================================
// AJOUTER AU PANIER
// IMPORTANT : cette partie doit être AVANT header.php
// =====================================================

$erreur = "";

if (isset($_POST['ajouter'])) {

    $qte = isset($_POST['qte']) ? (int) $_POST['qte'] : 0;

    if ($qte > 0 && $qte <= (int)$p['stock']) {

        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        // Ajouter la quantité au panier
        $_SESSION['panier'][$id] =
            ($_SESSION['panier'][$id] ?? 0) + $qte;

        // Ne jamais dépasser le stock disponible
        if ($_SESSION['panier'][$id] > (int)$p['stock']) {
            $_SESSION['panier'][$id] = (int)$p['stock'];
        }

        // Redirection AVANT tout affichage HTML
        header("Location: panier.php");
        exit();

    } else {

        $erreur = "Quantité invalide ou stock insuffisant.";
    }
}


// =====================================================
// NOMBRE D'ARTICLES DANS LE PANIER
// =====================================================

$nombrePanier = 0;

if (!empty($_SESSION['panier'])) {

    foreach ($_SESSION['panier'] as $quantite) {

        $nombrePanier += (int)$quantite;
    }
}


// =====================================================
// IMAGE DU PRODUIT
// =====================================================

if (!empty($p['image'])) {

    $image = "image/" . $p['image'];

} else {

    $image = "image/default.png";
}


// =====================================================
// IMPORTANT : header.php APRÈS LES REDIRECTIONS
// =====================================================

include 'header.php';

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
    <?= htmlspecialchars($p['nom']) ?> - DrinkShop
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
   CONTENEUR PRINCIPAL
===================================================== */

.page-container{

    max-width:1200px;

    margin:0 auto;

    padding:45px 20px 70px;
}


/* =====================================================
   CARTE PRODUIT
===================================================== */

.product-card{

    background:#ffffff;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
        0 15px 45px rgba(15,23,42,.08);

    border:1px solid #edf0f5;

    display:grid;

    grid-template-columns:1fr 1fr;

    min-height:600px;
}


/* =====================================================
   ZONE IMAGE
===================================================== */

.product-image-area{

    background:
        linear-gradient(
            135deg,
            #f8fafc,
            #eef2f7
        );

    display:flex;

    justify-content:center;

    align-items:center;

    padding:55px;

    position:relative;
}


/* =====================================================
   CADRE IMAGE
===================================================== */

.product-image-wrapper{

    width:100%;

    height:480px;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#ffffff;

    border-radius:20px;

    box-shadow:
        0 10px 30px rgba(0,0,0,.06);

    overflow:hidden;
}


/* =====================================================
   IMAGE
===================================================== */

.product-image{

    width:100%;

    height:100%;

    object-fit:contain;

    padding:30px;

    transition:
        transform .4s ease;
}


.product-image:hover{

    transform:scale(1.04);
}


/* =====================================================
   BADGE
===================================================== */

.product-badge{

    position:absolute;

    top:30px;

    left:30px;

    background:#16a34a;

    color:#ffffff;

    padding:8px 15px;

    border-radius:30px;

    font-size:13px;

    font-weight:700;

    box-shadow:
        0 5px 15px rgba(22,163,74,.25);

    z-index:2;
}


.product-badge.rupture{

    background:#dc2626;

    box-shadow:
        0 5px 15px rgba(220,38,38,.20);
}


/* =====================================================
   INFORMATIONS PRODUIT
===================================================== */

.product-info{

    padding:55px;

    display:flex;

    flex-direction:column;

    justify-content:center;
}


/* =====================================================
   MARQUE
===================================================== */

.product-category{

    display:inline-flex;

    align-items:center;

    gap:7px;

    color:#2563eb;

    font-size:13px;

    font-weight:700;

    text-transform:uppercase;

    letter-spacing:.8px;

    margin-bottom:15px;
}


/* =====================================================
   NOM DU PRODUIT
===================================================== */

.product-title{

    font-size:42px;

    line-height:1.15;

    color:#111827;

    margin-bottom:18px;

    font-weight:800;
}


/* =====================================================
   DESCRIPTION
===================================================== */

.product-description{

    font-size:16px;

    line-height:1.8;

    color:#6b7280;

    margin-bottom:25px;

    max-width:520px;
}


/* =====================================================
   PRIX
===================================================== */

.price-box{

    margin-bottom:25px;
}


.price{

    font-size:34px;

    font-weight:800;

    color:#16a34a;
}


.currency{

    font-size:17px;

    font-weight:600;

    color:#374151;

    margin-left:5px;
}


/* =====================================================
   STOCK
===================================================== */

.stock-box{

    display:flex;

    align-items:center;

    gap:10px;

    margin-bottom:30px;

    padding:13px 16px;

    border-radius:10px;

    background:#f8fafc;

    width:max-content;

    max-width:100%;
}


.stock-dot{

    width:10px;

    height:10px;

    background:#16a34a;

    border-radius:50%;

    box-shadow:
        0 0 0 4px #dcfce7;
}


.stock-dot.rupture{

    background:#dc2626;

    box-shadow:
        0 0 0 4px #fee2e2;
}


.stock-text{

    font-size:14px;

    font-weight:600;

    color:#374151;
}


/* =====================================================
   MESSAGE ERREUR
===================================================== */

.error-message{

    display:flex;

    align-items:center;

    gap:10px;

    background:#fef2f2;

    color:#b91c1c;

    border:1px solid #fecaca;

    padding:13px 16px;

    border-radius:10px;

    margin-bottom:20px;

    font-size:14px;

    font-weight:600;
}


/* =====================================================
   FORMULAIRE
===================================================== */

.buy-form{

    display:flex;

    align-items:end;

    gap:14px;

    flex-wrap:wrap;
}


.quantity-group{

    display:flex;

    flex-direction:column;

    gap:8px;
}


.quantity-label{

    font-size:13px;

    font-weight:700;

    color:#374151;
}


.quantity-input{

    width:85px;

    height:52px;

    padding:10px;

    text-align:center;

    border:1px solid #d1d5db;

    border-radius:10px;

    outline:none;

    font-size:17px;

    font-weight:700;

    background:#ffffff;
}


.quantity-input:focus{

    border-color:#2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,.1);
}


/* =====================================================
   BOUTON AJOUTER
===================================================== */

.add-button{

    height:52px;

    padding:0 28px;

    border:none;

    border-radius:10px;

    background:#2563eb;

    color:#ffffff;

    font-size:15px;

    font-weight:700;

    cursor:pointer;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:9px;

    transition:
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;
}


.add-button:hover{

    background:#1d4ed8;

    transform:translateY(-2px);

    box-shadow:
        0 8px 20px rgba(37,99,235,.25);
}


.add-button:active{

    transform:translateY(0);
}


/* =====================================================
   LIEN PANIER
===================================================== */

.cart-link{

    display:inline-flex;

    align-items:center;

    gap:8px;

    margin-top:22px;

    color:#2563eb;

    text-decoration:none;

    font-size:14px;

    font-weight:700;

    width:max-content;
}


.cart-link:hover{

    text-decoration:underline;
}


/* =====================================================
   AVANTAGES
===================================================== */

.features{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:12px;

    margin-top:35px;

    padding-top:30px;

    border-top:1px solid #e5e7eb;
}


.feature{

    display:flex;

    flex-direction:column;

    gap:6px;

    font-size:12px;

    color:#6b7280;
}


.feature-icon{

    font-size:20px;
}


.feature strong{

    color:#374151;

    font-size:13px;
}


/* =====================================================
   TABLETTE
===================================================== */

@media(max-width:900px){

    .product-card{

        grid-template-columns:1fr;
    }


    .product-image-area{

        padding:30px;
    }


    .product-image-wrapper{

        height:400px;
    }


    .product-info{

        padding:40px;
    }


    .product-title{

        font-size:34px;
    }

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:600px){

    .page-container{

        padding:
            20px 12px
            45px;
    }


    .product-card{

        border-radius:18px;
    }


    .product-image-area{

        padding:18px;
    }


    .product-image-wrapper{

        height:300px;

        border-radius:15px;
    }


    .product-image{

        padding:20px;
    }


    .product-badge{

        top:22px;

        left:22px;
    }


    .product-info{

        padding:28px 22px;
    }


    .product-title{

        font-size:28px;
    }


    .price{

        font-size:28px;
    }


    .buy-form{

        width:100%;

        align-items:stretch;
    }


    .quantity-group{

        width:90px;
    }


    .add-button{

        flex:1;

        padding:0 15px;
    }


    .features{

        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div class="page-container">


<!-- =================================================
     CARTE PRODUIT
================================================== -->

<div class="product-card">


    <!-- =================================================
         IMAGE DU PRODUIT
    ================================================== -->

    <div class="product-image-area">


        <?php if ((int)$p['stock'] > 0): ?>

            <div class="product-badge">

                ✓ Disponible

            </div>

        <?php else: ?>

            <div class="product-badge rupture">

                Rupture de stock

            </div>

        <?php endif; ?>


        <div class="product-image-wrapper">

            <img
                src="<?= htmlspecialchars($image) ?>"
                alt="<?= htmlspecialchars($p['nom']) ?>"
                class="product-image"
                onerror="this.onerror=null;this.src='image/default.png';"
            >

        </div>

    </div>


    <!-- =================================================
         INFORMATIONS DU PRODUIT
    ================================================== -->

    <div class="product-info">


        <div class="product-category">

            🥤 DrinkShop

        </div>


        <h1 class="product-title">

            <?= htmlspecialchars($p['nom']) ?>

        </h1>


        <p class="product-description">

            <?= nl2br(htmlspecialchars($p['description'])) ?>

        </p>


        <!-- =================================================
             PRIX
        ================================================== -->

        <div class="price-box">

            <span class="price">

                <?= number_format(
                    $p['prix'],
                    0,
                    ',',
                    ' '
                ) ?>

            </span>

            <span class="currency">

                GNF

            </span>

        </div>


        <!-- =================================================
             STOCK
        ================================================== -->

        <div class="stock-box">

            <span
                class="stock-dot <?= ((int)$p['stock'] <= 0) ? 'rupture' : '' ?>"
            ></span>


            <span class="stock-text">

                <?php if ((int)$p['stock'] > 0): ?>

                    <?= htmlspecialchars($p['stock']) ?>

                    produit(s) disponible(s)

                <?php else: ?>

                    Produit momentanément indisponible

                <?php endif; ?>

            </span>

        </div>


        <!-- =================================================
             ERREUR
        ================================================== -->

        <?php if (!empty($erreur)): ?>

            <div class="error-message">

                ⚠️

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             AJOUT AU PANIER
        ================================================== -->

        <?php if ((int)$p['stock'] > 0): ?>

            <form
                method="POST"
                class="buy-form"
            >


                <div class="quantity-group">

                    <label
                        for="qte"
                        class="quantity-label"
                    >

                        Quantité

                    </label>


                    <input
                        id="qte"
                        class="quantity-input"
                        type="number"
                        name="qte"
                        value="1"
                        min="1"
                        max="<?= (int)$p['stock'] ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="ajouter"
                    class="add-button"
                >

                    🛒

                    Ajouter au panier

                </button>


            </form>


            <?php if ($nombrePanier > 0): ?>

                <a
                    href="panier.php"
                    class="cart-link"
                >

                    🛒 Voir mon panier

                    (<?= $nombrePanier ?>)

                </a>

            <?php endif; ?>


        <?php endif; ?>


        <!-- =================================================
             AVANTAGES
        ================================================== -->

        <div class="features">


            <div class="feature">

                <span class="feature-icon">

                    🔒

                </span>

                <strong>

                    Paiement sécurisé

                </strong>

                <span>

                    Transactions protégées

                </span>

            </div>


            <div class="feature">

                <span class="feature-icon">

                    🚚

                </span>

                <strong>

                    Livraison

                </strong>

                <span>

                    Livraison disponible

                </span>

            </div>


            <div class="feature">

                <span class="feature-icon">

                    ✓

                </span>

                <strong>

                    Qualité garantie

                </strong>

                <span>

                    Produits sélectionnés

                </span>

            </div>


        </div>


    </div>

</div>


</div>

</body>

</html>

