<?php
include 'admin.php';

// Ajouter une catégorie
if (isset($_POST['add_cat'])) {

    $nom = trim($_POST['nom']);

    if (!empty($nom)) {

        $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
        $stmt->execute([$nom]);

        echo "<script>alert('Catégorie ajoutée avec succès.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des catégories</title>

    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            margin:20px;
        }

        form{
            width:400px;
            background:#f5f5f5;
            padding:20px;
            border-radius:10px;
            margin-bottom:30px;
        }

        input{
            width:100%;
            padding:10px;
            margin-bottom:10px;
            box-sizing:border-box;
        }

        button{
            background:#28a745;
            color:#fff;
            border:none;
            padding:10px 20px;
            border-radius:5px;
            cursor:pointer;
        }

        button:hover{
            background:#218838;
        }

        table{
            width:60%;
            border-collapse:collapse;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:10px;
            text-align:center;
        }

        table th{
            background:#343a40;
            color:#fff;
        }
    </style>
</head>
<body>

<h1>Gestion des catégories</h1>

<form method="POST">

    <label>Nom de la catégorie</label>

    <input
        type="text"
        name="nom"
        placeholder="Nouvelle catégorie"
        required
    >

    <button type="submit" name="add_cat">
        Ajouter
    </button>

</form>

<h2>Liste des catégories</h2>

<table>

<tr>
    <th>ID</th>
    <th>Nom</th>
</tr>

<?php
$res = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");

while ($c = $res->fetch(PDO::FETCH_ASSOC)) {
?>

<tr>
    <td><?= $c['id_categorie']; ?></td>
    <td><?= htmlspecialchars($c['nom']); ?></td>
</tr>

<?php
}
?>

</table>

</body>
</html>