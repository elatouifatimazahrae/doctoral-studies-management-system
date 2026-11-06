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
$success = '';

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

// Get additional formation data
$sql = "SELECT * FROM formation_plus WHERE id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$formationPlus = $result->fetch_assoc();
$stmt->close();

// Get related data
$sql = "SELECT cd.* FROM champs_disciplinaires cd 
        JOIN formation_champs fc ON cd.id = fc.id_champ 
        WHERE fc.id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$champsDisciplinaires = $stmt->get_result();
$stmt->close();

$sql = "SELECT sr.* FROM structures_recherche sr 
        JOIN formation_structures fs ON sr.id = fs.id_structure 
        WHERE fs.id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$structuresRecherche = $stmt->get_result();
$stmt->close();

$sql = "SELECT ca.* FROM conditions_acces ca 
        JOIN formation_conditions fc ON ca.id = fc.id_condition 
        WHERE fc.id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$conditionsAcces = $stmt->get_result();
$stmt->close();

$sql = "SELECT s.* FROM specialites s 
        JOIN formation_specialites fs ON s.id = fs.id_specialite 
        WHERE fs.id_formation = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$specialites = $stmt->get_result();
$stmt->close();

// Process form submissions for adding related items
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_champ':
                $theme = sanitize($_POST['theme']);
                if (!empty($theme)) {
                    // First check if it already exists
                    $sql = "SELECT id FROM champs_disciplinaires WHERE theme = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $theme);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $champ = $result->fetch_assoc();
                        $champId = $champ['id'];
                    } else {
                        // Insert new champ
                        $sql = "INSERT INTO champs_disciplinaires (theme) VALUES (?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $theme);
                        $stmt->execute();
                        $champId = $conn->insert_id;
                    }
                    
                    // Link to formation
                    $sql = "INSERT INTO formation_champs (id_formation, id_champ) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $id, $champId);
                    
                    if ($stmt->execute()) {
                        $success = "Champ disciplinaire ajouté avec succès";
                    } else {
                        $error = "Erreur lors de l'ajout du champ disciplinaire";
                    }
                }
                break;
                
            case 'add_structure':
                $nom = sanitize($_POST['nom']);
                $directeur = sanitize($_POST['directeur']);
                $email = sanitize($_POST['email']);
                
                if (!empty($nom)) {
                    // First check if it already exists
                    $sql = "SELECT id FROM structures_recherche WHERE structure_nom = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $nom);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $structure = $result->fetch_assoc();
                        $structureId = $structure['id'];
                    } else {
                        // Insert new structure
                        $sql = "INSERT INTO structures_recherche (structure_nom, directeur, email) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sss", $nom, $directeur, $email);
                        $stmt->execute();
                        $structureId = $conn->insert_id;
                    }
                    
                    // Link to formation
                    $sql = "INSERT INTO formation_structures (id_formation, id_structure) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $id, $structureId);
                    
                    if ($stmt->execute()) {
                        $success = "Structure de recherche ajoutée avec succès";
                    } else {
                        $error = "Erreur lors de l'ajout de la structure de recherche";
                    }
                }
                break;
                
            case 'add_condition':
                $texte = sanitize($_POST['texte']);
                
                if (!empty($texte)) {
                    // Insert new condition
                    $sql = "INSERT INTO conditions_acces (condition_texte) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $texte);
                    $stmt->execute();
                    $conditionId = $conn->insert_id;
                    
                    // Link to formation
                    $sql = "INSERT INTO formation_conditions (id_formation, id_condition) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $id, $conditionId);
                    
                    if ($stmt->execute()) {
                        $success = "Condition d'accès ajoutée avec succès";
                    } else {
                        $error = "Erreur lors de l'ajout de la condition d'accès";
                    }
                }
                break;
                
            case 'add_specialite':
                $nom = sanitize($_POST['nom']);
                
                if (!empty($nom)) {
                    // First check if it already exists
                    $sql = "SELECT id FROM specialites WHERE specialite_nom = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $nom);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $specialite = $result->fetch_assoc();
                        $specialiteId = $specialite['id'];
                    } else {
                        // Insert new specialité
                        $sql = "INSERT INTO specialites (specialite_nom) VALUES (?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $nom);
                        $stmt->execute();
                        $specialiteId = $conn->insert_id;
                    }
                    
                    // Link to formation
                    $sql = "INSERT INTO formation_specialites (id_formation, id_specialite) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $id, $specialiteId);
                    
                    if ($stmt->execute()) {
                        $success = "Spécialité ajoutée avec succès";
                    } else {
                        $error = "Erreur lors de l'ajout de la spécialité";
                    }
                }
                break;
                
            case 'update_description':
                $description = sanitize($_POST['description']);
                
                $sql = "UPDATE formations_doctorales SET description = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $description, $id);
                
                if ($stmt->execute()) {
                    $success = "Description mise à jour avec succès";
                    $formation['description'] = $description;
                } else {
                    $error = "Erreur lors de la mise à jour de la description";
                }
                break;
        }
        
        // Refresh the page to show changes without the form resubmission warning
        if (empty($error)) {
            header("Location: formation_details.php?id=$id&success=" . urlencode($success));
            exit();
        }
    }
}

