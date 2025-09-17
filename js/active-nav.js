// Navigation Active Dynamique
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link[href^="#"]');
    const sections = document.querySelectorAll('section, div[id]');
    
    // Fonction pour mettre à jour la classe active
    function updateActiveNav() {
        let current = '';
        const scrollPosition = window.scrollY + 100; // Offset pour déclencher plus tôt
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (sectionId && scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = sectionId;
            }
        });
        
        // Si on est tout en haut, activer "Accueil"
        if (window.scrollY < 100) {
            current = 'home';
        }
        
        // Mettre à jour les classes active
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href === `#${current}`) {
                link.classList.add('active');
            }
        });
    }
    
    // Écouter le scroll
    window.addEventListener('scroll', updateActiveNav);
    
    // Smooth scroll pour les liens de navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80; // Offset pour la navbar fixe
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Initialiser au chargement
    updateActiveNav();
});

// Observer pour une détection plus précise
const observerOptions = {
    root: null,
    rootMargin: '-20% 0px -80% 0px', // Zone de détection au centre de l'écran
    threshold: 0
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const sectionId = entry.target.getAttribute('id');
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
}, observerOptions);

// Observer toutes les sections avec un ID
document.addEventListener('DOMContentLoaded', function() {
    const sectionsToObserve = document.querySelectorAll('[id]');
    sectionsToObserve.forEach(section => {
        if (section.getAttribute('id')) {
            observer.observe(section);
        }
    });
});
