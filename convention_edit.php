<?php
ob_start();
// Include header
require_once "includes/header.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: conventions.php");
    exit();
}

$id = intval($_GET['id']);
$error = '';
$success = false;

// Get convention data
$sql = "SELECT c.*, o.nom as organisme_nom, o.type as organisme_type, 
               o.universite_etrangere, o.universite_marocaine 
        FROM conventions c 
        JOIN organismes o ON c.organisme_id = o.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$convention = $result->fetch_assoc();
$stmt->close();

// Check if convention exists
if (!$convention) {
    header("Location: conventions.php");
    exit();
}

// Get all centers for dropdown
$sql = "SELECT id, nom FROM ced ORDER BY nom ASC";
$centers = $conn->query($sql);

// Get all organisms for dropdown
$sql = "SELECT id, nom FROM organismes ORDER BY nom ASC";
$organisms = $conn->query($sql);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $ced_id = isset($_POST['ced_id']) ? intval($_POST['ced_id']) : 0;
    $organisme_id = isset($_POST['organisme_id']) ? intval($_POST['organisme_id']) : 0;
    $new_organisme = sanitize($_POST['new_organisme']);
    $type_organisme = sanitize($_POST['type_organisme']);
    $date_signature = sanitize($_POST['date_signature']);
    $objectifs = sanitize($_POST['objectifs']);
    $type = sanitize($_POST['type']);
    $ville = sanitize($_POST['ville']);
    $date_debut = sanitize($_POST['date_debut']);
    $date_fin = sanitize($_POST['date_fin']);
    $universite_etrangere = sanitize($_POST['universite_etrangere']);
    $universite_marocaine = sanitize($_POST['universite_marocaine']);
    
    // Validate data
    if ($ced_id <= 0) {
        $error = "Veuillez sélectionner un centre doctoral";
    } else if ($organisme_id <= 0 && empty($new_organisme)) {
        $error = "Veuillez sélectionner un organisme ou en créer un nouveau";
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            // If a new organism was provided, create it
            if ($organisme_id <= 0 && !empty($new_organisme)) {
                $sql = "INSERT INTO organismes (nom, type, universite_etrangere, universite_marocaine) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $new_organisme, $type_organisme, $universite_etrangere, $universite_marocaine);
                $stmt->execute();
                $organisme_id = $conn->insert_id;
            }
            
            // Update convention
            $sql = "UPDATE conventions 
                    SET ced_id = ?, organisme_id = ?, date_signature = ?, objectifs = ?, 
                        type = ?, ville = ?, date_debut = ?, date_fin = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssssi", $ced_id, $organisme_id, $date_signature, $objectifs, 
                                         $type, $ville, $date_debut, $date_fin, $id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to conventions page with success message
            header("Location: conventions.php?updated=1");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error = "Erreur lors de la mise à jour de la convention: " . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1>Modifier une Convention</h1>
    <a href="conventions.php" class="btn btn-secondary">
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
            <h2>Informations de la convention</h2>
        </div>
        <div class="card-content">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" class="form">
                <div class="form-group">
                    <label for="ced_id"><i class="fas fa-university"></i> Centre Doctoral <span class="required">*</span></label>
                    <select id="ced_id" name="ced_id" required>
                        <option value="">Sélectionnez un centre</option>
                        <?php if ($centers && $centers->num_rows > 0): ?>
                            <?php while ($center = $centers->fetch_assoc()): ?>
                                <option value="<?php echo $center['id']; ?>" <?php echo ($convention['ced_id'] == $center['id']) ? 'selected' : ''; ?>>
                                    <?php echo $center['nom']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="organisme_id"><i class="fas fa-building"></i> Organisme</label>
                    <select id="organisme_id" name="organisme_id" onchange="checkOrganisme()">
                        <option value="">Sélectionnez un organisme</option>
                        <option value="0">Créer un nouvel organisme</option>
                        <?php if ($organisms && $organisms->num_rows > 0): ?>
                            <?php while ($organism = $organisms->fetch_assoc()): ?>
                                <option value="<?php echo $organism['id']; ?>" <?php echo ($convention['organisme_id'] == $organism['id']) ? 'selected' : ''; ?>>
                                    <?php echo $organism['nom']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div id="new-organisme-form" style="display: none;">
                    <div class="form-group">
                        <label for="new_organisme"><i class="fas fa-plus-circle"></i> Nouvel Organisme</label>
                        <input type="text" id="new_organisme" name="new_organisme" placeholder="Nom du nouvel organisme">
                    </div>
                    
                    <div class="form-group">
                        <label for="type_organisme"><i class="fas fa-tag"></i> Type d'Organisme</label>
                        <input type="text" id="type_organisme" name="type_organisme" placeholder="Type d'organisme">
                    </div>
                    
                    <div class="form-group">
                        <label for="universite_etrangere"><i class="fas fa-university"></i> Université Étrangère</label>
                        <input type="text" id="universite_etrangere" name="universite_etrangere" placeholder="Nom de l'université étrangère">
                    </div>
                    
                    <div class="form-group">
                        <label for="universite_marocaine"><i class="fas fa-university"></i> Université Marocaine</label>
                        <input type="text" id="universite_marocaine" name="universite_marocaine" placeholder="Nom de l'université marocaine">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="type"><i class="fas fa-file-contract"></i> Type de Convention <span class="required">*</span></label>
                    <input type="text" id="type" name="type" value="<?php echo $convention['type']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="objectifs"><i class="fas fa-bullseye"></i> Objectifs</label>
                    <textarea id="objectifs" name="objectifs" rows="4"><?php echo $convention['objectifs']; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="date_signature"><i class="fas fa-calendar-check"></i> Date de Signature</label>
                        <input type="date" id="date_signature" name="date_signature" value="<?php echo $convention['date_signature']; ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="ville"><i class="fas fa-map-marker-alt"></i> Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo $convention['ville']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de début</label>
                        <input type="date" id="date_debut" name="date_debut" value="<?php echo $convention['date_debut']; ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de fin</label>
                        <input type="date" id="date_fin" name="date_fin" value="<?php echo $convention['date_fin']; ?>">
                    </div>
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

<script>
    function checkOrganisme() {
        var organismeSelect = document.getElementById('organisme_id');
        var newOrganismeForm = document.getElementById('new-organisme-form');
        
        if (organismeSelect.value === '0') {
            newOrganismeForm.style.display = 'block';
            document.getElementById('new_organisme').setAttribute('required', 'required');
        } else {
            newOrganismeForm.style.display = 'none';
            document.getElementById('new_organisme').removeAttribute('required');
        }
    }
    
    // Check initially
    checkOrganisme();
</script>

<?php
// Include footer
require_once "includes/footer.php";
?>