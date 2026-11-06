<?php
ob_start();
// Include header
require_once "includes/header.php";

// Check if user is director, otherwise redirect
if (!isDirector()) {
    header("Location: dashboard.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: centers.php");
    exit();
}

$id = intval($_GET['id']);
$error = '';
$success = false;

// Get center data
$center = getRecordById($conn, "ced", $id);

// Check if center exists
if (!$center) {
    header("Location: centers.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nom = sanitize($_POST['nom']);
    $directeur = sanitize($_POST['directeur']);
    $email = sanitize($_POST['email']);
    $gsm = sanitize($_POST['gsm']);
    
    // Validate data
    if (empty($nom)) {
        $error = "Le nom du centre est obligatoire";
    } else {
        // Update in database
        $sql = "UPDATE ced SET nom = ?, directeur = ?, email_contact = ?, gsm = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nom, $directeur, $email, $gsm, $id);
        
        if ($stmt->execute()) {
            // Redirect to centers page with success message
            header("Location: centers.php?updated=1");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour du centre: " . $conn->error;
        }
        
        $stmt->close();
    }
}
?>

<div class="page-header">
    <h1>Modifier un Centre Doctoral</h1>
    <a href="centers.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="card">
        <div class="card-content">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" class="form">
                <div class="form-group">
                    <label for="nom"><i class="fas fa-university"></i> Nom du Centre <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" value="<?php echo $center['nom']; ?>" placeholder="Entrez le nom du centre" required>
                </div>
                
                <div class="form-group">
                    <label for="directeur"><i class="fas fa-user-tie"></i> Directeur</label>
                    <input type="text" id="directeur" name="directeur" value="<?php echo $center['directeur']; ?>" placeholder="Nom du directeur">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email de Contact</label>
                    <input type="email" id="email" name="email" value="<?php echo $center['email_contact']; ?>" placeholder="Adresse email de contact">
                </div>
                
                <div class="form-group">
                    <label for="gsm"><i class="fas fa-phone"></i> Numéro GSM</label>
                    <input type="text" id="gsm" name="gsm" value="<?php echo $center['gsm']; ?>" placeholder="Numéro de téléphone">
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
require_once "includes/footer.php";
?>