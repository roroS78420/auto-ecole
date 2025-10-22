<?php
// Fichier : /autoecole/php/update-lesson.php

// 1. On démarre la session
session_start();

// 2. On inclut les fichiers de connexion (en remontant d'un dossier)
require "../core/Functions.php";
require "../core/Constants.php";

// 3. On crée la connexion à la base de données
$bdd = connectBDD(HOSTNAME, DATABASE, USERNAME, PASSWORD);

// On prépare la réponse JSON
header('Content-Type: application/json');

// --- Sécurité ---
// 4. L'utilisateur est-il connecté et est-ce un moniteur/admin ?
if (!isset($_SESSION['id_u']) || (int)$_SESSION['lvl'] < 2) { // lvl 2 (Moniteur) ou 3 (Admin)
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

// 5. Récupérer les données envoyées par AJAX
$id_lesson = $_POST['id'];
$start = $_POST['start'];
$end = $_POST['end'];

// 6. Valider les données
if (empty($id_lesson) || empty($start) || empty($end)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

// 7. Mettre à jour la base de données
try {
    $sql = "UPDATE lessons 
            SET date_l = ?, date_fin = ? 
            WHERE id_l = ?";
    
    $params = [$start, $end, $id_lesson];

    // Sécurité : Un moniteur ne peut modifier que SES propres cours
    if ((int)$_SESSION['lvl'] == 2) {
        $sql .= " AND id_m = ?";
        $params[] = $_SESSION['id_u'];
    }

    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cours non trouvé ou permission refusée.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>