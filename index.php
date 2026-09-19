<?php
require 'db.php';
include 'header.php';

/* =========================================================
   RÉCUPÉRER LES 3 BOISSONS
========================================================= */

try {

    $req = $pdo->query("
        SELECT *
        FROM boissons
        ORDER BY id_boisson DESC
        LIMIT 3
    ");

    $boissons = $req->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $boissons = [];

}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="DrinkShop - Découvrez nos meilleures boissons et passez votre commande en ligne.">

    <title>DrinkShop - Accueil</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background: #f6f8fb;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }


        /* =====================================================
           HERO
        ===================================================== */

        .hero {

            min-height: 560px;

            display: flex;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 80px 20px;

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    rgba(7, 25, 48, 0.88),
                    rgba(0, 102, 204, 0.60)
                ),
                url("images/boissons.jpg");

            background-size: cover;

            background-position: center;
        }


        .hero::before {

            content: "";

            position: absolute;

            width: 400px;

            height: 400px;

            border-radius: 50%;

            background: rgba(255,255,255,0.08);

            top: -180px;

            right: -100px;
        }


        .hero::after {

            content: "";

            position: absolute;

            width: 300px;

            height: 300px;

            border-radius: 50%;

            background: rgba(255,255,255,0.06);

            bottom: -150px;

            left: -100px;
        }


        .hero-content {

            max-width: 900px;

            position: relative;

            z-index: 2;

            color: white;

            animation: heroAppear 1s ease;
        }


        @keyframes heroAppear {

            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        .hero-badge {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            background: rgba(255,255,255,0.15);

            border: 1px solid rgba(255,255,255,0.25);

            backdrop-filter: blur(10px);

            padding: 9px 18px;

            border-radius: 50px;

            font-size: 14px;

            margin-bottom: 22px;
        }


        .hero h1 {

            font-size: clamp(38px, 6vw, 68px);

            line-height: 1.1;

            font-weight: 800;

            margin-bottom: 22px;

            letter-spacing: -1px;
        }


        .hero h1 span {
            color: #60a5fa;
        }


        .hero p {

            max-width: 700px;

            margin: auto;

            font-size: clamp(17px, 2vw, 21px);

            color: rgba(255,255,255,0.88);

            margin-bottom: 34px;
        }


        .hero-buttons {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 14px;

            flex-wrap: wrap;
        }


        .hero-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            padding: 14px 25px;

            background: #2563eb;

            color: white;

            border-radius: 10px;

            font-weight: 700;

            transition: all 0.3s ease;

            box-shadow: 0 8px 25px rgba(37,99,235,0.35);
        }


        .hero-btn:hover {

            background: #1d4ed8;

            transform: translateY(-3px);
        }


        .hero-btn-secondary {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            padding: 14px 25px;

            background: rgba(255,255,255,0.12);

            color: white;

            border: 1px solid rgba(255,255,255,0.35);

            border-radius: 10px;

            font-weight: 700;

            backdrop-filter: blur(10px);

            transition: all 0.3s ease;
        }


        .hero-btn-secondary:hover {

            background: white;

            color: #1d4ed8;

            transform: translateY(-3px);
        }


        /* =====================================================
           STATISTIQUES HERO
        ===================================================== */

        .hero-stats {

            max-width: 850px;

            margin: 45px auto 0;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 15px;
        }


        .hero-stat {

            padding: 15px;

            border-radius: 12px;

            background: rgba(255,255,255,0.10);

            border: 1px solid rgba(255,255,255,0.15);

            backdrop-filter: blur(8px);
        }


        .hero-stat strong {

            display: block;

            font-size: 20px;
        }


        .hero-stat span {

            font-size: 13px;

            color: rgba(255,255,255,0.75);
        }


        /* =====================================================
           SECTION PRODUITS
        ===================================================== */

        .section {

            max-width: 1200px;

            margin: 0 auto;

            padding: 80px 20px;
        }


        .section-header {

            display: flex;

            justify-content: space-between;

            align-items: end;

            gap: 20px;

            margin-bottom: 40px;
        }


        .section-title {
            text-align: left;
        }


        .section-label {

            display: inline-block;

            color: #2563eb;

            font-size: 13px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            margin-bottom: 8px;
        }


        .section-title h2 {

            font-size: 34px;

            color: #111827;

            line-height: 1.2;

            margin-bottom: 10px;
        }


        .section-title p {
            color: #6b7280;
            font-size: 16px;
        }


        .all-products {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            color: #2563eb;

            font-weight: 700;

            white-space: nowrap;

            transition: 0.3s;
        }


        .all-products:hover {

            gap: 13px;

            color: #1d4ed8;
        }


        /* =====================================================
           CARTES
        ===================================================== */

        .produits {

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 28px;
        }


        .card {

            background: white;

            border-radius: 18px;

            overflow: hidden;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 5px 20px rgba(15,23,42,0.06);

            transition:
                transform 0.35s ease,
                box-shadow 0.35s ease;
        }


        .card:hover {

            transform: translateY(-8px);

            box-shadow:
                0 18px 40px rgba(15,23,42,0.13);
        }


        .card-image {

            height: 245px;

            position: relative;

            overflow: hidden;

            background: #f1f5f9;
        }


        .card img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            transition: transform 0.5s ease;
        }


        .card:hover img {
            transform: scale(1.07);
        }


        .product-badge {

            position: absolute;

            top: 15px;

            left: 15px;

            background: #2563eb;

            color: white;

            padding: 6px 11px;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 700;

            z-index: 2;
        }


        .card-content {
            padding: 22px;
        }


        .card h3 {

            font-size: 20px;

            color: #111827;

            margin-bottom: 10px;
        }


        .description {

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 15px;

            display: -webkit-box;

            -webkit-line-clamp: 2;

            -webkit-box-orient: vertical;

            overflow: hidden;
        }


        .product-footer {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-top: 15px;
        }


        .prix {

            color: #16a34a;

            font-size: 21px;

            font-weight: 800;
        }


        .prix small {

            font-size: 12px;

            font-weight: 600;

            color: #6b7280;
        }


        .stock {

            font-size: 12px;

            color: #6b7280;

            margin-top: 3px;
        }


        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 10px 16px;

            background: #2563eb;

            color: white;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.3s;

            white-space: nowrap;
        }


        .btn:hover {

            background: #1d4ed8;

            transform: translateY(-2px);
        }


        /* =====================================================
           AUCUN PRODUIT
        ===================================================== */

        .no-product {

            grid-column: 1 / -1;

            text-align: center;

            background: white;

            padding: 50px 20px;

            border-radius: 16px;

            border: 1px solid #e5e7eb;

            color: #6b7280;
        }


        .no-product-icon {

            font-size: 45px;

            margin-bottom: 10px;
        }


        /* =====================================================
           PROMOTION
        ===================================================== */

        .promo {

            max-width: 1200px;

            margin: 0 auto 80px;

            padding: 0 20px;
        }


        .promo-box {

            background:
                linear-gradient(
                    120deg,
                    #0f172a,
                    #1e40af
                );

            border-radius: 22px;

            padding: 45px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 30px;

            color: white;

            overflow: hidden;

            position: relative;
        }


        .promo-box::after {

            content: "🥤";

            position: absolute;

            right: 30px;

            bottom: -35px;

            font-size: 150px;

            opacity: 0.08;
        }


        .promo-content {

            position: relative;

            z-index: 2;
        }


        .promo-content h2 {

            font-size: 30px;

            margin-bottom: 10px;
        }


        .promo-content p {

            color: rgba(255,255,255,0.75);

            max-width: 600px;
        }


        .promo-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 13px 22px;

            background: white;

            color: #1d4ed8;

            border-radius: 9px;

            font-weight: 800;

            white-space: nowrap;

            position: relative;

            z-index: 2;

            transition: 0.3s;
        }


        .promo-btn:hover {

            transform: translateY(-3px);

            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }


        /* =====================================================
           PRÉSENTATION
        ===================================================== */

        .presentation {

            background: white;

            border-top: 1px solid #e5e7eb;

            border-bottom: 1px solid #e5e7eb;

            padding: 80px 20px;

            text-align: center;
        }


        .presentation-inner {

            max-width: 900px;

            margin: auto;
        }


        .presentation-icon {

            width: 65px;

            height: 65px;

            margin: 0 auto 20px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eff6ff;

            border-radius: 16px;

            font-size: 30px;
        }


        .presentation h2 {

            font-size: 32px;

            color: #111827;

            margin-bottom: 15px;
        }


        .presentation p {

            max-width: 750px;

            margin: auto;

            color: #6b7280;

            line-height: 1.8;

            font-size: 16px;
        }


        /* =====================================================
           AVANTAGES
        ===================================================== */

        .advantages {

            max-width: 1100px;

            margin: 0 auto;

            padding: 70px 20px;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 25px;
        }


        .advantage {

            text-align: center;

            padding: 28px 20px;

            background: white;

            border-radius: 15px;

            border: 1px solid #e5e7eb;

            transition: 0.3s;
        }


        .advantage:hover {

            transform: translateY(-5px);

            box-shadow:
                0 12px 30px rgba(15,23,42,0.08);
        }


        .advantage-icon {

            width: 55px;

            height: 55px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            border-radius: 14px;

            background: #eff6ff;

            font-size: 25px;
        }


        .advantage h3 {

            font-size: 18px;

            margin-bottom: 8px;

            color: #111827;
        }


        .advantage p {

            color: #6b7280;

            font-size: 14px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        footer {

            background: #0f172a;

            color: white;

            padding: 60px 20px 25px;
        }


        .footer-container {

            max-width: 1200px;

            margin: auto;

            display: grid;

            grid-template-columns: 2fr 1fr 1fr;

            gap: 50px;

            padding-bottom: 40px;
        }


        .footer-brand {

            max-width: 400px;
        }


        .footer-logo {

            width: 125px;

            max-height: 65px;

            object-fit: contain;

            margin-bottom: 15px;
        }


        .footer-brand h2 {

            margin-bottom: 12px;

            font-size: 25px;
        }


        .footer-brand p {

            color: #94a3b8;

            line-height: 1.7;

            font-size: 14px;
        }


        .footer-column h3 {

            font-size: 17px;

            margin-bottom: 20px;

            color: white;
        }


        /* =====================================================
           INFORMATIONS
        ===================================================== */

        .footer-info {

            color: #94a3b8;

            font-size: 14px;

            margin-bottom: 12px;

            line-height: 1.6;
        }


        .footer-info strong {

            color: #e2e8f0;
        }


        .footer-info a {

            color: #94a3b8;

            transition: 0.3s;
        }


        .footer-info a:hover {

            color: white;
        }


        /* =====================================================
           LIENS FOOTER
        ===================================================== */

        .footer-column a.footer-link {

            display: block;

            color: #94a3b8;

            margin-bottom: 10px;

            font-size: 14px;

            transition: 0.3s;
        }


        .footer-column a.footer-link:hover {

            color: white;

            transform: translateX(3px);
        }


        .footer-bottom {

            max-width: 1200px;

            margin: auto;

            padding-top: 22px;

            border-top: 1px solid #1e293b;

            text-align: center;

            color: #64748b;

            font-size: 13px;
        }


        /* =====================================================
           RESPONSIVE TABLETTE
        ===================================================== */

        @media (max-width: 900px) {

            .produits {

                grid-template-columns: repeat(2, 1fr);
            }


            .advantages {

                grid-template-columns: repeat(2, 1fr);
            }


            .footer-container {

                grid-template-columns: 1fr 1fr;
            }


            .footer-brand {

                grid-column: 1 / -1;
            }


            .promo-box {

                flex-direction: column;

                align-items: flex-start;
            }

        }


        /* =====================================================
           RESPONSIVE MOBILE
        ===================================================== */

        @media (max-width: 650px) {

            .hero {

                min-height: 600px;

                padding: 70px 18px;
            }


            .hero h1 {

                font-size: 40px;
            }


            .hero p {

                font-size: 16px;
            }


            .hero-buttons {

                flex-direction: column;

                width: 100%;
            }


            .hero-btn,
            .hero-btn-secondary {

                width: 100%;

                max-width: 300px;
            }


            .hero-stats {

                grid-template-columns: 1fr;

                max-width: 300px;
            }


            .section {

                padding: 60px 18px;
            }


            .section-header {

                display: block;
            }


            .section-title {

                margin-bottom: 20px;
            }


            .section-title h2 {

                font-size: 28px;
            }


            .produits {

                grid-template-columns: 1fr;

                gap: 20px;
            }


            .card {

                width: 100%;
            }


            .card-image {

                height: 230px;
            }


            .advantages {

                grid-template-columns: 1fr;

                padding: 50px 18px;
            }


            .promo {

                padding: 0 18px;

                margin-bottom: 60px;
            }


            .promo-box {

                padding: 30px 25px;

                border-radius: 18px;
            }


            .promo-content h2 {

                font-size: 25px;
            }


            .presentation {

                padding: 60px 20px;
            }


            .presentation h2 {

                font-size: 27px;
            }


            .footer-container {

                grid-template-columns: 1fr;

                gap: 30px;
            }


            .footer-brand {

                grid-column: auto;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="hero-content">

        <div class="hero-badge">

            ✨ Qualité • Fraîcheur • Saveur

        </div>


        <h1>

            Bienvenue sur

            <span>DrinkShop</span> 🥤

        </h1>


        <p>

            Découvrez une sélection de boissons soigneusement
            choisies pour accompagner tous vos moments,
            directement depuis chez vous.

        </p>


        <div class="hero-buttons">

            <a href="produits.php" class="hero-btn">

                🛍️ Découvrir les boissons

            </a>


            <a href="#produits" class="hero-btn-secondary">

                ⭐ Voir la sélection

            </a>

        </div>


        <div class="hero-stats">

            <div class="hero-stat">

                <strong>🥤</strong>

                <span>Boissons variées</span>

            </div>


            <div class="hero-stat">

                <strong>🛒</strong>

                <span>Commande en ligne</span>

            </div>


            <div class="hero-stat">

                <strong>⚡</strong>

                <span>Service rapide</span>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     PRODUITS
========================================================= -->

<section class="section" id="produits">

    <div class="section-header">

        <div class="section-title">

            <span class="section-label">

                Notre sélection

            </span>


            <h2>

                ⭐ Boissons à la une

            </h2>


            <p>

                Découvrez nos dernières boissons disponibles.

            </p>

        </div>


        <a href="produits.php" class="all-products">

            Voir tous les produits →

        </a>

    </div>



    <div class="produits">

        <?php if (!empty($boissons)): ?>

            <?php foreach ($boissons as $p): ?>

                <article class="card">

                    <div class="card-image">

                        <span class="product-badge">

                            ⭐ À la une

                        </span>


                        <?php if (!empty($p['image'])): ?>

                            <img
                                src="image/<?= htmlspecialchars($p['image']) ?>"
                                alt="<?= htmlspecialchars($p['nom']) ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <img
                                src="images/default.jpg"
                                alt="Image non disponible"
                                loading="lazy"
                            >

                        <?php endif; ?>

                    </div>


                    <div class="card-content">

                        <h3>

                            <?= htmlspecialchars($p['nom']) ?>

                        </h3>


                        <?php if (!empty($p['description'])): ?>

                            <p class="description">

                                <?= htmlspecialchars($p['description']) ?>

                            </p>

                        <?php else: ?>

                            <p class="description">

                                Découvrez cette boisson disponible
                                sur DrinkShop.

                            </p>

                        <?php endif; ?>


                        <div class="product-footer">

                            <div>

                                <div class="prix">

                                    <?= number_format(
                                        (float)$p['prix'],
                                        0,
                                        ',',
                                        ' '
                                    ) ?>

                                    <small>GNF</small>

                                </div>


                                <div class="stock">

                                    <?php if ((int)$p['stock'] > 0): ?>

                                        ✓
                                        <?= htmlspecialchars($p['stock']) ?>
                                        disponible(s)

                                    <?php else: ?>

                                        <span style="color:#dc2626;">

                                            ✕ Rupture de stock

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <a
                                class="btn"
                                href="produit.php?id_boisson=<?= (int)$p['id_boisson'] ?>"
                            >

                                👁️ Voir

                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="no-product">

                <div class="no-product-icon">

                    🥤

                </div>


                <h3>

                    Aucune boisson disponible

                </h3>


                <p>

                    Les boissons apparaîtront ici dès
                    qu'elles seront ajoutées.

                </p>

            </div>

        <?php endif; ?>

    </div>

</section>



<!-- =========================================================
     PROMOTION
========================================================= -->

<section class="promo">

    <div class="promo-box">

        <div class="promo-content">

            <h2>

                Trouvez votre boisson préférée 🥤

            </h2>


            <p>

                Parcourez notre catalogue et découvrez
                toutes les boissons disponibles sur DrinkShop.

            </p>

        </div>


        <a href="produits.php" class="promo-btn">

            Explorer le catalogue →

        </a>

    </div>

</section>



<!-- =========================================================
     PRÉSENTATION
========================================================= -->

<section class="presentation">

    <div class="presentation-inner">

        <div class="presentation-icon">

            🥤

        </div>


        <h2>

            Pourquoi choisir DrinkShop ?

        </h2>


        <p>

            DrinkShop vous permet de découvrir facilement
            vos boissons préférées et de passer votre commande
            directement en ligne. Notre objectif est de vous
            offrir une expérience simple, rapide et agréable,
            de la découverte du produit jusqu'à la commande.

        </p>

    </div>

</section>



<!-- =========================================================
     AVANTAGES
========================================================= -->

<section class="advantages">

    <div class="advantage">

        <div class="advantage-icon">

            🛍️

        </div>


        <h3>

            Large choix

        </h3>


        <p>

            Découvrez différentes boissons adaptées
            à vos envies et à vos moments.

        </p>

    </div>


    <div class="advantage">

        <div class="advantage-icon">

            🔒

        </div>


        <h3>

            Commande simple

        </h3>


        <p>

            Consultez vos produits et passez votre
            commande facilement en quelques clics.

        </p>

    </div>


    <div class="advantage">

        <div class="advantage-icon">

            ⚡

        </div>


        <h3>

            Service rapide

        </h3>


        <p>

            Une expérience pensée pour vous permettre
            de commander rapidement.

        </p>

    </div>

</section>



<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div class="footer-container">


        <!-- MARQUE -->

        <div class="footer-brand">

            <?php if (file_exists('image.png')): ?>

                <img
                    src="image.png"
                    class="footer-logo"
                    alt="DrinkShop"
                >

            <?php endif; ?>


            <h2>

                DrinkShop 🥤

            </h2>


            <p>

                Votre espace en ligne pour découvrir,
                consulter et commander vos boissons préférées.

            </p>

        </div>



        <!-- NAVIGATION -->

        <div class="footer-column">

            <h3>

                Navigation

            </h3>


            <a href="index.php" class="footer-link">

                🏠 Accueil

            </a>


            <a href="produits.php" class="footer-link">

                🥤 Produits

            </a>


            <a href="panier.php" class="footer-link">

                🛒 Panier

            </a>


            <a href="contact.php" class="footer-link">

                ✉️ Contact

            </a>

        </div>



        <!-- INFORMATIONS -->

        <div class="footer-column">

            <h3>

                Informations

            </h3>


            <!--
                REMPLACE CES INFORMATIONS
                PAR CELLES QUI SONT SUR TON IMAGE image.png
            -->


            <p class="footer-info">

                🥤 <strong>Société :</strong><br>

                Ste lingué-séré SARL

            </p>


            <p class="footer-info">

                📍 <strong>Adresse :</strong><br>

                Madina Corniche, Commune de Dixinn, Conakry

            </p>


            <p class="footer-info">

                📞 <strong>Téléphone :</strong><br>

                <a href="tel:+224000000000">

                    +224 628 53 62 73 / +224 614 36 38 25

                </a>

            </p>


            <p class="footer-info">

                ✉️ <strong>Email :</strong><br>

                <a href="mailto:contact@drinkshop.com">

                    lingué-sérésarl77@gmail.com

                </a>

            </p>


            

        </div>


    </div>



    <!-- COPYRIGHT -->

    <div class="footer-bottom">

        © <?= date('Y') ?> DrinkShop.
        Tous droits réservés.

    </div>

</footer>



</body>

</html>

