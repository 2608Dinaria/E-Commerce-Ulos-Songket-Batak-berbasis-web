<?php
include 'config.php';

// Add columns if they don't exist
$alter_query = "ALTER TABLE orders 
    ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL AFTER total_price, 
    ADD COLUMN IF NOT EXISTS shipping_method VARCHAR(50) NOT NULL AFTER payment_method";

if (mysqli_query($conn, $alter_query)) {
    echo "Database updated successfully.";
} else {
    echo "Error updating database: " . mysqli_error($conn);
}
?>
