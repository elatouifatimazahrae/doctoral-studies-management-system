<?php
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

// Get center data
$center = getRecordById($conn, "ced", $id);

// Check if center exists
if (!$center) {
    header("Location: centers.php");
    exit();
}

// Get formations for this center
$formations = getFormationsByCedId($conn, $id);

// Get conventions for this center
$conventions = getConventionsByCedId($conn, $id);
?>

<div class="page-header">
    <h1><?php echo $center['nom']; ?></h1>
    <div class="header-actions">
        <a href="formation_add.php?ced_id=<?php echo $id; ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Nouvelle Formation
        </a>
        <a href="convention_add.php?ced_id=<?php echo $id; ?>" class="btn btn-info">
            <i class="fas fa-plus"></i> Nouvelle Convention
        </a>
        <a href="center_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="centers.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="detail-container">
    <div class="detail-card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Informations du Centre</h2>
        </div>
        <div class="card-content">
            <div class="detail-item">
                <div class="detail-label">Nom du Centre:</div>
                <div class="detail-value"><?php echo $center['nom']; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Directeur:</div>
                <div class="detail-value"><?php echo !empty($center['directeur']) ? $center['directeur'] : 'Non défini'; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email de Contact:</div>
                <div class="detail-value"><?php echo !empty($center['email_contact']) ? $center['email_contact'] : 'Non défini'; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Numéro GSM:</div>
                <div class="detail-value"><?php echo !empty($center['gsm']) ? $center['gsm'] : 'Non défini'; ?></div>
            </div>
        </div>
    </div>
    
    <div class="detail-tabs">
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('formations-tab')">Formations</button>
            <button class="tab-btn" onclick="openTab('conventions-tab')">Conventions</button>
        </div>
        
        <div id="formations-tab" class="tab-content active">
            <div class="tab-header">
                <h3>Formations Doctorales</h3>
                <a href="formation_add.php?ced_id=<?php echo $id; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Intitulé</th>
                            <th>Email Contact</th>
                            <th>Date de début</th>
                            <th>Date de fin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($formations)): ?>
                            <?php foreach ($formations as $formation): ?>
                                <tr>
                                    <td><?php echo $formation['intitule']; ?></td>
                                    <td><?php echo $formation['contact_email']; ?></td>
                                    <td><?php echo formatDate($formation['date_debut']); ?></td>
                                    <td><?php echo formatDate($formation['date_fin']); ?></td>
                                    <td class="actions">
                                        <a href="formation_details.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-info" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="formation_edit.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" title="Supprimer" 
                                           onclick="confirmDeleteFormation(<?php echo $formation['id']; ?>, '<?php echo addslashes($formation['intitule']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucune formation trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="conventions-tab" class="tab-content">
            <div class="tab-header">
                <h3>Conventions</h3>
                <a href="convention_add.php?ced_id=<?php echo $id; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Organisme</th>
                            <th>Type</th>
                            <th>Date Signature</th>
                            <th>Ville</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($conventions)): ?>
                            <?php foreach ($conventions as $convention): ?>
                                <tr>
                                    <td><?php echo $convention['organisme_nom']; ?></td>
                                    <td><?php echo $convention['type']; ?></td>
                                    <td><?php echo formatDate($convention['date_signature']); ?></td>
                                    <td><?php echo $convention['ville']; ?></td>
                                    <td class="actions">
                                        <a href="convention_details.php?id=<?php echo $convention['id']; ?>" class="btn btn-sm btn-info" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="convention_edit.php?id=<?php echo $convention['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" title="Supprimer" 
                                           onclick="confirmDeleteConvention(<?php echo $convention['id']; ?>, '<?php echo addslashes($convention['type']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucune convention trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Formation Modal -->
<div id="deleteFormationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <span class="close" data-modal="deleteFormationModal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la formation <span id="formationName"></span>?</p>
            <p class="text-danger">Cette action est irréversible!</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel" data-modal="deleteFormationModal">Annuler</button>
            <a id="confirmDeleteFormation" href="#" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>

<!-- Delete Convention Modal -->
<div id="deleteConventionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <span class="close" data-modal="deleteConventionModal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la convention <span id="conventionName"></span>?</p>
            <p class="text-danger">Cette action est irréversible!</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel" data-modal="deleteConventionModal">Annuler</button>
            <a id="confirmDeleteConvention" href="#" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>

<script>
    // Tab functionality
    function openTab(tabId) {
        // Hide all tab contents
        var tabContents = document.getElementsByClassName("tab-content");
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove("active");
        }
        
        // Remove active class from all tab buttons
        var tabButtons = document.getElementsByClassName("tab-btn");
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove("active");
        }
        
        // Show the selected tab content and mark the button as active
        document.getElementById(tabId).classList.add("active");
        event.currentTarget.classList.add("active");
    }
    
    // Modal handling
    var modals = document.getElementsByClassName("modal");
    var closeButtons = document.getElementsByClassName("close");
    var cancelButtons = document.getElementsByClassName("modal-cancel");
    
    // Function to show the formation delete modal
    function confirmDeleteFormation(id, name) {
        document.getElementById("deleteFormationModal").style.display = "block";
        document.getElementById("formationName").textContent = name;
        document.getElementById("confirmDeleteFormation").href = "formation_delete.php?id=" + id + "&redirect=center_details.php?id=<?php echo $id; ?>";
    }
    
    // Function to show the convention delete modal
    function confirmDeleteConvention(id, name) {
        document.getElementById("deleteConventionModal").style.display = "block";
        document.getElementById("conventionName").textContent = name;
        document.getElementById("confirmDeleteConvention").href = "convention_delete.php?id=" + id + "&redirect=center_details.php?id=<?php echo $id; ?>";
    }
    
    // Close modal when clicking the close button
    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            var modalId = this.getAttribute("data-modal");
            document.getElementById(modalId).style.display = "none";
        }
    }
    
    // Close modal when clicking the cancel button
    for (var i = 0; i < cancelButtons.length; i++) {
        cancelButtons[i].onclick = function() {
            var modalId = this.getAttribute("data-modal");
            document.getElementById(modalId).style.display = "none";
        }
    }
    
    // Close modal when clicking outside the modal
    window.onclick = function(event) {
        for (var i = 0; i < modals.length; i++) {
            if (event.target == modals[i]) {
                modals[i].style.display = "none";
            }
        }
    }
</script>

<?php
// Include footer
require_once "includes/footer.php";
?>