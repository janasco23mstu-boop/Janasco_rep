</main>

</div>

<footer class="site-footer">

    <div class="footer-content">

        <h3>
            Japs <span>Creation</span>
        </h3>

        <p>
            Connect with me on social media
        </p>

        <div class="social-icons">

            <a href="https://www.facebook.com/jamespaul.deveraanasco" target="_blank" aria-label="Facebook">
                <i class="fa-brands fa-facebook-f"></i>
            </a>

            <a href="https://www.instagram.com/im.not_james/?hl=en" target="_blank" aria-label="Instagram">
                <i class="fa-brands fa-instagram"></i>
            </a>

            <a href="https://www.tiktok.com/@itsme.japs?_r=1&_t=ZS-99RaQRXSqnA" target="_blank" aria-label="TikTok">
                <i class="fa-brands fa-tiktok"></i>
            </a>

        </div>

        <p class="copyright">
            Japs Creation | Management System
        </p>

    </div>

</footer>

<script>
(function () {
    const layout = document.querySelector('.layout');
    const menuToggle = document.querySelector('.menu-toggle');
    const menuClose = document.querySelector('.menu-close');
    const backdrop = document.querySelector('.nav-backdrop');
    const navigation = document.querySelector('.sidebar-nav');
    const mobileBreakpoint = window.matchMedia('(max-width: 800px)');

    if (!layout || !menuToggle || !menuClose || !backdrop || !navigation) return;

    function closeMenu() {
        layout.classList.remove('nav-open');
        document.body.classList.remove('navigation-locked');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Open navigation menu');
        menuToggle.innerHTML = '<i class="fa-solid fa-bars" aria-hidden="true"></i>';
    }

    function openMenu() {
        layout.classList.add('nav-open');
        document.body.classList.add('navigation-locked');
        menuToggle.setAttribute('aria-expanded', 'true');
        menuToggle.setAttribute('aria-label', 'Close navigation menu');
        menuToggle.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
    }

    menuToggle.addEventListener('click', function () {
        layout.classList.contains('nav-open') ? closeMenu() : openMenu();
    });

    menuClose.addEventListener('click', closeMenu);
    backdrop.addEventListener('click', closeMenu);

    navigation.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (mobileBreakpoint.matches) closeMenu();
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMenu();
    });

    mobileBreakpoint.addEventListener('change', function (event) {
        if (!event.matches) closeMenu();
    });
})();
</script>
</body>

</html>