    <!-- Simple Auth Footer -->
    <footer style="text-align: center; padding: 20px; color: #777; font-size: 0.9rem;">
        &copy; <?= date('Y') ?> VonaTa. All rights reserved.
    </footer>

    <script>
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
