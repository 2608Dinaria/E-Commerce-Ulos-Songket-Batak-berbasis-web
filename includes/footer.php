    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <p style="margin-bottom: 20px; color: #ccc;">Yuk, tetap update sama koleksi dan promo terbaru dari VonaTa! Masukkan email kamu dan nikmati penawaran spesial setiap bulannya.</p>
                <div class="footer-logo">
                    <img src="assets/img/logo.jpg" alt="VonaTa" style="border-radius: 50%;">
                    <div style="color: #fff;">
                        <h4 style="margin: 0;">VonaTa</h4>
                        <small>Setiap Helai Benang Punya Cerita</small>
                    </div>
                </div>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-right">
                <div class="footer-column">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Customer Service</a></li>
                        <li><a href="#">Cara belanja</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Lainnya</h4>
                    <ul>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Langganan</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <p>Masukkan email kamu di sini</p>
                    <div class="newsletter">
                        <input type="email" placeholder="email@gmail.com...">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleDropdown(event) {
            event.preventDefault();
            document.getElementById("kategoriDropdown").classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown a') && !event.target.matches('.dropdown a i')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Password Toggle Script
        const togglePassword = document.querySelectorAll('.password-toggle');
        togglePassword.forEach(icon => {
            icon.addEventListener('click', function (e) {
                // toggle the type attribute
                const input = this.previousElementSibling;
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // toggle the eye slash icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
