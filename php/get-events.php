<?php
// Fichier : /autoecole/php/get-events.php

// 1. On démarre la session
session_start();

// 2. On inclut les fichiers de configuration et de connexion
// On utilise "../" pour remonter d'un dossier (de 'php' à 'autoecole')
require "../core/Functions.php";
require "../core/Constants.php";

// 3. On crée la connexion à la base de données !
// La variable $bdd est maintenant disponible.
$bdd = connectBDD(HOSTNAME, DATABASE, USERNAME, PASSWORD);

// 4. Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_u'])) {
    http_response_code(403);
    echo json_encode(["error" => "Accès non autorisé"]);
    exit();
}
$user_id = $_SESSION['id_u'];
$user_lvl = (int)$_SESSION['lvl'];

// 5. Récupérer les dates envoyées par FullCalendar
$start_date = $_GET['start'];
$end_date = $_GET['end'];

// 6. Préparer la requête SQL
$query = "SELECT 
            titre AS title, 
            date_l AS start, 
            date_fin AS end 
          FROM lessons 
          WHERE 
            date_l < ? AND date_fin > ?"; // Logique de chevauchement

$params = [$end_date, $start_date]; 

if ($user_lvl == 1) { // Élève
    $query .= " AND id_e = ?";
    $params[] = $user_id;
} elseif ($user_lvl == 2) { // Moniteur
    $query .= " AND id_m = ?";
    $params[] = $user_id;
}

$requete = $bdd->prepare($query);
$requete->execute($params);
$events = $requete->fetchAll(PDO::FETCH_ASSOC);

// 7. Renvoyer les données au calendrier
header('Content-Type: application/json');
echo json_encode($events);
?>