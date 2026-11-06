<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is director
function isDirector() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'director';
}

// Get user email
function getUserEmail() {
    return $_SESSION['email'] ?? 'Non connecté';
}

// Get user role display name
function getUserRoleDisplay() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] == 'director' ? 'Directeur de Pôle Doctorale' : 'Administrateur CED';
    }
    return 'Non défini';
}

// Format date for display
function formatDate($date) {
    if (empty($date) || $date == "0000-00-00") {
        return "Non défini";
    }
    $timestamp = strtotime($date);
    return date("d/m/Y", $timestamp);
}

// Sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get count of items in table
function getCount($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Check if a record exists
function recordExists($conn, $table, $field, $value) {
    $sql = "SELECT id FROM $table WHERE $field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Get all records from a table
function getAllRecords($conn, $table, $orderBy = 'id', $order = 'ASC') {
    $sql = "SELECT * FROM $table ORDER BY $orderBy $order";
    $result = $conn->query($sql);
    $records = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    
    return $records;
}

// Get single record by ID
function getRecordById($conn, $table, $id) {
    $sql = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $record = $result->fetch_assoc();
        $stmt->close();
        return $record;
    }
    
    $stmt->close();
    return null;
}

// Delete record by ID
function deleteRecord($conn, $table, $id) {
    $sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Get formations by CED ID
function getFormationsByCedId($conn, $cedId) {
    $sql = "SELECT f.* FROM formations_doctorales f 
            JOIN ced_formations cf ON f.id = cf.id_formation 
            WHERE cf.id_ced = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cedId);
    $stmt->execute();
    $result = $stmt->get_result();
    $formations = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $formations[] = $row;
        }
    }
    
    $stmt->close();
    return $formations;
}

// Get conventions by CED ID
function getConventionsByCedId($conn, $cedId) {
    $sql = "SELECT c.*, o.nom as organisme_nom FROM conventions c 
            JOIN organismes o ON c.organisme_id = o.id 
            WHERE c.ced_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cedId);
    $stmt->execute();
    $result = $stmt->get_result();
    $conventions = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $conventions[] = $row;
        }
    }
    
    $stmt->close();
    return $conventions;
}
?>