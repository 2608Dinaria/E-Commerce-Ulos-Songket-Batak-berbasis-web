<?php
// Turn off error reporting for display to avoid breaking JSON
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

include 'config.php';
// session_start() is already called in config.php

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$card_number = $_POST['card_number'] ?? '';
$card_holder = $_POST['card_holder'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Basic validation
if (empty($card_number) || empty($card_holder) || empty($expiry_date) || empty($cvv)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Ensure table exists (redundant safety check)
$create_cards_table = "CREATE TABLE IF NOT EXISTS user_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    card_holder VARCHAR(100) NOT NULL,
    expiry_date VARCHAR(7) NOT NULL,
    cvv VARCHAR(4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
mysqli_query($conn, $create_cards_table);

// Check for duplicate card
$check_query = "SELECT id FROM user_cards WHERE user_id = ? AND card_number = ?";
$stmt_check = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt_check, "is", $user_id, $card_number);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    // Card already exists, just return success
    echo json_encode(['success' => true, 'message' => 'Card already saved']);
    exit;
}

$query = "INSERT INTO user_cards (user_id, card_number, card_holder, expiry_date, cvv) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "issss", $user_id, $card_number, $card_holder, $expiry_date, $cvv);

if (mysqli_stmt_execute($stmt)) {
    $card_id = mysqli_insert_id($conn);
    // Mask the card number for response
    $masked_card = '**** **** **** ' . substr($card_number, -4);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Card saved successfully',
        'card' => [
            'id' => $card_id,
            'masked_number' => $masked_card,
            'brand' => 'VISA' // Demo value
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
