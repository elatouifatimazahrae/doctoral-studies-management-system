<?php
ob_start();
// Include header
require_once "includes/header.php";

// Process delete if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    deleteRecord($conn, "conventions", $id);
    
    // Redirect to avoid resubmission
    header("Location: conventions.php?deleted=1");
    exit();
}

// Get all conventions
$sql = "SELECT c.*, o.nom as organisme_nom, ced.nom as ced_nom 
        FROM conventions c 
        JOIN organismes o ON c.organisme_id = o.id 
        JOIN ced ON c.ced_id = ced.id 
        ORDER BY c.date_signature DESC";
$conventions = $conn->query($sql);
?>

<div class="page-header">
    <h1>Conventions</h1>
    <a href="convention_add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Convention
    </a>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Convention ajoutée avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Convention mise à jour avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Convention supprimée avec succès!
    </div>
<?php endif; ?>

<div class="data-container">
    <div class="card">
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Centre Doctoral</th>
                            <th>Organisme</th>
                            <th>Type</th>
                            <th>Date Signature</th>
                            <th>Ville</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($conventions && $conventions->num_rows > 0): ?>
                            <?php while ($convention = $conventions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $convention['ced_nom']; ?></td>
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
                                           onclick="confirmDelete(<?php echo $convention['id']; ?>, '<?php echo addslashes($convention['type']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune convention trouvée</td>
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
            <p>Êtes-vous sûr de vouloir supprimer la convention <span id="conventionName"></span>?</p>
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
    var conventionNameSpan = document.getElementById("conventionName");
    var confirmDeleteLink = document.getElementById("confirmDelete");
    var cancelButton = document.getElementById("cancelDelete");
    var closeButton = document.getElementsByClassName("close")[0];
    
    // Function to show the modal with convention details
    function confirmDelete(id, name) {
        modal.style.display = "block";
        conventionNameSpan.textContent = name;
        confirmDeleteLink.href = "conventions.php?delete=" + id;
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