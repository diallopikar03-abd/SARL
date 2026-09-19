<?php
session_start();

require 'db.php';
include 'header.php';

$message = "";

if (isset($_POST['reg'])) {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $mdp = $_POST['mdp'];


    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);


    if ($check->fetch()) {

        $message = "Cet email existe déjà.";

    } else {


        // Crypter le mot de passe
        $hash = password_hash($mdp, PASSWORD_DEFAULT);


        // Récupérer les valeurs ENUM du champ role
        $roleReq = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'role'");
        $roleInfo = $roleReq->fetch(PDO::FETCH_ASSOC);


        // Extraire les valeurs ENUM
        preg_match("/^enum\((.*)\)$/", $roleInfo['Type'], $matches);

        $roles = str_getcsv($matches[1], ',', "'");


        // Mettre client par défaut
        if (in_array('client', $roles)) {
            $role = "client";
        } else {
            $role = $roles[0];
        }



        // Insérer l'utilisateur
        $ins = $pdo->prepare("
            INSERT INTO utilisateurs
            (nom, prenom, email, telephone, adresse, mot_de_passe, role, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");


        $ins->execute([
            $nom,
            $prenom,
            $email,
            $telephone,
            $adresse,
            $hash,
            $role
        ]);


        header("Location: connexion.php");
        exit();

    }
}

?>


<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">
<title>Inscription - DrinkShop</title>


<style>

body{
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f4f4;
}


.formulaire{

    width:450px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,.15);

}


h1{
    text-align:center;
    color:#2c3e50;
}


label{
    font-weight:bold;
}


input, textarea{

    width:100%;
    padding:10px;
    margin:8px 0 15px;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;

}


textarea{
    height:100px;
}


button{

    width:100%;
    padding:12px;
    background:#28a745;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;

}


button:hover{

    background:#218838;

}


.message{

    color:red;
    text-align:center;

}

</style>

</head>


<body>


<div class="formulaire">


<h1>Créer un compte</h1>


<?php if (!empty($message)): ?>

<p class="message">
<?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>


<form method="POST">


<label>Nom :</label>

<input 
type="text" 
name="nom" 
required>


<label>Prénom :</label>

<input 
type="text" 
name="prenom" 
required>


<label>Email :</label>

<input 
type="email" 
name="email" 
required>


<label>Téléphone :</label>

<input 
type="text" 
name="telephone" 
required>


<label>Adresse :</label>

<textarea 
name="adresse" 
required></textarea>


<label>Mot de passe :</label>

<input 
type="password" 
name="mdp" 
required>


<button type="submit" name="reg">
Créer mon compte
</button>


</form>


</div>


</body>

</html>