<?php

session_start();

require 'db.php';

$msg = "";
$type_msg = "";

/*
|--------------------------------------------------------------------------
| Récupérer la commande
|--------------------------------------------------------------------------
|
| Après le paiement, on redirige le client vers :
|
| avis.php?id_commande=123
|
*/

$id_commande = intval($_GET['id_commande'] ?? $_POST['id_commande'] ?? 0);


/*
|--------------------------------------------------------------------------
| Vérifier qu'une commande existe
|--------------------------------------------------------------------------
*/

if ($id_commande <= 0) {

    $msg = "Aucune commande valide n'a été sélectionnée.";
    $type_msg = "error";

    $commande = null;
    $boissons = [];

} else {

    try {

        /*
        |--------------------------------------------------------------------------
        | Récupérer la commande
        |--------------------------------------------------------------------------
        |
        | On récupère l'utilisateur qui a effectué la commande.
        |
        */

        $stmt = $pdo->prepare("
            SELECT
                id_commande,
                id_utilisateur,
                numero_commande,
                montant_total,
                statut,
                date_commande
            FROM commandes
            WHERE id_commande = ?
            LIMIT 1
        ");

        $stmt->execute([$id_commande]);

        $commande = $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | Vérifier que la commande existe
        |--------------------------------------------------------------------------
        */

        if (!$commande) {

            $msg = "Cette commande n'existe pas.";
            $type_msg = "error";

            $boissons = [];

        /*
        |--------------------------------------------------------------------------
        | Vérifier que la commande est payée
        |--------------------------------------------------------------------------
        */

        } elseif (
            !in_array(
                strtolower(trim($commande['statut'])),
                ['payee', 'payé', 'paye']
            )
        ) {

            $msg = "Vous pourrez donner votre avis après le paiement de la commande.";
            $type_msg = "error";

            $boissons = [];

        } else {

            /*
            |--------------------------------------------------------------------------
            | Récupérer les boissons de cette commande
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | Une boisson disponible dans le formulaire doit obligatoirement
            | avoir été achetée dans cette commande.
            |
            */

            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    b.id_boisson,
                    b.nom

                FROM commande_details cd

                INNER JOIN boissons b
                    ON cd.id_boisson = b.id_boisson

                WHERE cd.id_commande = ?

                ORDER BY b.nom ASC
            ");

            $stmt->execute([$id_commande]);

            $boissons = $stmt->fetchAll(PDO::FETCH_ASSOC);


            if (empty($boissons)) {

                $msg = "Aucune boisson n'a été trouvée dans cette commande.";
                $type_msg = "error";
            }
        }

    } catch (PDOException $e) {

        $commande = null;
        $boissons = [];

        $msg = "Une erreur est survenue lors de la récupération de la commande.";
        $type_msg = "error";
    }
}


/*
|--------------------------------------------------------------------------
| Enregistrer l'avis
|--------------------------------------------------------------------------
*/

if (
    isset($_POST['send'])
    && $commande
    && !empty($boissons)
) {

    $id_boisson = intval($_POST['id_boisson'] ?? 0);

    $note = intval($_POST['note'] ?? 0);

    $commentaire = trim(
        $_POST['commentaire'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | ID utilisateur récupéré directement depuis la commande
    |--------------------------------------------------------------------------
    |
    | Pas besoin de $_SESSION['user_id'].
    |
    */

    $id_utilisateur = intval(
        $commande['id_utilisateur']
    );


    /*
    |--------------------------------------------------------------------------
    | Vérification de la boisson
    |--------------------------------------------------------------------------
    */

    $boissonAutorisee = false;

    foreach ($boissons as $boisson) {

        if (
            (int)$boisson['id_boisson']
            === $id_boisson
        ) {

            $boissonAutorisee = true;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifications
    |--------------------------------------------------------------------------
    */

    if ($id_boisson <= 0) {

        $msg = "Veuillez sélectionner une boisson.";

        $type_msg = "error";

    } elseif (!$boissonAutorisee) {

        $msg = "Cette boisson ne fait pas partie de cette commande.";

        $type_msg = "error";

    } elseif ($note < 1 || $note > 5) {

        $msg = "Veuillez choisir une note entre 1 et 5.";

        $type_msg = "error";

    } elseif (empty($commentaire)) {

        $msg = "Veuillez écrire un commentaire.";

        $type_msg = "error";

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Vérifier si cette commande possède déjà un avis
            |--------------------------------------------------------------------------
            |
            | On évite que le même client donne plusieurs fois un avis
            | sur la même boisson pour cette commande.
            |
            */

            $checkAvis = $pdo->prepare("
                SELECT id_avisi
                FROM avis
                WHERE d_utilisateur = ?
                AND id_boisson = ?
                LIMIT 1
            ");

            $checkAvis->execute([
                $id_utilisateur,
                $id_boisson
            ]);


            if ($checkAvis->fetch()) {

                $msg = "Vous avez déjà donné un avis pour cette boisson.";

                $type_msg = "error";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Enregistrer l'avis
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    INSERT INTO avis
                    (
                        d_utilisateur,
                        id_boisson,
                        note,
                        commentaire,
                        date_avis
                    )
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $id_utilisateur,
                    $id_boisson,
                    $note,
                    $commentaire
                ]);


                $msg = "Merci ! Votre avis a été envoyé avec succès.";

                $type_msg = "success";


                /*
                |--------------------------------------------------------------------------
                | Nettoyer le formulaire
                |--------------------------------------------------------------------------
                */

                $_POST = [];
            }

        } catch (PDOException $e) {

            $msg = "Erreur lors de l'enregistrement de votre avis.";

            $type_msg = "error";
        }
    }
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

<title>Donner votre avis</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f7fb;

    color: #222;
}

.avis-container {

    max-width: 680px;

    margin: 45px auto;

    padding: 20px;
}

.avis-card {

    background: white;

    border-radius: 18px;

    padding: 35px;

    box-shadow:
        0 10px 35px rgba(0,0,0,0.08);
}

.avis-header {

    text-align: center;

    margin-bottom: 30px;
}

.avis-header h1 {

    margin: 0 0 10px;

    font-size: 30px;
}

.avis-header p {

    color: #777;

    margin: 0;
}


/*
|--------------------------------------------------------------------------
| Commande
|--------------------------------------------------------------------------
*/

.commande-info {

    background: #f8f9fa;

    border: 1px solid #eee;

    border-radius: 12px;

    padding: 16px;

    margin-bottom: 25px;
}

.commande-info strong {

    color: #007bff;
}


/*
|--------------------------------------------------------------------------
| Formulaire
|--------------------------------------------------------------------------
*/

.form-group {

    margin-bottom: 22px;
}

.form-group label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;
}

select,
textarea {

    width: 100%;

    padding: 13px;

    border: 1px solid #ddd;

    border-radius: 9px;

    font-size: 15px;

    outline: none;
}

select:focus,
textarea:focus {

    border-color: #007bff;

    box-shadow:
        0 0 0 3px rgba(0,123,255,.10);
}

textarea {

    min-height: 150px;

    resize: vertical;
}


/*
|--------------------------------------------------------------------------
| Étoiles
|--------------------------------------------------------------------------
*/

.stars {

    display: flex;

    flex-direction: row-reverse;

    justify-content: flex-end;

    gap: 5px;
}

.stars input {

    display: none;
}

.stars label {

    font-size: 42px;

    color: #ccc;

    cursor: pointer;

    transition: .2s;
}

.stars label:hover,
.stars label:hover ~ label {

    color: #ffc107;
}

.stars input:checked ~ label {

    color: #ffc107;
}


/*
|--------------------------------------------------------------------------
| Bouton
|--------------------------------------------------------------------------
*/

.btn-send {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 9px;

    background: #007bff;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: .2s;
}

.btn-send:hover {

    background: #0056b3;
}


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

.message {

    padding: 14px;

    border-radius: 9px;

    margin-bottom: 20px;

    text-align: center;

    font-weight: bold;
}

.success {

    background: #d4edda;

    color: #155724;

    border: 1px solid #c3e6cb;
}

.error {

    background: #f8d7da;

    color: #721c24;

    border: 1px solid #f5c6cb;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width:700px) {

    .avis-container {

        margin: 20px auto;

        padding: 12px;
    }

    .avis-card {

        padding: 25px 18px;
    }

    .avis-header h1 {

        font-size: 25px;
    }

    .stars label {

        font-size: 35px;
    }
}

</style>

</head>


<body>


<?php include 'header.php'; ?>


<div class="avis-container">

    <div class="avis-card">


        <div class="avis-header">

            <h1>
                ⭐ Donnez votre avis
            </h1>

            <p>
                Merci pour votre commande !
                Votre avis nous aide à améliorer nos boissons.
            </p>

        </div>


        <?php if ($commande): ?>

            <div class="commande-info">

                Commande :

                <strong>
                    <?= htmlspecialchars(
                        $commande['numero_commande']
                        ?? ('CMD-' . $commande['id_commande'])
                    ) ?>
                </strong>

                <br>

                Montant :

                <strong>
                    <?= number_format(
                        (float)$commande['montant_total'],
                        0,
                        ',',
                        ' '
                    ) ?>
                    GNF
                </strong>

            </div>

        <?php endif; ?>


        <?php if (!empty($msg)): ?>

            <div class="message <?= htmlspecialchars($type_msg) ?>">

                <?= htmlspecialchars($msg) ?>

            </div>

        <?php endif; ?>


        <?php if (
            $commande
            && !empty($boissons)
            && $type_msg !== "success"
        ): ?>


            <form method="POST">

                <input
                    type="hidden"
                    name="id_commande"
                    value="<?= (int)$id_commande ?>"
                >


                <!-- BOISSON -->

                <div class="form-group">

                    <label for="id_boisson">

                        Quelle boisson avez-vous appréciée ?

                    </label>

                    <select
                        name="id_boisson"
                        id="id_boisson"
                        required
                    >

                        <option value="">

                            -- Choisissez une boisson --

                        </option>


                        <?php foreach ($boissons as $boisson): ?>

                            <option
                                value="<?= (int)$boisson['id_boisson'] ?>"
                                <?= (
                                    isset($_POST['id_boisson'])
                                    &&
                                    $_POST['id_boisson']
                                    == $boisson['id_boisson']
                                )
                                ? 'selected'
                                : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $boisson['nom']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- NOTE -->

                <div class="form-group">

                    <label>

                        Votre note

                    </label>


                    <div class="stars">


                        <input
                            type="radio"
                            id="star5"
                            name="note"
                            value="5"
                            required
                        >

                        <label for="star5">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star4"
                            name="note"
                            value="4"
                        >

                        <label for="star4">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star3"
                            name="note"
                            value="3"
                        >

                        <label for="star3">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star2"
                            name="note"
                            value="2"
                        >

                        <label for="star2">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star1"
                            name="note"
                            value="1"
                        >

                        <label for="star1">
                            ★
                        </label>


                    </div>

                </div>


                <!-- COMMENTAIRE -->

                <div class="form-group">

                    <label for="commentaire">

                        Votre commentaire

                    </label>


                    <textarea
                        name="commentaire"
                        id="commentaire"
                        placeholder="Que pensez-vous de cette boisson ?"
                        required
                    ><?= htmlspecialchars(
                        $_POST['commentaire'] ?? ''
                    ) ?></textarea>

                </div>


                <!-- BOUTON -->

                <button
                    type="submit"
                    name="send"
                    class="btn-send"
                >

                    ⭐ Envoyer mon avis

                </button>

            </form>


        <?php elseif ($type_msg === "success"): ?>

            <div style="
                text-align:center;
                padding:20px;
            ">

                <div style="
                    font-size:50px;
                    margin-bottom:15px;
                ">
                    ✅
                </div>

                <h2>
                    Merci pour votre avis !
                </h2>

                <p style="color:#777;">

                    Votre avis a bien été enregistré.

                </p>

                <a
                    href="produits.php"
                    style="
                        display:inline-block;
                        margin-top:15px;
                        background:#007bff;
                        color:white;
                        padding:12px 20px;
                        border-radius:8px;
                        text-decoration:none;
                    "
                >

                    Continuer mes achats

                </a>

            </div>


        <?php endif; ?>


    </div>

</div>


</body>

</html>

