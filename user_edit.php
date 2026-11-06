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
    header("Location: users.php");
    exit();
}

$id = intval($_GET['id']);
$error = '';
$success = false;

// Get user data
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user) {
    header("Location: users.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    
    // Validate data
    if (empty($email)) {
        $error = "L'email est obligatoire";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide";
    } else if (!empty($password) && $password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else if (!empty($password) && strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
        // Check if email already exists for other users
        $sql = "SELECT id FROM admins WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            // Update user
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admins SET email = ?, password = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $email, $hashed_password, $role, $id);
            } else {
                // Update without changing password
                $sql = "UPDATE admins SET email = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $email, $role, $id);
            }
            
            if ($stmt->execute()) {
                // Redirect to users page with success message
                header("Location: users.php?updated=1");
                exit();
            } else {
                $error = "Erreur lors de la mise à jour de l'utilisateur: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}
?>

<div class="page-header">
    <h1>Modifier un Utilisateur</h1>
    <a href="users.php" class="btn btn-secondary">
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
            <h2>Informations de l'utilisateur</h2>
        </div>
        <div class="card-content">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" class="form">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Adresse Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Nouveau Mot de Passe</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Laissez vide pour ne pas changer">
                        <span class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirmer le Nouveau Mot de Passe</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez le nouveau mot de passe">
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role"><i class="fas fa-user-tag"></i> Rôle <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="ced_admin" <?php echo $user['role'] == 'ced_admin' ? 'selected' : ''; ?>>Administrateur CED</option>
                        <option value="director" <?php echo $user['role'] == 'director' ? 'selected' : ''; ?>>Directeur de Pôle Doctorale</option>
                    </select>
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
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const icon = document.querySelector(`#${inputId} + .toggle-password i`);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php
// Include footer
require_once "includes/footer.php";
?>