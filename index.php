<?php
// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include database connection
require_once "includes/db.php";

$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Query to check if email exists
        $sql = "SELECT id, email, password, role FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // For simplicity in this demo, we're checking the password without hashing
            // In production, use password_verify() with properly hashed passwords
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Email non trouvé";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études Doctorales - Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form-container">
            <div class="login-header">
                <h1><i class="fas fa-graduation-cap"></i> Études Doctorales</h1>
                <p>Système de Gestion Administrative</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="login-form">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de Passe</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se Connecter
                </button>
            </form>
        </div>
        
        <div class="login-info">
            <h2>Bienvenue dans le système de gestion des études doctorales</h2>
            <p>Cette plateforme permet de gérer les centres d'études doctorales, les formations et les conventions.</p>
            <div class="login-features">
                <div class="feature">
                    <i class="fas fa-university"></i>
                    <h3>Centres Doctoraux</h3>
                    <p>Gérez les centres d'études doctorales et leurs informations</p>
                </div>
                <div class="feature">
                    <i class="fas fa-book"></i>
                    <h3>Formations</h3>
                    <p>Administrez les formations doctorales et leurs détails</p>
                </div>
                <div class="feature">
                    <i class="fas fa-file-contract"></i>
                    <h3>Conventions</h3>
                    <p>Suivez les conventions et partenariats</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            
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
</body>
</html>