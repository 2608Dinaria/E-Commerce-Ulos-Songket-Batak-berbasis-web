<?php
include 'config.php';
$current_page = 'about';

// Extra CSS for this page
$extra_css = '
    <style>
        .about-section {
            display: flex;
            align-items: center;
            padding: 60px 80px;
            gap: 60px;
        }
        /* 
           Using nth-of-type to ensure we count sections correctly.
           Section 1 (Odd) -> Normal (Image Left)
           Section 2 (Even) -> Reverse (Text Left)
        */
        .about-section:nth-of-type(even) {
            flex-direction: row-reverse;
            background-color: #f9f9f9;
        }
        .about-image {
            flex: 1;
        }
        .about-image img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .about-text {
            flex: 1;
        }
        .about-text h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }
        .about-text p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }
        .values-list {
            list-style: none;
            padding: 0;
        }
        .values-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .values-list li i {
            color: var(--primary-color);
            margin-top: 5px;
        }
        .values-list strong {
            color: #333;
        }
        
        /* Specific tweaks for logo section */
        .logo-section img {
            width: 300px; /* Limit logo size */
            display: block;
            margin: 0 auto;
            box-shadow: none;
        }
        .logo-box {
            border: 1px solid #ddd;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
        }
    </style>
';

include 'includes/header.php';
?>

    <!-- Section 1: Menganyam Tradisi -->
    <section class="about-section">
        <div class="about-image">
            <!-- Placeholder: Mannequin with red cloth -->
            <img src="assets/img/brosur 1.png" alt="VonaTa Collection">
        </div>
        <div class="about-text">
            <h2>Menganyam Tradisi, Menenun Makna</h2>
            <p><strong>VonaTa</strong> lahir dari cinta terhadap warisan budaya dan semangat untuk menjaga tenun tradisional tetap hidup di zaman modern. Nama <strong>VonaTa</strong> berasal dari kata "<strong>woven</strong>" (tenun) dan "<strong>ta</strong>" (tangan) — melambangkan karya tenunan tangan asli, bukan mesin.</p>
        </div>
    </section>

    <!-- Section 2: Cerita Kami -->
    <section class="about-section">
        <div class="about-image">
            <!-- Placeholder: Weaving hands -->
            <img src="assets/img/penenun.png" alt="Proses Menenun">
        </div>
        <div class="about-text">
            <h2>Cerita Kami</h2>
            <p>Kami percaya bahwa setiap helai benang membawa cerita. Ditenun oleh pengrajin lokal Sumatera Utara, setiap kain VonaTa menyimpan makna filosofi, doa, dan keindahan budaya dalam setiap detailnya.</p>
            <p>Kami ingin menghadirkan tenun yang bukan hanya indah dipandang, tapi juga bermakna, bisa kamu kenakan dengan bangga di acara adat, momen formal, hingga gaya kasual modern.</p>
        </div>
    </section>

    <!-- Section 3: Nilai yang Kami Pegang -->
    <section class="about-section">
        <div class="about-image">
            <!-- Placeholder: Folded cloth with text overlay -->
            <img src="assets/img/brosur.png" alt="Nilai VonaTa">
        </div>
        <div class="about-text">
            <h2>Nilai yang Kami Pegang</h2>
            <ul class="values-list">
                <li>
                    <i class="fas fa-certificate"></i>
                    <div><strong>Keaslian</strong> – Semua produk dibuat 100% handmade oleh pengrajin lokal.</div>
                </li>
                <li>
                    <i class="fas fa-palette"></i>
                    <div><strong>Inovasi</strong> – Motif tradisional dipadukan dengan sentuhan desain modern.</div>
                </li>
                <li>
                    <i class="fas fa-leaf"></i>
                    <div><strong>Keberlanjutan</strong> – Kami berkomitmen pada bahan alami dan proses yang etis.</div>
                </li>
                <li>
                    <i class="fas fa-heart"></i>
                    <div><strong>Pemberdayaan</strong> – Setiap pembelianmu ikut mendukung kehidupan para penenun lokal.</div>
                </li>
            </ul>
        </div>
    </section>

    <!-- Section 4: Filosofi Warna & Desain -->
    <section class="about-section">
        <div class="about-image logo-box">
            <div style="text-align: center;">
                <img src="assets/img/logo.jpg" alt="VonaTa Logo" style="width: 150px; margin-bottom: 20px;">
                <h2 style="font-size: 3rem; margin: 0;">VonaTa</h2>
                <p style="font-size: 1.2rem; color: #555;">Setiap Helai Benang Punya Cerita</p>
            </div>
        </div>
        <div class="about-text">
            <h2>Filosofi Warna & Desain</h2>
            <p>Warna merah melambangkan semangat dan keberanian. Putih menggambarkan ketulusan, dan hitam melambangkan keteguhan.</p>
            <p>Semua berpadu dalam desain yang kuat, elegan, dan abadi, seperti cerita yang teranyam di setiap kain VonaTa.</p>
        </div>
    </section>

    <!-- Slogan Section -->
    <section style="text-align: center; padding: 60px; background: #fff;">
        <h2 style="font-size: 2rem; color: #333;">Setiap Helai Benang Punya Cerita</h2>
    </section>

<?php include 'includes/footer.php'; ?>
