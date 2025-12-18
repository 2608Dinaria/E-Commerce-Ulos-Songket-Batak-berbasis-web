<?php
include 'auth_check.php';
include '../config.php';


$message = "";
$log = [];

if (isset($_POST['run_fix'])) {
    // 1. Fix products table (main image)
    $products = mysqli_query($conn, "SELECT id, image FROM products WHERE image LIKE '% %'");
    while ($p = mysqli_fetch_assoc($products)) {
        $old_name = $p['image'];
        $new_name = str_replace(' ', '_', $old_name);
        
        // Rename physical file
        $old_path = '../assets/img/' . $old_name;
        $new_path = '../assets/img/' . $new_name;
        
        if (file_exists($old_path)) {
            if (rename($old_path, $new_path)) {
                // Update DB
                mysqli_query($conn, "UPDATE products SET image = '$new_name' WHERE id = " . $p['id']);
                $log[] = "SUCCESS: Renamed '$old_name' to '$new_name' (Product ID: {$p['id']})";
            } else {
                $log[] = "ERROR: Could not rename '$old_name' to '$new_name' (Permission denied?)";
            }
        } else {
            // File doesn't exist, but we update DB to match expected filename if implementation was mixed
            // OR we just log that file is missing.
            // Better strategy: If file missing, maybe it's already renamed but DB old?
            if (file_exists($new_path)) {
                mysqli_query($conn, "UPDATE products SET image = '$new_name' WHERE id = " . $p['id']);
                $log[] = "INFO: File '$new_name' already exists, updated DB for Product ID: {$p['id']}";
            } else {
                $log[] = "WARNING: File '$old_path' not found. Skipped Product ID: {$p['id']}";
            }
        }
    }

    // 2. Fix product_images table (variant images)
    $images = mysqli_query($conn, "SELECT id, image FROM product_images WHERE image LIKE '% %'");
    while ($img = mysqli_fetch_assoc($images)) {
        $old_name = $img['image'];
        $new_name = str_replace(' ', '_', $old_name);
        
        $old_path = '../assets/img/' . $old_name;
        $new_path = '../assets/img/' . $new_name;
        
        if (file_exists($old_path)) {
            if (rename($old_path, $new_path)) {
                mysqli_query($conn, "UPDATE product_images SET image = '$new_name' WHERE id = " . $img['id']);
                $log[] = "SUCCESS: Renamed '$old_name' to '$new_name' (Image ID: {$img['id']})";
            } else {
                $log[] = "ERROR: Could not rename '$old_name' to '$new_name' (Permission denied?)";
            }
        } else {
            if (file_exists($new_path)) {
                mysqli_query($conn, "UPDATE product_images SET image = '$new_name' WHERE id = " . $img['id']);
                $log[] = "INFO: File '$new_name' already exists, updated DB for Image ID: {$img['id']}";
            } else {
                $log[] = "WARNING: File '$old_path' not found. Skipped Image ID: {$img['id']}";
            }
        }
    }
    
    // 3. Specific fix for Ulos Ragidup image (ragidup1.jpg -> ragidup.jpg)
    // ONLY if ragidup1.jpg is missing and ragidup.jpg exists
    $ragidup_check = mysqli_query($conn, "SELECT id FROM products WHERE image = 'ragidup1.jpg'");
    if (mysqli_num_rows($ragidup_check) > 0) {
        $old_path = '../assets/img/ragidup1.jpg';
        $new_path = '../assets/img/ragidup.jpg';
        
        if (!file_exists($old_path) && file_exists($new_path)) {
            mysqli_query($conn, "UPDATE products SET image = 'ragidup.jpg' WHERE image = 'ragidup1.jpg'");
            $log[] = "SUCCESS: Fixed missing image for Ulos Ragidup (ragidup1.jpg -> ragidup.jpg)";
        }
    }
    
    $message = "Perbaikan selesai dijalankan.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Filenames</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        .box { max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        .btn { background: #2196f3; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-size: 16px; }
        .btn:hover { background: #1976d2; }
        .log { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; margin-top: 20px; max-height: 300px; overflow-y: auto; font-family: monospace; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Perbaikan Nama File Gambar</h1>
        <p>Halaman ini akan secara otomatis:</p>
        <ol>
            <li>Mencari gambar di database yang memiliki <strong>spasi</strong> pada namanya.</li>
            <li>Mengubah nama file fisik di folder <code>assets/img/</code> (mengganti spasi dengan <code>_</code>).</li>
            <li>Memperbarui database agar sesuai dengan nama file baru.</li>
        </ol>
        
        <p><strong>PENTING:</strong> Pastikan folder <code>assets/img/</code> memiliki izin tulis (writable).</p>
        
        <?php if ($message): ?>
            <div style="padding: 10px; background: #e8f5e9; color: green; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="run_fix" class="btn">Jalankan Perbaikan Sekarang</button>
        </form>
        
        <?php if (!empty($log)): ?>
        <div class="log">
            <?php foreach ($log as $l): ?>
                <?php 
                    $color = 'black';
                    if (strpos($l, 'SUCCESS') === 0) $color = 'green';
                    if (strpos($l, 'ERROR') === 0) $color = 'red';
                    if (strpos($l, 'WARNING') === 0) $color = 'orange';
                    if (strpos($l, 'INFO') === 0) $color = 'blue';
                ?>
                <div style="color: <?= $color ?>"><?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
