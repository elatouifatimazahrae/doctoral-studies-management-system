<?php
// Include header
require_once "includes/header.php";

// Get counts for dashboard
$centersCount = getCount($conn, "ced");
$formationsCount = getCount($conn, "formations_doctorales");
$conventionsCount = getCount($conn, "conventions");
$specialitesCount = getCount($conn, "specialites");

// Get recent formations
$sql = "SELECT * FROM formations_doctorales ORDER BY id DESC LIMIT 5";
$recentFormations = $conn->query($sql);

// Get recent conventions
$sql = "SELECT c.*, o.nom as organisme_nom FROM conventions c 
        JOIN organismes o ON c.organisme_id = o.id
        ORDER BY c.id DESC LIMIT 5";
$recentConventions = $conn->query($sql);
?>

<div class="page-header">
    <h1>Tableau de bord</h1>
    <p>Bienvenue dans le système de gestion des études doctorales</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-university"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $centersCount; ?></h3>
            <p>Centres Doctoraux</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $formationsCount; ?></h3>
            <p>Formations</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $conventionsCount; ?></h3>
            <p>Conventions</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-tags"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $specialitesCount; ?></h3>
            <p>Spécialités</p>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Formations Récentes</h2>
            <a href="formations.php" class="btn btn-sm btn-outline">Voir tout</a>
        </div>
        
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Intitulé</th>
                            <th>Email Contact</th>
                            <th>Date de début</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentFormations && $recentFormations->num_rows > 0): ?>
                            <?php while ($formation = $recentFormations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $formation['intitule']; ?></td>
                                    <td><?php echo $formation['contact_email']; ?></td>
                                    <td><?php echo formatDate($formation['date_debut']); ?></td>
                                    <td>
                                        <a href="formation_details.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Aucune formation trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Conventions Récentes</h2>
            <a href="conventions.php" class="btn btn-sm btn-outline">Voir tout</a>
        </div>
        
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Organisme</th>
                            <th>Type</th>
                            <th>Date Signature</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentConventions && $recentConventions->num_rows > 0): ?>
                            <?php while ($convention = $recentConventions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $convention['organisme_nom']; ?></td>
                                    <td><?php echo $convention['type']; ?></td>
                                    <td><?php echo formatDate($convention['date_signature']); ?></td>
                                    <td>
                                        <a href="convention_details.php?id=<?php echo $convention['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Aucune convention trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once "includes/footer.php";
?>