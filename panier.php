<?php

session_start();

require 'db.php';


// =====================================================
// INITIALISER LE PANIER
// =====================================================

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}


// =====================================================
// SUPPRIMER UN PRODUIT
// IMPORTANT : AVANT header.php
// =====================================================

if (isset($_GET['del'])) {

    $id = (int) $_GET['del'];

    if (isset($_SESSION['panier'][$id])) {
        unset($_SESSION['panier'][$id]);
    }

    header("Location: panier.php");
    exit();
}


// =====================================================
// CALCUL DU PANIER
// =====================================================

$total_general = 0;

$produits_panier = [];


foreach ($_SESSION['panier'] as $id => $qte) {

    $id = (int) $id;
    $qte = (int) $qte;

    if ($qte <= 0) {
        continue;
    }

    // Récupérer le produit
    $stmt = $pdo->prepare("
        SELECT
            id_boisson,
            nom,
            prix,
            image,
            stock
        FROM boissons
        WHERE id_boisson = ?
    ");

    $stmt->execute([$id]);

    $produit = $stmt->fetch(PDO::FETCH_ASSOC);


    // Produit introuvable
    if (!$produit) {
        unset($_SESSION['panier'][$id]);
        continue;
    }


    // Vérifier le stock
    if ($qte > (int)$produit['stock']) {

        $qte = (int)$produit['stock'];

        $_SESSION['panier'][$id] = $qte;
    }


    // Si le stock est à zéro
    if ($qte <= 0) {

        unset($_SESSION['panier'][$id]);

        continue;
    }


    // Calcul sous-total
    $prix = (float) $produit['prix'];

    $sous_total = $prix * $qte;

    $total_general += $sous_total;


    // Ajouter au tableau
    $produits_panier[] = [

        'id' => $id,

        'nom' => $produit['nom'],

        'prix' => $prix,

        'quantite' => $qte,

        'sous_total' => $sous_total,

        'image' => $produit['image'],

        'stock' => $produit['stock']

    ];
}


// =====================================================
// NOMBRE TOTAL D'ARTICLES
// =====================================================

$nombre_articles = 0;

foreach ($produits_panier as $produit) {

    $nombre_articles += $produit['quantite'];
}


// =====================================================
// IMPORTANT
// Inclure header.php seulement maintenant
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

<title>Mon panier - DrinkShop</title>

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

.panier-container{

    max-width:1200px;

    margin:0 auto;

    padding:45px 20px 70px;
}


/* =====================================================
   EN-TÊTE
===================================================== */

.panier-header{

    display:flex;

    justify-content:space-between;

    align-items:end;

    margin-bottom:30px;

    gap:20px;
}


.panier-header h1{

    font-size:34px;

    color:#111827;

    font-weight:800;

    margin-bottom:8px;
}


.panier-header p{

    color:#6b7280;

    font-size:15px;
}


.article-count{

    background:#eff6ff;

    color:#2563eb;

    padding:10px 16px;

    border-radius:30px;

    font-size:14px;

    font-weight:700;

    white-space:nowrap;
}


/* =====================================================
   LAYOUT
===================================================== */

.panier-layout{

    display:grid;

    grid-template-columns:
        minmax(0, 1fr)
        350px;

    gap:25px;

    align-items:start;
}


/* =====================================================
   LISTE PRODUITS
===================================================== */

.panier-produits{

    display:flex;

    flex-direction:column;

    gap:15px;
}


/* =====================================================
   CARTE PRODUIT
===================================================== */

.panier-produit{

    background:#ffffff;

    border:1px solid #e8ebf0;

    border-radius:18px;

    padding:18px;

    display:grid;

    grid-template-columns:
        100px
        minmax(0,1fr)
        auto;

    align-items:center;

    gap:20px;

    box-shadow:
        0 5px 20px rgba(15,23,42,.04);

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}


.panier-produit:hover{

    transform:translateY(-2px);

    box-shadow:
        0 10px 25px rgba(15,23,42,.07);
}


/* =====================================================
   IMAGE PRODUIT
===================================================== */

.produit-image{

    width:100px;

    height:100px;

    border-radius:14px;

    background:#f8fafc;

    display:flex;

    justify-content:center;

    align-items:center;

    overflow:hidden;
}


.produit-image img{

    width:100%;

    height:100%;

    object-fit:contain;

    padding:8px;
}


/* =====================================================
   INFORMATIONS PRODUIT
===================================================== */

.produit-infos{

    min-width:0;
}


.produit-nom{

    font-size:18px;

    font-weight:750;

    color:#111827;

    margin-bottom:8px;

    overflow:hidden;

    text-overflow:ellipsis;

    white-space:nowrap;
}


.produit-prix{

    color:#16a34a;

    font-weight:700;

    font-size:15px;

    margin-bottom:12px;
}


.produit-details{

    display:flex;

    align-items:center;

    gap:10px;

    flex-wrap:wrap;
}


.quantite{

    background:#f3f4f6;

    color:#374151;

    padding:6px 11px;

    border-radius:7px;

    font-size:13px;

    font-weight:700;
}


.stock-info{

    color:#6b7280;

    font-size:12px;
}


/* =====================================================
   SOUS-TOTAL
===================================================== */

.produit-total{

    text-align:right;

    min-width:140px;
}


.produit-total-label{

    color:#9ca3af;

    font-size:12px;

    margin-bottom:5px;
}


.produit-total-prix{

    color:#111827;

    font-size:18px;

    font-weight:800;

    margin-bottom:12px;
}


/* =====================================================
   SUPPRIMER
===================================================== */

.supprimer{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    gap:5px;

    color:#dc2626;

    background:#fef2f2;

    border:1px solid #fee2e2;

    padding:7px 11px;

    border-radius:7px;

    text-decoration:none;

    font-size:12px;

    font-weight:700;

    transition:.2s;
}


.supprimer:hover{

    background:#fee2e2;

    border-color:#fecaca;
}


/* =====================================================
   RÉSUMÉ COMMANDE
===================================================== */

.resume{

    background:#ffffff;

    border:1px solid #e8ebf0;

    border-radius:18px;

    padding:25px;

    box-shadow:
        0 8px 25px rgba(15,23,42,.05);

    position:sticky;

    top:20px;
}


.resume h2{

    color:#111827;

    font-size:21px;

    margin-bottom:22px;

    font-weight:800;
}


/* =====================================================
   LIGNES RÉSUMÉ
===================================================== */

.resume-ligne{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:14px;

    color:#6b7280;

    font-size:14px;
}


.resume-ligne strong{

    color:#374151;
}


.resume-separateur{

    height:1px;

    background:#e5e7eb;

    margin:20px 0;
}


/* =====================================================
   TOTAL
===================================================== */

.total-final{

    display:flex;

    justify-content:space-between;

    align-items:end;

    gap:15px;

    margin-bottom:22px;
}


.total-final span{

    color:#374151;

    font-size:16px;

    font-weight:700;
}


.total-final strong{

    color:#16a34a;

    font-size:25px;

    font-weight:800;

    text-align:right;
}


/* =====================================================
   BOUTON COMMANDER
===================================================== */

.btn-commander{

    width:100%;

    height:52px;

    display:flex;

    justify-content:center;

    align-items:center;

    gap:8px;

    background:#2563eb;

    color:white;

    border-radius:10px;

    text-decoration:none;

    font-size:15px;

    font-weight:700;

    transition:
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;
}


.btn-commander:hover{

    background:#1d4ed8;

    transform:translateY(-2px);

    box-shadow:
        0 8px 20px rgba(37,99,235,.25);
}


/* =====================================================
   CONTINUER ACHATS
===================================================== */

.btn-continuer{

    width:100%;

    height:48px;

    margin-top:12px;

    display:flex;

    justify-content:center;

    align-items:center;

    gap:7px;

    background:#f8fafc;

    color:#374151;

    border:1px solid #e5e7eb;

    border-radius:10px;

    text-decoration:none;

    font-size:14px;

    font-weight:700;

    transition:.2s;
}


.btn-continuer:hover{

    background:#f1f5f9;

    border-color:#d1d5db;
}


/* =====================================================
   GARANTIES
===================================================== */

.garanties{

    margin-top:20px;

    padding-top:20px;

    border-top:1px solid #e5e7eb;

    display:flex;

    flex-direction:column;

    gap:13px;
}


.garantie{

    display:flex;

    align-items:center;

    gap:10px;

    color:#6b7280;

    font-size:12px;
}


.garantie-icon{

    width:30px;

    height:30px;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#f0fdf4;

    border-radius:8px;

    font-size:15px;
}


/* =====================================================
   PANIER VIDE
===================================================== */

.panier-vide{

    max-width:650px;

    margin:60px auto;

    background:#ffffff;

    border:1px solid #e8ebf0;

    border-radius:22px;

    padding:60px 30px;

    text-align:center;

    box-shadow:
        0 10px 30px rgba(15,23,42,.05);
}


.panier-vide-icon{

    width:80px;

    height:80px;

    margin:0 auto 20px;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#eff6ff;

    border-radius:50%;

    font-size:38px;
}


.panier-vide h2{

    color:#111827;

    font-size:25px;

    margin-bottom:10px;
}


.panier-vide p{

    color:#6b7280;

    line-height:1.6;

    margin-bottom:25px;
}


.btn-produits{

    display:inline-flex;

    justify-content:center;

    align-items:center;

    gap:8px;

    padding:13px 22px;

    background:#2563eb;

    color:white;

    border-radius:9px;

    text-decoration:none;

    font-weight:700;

    font-size:14px;

    transition:.2s;
}


.btn-produits:hover{

    background:#1d4ed8;

    transform:translateY(-2px);
}


/* =====================================================
   RESPONSIVE TABLETTE
===================================================== */

@media(max-width:900px){

    .panier-layout{

        grid-template-columns:1fr;
    }


    .resume{

        position:static;
    }

}


/* =====================================================
   RESPONSIVE MOBILE
===================================================== */

@media(max-width:650px){

    .panier-container{

        padding:
            25px 12px
            50px;
    }


    .panier-header{

        align-items:flex-start;

        flex-direction:column;

        margin-bottom:20px;
    }


    .panier-header h1{

        font-size:28px;
    }


    .panier-produit{

        grid-template-columns:
            80px
            minmax(0,1fr);

        gap:14px;

        padding:13px;
    }


    .produit-image{

        width:80px;

        height:80px;
    }


    .produit-nom{

        font-size:15px;
    }


    .produit-prix{

        font-size:13px;
    }


    .produit-total{

        grid-column:1 / -1;

        border-top:1px solid #edf0f3;

        padding-top:12px;

        display:flex;

        justify-content:space-between;

        align-items:center;

        text-align:left;

        width:100%;
    }


    .produit-total-label{

        margin:0;
    }


    .produit-total-prix{

        margin:0 0 0 auto;

        margin-right:12px;
    }


    .supprimer{

        white-space:nowrap;
    }


    .resume{

        padding:20px;
    }


    .total-final strong{

        font-size:21px;
    }

}


/* =====================================================
   TRÈS PETITS ÉCRANS
===================================================== */

@media(max-width:400px){

    .panier-produit{

        grid-template-columns:70px 1fr;
    }


    .produit-image{

        width:70px;

        height:70px;
    }


    .produit-details{

        flex-direction:column;

        align-items:flex-start;

        gap:5px;
    }

}

</style>

</head>

<body>

<div class="panier-container">

<?php if (empty($produits_panier)): ?>


<!-- =================================================
     PANIER VIDE
================================================== -->

<div class="panier-vide">


    <div class="panier-vide-icon">

        🛒

    </div>


    <h2>

        Votre panier est vide

    </h2>


    <p>

        Vous n'avez encore ajouté aucun produit.
        Découvrez nos boissons et ajoutez vos produits
        préférés à votre panier.

    </p>


    <a
        href="produits.php"
        class="btn-produits"
    >

        🥤 Découvrir les produits

    </a>


</div>


<?php else: ?>


<!-- =================================================
     EN-TÊTE DU PANIER
================================================== -->

<div class="panier-header">


    <div>

        <h1>

            🛒 Mon panier

        </h1>


        <p>

            Vérifiez vos produits avant de passer votre commande.

        </p>

    </div>


    <div class="article-count">

        <?= $nombre_articles ?>

        article<?= $nombre_articles > 1 ? 's' : '' ?>

    </div>


</div>


<!-- =================================================
     CONTENU
================================================== -->

<div class="panier-layout">


    <!-- =================================================
         LISTE DES PRODUITS
    ================================================== -->

    <div class="panier-produits">


        <?php foreach ($produits_panier as $produit): ?>


            <?php

            if (!empty($produit['image'])) {

                $imageProduit =
                    "image/" . $produit['image'];

            } else {

                $imageProduit =
                    "image/default.png";
            }

            ?>


            <div class="panier-produit">


                <!-- IMAGE -->

                <div class="produit-image">

                    <img
                        src="<?= htmlspecialchars($imageProduit) ?>"
                        alt="<?= htmlspecialchars($produit['nom']) ?>"
                        onerror="this.onerror=null;this.src='image/default.png';"
                    >

                </div>


                <!-- INFORMATIONS -->

                <div class="produit-infos">


                    <div class="produit-nom">

                        <?= htmlspecialchars(
                            $produit['nom'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>


                    <div class="produit-prix">

                        <?= number_format(
                            $produit['prix'],
                            0,
                            ',',
                            ' '
                        ) ?>

                        GNF / unité

                    </div>


                    <div class="produit-details">


                        <span class="quantite">

                            Quantité :
                            <?= $produit['quantite'] ?>

                        </span>


                        <span class="stock-info">

                            Stock disponible :
                            <?= (int)$produit['stock'] ?>

                        </span>


                    </div>


                </div>


                <!-- TOTAL PRODUIT -->

                <div class="produit-total">


                    <div>

                        <div class="produit-total-label">

                            Sous-total

                        </div>


                        <div class="produit-total-prix">

                            <?= number_format(
                                $produit['sous_total'],
                                0,
                                ',',
                                ' '
                            ) ?>

                            GNF

                        </div>

                    </div>


                    <a
                        href="panier.php?del=<?= $produit['id'] ?>"
                        class="supprimer"
                        onclick="return confirm('Voulez-vous vraiment supprimer ce produit du panier ?');"
                    >

                        🗑 Supprimer

                    </a>


                </div>


            </div>


        <?php endforeach; ?>


    </div>


    <!-- =================================================
         RÉSUMÉ DE LA COMMANDE
    ================================================== -->

    <aside class="resume">


        <h2>

            Résumé de la commande

        </h2>


        <div class="resume-ligne">

            <span>

                Produits

            </span>

            <strong>

                <?= $nombre_articles ?>

                article<?= $nombre_articles > 1 ? 's' : '' ?>

            </strong>

        </div>


        <div class="resume-ligne">

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


        <div class="resume-ligne">

            <span>

                Livraison

            </span>

            <strong>

                À confirmer

            </strong>

        </div>


        <div class="resume-separateur"></div>


        <div class="total-final">

            <span>

                Total

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


        <!-- COMMANDER -->

        <a
            href="commander.php"
            class="btn-commander"
        >

            Passer la commande

            <span>→</span>

        </a>


        <!-- CONTINUER -->

        <a
            href="produits.php"
            class="btn-continuer"
        >

            ← Continuer mes achats

        </a>


        <!-- GARANTIES -->

        <div class="garanties">


            <div class="garantie">

                <span class="garantie-icon">

                    🔒

                </span>

                <span>

                    Paiement sécurisé

                </span>

            </div>


            <div class="garantie">

                <span class="garantie-icon">

                    🚚

                </span>

                <span>

                    Livraison disponible

                </span>

            </div>


            <div class="garantie">

                <span class="garantie-icon">

                    ✓

                </span>

                <span>

                    Produits vérifiés

                </span>

            </div>


        </div>


    </aside>


</div>


<?php endif; ?>

</div>

</body>

</html>
