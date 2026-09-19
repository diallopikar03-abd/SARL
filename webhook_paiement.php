<?php
// =====================================================
// webhook_paiement.php
// Notification de paiement Orange Money
// =====================================================

header('Content-Type: application/json; charset=utf-8');

// =====================================================
// 1. CONNEXION À LA BASE DE DONNÉES
// =====================================================

try {

    $pdo = new PDO(
        "mysql:host=localhost;dbname=Site_web;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ]);

    exit;
}


// =====================================================
// 2. RÉCUPÉRER LES DONNÉES ENVOYÉES
// =====================================================

$input = file_get_contents("php://input");


// =====================================================
// 3. VÉRIFIER QUE DES DONNÉES ONT ÉTÉ REÇUES
// =====================================================

if (empty($input)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Aucune donnée reçue'
    ]);

    exit;
}


// =====================================================
// 4. CONVERTIR LE JSON
// =====================================================

$data = json_decode($input, true);


// =====================================================
// 5. VÉRIFIER LE JSON
// =====================================================

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données JSON invalides'
    ]);

    exit;
}


// =====================================================
// 6. ENREGISTRER LA NOTIFICATION POUR DEBUG
// =====================================================

// Pendant le développement, cela permet de voir
// exactement ce qu'Orange envoie.

file_put_contents(
    __DIR__ . '/webhook_log.txt',
    date('Y-m-d H:i:s') .
    " - " .
    $input .
    PHP_EOL,
    FILE_APPEND
);


// =====================================================
// 7. RÉCUPÉRER LA RÉFÉRENCE DE COMMANDE
// =====================================================

// Selon l'API/configuration Orange utilisée,
// le nom exact du champ peut être différent.

$order_id =
    $data['order_id']
    ?? $data['orderId']
    ?? $data['orderID']
    ?? null;


// =====================================================
// 8. RÉCUPÉRER LE STATUT
// =====================================================

$status =
    $data['status']
    ?? $data['payment_status']
    ?? $data['transaction_status']
    ?? null;


// =====================================================
// 9. VÉRIFIER LA RÉFÉRENCE
// =====================================================

if (empty($order_id)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Référence de commande absente'
    ]);

    exit;
}


// =====================================================
// 10. RECHERCHER LA COMMANDE
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM commandes
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$order_id]);

$commande = $stmt->fetch();


// =====================================================
// 11. COMMANDE INTROUVABLE
// =====================================================

if (!$commande) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Commande introuvable'
    ]);

    exit;
}


// =====================================================
// 12. DÉTERMINER SI LE PAIEMENT EST RÉUSSI
// =====================================================

$statuts_reussis = [
    'SUCCESS',
    'SUCCESSFUL',
    'PAID',
    'COMPLETED',
    'COMPLETED_SUCCESS'
];


// Mettre le statut reçu en majuscules

$status_normalise = strtoupper(
    trim((string)$status)
);


// =====================================================
// 13. SI LE PAIEMENT EST RÉUSSI
// =====================================================

if (in_array($status_normalise, $statuts_reussis, true)) {

    // -------------------------------------------------
    // Mettre la commande à jour
    // -------------------------------------------------

    $update = $pdo->prepare("
        UPDATE commandes
        SET statut = ?
        WHERE id = ?
    ");

    $update->execute([
        'Payée',
        $order_id
    ]);


    // -------------------------------------------------
    // Réponse à Orange
    // -------------------------------------------------

    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Paiement confirmé',
        'order_id' => $order_id
    ]);

    exit;
}


// =====================================================
// 14. SI LE PAIEMENT EST REFUSÉ / ANNULÉ
// =====================================================

$statuts_echec = [
    'FAILED',
    'FAILURE',
    'CANCELLED',
    'CANCELED',
    'DECLINED',
    'EXPIRED'
];


if (in_array($status_normalise, $statuts_echec, true)) {

    $update = $pdo->prepare("
        UPDATE commandes
        SET statut = ?
        WHERE id = ?
    ");

    $update->execute([
        'Annulée',
        $order_id
    ]);


    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Paiement refusé ou annulé',
        'order_id' => $order_id
    ]);

    exit;
}


// =====================================================
// 15. STATUT INCONNU
// =====================================================

http_response_code(200);

echo json_encode([
    'success' => false,
    'message' => 'Statut de paiement non reconnu',
    'order_id' => $order_id,
    'status' => $status
]);

?>