// Check for success message from redirects
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<div class="page-header">
    <h1><?php echo $formation['intitule']; ?></h1>
    <div class="header-actions">
        <a href="formation_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="<?php echo !empty($formation['ced_id']) ? 'center_details.php?id=' . $formation['ced_id'] : 'formations.php'; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> La formation a été créée avec succès. Vous pouvez maintenant ajouter plus d'informations ci-dessous.
    </div>
<?php endif; ?>

<div class="detail-container">
    <div class="formation-info">
        <div class="formation-image">
            <?php if (!empty($formationPlus['image'])): ?>
                <img src="<?php echo $formationPlus['image']; ?>" alt="<?php echo $formation['intitule']; ?>">
            <?php else: ?>
                <div class="no-image">
                    <i class="fas fa-image"></i>
                    <p>Aucune image</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="formation-details">
            <div class="detail-item">
                <div class="detail-label">Centre Doctoral:</div>
                <div class="detail-value">
                    <?php echo !empty($formation['ced_nom']) ? $formation['ced_nom'] : 'Non assigné'; ?>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Email de Contact:</div>
                <div class="detail-value">
                    <?php echo !empty($formation['contact_email']) ? $formation['contact_email'] : 'Non défini'; ?>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Date de début:</div>
                <div class="detail-value">
                    <?php echo formatDate($formation['date_debut']); ?>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Date de fin:</div>
                <div class="detail-value">
                    <?php echo formatDate($formation['date_fin']); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="accordion-container">
        <!-- Description Section -->
        <div class="accordion">
            <div class="accordion-header">
                <h3><i class="fas fa-align-left"></i> Description</h3>
                <button class="toggle-btn"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="accordion-content">
                <div class="accordion-text">
                    <?php if (!empty($formation['description'])): ?>
                        <p><?php echo nl2br($formation['description']); ?></p>
                    <?php else: ?>
                        <p class="text-muted">Aucune description fournie</p>
                    <?php endif; ?>
                </div>
                <button class="btn btn-sm btn-outline edit-btn" data-target="description-form">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                
                <div id="description-form" class="edit-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_description">
                        <div class="form-group">
                            <textarea name="description" rows="5" placeholder="Entrez une description"><?php echo $formation['description']; ?></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-target="description-form">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Champs Disciplinaires Section -->
        <div class="accordion">
            <div class="accordion-header">
                <h3><i class="fas fa-tag"></i> Champs Disciplinaires</h3>
                <button class="toggle-btn"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="accordion-content">
                <div class="accordion-list">
                    <?php if ($champsDisciplinaires && $champsDisciplinaires->num_rows > 0): ?>
                        <ul>
                            <?php while ($champ = $champsDisciplinaires->fetch_assoc()): ?>
                                <li>
                                    <span><?php echo $champ['theme']; ?></span>
                                    <a href="#" class="btn-icon btn-delete" title="Supprimer" 
                                       onclick="confirmDeleteRelation('champ', <?php echo $champ['id']; ?>, '<?php echo addslashes($champ['theme']); ?>')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucun champ disciplinaire associé</p>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-sm btn-outline add-btn" data-target="champ-form">
                    <i class="fas fa-plus"></i> Ajouter un champ
                </button>
                
                <div id="champ-form" class="add-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_champ">
                        <div class="form-group">
                            <label for="theme">Thème:</label>
                            <input type="text" id="theme" name="theme" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-target="champ-form">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Structures de Recherche Section -->
        <div class="accordion">
            <div class="accordion-header">
                <h3><i class="fas fa-flask"></i> Structures de Recherche</h3>
                <button class="toggle-btn"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="accordion-content">
                <div class="accordion-list">
                    <?php if ($structuresRecherche && $structuresRecherche->num_rows > 0): ?>
                        <ul>
                            <?php while ($structure = $structuresRecherche->fetch_assoc()): ?>
                                <li>
                                    <div>
                                        <strong><?php echo $structure['structure_nom']; ?></strong>
                                        <?php if (!empty($structure['directeur'])): ?>
                                            <p>Directeur: <?php echo $structure['directeur']; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($structure['email'])): ?>
                                            <p>Email: <?php echo $structure['email']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a href="#" class="btn-icon btn-delete" title="Supprimer" 
                                       onclick="confirmDeleteRelation('structure', <?php echo $structure['id']; ?>, '<?php echo addslashes($structure['structure_nom']); ?>')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucune structure de recherche associée</p>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-sm btn-outline add-btn" data-target="structure-form">
                    <i class="fas fa-plus"></i> Ajouter une structure
                </button>
                
                <div id="structure-form" class="add-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_structure">
                        <div class="form-group">
                            <label for="nom">Nom:</label>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="directeur">Directeur:</label>
                            <input type="text" id="directeur" name="directeur">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-target="structure-form">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Conditions d'Accès Section -->
        <div class="accordion">
            <div class="accordion-header">
                <h3><i class="fas fa-lock"></i> Conditions d'Accès</h3>
                <button class="toggle-btn"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="accordion-content">
                <div class="accordion-list">
                    <?php if ($conditionsAcces && $conditionsAcces->num_rows > 0): ?>
                        <ul>
                            <?php while ($condition = $conditionsAcces->fetch_assoc()): ?>
                                <li>
                                    <span><?php echo $condition['condition_texte']; ?></span>
                                    <a href="#" class="btn-icon btn-delete" title="Supprimer" 
                                       onclick="confirmDeleteRelation('condition', <?php echo $condition['id']; ?>, 'cette condition')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucune condition d'accès spécifiée</p>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-sm btn-outline add-btn" data-target="condition-form">
                    <i class="fas fa-plus"></i> Ajouter une condition
                </button>
                
                <div id="condition-form" class="add-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_condition">
                        <div class="form-group">
                            <label for="texte">Condition:</label>
                            <textarea id="texte" name="texte" rows="3" required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-target="condition-form">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Liste des Spécialités Section -->
        <div class="accordion">
            <div class="accordion-header">
                <h3><i class="fas fa-list"></i> Liste des Spécialités</h3>
                <button class="toggle-btn"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="accordion-content">
                <div class="accordion-list">
                    <?php if ($specialites && $specialites->num_rows > 0): ?>
                        <ul>
                            <?php while ($specialite = $specialites->fetch_assoc()): ?>
                                <li>
                                    <span><?php echo $specialite['specialite_nom']; ?></span>
                                    <a href="#" class="btn-icon btn-delete" title="Supprimer" 
                                       onclick="confirmDeleteRelation('specialite', <?php echo $specialite['id']; ?>, '<?php echo addslashes($specialite['specialite_nom']); ?>')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucune spécialité associée</p>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-sm btn-outline add-btn" data-target="specialite-form">
                    <i class="fas fa-plus"></i> Ajouter une spécialité
                </button>
                
                <div id="specialite-form" class="add-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_specialite">
                        <div class="form-group">
                            <label for="nom">Nom de la spécialité:</label>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-target="specialite-form">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteRelationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer <span id="relationName"></span>?</p>
        </div>
        <div class="modal-footer">
            <button id="cancelDeleteRelation" class="btn btn-secondary">Annuler</button>
            <a id="confirmDeleteRelation" href="#" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>

