<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once "includes/db.php";
require_once "includes/functions.php";

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Check if parameters are provided
if (!isset($_GET['type']) || !isset($_GET['id']) || !isset($_GET['formation_id'])) {
    header("Location: formations.php");
    exit();
}

$type = $_GET['type'];
$id = intval($_GET['id']);
$formationId = intval($_GET['formation_id']);
$success = false;
$message = "";

// Process delete based on type
switch ($type) {
    case 'champ':
        $sql = "DELETE FROM formation_champs WHERE id_formation = ? AND id_champ = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $formationId, $id);
        $success = $stmt->execute();
        $message = "Champ disciplinaire supprimé avec succès";
        break;
        
    case 'structure':
        $sql = "DELETE FROM formation_structures WHERE id_formation = ? AND id_structure = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $formationId, $id);
        $success = $stmt->execute();
        $message = "Structure de recherche supprimée avec succès";
        break;
        
    case 'condition':
        $sql = "DELETE FROM formation_conditions WHERE id_formation = ? AND id_condition = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $formationId, $id);
        $success = $stmt->execute();
        $message = "Condition d'accès supprimée avec succès";
        break;
        
    case 'specialite':
        $sql = "DELETE FROM formation_specialites WHERE id_formation = ? AND id_specialite = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $formationId, $id);
        $success = $stmt->execute();
        $message = "Spécialité supprimée avec succès";
        break;
        
    default:
        $message = "Type de relation invalide";
}

// Redirect back to formation details
if ($success) {
    header("Location: formation_details.php?id=" . $formationId . "&success=" . urlencode($message));
} else {
    header("Location: formation_details.php?id=" . $formationId . "&error=" . urlencode("Erreur lors de la suppression: " . $conn->error));
}
exit();
?>