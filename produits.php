<?php
session_start();

require 'db.php';
include 'header.php';

// =====================================================
// RÉCUPÉRER TOUS LES PRODUITS
// =====================================================

$req = $pdo->query("
    SELECT *
    FROM boissons
    ORDER BY nom ASC
");

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Catalogue des boissons</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =====================================================
   BODY
===================================================== */

body {

    font-family:
        "Segoe UI",
        Arial,
        Helvetica,
        sans-serif;

    background: #f7f8fa;

    color: #1f2937;

    min-height: 100vh;
}


/* =====================================================
   HERO
===================================================== */

.hero {

    max-width: 1250px;

    margin: 35px auto 25px;

    padding: 0 25px;
}


.hero-box {

    background:
        linear-gradient(
            135deg,
            #1d4ed8,
            #2563eb,
            #3b82f6
        );

    border-radius: 22px;

    padding: 50px;

    color: white;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 15px 35px rgba(37, 99, 235, 0.20);
}


.hero-box::after {

    content: "";

    position: absolute;

    width: 280px;

    height: 280px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.10);

    right: -80px;

    top: -100px;
}


.hero-text {

    position: relative;

    z-index: 2;

    max-width: 700px;
}


.hero-text h1 {

    font-size: 42px;

    line-height: 1.2;

    margin-bottom: 15px;
}


.hero-text p {

    font-size: 17px;

    line-height: 1.7;

    color: #e0e7ff;
}


/* =====================================================
   RECHERCHE
===================================================== */

.search-container {

    max-width: 1250px;

    margin: 25px auto;

    padding: 0 25px;
}


.search-box {

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 13px 18px;

    display: flex;

    align-items: center;

    gap: 10px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.04);
}


.search-box span {

    font-size: 20px;
}


.search-box input {

    border: none;

    outline: none;

    width: 100%;

    font-size: 15px;

    background: transparent;
}


/* =====================================================
   CATALOGUE
===================================================== */

.catalogue-container {

    max-width: 1250px;

    margin: 35px auto;

    padding: 0 25px;
}


/* =====================================================
   TITRE SECTION
===================================================== */

.section-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;
}


.section-header h2 {

    font-size: 28px;

    color: #111827;
}


.section-header p {

    margin-top: 5px;

    color: #6b7280;

    font-size: 14px;
}


/* =====================================================
   GRILLE PRODUITS
===================================================== */

.catalogue {

    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(240px, 1fr));

    gap: 25px;
}


/* =====================================================
   CARTE PRODUIT
===================================================== */

.card {

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    overflow: hidden;

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}


.card:hover {

    transform: translateY(-7px);

    box-shadow:
        0 18px 40px rgba(0,0,0,0.10);
}


/* =====================================================
   IMAGE
===================================================== */

.image-container {

    height: 220px;

    background: #f3f4f6;

    overflow: hidden;

    position: relative;
}


.card img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition:
        transform 0.4s ease;
}


.card:hover img {

    transform: scale(1.06);
}


/* =====================================================
   BADGE STOCK
===================================================== */

.stock-badge {

    position: absolute;

    top: 12px;

    left: 12px;

    background: #dcfce7;

    color: #166534;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;
}


.stock-badge.rupture {

    background: #fee2e2;

    color: #991b1b;
}


/* =====================================================
   CONTENU CARTE
===================================================== */

.card-content {

    padding: 20px;
}


