// Gestion du mode sombre/clair
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle.querySelector('i');
    
    // Vérifier la préférence sauvegardée ou utiliser le mode système
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const currentTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
    
    // Appliquer le thème initial
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    // Gestionnaire de clic pour le bouton toggle
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Appliquer le nouveau thème
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
        
        // Animation du bouton
        themeToggle.style.transform = 'translateY(-50%) scale(0.9)';
        setTimeout(() => {
            themeToggle.style.transform = 'translateY(-50%) scale(1)';
        }, 150);
    });
    
    // Mettre à jour l'icône selon le thème
    function updateThemeIcon(theme) {
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-sun-fill';
            themeToggle.setAttribute('title', 'Passer en mode clair');
        } else {
            themeIcon.className = 'bi bi-moon-fill';
            themeToggle.setAttribute('title', 'Passer en mode sombre');
        }
    }
    
    // Écouter les changements de préférence système
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            updateThemeIcon(newTheme);
        }
    });
});
