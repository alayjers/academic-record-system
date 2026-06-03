</div> 
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');

            const currentTheme = localStorage.getItem('theme') || 'light';
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (themeText) themeText.textContent = 'Dark Mode';
            }

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    let theme = 'light';
                    if (document.documentElement.getAttribute('data-theme') !== 'dark') {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        if (themeText) themeText.textContent = 'Dark Mode';
                        theme = 'dark';
                    } else {
                        document.documentElement.removeAttribute('data-theme');
                        if (themeText) themeText.textContent = 'Light Mode';
                    }
                    localStorage.setItem('theme', theme);
                });
            }
        });
    </script>
</body>
</html>