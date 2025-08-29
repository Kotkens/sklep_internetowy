// Enhanced categories loading with preloading
document.addEventListener('DOMContentLoaded', function() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    // Kategorie obrazów URLs
    const imageUrls = [
        './assets/images/categories/elektronika.jpg',
        './assets/images/categories/moda.jpg',
        './assets/images/categories/dom-ogrod.jpg',
        './assets/images/categories/supermarket.jpg',
        './assets/images/categories/dziecko.jpg',
        './assets/images/categories/uroda.jpg',
        './assets/images/categories/zdrowie.jpg',
        './assets/images/categories/Kultura-i-rozrywka.jpg',
        './assets/images/categories/Sport-i-turystyka.jpg',
        './assets/images/categories/motoryzacja.jpg',
        './assets/images/categories/kolekcje-sztuka.jpg'
    ];
    
    let loadedImages = 0;
    const totalImages = imageUrls.length;
    
    // Preload obrazów
    imageUrls.forEach((url, index) => {
        const img = new Image();
        
        img.onload = function() {
            loadedImages++;
            const categoryCard = categoryCards[index];
            
            if (categoryCard) {
                categoryCard.classList.add('image-loaded');
                categoryCard.classList.add('loaded');
                
                // Animacja pojawienia się
                setTimeout(() => {
                    categoryCard.style.opacity = '1';
                    categoryCard.style.transform = 'translateY(0)';
                }, index * 100);
            }
            
            // Loguj progres
            console.log(`Loaded image ${index + 1}/${totalImages}: ${url}`);
        };
        
        img.onerror = function() {
            loadedImages++;
            const categoryCard = categoryCards[index];
            
            if (categoryCard) {
                categoryCard.classList.add('image-loaded');
                categoryCard.classList.add('loaded');
                
                // Fallback background dla błędów ładowania
                categoryCard.style.background = `linear-gradient(135deg, 
                    ${getRandomColor()} 0%, 
                    ${getRandomColor()} 100%)`;
                
                // Animacja pojawienia się mimo błędu
                setTimeout(() => {
                    categoryCard.style.opacity = '1';
                    categoryCard.style.transform = 'translateY(0)';
                }, index * 100);
            }
            
            console.log(`Failed to load image ${index + 1}: ${url}`);
        };
        
        img.src = url;
    });
    
    // Enhanced hover effects
    categoryCards.forEach((card, index) => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 12px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
        
        // Force pokazanie po maksymalnie 3 sekundach
        setTimeout(() => {
            if (!card.classList.contains('loaded')) {
                card.classList.add('loaded');
                card.classList.add('image-loaded');
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                card.style.background = 'linear-gradient(135deg, #ff6b35 0%, #f9ca24 100%)';
                console.log(`Force loaded category ${index}: timeout reached`);
            }
        }, 3000 + index * 200);
    });
    
    // Utility function for random colors
    function getRandomColor() {
        const colors = ['#ff6b35', '#f9ca24', '#6c5ce7', '#a29bfe', '#fd79a8', '#fdcb6e'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    // Magnetic hover effect
    function addMagneticEffect() {
        categoryCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                const moveX = x * 0.15;
                const moveY = y * 0.15;
                
                card.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.03)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }
    
    // Initialize magnetic effect after a short delay
    setTimeout(() => {
        addMagneticEffect();
    }, 1000);
    
    // Debug info
    console.log(`Categories loader initialized. Found ${categoryCards.length} category cards.`);
});
