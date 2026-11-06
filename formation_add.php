<?php
ob_start();
// Include header
require_once "includes/header.php";

$error = '';
$success = false;
$cedId = isset($_GET['ced_id']) ? intval($_GET['ced_id']) : 0;

// Get all centers for dropdown
$sql = "SELECT id, nom FROM ced ORDER BY nom ASC";
$centers = $conn->query($sql);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $intitule = sanitize($_POST['intitule']);
    $description = sanitize($_POST['description']);
    $email = sanitize($_POST['email']);
    $date_debut = sanitize($_POST['date_debut']);
    $date_fin = sanitize($_POST['date_fin']);
    $ced_id = isset($_POST['ced_id']) ? intval($_POST['ced_id']) : 0;
    $image = sanitize($_POST['image']);
    $short_desc = sanitize($_POST['short_desc']);
    
    // Validate data
    if (empty($intitule)) {
        $error = "L'intitulé de la formation est obligatoire";
    } else if ($ced_id <= 0) {
        $error = "Veuillez sélectionner un centre doctoral";
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Insert into formations_doctorales
            $sql = "INSERT INTO formations_doctorales (intitule, description, contact_email, date_debut, date_fin) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $intitule, $description, $email, $date_debut, $date_fin);
            $stmt->execute();
            
            // Get last inserted ID
            $formation_id = $conn->insert_id;
            
            // Insert into ced_formations
            $sql = "INSERT INTO ced_formations (id_ced, id_formation) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $ced_id, $formation_id);
            $stmt->execute();
            
            // Insert into formation_plus
            $sql = "INSERT INTO formation_plus (id_formation, image, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $formation_id, $image, $short_desc);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to formation details page for additional info
            header("Location: formation_details.php?id=" . $formation_id . "&added=1");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error = "Erreur lors de l'ajout de la formation: " . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1>Ajouter une Formation Doctorale</h1>
    <a href="<?php echo isset($_GET['ced_id']) ? 'center_details.php?id=' . $_GET['ced_id'] : 'formations.php'; ?>" class="btn btn-secondary">
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
        <div class="card-header">
            <h2>Informations de base</h2>
            <p>Veuillez remplir les informations de base de la formation. Vous pourrez ajouter plus de détails après l'enregistrement.</p>
        </div>
        <div class="card-content">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . ($cedId ? '?ced_id=' . $cedId : ''); ?>" class="form">
                <div class="form-group">
                    <label for="intitule"><i class="fas fa-book"></i> Intitulé <span class="required">*</span></label>
                    <input type="text" id="intitule" name="intitule" placeholder="Entrez l'intitulé de la formation" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Entrez une description de la formation"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email de Contact</label>
                    <input type="email" id="email" name="email" placeholder="Adresse email de contact">
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de début</label>
                        <input type="date" id="date_debut" name="date_debut">
                    </div>
                    
                    <div class="form-group half">
                        <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de fin</label>
                        <input type="date" id="date_fin" name="date_fin">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="ced_id"><i class="fas fa-university"></i> Centre Doctoral <span class="required">*</span></label>
                    <select id="ced_id" name="ced_id" required>
                        <option value="">Sélectionnez un centre</option>
                        <?php if ($centers && $centers->num_rows > 0): ?>
                            <?php while ($center = $centers->fetch_assoc()): ?>
                                <option value="<?php echo $center['id']; ?>" <?php echo ($cedId == $center['id']) ? 'selected' : ''; ?>>
                                    <?php echo $center['nom']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Image URL</label>
                    <input type="text" id="image" name="image" placeholder="URL de l'image représentative">
                </div>
                
                <div class="form-group">
                    <label for="short_desc"><i class="fas fa-info-circle"></i> Description courte</label>
                    <textarea id="short_desc" name="short_desc" rows="2" placeholder="Brève description pour la liste des formations"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer et continuer
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