<script>
    // Accordion functionality
    var accordions = document.querySelectorAll('.accordion-header');
    
    accordions.forEach(function(accordion) {
        accordion.addEventListener('click', function() {
            this.classList.toggle('active');
            var content = this.nextElementSibling;
            
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                this.querySelector('.toggle-btn i').className = 'fas fa-chevron-down';
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                this.querySelector('.toggle-btn i').className = 'fas fa-chevron-up';
            }
        });
    });
    
    // Form toggle functionality
    var addButtons = document.querySelectorAll('.add-btn');
    var editButtons = document.querySelectorAll('.edit-btn');
    var cancelButtons = document.querySelectorAll('.cancel-btn');
    
    addButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'block';
            this.style.display = 'none';
        });
    });
    
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'block';
            this.style.display = 'none';
        });
    });
    
    cancelButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'none';
            
            // Find and show the corresponding add/edit button
            if (targetId.includes('form')) {
                var buttonType = targetId.includes('description') ? '.edit-btn' : '.add-btn';
                var relatedButton = document.querySelector(buttonType + '[data-target="' + targetId + '"]');
                if (relatedButton) {
                    relatedButton.style.display = 'inline-block';
                }
            }
        });
    });
    
    // Modal for deleting relations
    var modal = document.getElementById("deleteRelationModal");
    var relationNameSpan = document.getElementById("relationName");
    var confirmDeleteRelationLink = document.getElementById("confirmDeleteRelation");
    var cancelButton = document.getElementById("cancelDeleteRelation");
    var closeButton = document.getElementsByClassName("close")[0];
    
    function confirmDeleteRelation(type, id, name) {
        modal.style.display = "block";
        relationNameSpan.textContent = name;
        
        var url = "delete_relation.php?type=" + type + "&id=" + id + "&formation_id=<?php echo $id; ?>";
        confirmDeleteRelationLink.href = url;
    }
    
    closeButton.onclick = function() {
        modal.style.display = "none";
    }
    
    cancelButton.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<?php
// Include footer
require_once "includes/footer.php";
?>