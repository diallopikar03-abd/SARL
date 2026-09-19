
<?php
// =====================================================
// succes.php
// Page de retour après paiement réussi
// =====================================================

session_start();

header('Content-Type: text/html; charset=utf-8');

// Récupérer la référence de commande si Orange l'envoie
$order_id = $_GET['order_id'] ?? $_GET['orderId'] ?? '';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement réussi</title>

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
            background: #22c55e;
            color: white;
            font-size: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #16a34a;
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

        .bouton {
            display: inline-block;
            margin-top: 20px;
            padding: 13px 25px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .bouton:hover {
            background: #15803d;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

<div class="container">

    <div class="icon">
        ✓
    </div>

    <h1>Paiement réussi !</h1>

    <p>
        Votre paiement a été effectué avec succès.
    </p>

    <p>
        Merci pour votre commande.
    </p>

    <?php if (!empty($order_id)): ?>

        <div class="commande">
            <strong>Référence de commande :</strong><br>
            <?= htmlspecialchars($order_id) ?>
        </div>

    <?php endif; ?>

    <a href="index.php" class="bouton">
        Retour à l'accueil
    </a>

</div>

</body>
</html>
```
