<?php
require_once "includes/header.php";
require_once "includes/db.php";

// Vérification de l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>Convention introuvable.</div>";
    require_once "includes/footer.php";
    exit;
}

$id = intval($_GET['id']);

// Récupération de la convention
$sql = "
    SELECT c.*, 
           ced.nom AS ced_nom,
           o.nom AS organisme_nom,
           o.type AS organisme_type,
           o.universite_etrangere,
           o.universite_marocaine
    FROM conventions c
    JOIN ced ON c.ced_id = ced.id
    JOIN organismes o ON c.organisme_id = o.id
    WHERE c.id = $id
    LIMIT 1
";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Aucune convention trouvée.</div>";
    require_once "includes/footer.php";
    exit;
}

$convention = $result->fetch_assoc();
?>

<!-- 🌟 STYLE CSS ULTRA MODERNE -->
<style>
    :root {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
        --secondary: #10b981;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #94a3b8;
        --border-radius: 16px;
        --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .convention-details {
        max-width: 900px;
        margin: 2.5rem auto;
        background: white;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        font-family: 'Inter', system-ui, sans-serif;
        color: var(--dark);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .convention-details::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary), var(--secondary));
    }

    .convention-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .convention-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        line-height: 1.2;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary);
        color: white;
        padding: 0.65rem 1.25rem;
        border-radius: 50px;
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
        box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2);
        border: none;
        cursor: pointer;
    }

    .back-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .detail-card {
        background: var(--light);
        padding: 1.25rem;
        border-radius: 12px;
        border-left: 3px solid var(--primary);
        transition: var(--transition);
    }

    .detail-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow);
    }

    .detail-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-label i {
        font-size: 1rem;
    }

    .detail-value {
        font-size: 1.05rem;
        color: var(--dark);
        line-height: 1.6;
        margin: 0;
    }

    .objectifs-card {
        grid-column: 1 / -1;
        background: rgba(79, 70, 229, 0.05);
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 3px solid var(--secondary);
    }

    .objectifs-text {
        white-space: pre-line;
    }

    .divider {
        margin: 2rem 0;
        border: none;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(79, 70, 229, 0.2), transparent);
    }

    /* Animation */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .convention-details {
        animation: fadeInUp 0.5s ease-out forwards;
    }

    .detail-card {
        animation: fadeInUp 0.5s ease-out forwards;
        animation-delay: calc(var(--order) * 0.1s);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .convention-details {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .convention-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .convention-title {
            font-size: 1.5rem;
        }
    }
</style>

<!-- 🌟 CONTENU AMÉLIORÉ -->
<div class="convention-details">
    <div class="convention-header">
        <h1 class="convention-title">Détails de la Convention</h1>
        <a href="conventions.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="detail-grid">
        <div class="detail-card" style="--order: 1;">
            <div class="detail-label">
                <i class="fas fa-university"></i> Centre Doctoral
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['ced_nom']) ?></p>
        </div>

        <div class="detail-card" style="--order: 2;">
            <div class="detail-label">
                <i class="fas fa-building"></i> Organisme
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['organisme_nom']) ?> (<?= htmlspecialchars($convention['organisme_type']) ?>)</p>
        </div>

        <div class="detail-card" style="--order: 3;">
            <div class="detail-label">
                <i class="fas fa-flag"></i> Université Marocaine
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['universite_marocaine']) ?></p>
        </div>

        <div class="detail-card" style="--order: 4;">
            <div class="detail-label">
                <i class="fas fa-globe"></i> Université Étrangère
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['universite_etrangere']) ?></p>
        </div>

        <div class="detail-card" style="--order: 5;">
            <div class="detail-label">
                <i class="fas fa-tag"></i> Type
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['type']) ?></p>
        </div>

        <div class="detail-card" style="--order: 6;">
            <div class="detail-label">
                <i class="fas fa-map-marker-alt"></i> Ville
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['ville']) ?></p>
        </div>

        <div class="detail-card" style="--order: 7;">
            <div class="detail-label">
                <i class="fas fa-calendar-check"></i> Date de Signature
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['date_signature']) ?></p>
        </div>

        <div class="detail-card" style="--order: 8;">
            <div class="detail-label">
                <i class="fas fa-calendar-day"></i> Date Début
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['date_debut']) ?></p>
        </div>

        <div class="detail-card" style="--order: 9;">
            <div class="detail-label">
                <i class="fas fa-calendar-times"></i> Date Fin
            </div>
            <p class="detail-value"><?= htmlspecialchars($convention['date_fin']) ?></p>
        </div>

        <div class="objectifs-card detail-card" style="--order: 10;">
            <div class="detail-label">
                <i class="fas fa-bullseye"></i> Objectifs
            </div>
            <p class="detail-value objectifs-text"><?= nl2br(htmlspecialchars($convention['objectifs'])) ?></p>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>