<?php
ob_start();
// Include header
require_once "includes/header.php";

// Check if user is director, otherwise redirect
if (!isDirector()) {
    header("Location: dashboard.php");
    exit();
}

// Process delete if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    deleteRecord($conn, "ced", $id);
    
    // Redirect to avoid resubmission
    header("Location: centers.php?deleted=1");
    exit();
}

// Get all centers
$sql = "SELECT * FROM ced ORDER BY nom ASC";
$centers = $conn->query($sql);
?>

<div class="page-header">
    <h1>Centres Doctoraux</h1>
    <a href="center_add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouveau Centre
    </a>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Centre ajouté avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Centre mis à jour avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Centre supprimé avec succès!
    </div>
<?php endif; ?>

<div class="data-container">
    <div class="card">
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Directeur</th>
                            <th>Email Contact</th>
                            <th>GSM</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($centers && $centers->num_rows > 0): ?>
                            <?php while ($center = $centers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $center['nom']; ?></td>
                                    <td><?php echo $center['directeur']; ?></td>
                                    <td><?php echo $center['email_contact']; ?></td>
                                    <td><?php echo $center['gsm']; ?></td>
                                    <td class="actions">
                                        <a href="center_details.php?id=<?php echo $center['id']; ?>" class="btn btn-sm btn-info" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="center_edit.php?id=<?php echo $center['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" title="Supprimer" 
                                           onclick="confirmDelete(<?php echo $center['id']; ?>, '<?php echo addslashes($center['nom']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucun centre trouvé</td>
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
            <p>Êtes-vous sûr de vouloir supprimer le centre <span id="centerName"></span>?</p>
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
    var centerNameSpan = document.getElementById("centerName");
    var confirmDeleteLink = document.getElementById("confirmDelete");
    var cancelButton = document.getElementById("cancelDelete");
    var closeButton = document.getElementsByClassName("close")[0];
    
    // Function to show the modal with center details
    function confirmDelete(id, name) {
        modal.style.display = "block";
        centerNameSpan.textContent = name;
        confirmDeleteLink.href = "centers.php?delete=" + id;
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