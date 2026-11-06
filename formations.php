<?php
ob_start();
// Include header
require_once "includes/header.php";

// Process delete if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    deleteRecord($conn, "formations_doctorales", $id);
    
    // Redirect to avoid resubmission
    header("Location: formations.php?deleted=1");
    exit();
}

// Get all formations
$sql = "SELECT f.*, c.nom as ced_nom FROM formations_doctorales f 
        LEFT JOIN ced_formations cf ON f.id = cf.id_formation 
        LEFT JOIN ced c ON cf.id_ced = c.id 
        ORDER BY f.intitule ASC";
$formations = $conn->query($sql);
?>

<div class="page-header">
    <h1>Formations Doctorales</h1>
    <a href="formation_add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Formation
    </a>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Formation ajoutée avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Formation mise à jour avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Formation supprimée avec succès!
    </div>
<?php endif; ?>

<div class="data-container">
    <div class="card">
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Intitulé</th>
                            <th>Centre Doctoral</th>
                            <th>Email Contact</th>
                            <th>Date de début</th>
                            <th>Date de fin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($formations && $formations->num_rows > 0): ?>
                            <?php while ($formation = $formations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $formation['intitule']; ?></td>
                                    <td><?php echo !empty($formation['ced_nom']) ? $formation['ced_nom'] : 'Non assigné'; ?></td>
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
                                           onclick="confirmDelete(<?php echo $formation['id']; ?>, '<?php echo addslashes($formation['intitule']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune formation trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la formation <span id="formationName"></span>?</p>
            <p class="text-danger">Cette action est irréversible!</p>
        </div>
        <div class="modal-footer">
            <button id="cancelDelete" class="btn btn-secondary">Annuler</button>
            <a id="confirmDelete" href="#" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById("deleteModal");
    var formationNameSpan = document.getElementById("formationName");
    var confirmDeleteLink = document.getElementById("confirmDelete");
    var cancelButton = document.getElementById("cancelDelete");
    var closeButton = document.getElementsByClassName("close")[0];
    
    // Function to show the modal with formation details
    function confirmDelete(id, name) {
        modal.style.display = "block";
        formationNameSpan.textContent = name;
        confirmDeleteLink.href = "formations.php?delete=" + id;
    }
    
    // Close the modal when clicking the close button, cancel or outside
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