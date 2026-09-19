
<?php

include 'admin.php';

/* =========================================================
   STATISTIQUES
========================================================= */

$totalBoissons = 0;
$commandesEnAttente = 0;
$totalCommandes = 0;
$totalVentes = 0;


/* =========================================================
   TOTAL BOISSONS
========================================================= */

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM boissons
    ");

    $totalBoissons = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalBoissons = 0;

}


/* =========================================================
   COMMANDES EN ATTENTE
========================================================= */

try {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM commandes
        WHERE LOWER(statut) IN (
            'en attente',
            'en_attente'
        )
    ");

    $stmt->execute();

    $commandesEnAttente = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $commandesEnAttente = 0;

}


/* =========================================================
   TOTAL COMMANDES
========================================================= */

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM commandes
    ");

    $totalCommandes = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalCommandes = 0;

}


/* =========================================================
   CHIFFRE D'AFFAIRES
========================================================= */

try {

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(montant_total), 0)
        FROM commandes
        WHERE LOWER(statut) NOT IN (
            'annulée',
            'annulee'
        )
    ");

    $totalVentes = (float) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalVentes = 0;

}


/* =========================================================
   COMMANDES RECENTES
========================================================= */

$commandesRecentes = [];

try {

    $stmt = $pdo->query("
        SELECT *
        FROM commandes
        ORDER BY id DESC
        LIMIT 5
    ");

    $commandesRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    /*
     * Si la colonne id n'existe pas,
     * on utilise id_commande.
     */

    try {

        $stmt = $pdo->query("
            SELECT *
            FROM commandes
            ORDER BY id_commande DESC
            LIMIT 5
        ");

        $commandesRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e2) {

        $commandesRecentes = [];

    }

}


/* =========================================================
   STATUTS DES COMMANDES
========================================================= */

$statuts = [];

try {

    $stmt = $pdo->query("
        SELECT
            statut,
            COUNT(*) AS total
        FROM commandes
        GROUP BY statut
        ORDER BY total DESC
    ");

    $statuts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $statuts = [];

}


/* =========================================================
   NOM ADMINISTRATEUR
========================================================= */

$nomAdmin = $_SESSION['nom'] ?? 'Administrateur';


/* =========================================================
   PREMIERE LETTRE DU NOM
========================================================= */

$initialeAdmin = strtoupper(
    mb_substr(
        $nomAdmin,
        0,
        1,
        'UTF-8'
    )
);

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
        Dashboard Administrateur - DrinkShop
    </title>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


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
                "Segoe UI",
                Arial,
                sans-serif;

            background:#f5f7fb;

            color:#1f2937;

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar{

            position:fixed;

            left:0;
            top:0;

            width:270px;
            height:100vh;

            background:#111827;

            color:white;

            padding:25px 15px;

            z-index:1000;

            overflow-y:auto;

            box-shadow:
                4px 0 15px
                rgba(0,0,0,.08);

        }


        /* =====================================================
           LOGO
        ===================================================== */

        .logo{

            display:flex;

            align-items:center;

            gap:12px;

            padding:
                0 12px 30px;

            border-bottom:
                1px solid
                rgba(255,255,255,.1);

            margin-bottom:25px;

        }


        .logo img{

            width:45px;
            height:45px;

            object-fit:contain;

            border-radius:10px;

            background:white;

            padding:4px;

        }


        .logo h2{

            font-size:20px;

            color:white;

            font-weight:700;

        }


        /* =====================================================
           MENU
        ===================================================== */

        .menu{

            list-style:none;

        }


        .menu li{

            margin-bottom:7px;

        }


        .menu a{

            display:flex;

            align-items:center;

            gap:13px;

            padding:13px 15px;

            color:#cbd5e1;

            text-decoration:none;

            border-radius:9px;

            transition:.25s;

            font-size:14px;

            font-weight:500;

        }


        .menu a:hover{

            background:
                rgba(37,99,235,.25);

            color:white;

        }


        .menu a.active{

            background:#2563eb;

            color:white;

            box-shadow:
                0 4px 12px
                rgba(37,99,235,.25);

        }


        .menu i{

            width:20px;

            min-width:20px;

            text-align:center;

            font-size:15px;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main{

            margin-left:270px;

            min-height:100vh;

        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar{

            height:85px;

            background:white;

            border-bottom:
                1px solid #e5e7eb;

            display:flex;

            justify-content:space-between;

            align-items:center;

            padding:0 30px;

            position:sticky;

            top:0;

            z-index:900;

            box-shadow:
                0 2px 10px
                rgba(15,23,42,.05);

        }


        .topbar-title{

            display:flex;

            flex-direction:column;

            justify-content:center;

        }


        .topbar-title h1{

            font-size:25px;

            font-weight:700;

            color:#111827;

            line-height:1.2;

        }


        .topbar-title h1::before{

            content:"";

            display:inline-block;

            width:5px;

            height:25px;

            background:#2563eb;

            border-radius:5px;

            margin-right:10px;

            vertical-align:-3px;

        }


        .topbar-title p{

            font-size:13px;

            color:#6b7280;

            margin-top:6px;

        }


        /* =====================================================
           ADMIN PROFILE
        ===================================================== */

        .admin-profile{

            display:flex;

            align-items:center;

            gap:12px;

        }


        .avatar{

            width:44px;

            height:44px;

            border-radius:50%;

            background:#2563eb;

            color:white;

            display:flex;

            align-items:center;

            justify-content:center;

            font-weight:bold;

            font-size:16px;

            box-shadow:
                0 4px 10px
                rgba(37,99,235,.25);

        }


        .profile-info strong{

            display:block;

            font-size:14px;

            color:#111827;

        }


        .profile-info span{

            color:#6b7280;

            font-size:12px;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content{

            padding:30px;

        }


        /* =====================================================
           WELCOME
        ===================================================== */

        .welcome{

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color:white;

            border-radius:15px;

            padding:25px 30px;

            margin-bottom:25px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            overflow:hidden;

            box-shadow:
                0 8px 25px
                rgba(37,99,235,.15);

        }


        .welcome h2{

            font-size:24px;

            margin-bottom:7px;

        }


        .welcome p{

            opacity:.9;

            font-size:14px;

        }


        .welcome-icon{

            font-size:70px;

            opacity:.15;

        }


        /* =====================================================
           STATS GRID
        ===================================================== */

        .stats-grid{

            display:grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:20px;

            margin-bottom:30px;

        }


        /* =====================================================
           STAT CARD
        ===================================================== */

        .stat-card{

            background:white;

            border-radius:14px;

            padding:22px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.06);

            border:
                1px solid #eef0f4;

            transition:.25s;

        }


        .stat-card:hover{

            transform:
                translateY(-4px);

            box-shadow:
                0 8px 25px
                rgba(15,23,42,.1);

        }


        .stat-top{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:18px;

        }


        .stat-title{

            color:#6b7280;

            font-size:13px;

            font-weight:500;

        }


        .stat-icon{

            width:45px;

            height:45px;

            border-radius:11px;

            display:flex;

            align-items:center;

            justify-content:center;

            font-size:19px;

        }


        .icon-blue{

            background:#dbeafe;

            color:#2563eb;

        }


        .icon-orange{

            background:#ffedd5;

            color:#ea580c;

        }


        .icon-green{

            background:#dcfce7;

            color:#16a34a;

        }


        .icon-purple{

            background:#f3e8ff;

            color:#9333ea;

        }


        .stat-number{

            font-size:28px;

            font-weight:700;

            color:#111827;

        }


        .stat-footer{

            margin-top:7px;

            font-size:12px;

            color:#9ca3af;

        }


        /* =====================================================
           DASHBOARD GRID
        ===================================================== */

        .dashboard-grid{

            display:grid;

            grid-template-columns:
                2fr 1fr;

            gap:20px;

        }


        /* =====================================================
           PANELS
        ===================================================== */

        .panel{

            background:white;

            border-radius:14px;

            border:
                1px solid #eef0f4;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.05);

            overflow:hidden;

        }


        .panel-header{

            padding:20px 22px;

            border-bottom:
                1px solid #eef0f4;

            display:flex;

            justify-content:space-between;

            align-items:center;

        }


        .panel-header h3{

            font-size:16px;

            color:#111827;

        }


        .panel-header a{

            color:#2563eb;

            font-size:12px;

            text-decoration:none;

            font-weight:600;

        }


        .panel-header a:hover{

            text-decoration:underline;

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table-container{

            overflow-x:auto;

        }


        table{

            width:100%;

            border-collapse:collapse;

        }


        th{

            text-align:left;

            font-size:12px;

            color:#6b7280;

            font-weight:600;

            padding:14px 20px;

            background:#f9fafb;

        }


        td{

            padding:15px 20px;

            border-top:
                1px solid #f0f1f3;

            font-size:13px;

        }


        tbody tr:hover{

            background:#fafbff;

        }


        /* =====================================================
           BADGES
        ===================================================== */

        .badge{

            display:inline-block;

            padding:6px 10px;

            border-radius:20px;

            font-size:11px;

            font-weight:600;

        }


        .badge-attente{

            background:#fff7ed;

            color:#ea580c;

        }


        .badge-payee{

            background:#eff6ff;

            color:#2563eb;

        }


        .badge-livree{

            background:#ecfdf5;

            color:#059669;

        }


        .badge-annulee{

            background:#fef2f2;

            color:#dc2626;

        }


        .badge-default{

            background:#f3f4f6;

            color:#4b5563;

        }


        /* =====================================================
           STATUS LIST
        ===================================================== */

        .status-list{

            padding:20px;

        }


        .status-item{

            margin-bottom:20px;

        }


        .status-item:last-child{

            margin-bottom:0;

        }


        .status-header{

            display:flex;

            justify-content:space-between;

            margin-bottom:8px;

            font-size:13px;

        }


        .progress{

            height:8px;

            background:#eef2f7;

            border-radius:10px;

            overflow:hidden;

        }


        .progress-bar{

            height:100%;

            background:#2563eb;

            border-radius:10px;

            transition:.3s;

        }


        /* =====================================================
           QUICK ACTIONS
        ===================================================== */

        .quick-actions{

            padding:20px;

            display:grid;

            grid-template-columns:
                repeat(3,1fr);

            gap:12px;

        }


        .quick-action{

            padding:15px;

            border:
                1px solid #e5e7eb;

            border-radius:10px;

            text-decoration:none;

            color:#374151;

            display:flex;

            align-items:center;

            gap:10px;

            font-size:13px;

            font-weight:500;

            transition:.2s;

            background:white;

        }


        .quick-action:hover{

            border-color:#2563eb;

            color:#2563eb;

            background:#eff6ff;

            transform:
                translateY(-2px);

        }


        .quick-action i{

            color:#2563eb;

            width:18px;

            text-align:center;

        }


        /* =====================================================
           RESPONSIVE TABLETTE
        ===================================================== */

        @media(max-width:1100px){

            .stats-grid{

                grid-template-columns:
                    repeat(2,1fr);

            }


            .dashboard-grid{

                grid-template-columns:1fr;

            }


            .quick-actions{

                grid-template-columns:
                    repeat(2,1fr);

            }

        }


        /* =====================================================
           RESPONSIVE MOBILE
        ===================================================== */

        @media(max-width:750px){

            .sidebar{

                width:70px;

                padding:20px 8px;

            }


            .logo h2,
            .menu span{

                display:none;

            }


            .logo{

                justify-content:center;

                padding:
                    0 0 25px;

            }


            .menu a{

                justify-content:center;

                padding:
                    13px 8px;

            }


            .menu i{

                width:auto;

            }


            .main{

                margin-left:70px;

            }


            .topbar{

                padding:0 15px;

                height:75px;

            }


            .topbar-title h1{

                font-size:20px;

            }


            .topbar-title h1::before{

                height:20px;

                width:4px;

            }


            .topbar-title p{

                display:none;

            }


            .content{

                padding:15px;

            }


            .stats-grid{

                grid-template-columns:1fr;

            }


            .profile-info{

                display:none;

            }


            .welcome{

                padding:20px;

            }


            .welcome h2{

                font-size:20px;

            }


            .welcome-icon{

                display:none;

            }


            .quick-actions{

                grid-template-columns:1fr;

            }

        }


        /* =====================================================
           PETITS TELEPHONES
        ===================================================== */

        @media(max-width:450px){

            .topbar-title h1{

                font-size:18px;

            }


            .avatar{

                width:38px;

                height:38px;

                font-size:14px;

            }


            .stat-number{

                font-size:24px;

            }


            .welcome h2{

                font-size:18px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        <img
            src="../image/Image1.png"
            alt="Logo DrinkShop"
            onerror="this.style.display='none';"
        >

        <h2>
            DrinkShop
        </h2>

    </div>


    <!-- MENU -->

    <ul class="menu">


        <!-- DASHBOARD -->

        <li>

            <a
                href="dashboard.php"
                class="active"
            >

                <i class="fa-solid fa-gauge-high"></i>

                <span>
                    Dashboard
                </span>

            </a>

        </li>


        <!-- BOISSONS -->

        <li>

            <a href="boissons.php">

                <i class="fa-solid fa-bottle-water"></i>

                <span>
                    Boissons
                </span>

            </a>

        </li>


        <!-- CATEGORIES -->

        <li>

            <a href="categories.php">

                <i class="fa-solid fa-layer-group"></i>

                <span>
                    Catégories
                </span>

            </a>

        </li>


        <!-- COMMANDES -->

        <li>

            <a href="commandes.php">

                <i class="fa-solid fa-cart-shopping"></i>

                <span>
                    Commandes
                </span>

            </a>

        </li>


        <!-- CLIENTS -->

        <li>

            <a href="clients.php">

                <i class="fa-solid fa-users"></i>

                <span>
                    Clients
                </span>

            </a>

        </li>


        <!-- AVIS -->

        <li>

            <a href="avis.php">

                <i class="fa-solid fa-star"></i>

                <span>
                    Avis clients
                </span>

            </a>

        </li>


        <!-- PAIEMENTS -->

        <li>

            <a href="paiements.php">

                <i class="fa-solid fa-credit-card"></i>

                <span>
                    Paiements
                </span>

            </a>

        </li>


        <!-- STATISTIQUES -->

        <li>

            <a href="statistiques.php">

                <i class="fa-solid fa-chart-line"></i>

                <span>
                    Statistiques
                </span>

            </a>

        </li>


    </ul>

</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- =====================================================
         TOPBAR
    ====================================================== -->

    <header class="topbar">


        <div class="topbar-title">

            <h1>
                Tableau de bord
            </h1>

            <p>
                Vue générale de votre boutique DrinkShop
            </p>

        </div>


        <!-- PROFIL ADMIN -->

        <div class="admin-profile">


            <div class="avatar">

                <?= htmlspecialchars($initialeAdmin) ?>

            </div>


            <div class="profile-info">

                <strong>

                    <?= htmlspecialchars($nomAdmin) ?>

                </strong>

                <span>
                    Administrateur
                </span>

            </div>


        </div>


    </header>



    <!-- =====================================================
         CONTENU
    ====================================================== -->

    <section class="content">


        <!-- =================================================
             BIENVENUE
        ================================================== -->

        <div class="welcome">


            <div>

                <h2>

                    Bonjour
                    <?= htmlspecialchars($nomAdmin) ?>
                    👋

                </h2>


                <p>

                    Voici un aperçu de l'activité de votre boutique
                    aujourd'hui.

                </p>

            </div>


            <div class="welcome-icon">

                <i class="fa-solid fa-store"></i>

            </div>


        </div>



        <!-- =================================================
             STATISTIQUES
        ================================================== -->

        <div class="stats-grid">


            <!-- TOTAL BOISSONS -->

            <div class="stat-card">


                <div class="stat-top">


                    <div class="stat-title">

                        Total des boissons

                    </div>


                    <div class="stat-icon icon-blue">

                        <i class="fa-solid fa-bottle-water"></i>

                    </div>


                </div>


                <div class="stat-number">

                    <?= htmlspecialchars($totalBoissons) ?>

                </div>


                <div class="stat-footer">

                    Produits disponibles

                </div>


            </div>



            <!-- COMMANDES EN ATTENTE -->

            <div class="stat-card">


                <div class="stat-top">


                    <div class="stat-title">

                        Commandes en attente

                    </div>


                    <div class="stat-icon icon-orange">

                        <i class="fa-solid fa-clock"></i>

                    </div>


                </div>


                <div class="stat-number">

                    <?= htmlspecialchars($commandesEnAttente) ?>

                </div>


                <div class="stat-footer">

                    Commandes à traiter

                </div>


            </div>



            <!-- TOTAL COMMANDES -->

            <div class="stat-card">


                <div class="stat-top">


                    <div class="stat-title">

                        Total commandes

                    </div>


                    <div class="stat-icon icon-purple">

                        <i class="fa-solid fa-cart-shopping"></i>

                    </div>


                </div>


                <div class="stat-number">

                    <?= htmlspecialchars($totalCommandes) ?>

                </div>


                <div class="stat-footer">

                    Toutes les commandes

                </div>


            </div>



            <!-- CHIFFRE AFFAIRES -->

            <div class="stat-card">


                <div class="stat-top">


                    <div class="stat-title">

                        Chiffre d'affaires

                    </div>


                    <div class="stat-icon icon-green">

                        <i class="fa-solid fa-money-bill-wave"></i>

                    </div>


                </div>


                <div class="stat-number">

                    <?= number_format(
                        $totalVentes,
                        0,
                        ',',
                        ' '
                    ) ?>

                    GNF

                </div>


                <div class="stat-footer">

                    Ventes enregistrées

                </div>


            </div>


        </div>



        <!-- =================================================
             PARTIE BASSE
        ================================================== -->

        <div class="dashboard-grid">


            <!-- =================================================
                 COMMANDES RECENTES
            ================================================== -->

            <div class="panel">


                <div class="panel-header">


                    <h3>

                        <i
                            class="fa-solid fa-cart-shopping"
                            style="
                                color:#2563eb;
                                margin-right:7px;
                            "
                        ></i>

                        Commandes récentes

                    </h3>


                    <a href="commandes.php">

                        Voir toutes

                    </a>


                </div>


                <div class="table-container">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Client
                                </th>

                                <th>
                                    Montant
                                </th>

                                <th>
                                    Statut
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($commandesRecentes)): ?>


                            <?php foreach ($commandesRecentes as $commande): ?>


                                <?php

                                $statut =
                                    $commande['statut']
                                    ?? 'Inconnu';


                                $statutNormalise =
                                    strtolower(
                                        trim(
                                            $statut
                                        )
                                    );


                                $classeStatut =
                                    'badge-default';


                                /*
                                 * EN ATTENTE
                                 */

                                if (
                                    $statutNormalise === 'en attente' ||
                                    $statutNormalise === 'en_attente'
                                ) {

                                    $classeStatut =
                                        'badge-attente';

                                }


                                /*
                                 * PAYEE
                                 */

                                elseif (
                                    $statutNormalise === 'payée' ||
                                    $statutNormalise === 'payee' ||
                                    $statutNormalise === 'payé' ||
                                    $statutNormalise === 'paye'
                                ) {

                                    $classeStatut =
                                        'badge-payee';

                                }


                                /*
                                 * LIVREE
                                 */

                                elseif (
                                    $statutNormalise === 'livrée' ||
                                    $statutNormalise === 'livree'
                                ) {

                                    $classeStatut =
                                        'badge-livree';

                                }


                                /*
                                 * ANNULEE
                                 */

                                elseif (
                                    $statutNormalise === 'annulée' ||
                                    $statutNormalise === 'annulee'
                                ) {

                                    $classeStatut =
                                        'badge-annulee';

                                }


                                /*
                                 * ID COMMANDE
                                 */

                                $idCommande =
                                    $commande['id']
                                    ??
                                    $commande['id_commande']
                                    ??
                                    '';


                                /*
                                 * NOM CLIENT
                                 */

                                $nomClient =
                                    $commande['nom_client']
                                    ??
                                    $commande['nom']
                                    ??
                                    'Client';


                                /*
                                 * MONTANT
                                 */

                                $montant =
                                    $commande['montant_total']
                                    ??
                                    $commande['montant']
                                    ??
                                    0;

                                ?>


                                <tr>


                                    <td>

                                        <strong>

                                            #<?= htmlspecialchars(
                                                $idCommande
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $nomClient
                                        ) ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= number_format(
                                                (float)$montant,
                                                0,
                                                ',',
                                                ' '
                                            ) ?>

                                            GNF

                                        </strong>

                                    </td>


                                    <td>

                                        <span
                                            class="badge
                                            <?= htmlspecialchars(
                                                $classeStatut
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $statut
                                            ) ?>

                                        </span>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="4"
                                    style="
                                        text-align:center;
                                        padding:35px;
                                        color:#9ca3af;
                                    "
                                >

                                    <i
                                        class="fa-solid fa-cart-shopping"
                                        style="
                                            font-size:25px;
                                            display:block;
                                            margin-bottom:10px;
                                        "
                                    ></i>

                                    Aucune commande enregistrée.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </div>



            <!-- =================================================
                 STATUTS
            ================================================== -->

            <div class="panel">


                <div class="panel-header">


                    <h3>

                        <i
                            class="fa-solid fa-chart-pie"
                            style="
                                color:#2563eb;
                                margin-right:7px;
                            "
                        ></i>

                        Statut des commandes

                    </h3>


                </div>


                <div class="status-list">


                <?php if (!empty($statuts)): ?>


                    <?php foreach ($statuts as $statut): ?>


                        <?php

                        $total =
                            (int)$statut['total'];


                        $pourcentage =
                            $totalCommandes > 0
                            ? round(
                                (
                                    $total
                                    /
                                    $totalCommandes
                                ) * 100
                            )
                            : 0;

                        ?>


                        <div class="status-item">


                            <div class="status-header">


                                <span>

                                    <?= htmlspecialchars(
                                        $statut['statut']
                                    ) ?>

                                </span>


                                <strong>

                                    <?= $total ?>

                                </strong>


                            </div>


                            <div class="progress">


                                <div
                                    class="progress-bar"
                                    style="
                                        width:<?= $pourcentage ?>%
                                    ";
                                ></div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <p
                        style="
                            color:#9ca3af;
                            font-size:13px;
                            text-align:center;
                            padding:20px 0;
                        "
                    >

                        Aucun statut disponible.

                    </p>


                <?php endif; ?>


                </div>


            </div>


        </div>



        <!-- =================================================
             ACTIONS RAPIDES
        ================================================== -->

        <div
            class="panel"
            style="margin-top:20px;"
        >


            <div class="panel-header">


                <h3>

                    <i
                        class="fa-solid fa-bolt"
                        style="
                            color:#f59e0b;
                            margin-right:7px;
                        "
                    ></i>

                    Actions rapides

                </h3>


            </div>


            <div class="quick-actions">


                <!-- BOISSONS -->

                <a
                    href="boissons.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-plus"></i>

                    Ajouter une boisson

                </a>


                <!-- CATEGORIES -->

                <a
                    href="categories.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-layer-group"></i>

                    Gérer les catégories

                </a>


                <!-- COMMANDES -->

                <a
                    href="commandes.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-list"></i>

                    Gérer les commandes

                </a>


                <!-- CLIENTS -->

                <a
                    href="clients.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-users"></i>

                    Gérer les clients

                </a>


                <!-- AVIS -->

                <a
                    href="avis.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-star"></i>

                    Gérer les avis clients

                </a>


                <!-- PAIEMENTS -->

                <a
                    href="paiements.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-credit-card"></i>

                    Gérer les paiements

                </a>


                <!-- STATISTIQUES -->

                <a
                    href="statistiques.php"
                    class="quick-action"
                >

                    <i class="fa-solid fa-chart-column"></i>

                    Voir les statistiques

                </a>


            </div>


        </div>


    </section>


</main>


</body>

</html>

