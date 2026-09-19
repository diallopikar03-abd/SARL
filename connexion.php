
<?php
session_start();

require 'db.php';
include 'header.php';

$erreur = "";

// =====================================================
// TRAITEMENT DE LA CONNEXION
// =====================================================

if (isset($_POST['login'])) {

    // Récupération des données
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mdp'] ?? '';

    // Vérification des champs
    if (empty($email) || empty($motDePasse)) {

        $erreur = "Veuillez remplir tous les champs.";

    } else {

        try {

            // =====================================================
            // RECHERCHE DE L'UTILISATEUR
            // =====================================================

            $stmt = $pdo->prepare("
                SELECT *
                FROM utilisateurs
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);


            // =====================================================
            // VÉRIFICATION EMAIL + MOT DE PASSE
            // =====================================================

            if (
                $utilisateur &&
                isset($utilisateur['mot_de_passe']) &&
                password_verify($motDePasse, $utilisateur['mot_de_passe'])
            ) {

                // =====================================================
                // SÉCURISER LA SESSION
                // =====================================================

                session_regenerate_id(true);


                // =====================================================
                // ENREGISTRER LES INFORMATIONS EN SESSION
                // =====================================================

                $_SESSION['user_id'] = $utilisateur['id'];
                $_SESSION['role'] = $utilisateur['role'];
                $_SESSION['nom'] = $utilisateur['nom'];

                if (isset($utilisateur['prenom'])) {
                    $_SESSION['prenom'] = $utilisateur['prenom'];
                }

                if (isset($utilisateur['email'])) {
                    $_SESSION['email'] = $utilisateur['email'];
                }


                // =====================================================
                // REDIRECTION SELON LE RÔLE
                // =====================================================

                $role = strtolower(trim($utilisateur['role']));


                // -----------------------------------------------------
                // ADMINISTRATEUR
                // -----------------------------------------------------

                if ($role === 'admin' || $role === 'administrateur') {

                    header("Location: admin/dashboard.php");
                    exit;
                }


                // -----------------------------------------------------
                // CLIENT
                // -----------------------------------------------------

                elseif ($role === 'client') {

                    header("Location: index.php");
                    exit;
                }


                // -----------------------------------------------------
                // AUTRE RÔLE
                // -----------------------------------------------------

                else {

                    header("Location: index.php");
                    exit;
                }


            } else {

                // Identifiants incorrects
                $erreur = "Email ou mot de passe incorrect.";
            }

        } catch (PDOException $e) {

            $erreur = "Une erreur est survenue lors de la connexion.";

            // Pour le développement uniquement :
            // $erreur = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f4f4;
        }

        .login {
            width: 400px;
            max-width: 90%;
            margin: 70px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
        }

        .login h1 {
            text-align: center;
            margin: 0 0 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #28a745;
        }

        .btn-connexion {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-connexion:hover {
            background: #218838;
        }

        .erreur {
            background: #ffe6e6;
            color: #dc3545;
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #f5c2c7;
        }

        .retour {
            text-align: center;
            margin-top: 20px;
        }

        .retour a {
            color: #28a745;
            text-decoration: none;
        }

        .retour a:hover {
            text-decoration: underline;
        }

        @media (max-width: 500px) {

            .login {
                margin: 30px auto;
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="login">

    <h1>Connexion</h1>


    <!-- =====================================================
         MESSAGE D'ERREUR
         ===================================================== -->

    <?php if (!empty($erreur)) : ?>

        <div class="erreur">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         FORMULAIRE
         ===================================================== -->

    <form method="POST" action="">

        <div class="form-group">

            <label for="email">
                Adresse email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Entrez votre email"
                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="mdp">
                Mot de passe
            </label>

            <input
                type="password"
                id="mdp"
                name="mdp"
                placeholder="Entrez votre mot de passe"
                required
            >

        </div>


        <button
            type="submit"
            name="login"
            class="btn-connexion"
        >
            Se connecter
        </button>

    </form>


    <div class="retour">

        <a href="index.php">
            ← Retour à l'accueil
        </a>

    </div>

</div>

</body>

</html>

