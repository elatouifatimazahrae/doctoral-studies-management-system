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
    deleteRecord($conn, "admins", $id);
    
    // Redirect to avoid resubmission
    header("Location: users.php?deleted=1");
    exit();
}

// Get all users
$sql = "SELECT * FROM admins ORDER BY email ASC";
$users = $conn->query($sql);
?>

<div class="page-header">
    <h1>Gestion des Utilisateurs</h1>
    <a href="user_add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvel Utilisateur
    </a>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Utilisateur ajouté avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Utilisateur mis à jour avec succès!
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Utilisateur supprimé avec succès!
    </div>
<?php endif; ?>

<div class="data-container">
    <div class="card">
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php if ($user['role'] == 'director'): ?>
                                            <span class="badge badge-primary">Directeur</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Admin CED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): // Prevent deleting own account ?>
                                            <a href="#" class="btn btn-sm btn-danger" title="Supprimer" 
                                               onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['email']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">Aucun utilisateur trouvé</td>
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
            <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <span id="userName"></span>?</p>
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
    var userNameSpan = document.getElementById("userName");
    var confirmDeleteLink = document.getElementById("confirmDelete");
    var cancelButton = document.getElementById("cancelDelete");
    var closeButton = document.getElementsByClassName("close")[0];
    
    // Function to show the modal with user details
    function confirmDelete(id, email) {
        modal.style.display = "block";
        userNameSpan.textContent = email;
        confirmDeleteLink.href = "users.php?delete=" + id;
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