<script>
    (function() {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenuDropdown = document.getElementById('userMenuDropdown');
        if (!userMenuBtn || !userMenuDropdown) return;

        const closeUserAccountMenu = () => {
            userMenuDropdown.classList.add('hidden');
            userMenuBtn.setAttribute('aria-expanded', 'false');
        };

        const toggleUserAccountMenu = () => {
            const willOpen = userMenuDropdown.classList.contains('hidden');
            userMenuDropdown.classList.toggle('hidden', !willOpen);
            userMenuBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        };

        window.closeUserAccountMenu = closeUserAccountMenu;

        userMenuBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleUserAccountMenu();
        });

        document.addEventListener('click', (event) => {
            if (userMenuDropdown.classList.contains('hidden')) return;
            if (userMenuBtn.contains(event.target) || userMenuDropdown.contains(event.target)) return;
            closeUserAccountMenu();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeUserAccountMenu();
            }
        });

        userMenuDropdown.querySelectorAll('[data-open-logout-modal], [data-open-profil-modal]').forEach((el) => {
            el.addEventListener('click', () => {
                closeUserAccountMenu();
            });
        });
    })();
</script>
