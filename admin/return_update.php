<?php
include 'auth_check.php';
include '../config.php';

// Handle file upload for transfer proof
if (isset($_GET['upload']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $id = intval($_POST['id']);
    
    // If confirm_received flag is set, update status to shipped_back first
    if (isset($_POST['confirm_received'])) {
        mysqli_query($conn, "UPDATE returns SET status='shipped_back', updated_at=NOW() WHERE id=$id");
    }
    
    if (isset($_FILES['transfer_proof']) && $_FILES['transfer_proof']['error'] === 0) {
        $ext = pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION);
        $filename = 'transfer_' . $id . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['transfer_proof']['tmp_name'], '../uploads/returns/' . $filename)) {
            // Update return status to completed
            mysqli_query($conn, "UPDATE returns SET status='completed', transfer_proof='$filename', updated_at=NOW() WHERE id=$id");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
    exit;
}

// Handle status update
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: returns.php");
    exit;
}

$id = intval($_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);
$notes = isset($_GET['notes']) ? mysqli_real_escape_string($conn, $_GET['notes']) : '';

$valid_statuses = ['pending', 'approved', 'rejected', 'shipped_back', 'completed'];
if (!in_array($status, $valid_statuses)) {
    header("Location: returns.php");
    exit;
}

// Update return status
if ($notes) {
    mysqli_query($conn, "UPDATE returns SET status='$status', admin_notes='$notes', updated_at=NOW() WHERE id=$id");
} else {
    mysqli_query($conn, "UPDATE returns SET status='$status', updated_at=NOW() WHERE id=$id");
}

header("Location: returns.php");
exit;
?>
