<?php

include 'admin.php';


// ===============================
// AJOUTER UNE BOISSON
// ===============================

if (isset($_POST['add'])) {

    $nom = trim($_POST['nom']);
    $marque = trim($_POST['marque']);
    $contenance = trim($_POST['contenance']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $categorie = intval($_POST['categorie']);


    if (
        !empty($nom) &&
        !empty($marque) &&
        !empty($contenance) &&
        $prix > 0 &&
        $stock >= 0
    ) {

        try {

            $sql = "INSERT INTO boissons
                    (nom, marque, contenance, description, prix, stock, id_categorie)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $nom,
                $marque,
                $contenance,
                $description,
                $prix,
                $stock,
                $categorie
            ]);


            echo "<script>
                    alert('Boisson ajoutée avec succès !');
                    window.location.href='boissons.php';
                  </script>";

        } catch(PDOException $e) {

            echo "Erreur SQL : " . $e->getMessage();

        }

    } else {

        echo "<script>
                alert('Veuillez remplir correctement les champs !');
              </script>";

    }

}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Gestion des boissons</title>


    <style>

        body{
            font-family:Arial, Helvetica, sans-serif;
            margin:20px;
            background:#f5f6f8;
        }


        h1{
            color:#343a40;
        }


        h2{
            color:#343a40;
            margin-top:30px;
        }


        form{
            width:500px;
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:30px;
            box-shadow:0 3px 10px rgba(0,0,0,0.10);
        }


        label{
            display:block;
            margin-top:10px;
            font-weight:bold;
        }


        input,
        textarea,
        select{

            width:100%;
            padding:9px;
            margin:8px 0;

            box-sizing:border-box;

            border:1px solid #ccc;
            border-radius:5px;
        }


        textarea{
            height:80px;
            resize:vertical;
        }


        button{

            background:#28a745;
            color:white;

            border:none;

            padding:10px 20px;

            cursor:pointer;

            border-radius:5px;

            font-size:15px;

            margin-top:10px;
        }


        button:hover{
            background:#218838;
        }


        /* TABLEAU */

        .table-container{
            overflow-x:auto;
            background:white;
            border-radius:10px;
            box-shadow:0 3px 10px rgba(0,0,0,0.10);
        }


        table{

            width:100%;

            border-collapse:collapse;

            min-width:900px;
        }


        table th,
        table td{

            border:1px solid #ddd;

            padding:10px;

            text-align:center;
        }


        table th{

            background:#343a40;

            color:white;
        }


        table tr:nth-child(even){
            background:#f8f9fa;
        }


        table tr:hover{
            background:#eef2f5;
        }

    </style>

</head>


<body>


<h1>Gestion des Boissons</h1>


<!-- ===============================
     FORMULAIRE
================================ -->


<form method="POST">


    <label>Nom de la boisson</label>

    <input
        type="text"
        name="nom"
        placeholder="Ex : Coca-Cola"
        required
    >


    <label>Marque</label>

    <input
        type="text"
        name="marque"
        placeholder="Ex : Coca-Cola"
        required
    >


    <label>Contenance</label>

    <input
        type="text"
        name="contenance"
        placeholder="Ex : 33 cl, 50 cl, 1 L"
        required
    >


    <label>Description</label>

    <textarea
        name="description"
        placeholder="Description de la boisson"
    ></textarea>


    <label>Prix (GNF)</label>

    <input
        type="number"
        step="0.01"
        name="prix"
        placeholder="Ex : 10.00"
        required
    >


    <label>Stock</label>

    <input
        type="number"
        name="stock"
        min="0"
        placeholder="Ex : 50"
        required
    >


    <label>ID Catégorie</label>

    <input
        type="number"
        name="categorie"
        min="1"
        placeholder="Ex : 1"
        required
    >


    <button type="submit" name="add">

        Ajouter

    </button>


</form>


<!-- ===============================
     LISTE DES BOISSONS
================================ -->


<h2>Liste des boissons</h2>


<div class="table-container">

<table>


<tr>

    <th>ID</th>

    <th>Nom</th>

    <th>Marque</th>

    <th>Contenance</th>

    <th>Description</th>

    <th>Prix</th>

    <th>Stock</th>

    <th>Catégorie</th>

</tr>


<?php

$produits = $pdo->query("
    SELECT *
    FROM boissons
    ORDER BY id_boisson DESC
");


while($p = $produits->fetch(PDO::FETCH_ASSOC)){

?>


<tr>


    <td>
        <?= htmlspecialchars($p['id_boisson']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['nom']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['marque']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['contenance']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['description']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['prix']); ?> GNF
    </td>


    <td>
        <?= htmlspecialchars($p['stock']); ?>
    </td>


    <td>
        <?= htmlspecialchars($p['id_categorie']); ?>
    </td>


</tr>


<?php

}

?>


</table>

</div>


</body>
</html>