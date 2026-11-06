<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once "includes/db.php";
require_once "includes/functions.php";

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études Doctorales - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> Études Doc</h2>
                <span class="user-role"><?php echo getUserRoleDisplay(); ?></span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    
                    <?php if (isDirector()): ?>
                    <li>
                        <a href="centers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'centers.php' ? 'active' : ''; ?>">
                            <i class="fas fa-university"></i> Centres Doctoraux
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li>
                        <a href="formations.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'formations.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> Formations
                        </a>
                    </li>
                    
                    <li>
                        <a href="conventions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'conventions.php' ? 'active' : ''; ?>">
                            <i class="fas fa-file-contract"></i> Conventions
                        </a>
                    </li>
                    
                    <?php if (isDirector()): ?>
                    <li>
                        <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="sidebar-divider"></li>
                    
                    
                    
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p>© 2025 Études Doctorales</p>
            </div>
        </aside>
        
        <main class="main-content">
            <header class="main-header">
                <div class="header-search">
                    <button id="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    
                </div>
                
                <div class="header-user">
                    
                    
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name"><?php echo getUserEmail(); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        
                    </div>
                </div>
            </header>
            
            <div class="main-container">