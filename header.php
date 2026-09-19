<?php

/* =========================================================
   SESSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   NOMBRE D'ARTICLES DANS LE PANIER
========================================================= */

$nombrePanier = 0;

if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {

    $nombrePanier = array_sum($_SESSION['panier']);

}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>DrinkShop</title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f7fa;

            color: #1f2937;
        }


        /* =====================================================
           NAVIGATION
        ===================================================== */

        nav {

            width: 100%;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e3a8a
                );

            min-height: 75px;

            padding: 10px 5%;

            display: flex;

            align-items: center;

            gap: 8px;

            flex-wrap: wrap;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.12);

            position: relative;

            z-index: 1000;
        }


        /* =====================================================
           LOGO
        ===================================================== */

        .logo-link {

            display: flex;

            align-items: center;

            margin-right: 15px;
        }


        .logo {

            width: 65px;

            height: 65px;

            object-fit: contain;

            border-radius: 12px;

            background: white;

            padding: 4px;

            transition: 0.3s;
        }


        .logo:hover {

            transform: scale(1.05);
        }


        /* =====================================================
           LIENS
        ===================================================== */

        nav a {

            color: white;

            text-decoration: none;

            padding: 10px 13px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 600;

            transition:
                background 0.3s ease,
                color 0.3s ease,
                transform 0.3s ease;
        }


        nav a:hover {

            background:
                rgba(255,255,255,0.12);

            color: #60a5fa;

            transform: translateY(-1px);
        }


        /* =====================================================
           PANIER
        ===================================================== */

        .panier-link {

            position: relative;

            display: inline-flex;

            align-items: center;

            gap: 5px;
        }


        .panier-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 21px;

            height: 21px;

            padding: 0 5px;

            background: #ef4444;

            color: white;

            border-radius: 50px;

            font-size: 11px;

            font-weight: 800;
        }


        /* =====================================================
           ADMINISTRATION
        ===================================================== */

        .admin-link {

            background: #dc2626 !important;

            color: white !important;
        }


        .admin-link:hover {

            background: #b91c1c !important;

            color: white !important;
        }


        /* =====================================================
           COMPTE
        ===================================================== */

        .account-link {

            background: #2563eb;
        }


        .account-link:hover {

            background: #1d4ed8;
        }


        /* =====================================================
           LIGNE
        ===================================================== */

        hr {

            display: none;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            nav {

                justify-content: center;

                padding: 12px 15px;

                gap: 5px;
            }


            .logo-link {

                width: 100%;

                justify-content: center;

                margin-right: 0;

                margin-bottom: 5px;
            }


            nav a {

                font-size: 13px;

                padding: 9px 10px;
            }

        }


        @media (max-width: 600px) {

            nav {

                padding: 12px 10px;
            }


            nav a {

                font-size: 12px;

                padding: 8px 9px;
            }


            .logo {

                width: 60px;

                height: 60px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVIGATION
========================================================= -->

<nav>


    <!-- LOGO -->

    <a href="index.php" class="logo-link">

        <img
            src="logo.jpeg"
            class="logo"
            alt="Logo DrinkShop"
        >

    </a>



    <!-- ACCUEIL -->

    <a href="index.php">

        🏠 Accueil

    </a>



    <!-- PRODUITS -->

    <a href="produits.php">

        🥤 Boissons

    </a>



    <!-- PANIER -->

    <a href="panier.php" class="panier-link">

        🛒 Panier

        <span class="panier-badge">

            <?= $nombrePanier ?>

        </span>

    </a>



<?php if (isset($_SESSION['user_id'])): ?>


    <!-- =====================================================
         UTILISATEUR CONNECTÉ
    ====================================================== -->


    <!-- MES COMMANDES -->

    <a href="mes_commandes.php">

        📦 Mes commandes

    </a>



    <!-- AVIS CLIENTS -->

    <a href="avis.php">

        ⭐ Avis des clients

    </a>



    <!-- ADMINISTRATION -->

    <?php

    if (
        isset($_SESSION['role'])
        &&
        (
            $_SESSION['role'] === 'admin'
            ||
            $_SESSION['role'] === 'administrateur'
        )
    ):

    ?>

        <a
            href="admin/dashboard.php"
            class="admin-link"
        >

            ⚙️ Administration

        </a>

    <?php endif; ?>



    <!-- DÉCONNEXION -->

    <a href="deconnexion.php">

        🚪 Déconnexion

    </a>



<?php else: ?>


    <!-- =====================================================
         UTILISATEUR NON CONNECTÉ
    ====================================================== -->


    <!-- AVIS CLIENTS -->

    <a href="avis.php">

        ⭐ Avis des clients

    </a>



    <!-- CONNEXION -->

    <a
        href="connexion.php"
        class="account-link"
    >

        🔑 Mon compte

    </a>


<?php endif; ?>


</nav>


</body>

</html>

