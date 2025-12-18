# VonaTa - E-Commerce Ulos & Songket Batak

VonaTa adalah sebuah aplikasi e-commerce berbasis web yang dirancang khusus untuk memasarkan dan menjual produk kerajinan tangan tradisional Batak, yaitu Ulos dan Songket. Aplikasi ini bertujuan untuk melestarikan budaya sekaligus memudahkan pelanggan dalam mendapatkan produk tenun berkualitas tinggi secara online.

## 🚀 Fitur Utama

Aplikasi ini dibagi menjadi dua modul utama: Halaman Pengunjung (User) dan Panel Admin.

### 🛍️ Halaman Pengunjung (User)
*   **Katalog Produk**: Menampilkan daftar produk dengan filter berdasarkan kategori (Adat, Fashion, Seremonial) dan harga.
*   **Detail Produk**: Informasi lengkap mengenai produk, termasuk varian warna, harga, stok, dan deskripsi detail.
*   **Manajemen Akun**: Fitur registrasi, login, edit profil, dan manajemen alamat pengiriman.
*   **Keranjang Belanja**: Menambahkan produk ke keranjang sebelum checkout.
*   **Checkout & Pembayaran**: Proses pembelian yang mudah dengan validasi alamat dan metode pembayaran.
*   **Beli Langsung**: Fitur *Direct Buy* untuk pembelian cepat tanpa masuk keranjang.
*   **Lacak Pesanan**: Memantau status pesanan yang sedang diproses atau dikirim.
*   **Riwayat Pesanan**: Melihat daftar transaksi yang pernah dilakukan.
*   **Pengembalian Barang (Retur)**: Mengajukan pengembalian barang jika terdapat ketidaksesuaian linkungan upload bukti foto.
*   **Rating & Ulasan**: Memberikan penilaian bintang dan ulasan pada produk yang telah dibeli.

### 🛡️ Panel Admin
*   **Dashboard**: Ringkasan statistik penjualan, jumlah pesanan, dan produk terlaris.
*   **Manajemen Produk**: Tambah, edit, hapus produk, termasuk upload gambar dan pengaturan stok.
*   **Manajemen Kategori**: Mengelola kategori produk (misal: Tenun Adat, Modern Wear, Acara Seremonial).
*   **Manajemen Pesanan**: Melihat detail pesanan masuk, mengupdate status pembayaran dan pengiriman.
*   **Manajemen Pengembalian**: Menyetujui atau menolak permohonan retur barang dari pelanggan.
*   **Laporan Penjualan**: Mencetak laporan transaksi penjualan dalam periode tertentu.
*   **Manajemen Pengguna**: Melihat daftar pengguna terdaftar.

## 🛠️ Teknologi yang Digunakan

*   **Bahasa Pemrograman**: PHP (Native/Procedural)
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3, JavaScript (Bootstrap Framework)
*   **Server**: Apache (via XAMPP)

## 📦 Panduan Instalasi

Berikut adalah langkah-langkah untuk menjalankan proyek ini di komputer lokal (Localhost):

1.  **Persiapan Lingkungan**:
    *   Pastikan aplikasi **XAMPP** sudah terinstal di komputer Anda.

2.  **Clone/Copy Proyek**:
    *   Letakkan folder proyek `e-commerce` ke dalam direktori `htdocs` di instalasi XAMPP Anda (biasanya di `C:\xampp\htdocs\`).

3.  **Konfigurasi Database**:
    *   Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    *   Buat database baru dengan nama `e_commerce` (sesuaikan dengan konfigurasi di `config.php`).
    *   Impor file database yang tersedia di folder root (misalnya `db_schema.sql` atau file `.sql` terbaru lainnya) ke dalam database yang baru dibuat.

4.  **Konfigurasi Koneksi**:
    *   Buka file `config.php` (dan `admin/config.php` jika ada konfigurasi terpisah).
    *   Pastikan pengaturan host, user, password, dan nama database sudah sesuai. Contoh:
        ```php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "e_commerce";
        ```

5.  **Jalankan Aplikasi**:
    *   Buka browser dan akses:
        *   **Halaman User**: `http://localhost/e-commerce/`
        *   **Panel Admin**: `http://localhost/e-commerce/admin/`

## 📂 Struktur Folder

*   `/admin`: Halaman dan skrip khusus administrator.
*   `/assets`: Menyimpan gambar, file CSS, dan JavaScript.
*   `/includes`: Potongan kode yang digunakan berulang (Header, Footer, Koneksi DB).
*   `/uploads`: Direktori penyimpanan gambar produk yang diupload admin.
*   `index.php`: Halaman utama aplikasi.

---
*Dikembangkan dengan penuh dedikasi untuk pelestarian wastra nusantara.*
