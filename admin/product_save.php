<?php
include 'auth_check.php';
include '../config.php';

// This file handles saving product with multiple color variant images

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = mysqli_real_escape_string($conn, $_POST['name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$price = floatval($_POST['price']);
$stock = intval($_POST['stock']);
$category = mysqli_real_escape_string($conn, $_POST['category']);

// Save or Update Product
if ($id > 0) {
    // Update existing product
    $sql = "UPDATE products SET name='$name', description='$description', price=$price, stock=$stock, category='$category' WHERE id=$id";
    mysqli_query($conn, $sql);
    $product_id = $id;
} else {
    // Insert new product
    $sql = "INSERT INTO products (name, description, price, stock, category, image) VALUES ('$name', '$description', $price, $stock, '$category', 'placeholder.jpg')";
    mysqli_query($conn, $sql);
    $product_id = mysqli_insert_id($conn);
}

// Handle Color Variant Images
if (isset($_POST['colors']) && is_array($_POST['colors'])) {
    // Get existing images to check what to delete
    $existing_colors = [];
    $existing_query = mysqli_query($conn, "SELECT color FROM product_images WHERE product_id = $product_id");
    while ($row = mysqli_fetch_assoc($existing_query)) {
        $existing_colors[] = $row['color'];
    }
    
    $uploaded_colors = [];
    $primary_set = false;
    
    foreach ($_POST['colors'] as $index => $color) {
        $color = mysqli_real_escape_string($conn, trim($color));
        // Sanitize color name for filename (replace spaces with underscores)
        $safe_color = str_replace(' ', '_', $color);
        if (empty($color)) continue;
        
        $uploaded_colors[] = $color;
        $is_primary = isset($_POST['primary_color']) && $_POST['primary_color'] == $index ? 1 : 0;
        
        // Check if image is uploaded for this color
        $image_field = 'image_' . $index;
        if (isset($_FILES[$image_field]) && $_FILES[$image_field]['error'] === 0) {
            // Upload new image
            $ext = pathinfo($_FILES[$image_field]['name'], PATHINFO_EXTENSION);
            $new_filename = 'product_' . $product_id . '_' . strtolower($safe_color) . '_' . time() . '.' . $ext;
            
            if (move_uploaded_file($_FILES[$image_field]['tmp_name'], '../assets/img/' . $new_filename)) {
                // Delete old image for this color if exists
                $old_img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM product_images WHERE product_id=$product_id AND color='$color'"));
                if ($old_img && file_exists('../assets/img/' . $old_img['image'])) {
                    unlink('../assets/img/' . $old_img['image']);
                }
                
                // Insert or update
                mysqli_query($conn, "INSERT INTO product_images (product_id, color, image, is_primary) 
                    VALUES ($product_id, '$color', '$new_filename', $is_primary)
                    ON DUPLICATE KEY UPDATE image='$new_filename', is_primary=$is_primary");
                
                if ($is_primary) {
                    $primary_set = true;
                    // Update main product image for backward compatibility
                    mysqli_query($conn, "UPDATE products SET image='$new_filename' WHERE id=$product_id");
                }
            }
        } else {
            // No new image uploaded, just update is_primary flag if color exists
            $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM product_images WHERE product_id=$product_id AND color='$color'"));
            if ($check) {
                mysqli_query($conn, "UPDATE product_images SET is_primary=$is_primary WHERE product_id=$product_id AND color='$color'");
                
                if ($is_primary) {
                    $primary_set = true;
                    $img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM product_images WHERE product_id=$product_id AND color='$color'"));
                    if ($img) {
                        mysqli_query($conn, "UPDATE products SET image='{$img['image']}' WHERE id=$product_id");
                    }
                }
            }
        }
    }
    
    // Remove is_primary from all others if one is set
    if ($primary_set) {
        mysqli_query($conn, "UPDATE product_images SET is_primary=0 WHERE product_id=$product_id");
        $primary_color = mysqli_real_escape_string($conn, $_POST['colors'][$_POST['primary_color']]);
        mysqli_query($conn, "UPDATE product_images SET is_primary=1 WHERE product_id=$product_id AND color='$primary_color'");
    }
    
    // Delete colors that were removed
    $colors_to_delete = array_diff($existing_colors, $uploaded_colors);
    foreach ($colors_to_delete as $del_color) {
        $del_color = mysqli_real_escape_string($conn, $del_color);
        $del_img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM product_images WHERE product_id=$product_id AND color='$del_color'"));
        if ($del_img && file_exists('../assets/img/' . $del_img['image'])) {
            unlink('../assets/img/' . $del_img['image']);
        }
        mysqli_query($conn, "DELETE FROM product_images WHERE product_id=$product_id AND color='$del_color'");
    }
}

header("Location: products.php?success=1");
exit;
?>
