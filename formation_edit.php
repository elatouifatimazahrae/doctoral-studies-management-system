<?php
ob_start();
// Include header
require_once "includes/header.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formations.php");
    exit();
}

$id = intval($_GET['id']);
$error = '';
$success = false;

// Get formation data
$sql = "SELECT f.*, c.id as ced_id, c.nom as ced_nom 
        FROM formations_doctorales f 
        LEFT JOIN ced_formations cf ON f.id = cf.id_formation 
        LEFT JOIN ced c ON cf.id_ced = c.id 
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$formation = $result->fetch_assoc();
$stmt->close();

// Check if formation exists
if (!$formation) {
    header("Location: formations.php");
    exit();
}

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
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Update formation
            $sql = "UPDATE formations_doctorales 
                    SET intitule = ?, description = ?, contact_email = ?, date_debut = ?, date_fin = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $intitule, $description, $email, $date_debut, $date_fin, $id);
            $stmt->execute();
            
            // Update CED association
            if ($ced_id > 0) {
                // First delete existing association
                $sql = "DELETE FROM ced_formations WHERE id_formation = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Then insert new association
                $sql = "INSERT INTO ced_formations (id_ced, id_formation) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $ced_id, $id);
                $stmt->execute();
            }
            
            // Update or insert formation_plus
            $sql = "INSERT INTO formation_plus (id_formation, image, description) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE image = VALUES(image), description = VALUES(description)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $id, $image, $short_desc);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to formations page with success message
            header("Location: formations.php?updated=1");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error = "Erreur lors de la mise à jour de la formation: " . $e->getMessage();
        }
    }
}

// Get additional formation data
$sql = "SELECT * FROM formation_plus WHERE id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$formationPlus = $result->fetch_assoc();
$stmt->close();
?>

<div class="page-header">
    <h1>Modifier une Formation Doctorale</h1>
    <a href="formations.php" class="btn btn-secondary">
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
        </div>
        <div class="card-content">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" class="form">
                <div class="form-group">
                    <label for="intitule"><i class="fas fa-book"></i> Intitulé <span class="required">*</span></label>
                    <input type="text" id="intitule" name="intitule" value="<?php echo $formation['intitule']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo $formation['description']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email de Contact</label>
                    <input type="email" id="email" name="email" value="<?php echo $formation['contact_email']; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de début</label>
                        <input type="date" id="date_debut" name="date_debut" value="<?php echo $formation['date_debut']; ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de fin</label>
                        <input type="date" id="date_fin" name="date_fin" value="<?php echo $formation['date_fin']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="ced_id"><i class="fas fa-university"></i> Centre Doctoral</label>
                    <select id="ced_id" name="ced_id">
                        <option value="">Sélectionnez un centre</option>
                        <?php if ($centers && $centers->num_rows > 0): ?>
                            <?php while ($center = $centers->fetch_assoc()): ?>
                                <option value="<?php echo $center['id']; ?>" <?php echo ($formation['ced_id'] == $center['id']) ? 'selected' : ''; ?>>
                                    <?php echo $center['nom']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Image URL</label>
                    <input type="text" id="image" name="image" value="<?php echo $formationPlus['image'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="short_desc"><i class="fas fa-info-circle"></i> Description courte</label>
                    <textarea id="short_desc" name="short_desc" rows="2"><?php echo $formationPlus['description'] ?? ''; ?></textarea>
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