.card h3 {

    font-size: 18px;

    color: #111827;

    margin-bottom: 10px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =====================================================
   DESCRIPTION
===================================================== */

.description {

    color: #6b7280;

    font-size: 14px;

    line-height: 1.5;

    margin-bottom: 15px;

    min-height: 42px;
}


/* =====================================================
   PRIX
===================================================== */

.prix {

    font-size: 22px;

    font-weight: 800;

    color: #2563eb;

    margin-bottom: 15px;
}


.prix small {

    font-size: 12px;

    font-weight: 600;

    color: #6b7280;
}


/* =====================================================
   STOCK
===================================================== */

.stock {

    font-size: 13px;

    color: #6b7280;

    margin-bottom: 15px;
}


/* =====================================================
   BOUTON
===================================================== */

.btn {

    display: block;

    width: 100%;

    text-align: center;

    text-decoration: none;

    background: #2563eb;

    color: white;

    padding: 12px 15px;

    border-radius: 10px;

    font-size: 14px;

    font-weight: 700;

    transition: 0.3s;
}


.btn:hover {

    background: #1d4ed8;

    transform: translateY(-1px);
}


.btn.disabled {

    background: #9ca3af;

    pointer-events: none;
}


/* =====================================================
   FOOTER
===================================================== */

footer {

    margin-top: 70px;

    background: #111827;

    color: #d1d5db;

    padding: 35px 25px;
}


.footer-content {

    max-width: 1250px;

    margin: auto;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}


.footer-logo {

    color: white;

    font-size: 20px;

    font-weight: 800;
}


.footer-text {

    font-size: 13px;

    color: #9ca3af;
}


/* =====================================================
   RESPONSIVE TABLETTE
===================================================== */

@media (max-width: 900px) {

    .hero-box {

        padding: 40px 30px;
    }


    .hero-text h1 {

        font-size: 35px;
    }

}


/* =====================================================
   RESPONSIVE TÉLÉPHONE
===================================================== */

@media (max-width: 600px) {

    .hero {

        margin-top: 20px;

        padding: 0 15px;
    }


    .hero-box {

        padding: 30px 22px;

        border-radius: 18px;
    }


    .hero-text h1 {

        font-size: 28px;
    }


    .hero-text p {

        font-size: 14px;
    }


    .search-container {

        padding: 0 15px;
    }


    .catalogue-container {

        padding: 0 15px;
    }


    .section-header {

        display: block;
    }


    .section-header h2 {

        font-size: 24px;
    }


    .catalogue {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 12px;
    }


    .image-container {

        height: 150px;
    }


    .card-content {

        padding: 14px;
    }


    .card h3 {

        font-size: 15px;
    }


    .description {

        font-size: 12px;

        min-height: auto;

        margin-bottom: 10px;
    }


    .prix {

        font-size: 18px;
    }


    .stock {

        font-size: 11px;
    }


    .btn {

        padding: 10px 8px;

        font-size: 12px;
    }


    .footer-content {

        display: block;

        text-align: center;
    }


    .footer-text {

        margin-top: 10px;
    }

}


/* =====================================================
   PETIT TÉLÉPHONE
===================================================== */

@media (max-width: 380px) {

    .catalogue {

        grid-template-columns: 1fr;
    }


    .image-container {

        height: 210px;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     HERO
===================================================== -->

<section class="hero">

    <div class="hero-box">

        <div class="hero-text">

            <h1>
                Découvrez nos boissons 🥤
            </h1>

            <p>
                Découvrez notre sélection de boissons
                fraîches et savoureuses.
                Choisissez vos produits préférés
                et passez votre commande en quelques clics.
            </p>

        </div>

    </div>

</section>



<!-- =====================================================
     RECHERCHE
===================================================== -->

<div class="search-container">

    <div class="search-box">

        <span>🔎</span>

        <input
            type="text"
            id="recherche"
            placeholder="Rechercher une boisson..."
            autocomplete="off"
        >

    </div>

</div>



<!-- =====================================================
     CATALOGUE
===================================================== -->

<section class="catalogue-container">


    <div class="section-header">

        <div>

            <h2>
                Nos boissons
            </h2>

            <p>
                Découvrez tous nos produits disponibles
            </p>

        </div>

    </div>



    <div
        class="catalogue"
        id="catalogue"
    >


        <?php while ($p = $req->fetch(PDO::FETCH_ASSOC)): ?>


            <?php

            $stock = (int)$p['stock'];

            $nom = htmlspecialchars(
                $p['nom'],
                ENT_QUOTES,
                'UTF-8'
            );

            $image = htmlspecialchars(
                $p['image'],
                ENT_QUOTES,
                'UTF-8'
            );

            $prix = htmlspecialchars(
                $p['prix'],
                ENT_QUOTES,
                'UTF-8'
            );

            ?>


            <div
                class="card produit"
                data-nom="<?= strtolower($nom) ?>"
            >


                <!-- IMAGE -->

                <div class="image-container">


                    <?php if (!empty($image)): ?>

                        <img
                            src="image/<?= $image ?>"
                            alt="<?= $nom ?>"
                            loading="lazy"
                        >

                    <?php else: ?>

                        <img
                            src="image/default.jpg"
                            alt="Image non disponible"
                        >

                    <?php endif; ?>


                    <!-- STOCK -->

                    <?php if ($stock > 0): ?>

                        <span class="stock-badge">

                            ✓ Disponible

                        </span>

                    <?php else: ?>

                        <span class="stock-badge rupture">

                            Rupture

                        </span>

                    <?php endif; ?>


                </div>



                <!-- CONTENU -->

                <div class="card-content">


                    <h3>

                        <?= $nom ?>

                    </h3>


                    <p class="description">

                        Une boisson savoureuse
                        disponible dans notre boutique.

                    </p>


                    <!-- PRIX -->

                    <div class="prix">

                        <?= $prix ?>

                        <small>GNF</small>

                    </div>


                    <!-- STOCK -->

                    <p class="stock">

                        📦

                        <?php if ($stock > 0): ?>

                            <?= $stock ?>
                            unité(s) disponible(s)

                        <?php else: ?>

                            Produit momentanément
                            indisponible

                        <?php endif; ?>

                    </p>


                    <!-- BOUTON -->

                    <?php if ($stock > 0): ?>


                        <a
                            class="btn"
                            href="produit.php?id_boisson=<?= (int)$p['id_boisson'] ?>"
                        >

                            Voir le Panier →

                        </a>


                    <?php else: ?>


                        <span class="btn disabled">

                            Indisponible

                        </span>


                    <?php endif; ?>


                </div>


            </div>


        <?php endwhile; ?>


    </div>


</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    <div class="footer-content">


        <div class="footer-logo">

            🥤 DrinkShop

        </div>


        <div class="footer-text">

            © <?= date('Y') ?> DrinkShop.
            Tous droits réservés.

        </div>


    </div>

</footer>



<!-- =====================================================
     RECHERCHE EN DIRECT
===================================================== -->

<script>

const recherche =
    document.getElementById('recherche');

const produits =
    document.querySelectorAll('.produit');


recherche.addEventListener(
    'input',
    function()
    {

        const texte =
            this.value
                .toLowerCase()
                .trim();


        produits.forEach(
            function(produit)
            {

                const nom =
                    produit.dataset.nom;


                if (nom.includes(texte)) {

                    produit.style.display = '';

                } else {

                    produit.style.display = 'none';

                }

            }
        );

    }
);

</script>


</body>

</html>

