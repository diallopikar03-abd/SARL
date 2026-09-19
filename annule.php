<?php
// =====================================================
// annuler.php
// Page de retour après annulation du paiement
// =====================================================

session_start();

header('Content-Type: text/html; charset=utf-8');

// Récupérer la référence si elle existe
$order_id = $_GET['order_id'] ?? $_GET['orderId'] ?? '';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement annulé</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #ef4444;
            color: white;
            font-size: 42px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #dc2626;
            margin-bottom: 15px;
        }

        p {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .commande {
            margin: 20px 0;
            padding: 15px;
            background: #f1f5f9;
            border-radius: 10px;
            color: #334155;
        }

        .boutons {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .bouton {
            display: inline-block;
            padding: 13px 22px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .retour {
            background: #334155;
        }

        .retour:hover {
            background: #1e293b;
        }

        .panier {
            background: #f97316;
        }

        .panier:hover {
            background: #ea580c;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="icon">
        ✕
    </div>

    <h1>Paiement annulé</h1>

    <p>
        Votre paiement n'a pas été effectué.
    </p>

    <p>
        Votre commande n'a pas été validée.
    </p>

    <?php if (!empty($order_id)): ?>

        <div class="commande">
            <strong>Référence de commande :</strong><br>
            <?= htmlspecialchars($order_id) ?>
        </div>

    <?php endif; ?>

    <div class="boutons">

        <a href="panier.php" class="bouton panier">
            Retour au panier
        </a>

        <a href="index.php" class="bouton retour">
            Retour à l'accueil
        </a>

    </div>

</div>

</body>
</html>

