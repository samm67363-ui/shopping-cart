// ============================================
// KINETIC MODERNISM - INTERACTIVE EFFECTS
// Custom Cursor & Animations
// ============================================

class KineticCursor {
    constructor() {
        this.cursor = document.createElement('div');
        this.cursor.className = 'cursor';
        document.body.appendChild(this.cursor);
        
        this.x = window.innerWidth / 2;
        this.y = window.innerHeight / 2;
        
        this.init();
    }
    
    init() {
        document.addEventListener('mousemove', (e) => this.updatePosition(e));
        
        // Add interactive elements
        this.addInteractivity();
        
        // Animate cursor position
        this.animateCursor();
    }
    
    updatePosition(e) {
        this.x = e.clientX;
        this.y = e.clientY;
    }
    
    animateCursor() {
        this.cursor.style.left = this.x + 'px';
        this.cursor.style.top = this.y + 'px';
        
        requestAnimationFrame(() => this.animateCursor());
    }
    
    addInteractivity() {
        // Make buttons, links, and inputs expand cursor
        const interactiveElements = document.querySelectorAll(
            'button, a, input, textarea, .product-card, .interactive'
        );
        
        interactiveElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                this.cursor.classList.add('active');
            });
            
            el.addEventListener('mouseleave', () => {
                this.cursor.classList.remove('active');
            });
        });
    }
}

// Initialize cursor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new KineticCursor();
});

// ============================================
// SMOOTH PAGE TRANSITIONS
// ============================================
class PageTransition {
    constructor() {
        this.setupTransitions();
    }
    
    setupTransitions() {
        // Fade in on page load
        const mainContent = document.querySelector('main') || document.body;
        mainContent.style.animation = 'fadeInUp 0.8s ease-out';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PageTransition();
});

// ============================================
// INTERSECTION OBSERVER FOR STAGGERED ANIMATIONS
// ============================================
class StaggerAnimation {
    constructor() {
        this.observer = new IntersectionObserver(
            (entries) => this.handleIntersection(entries),
            { threshold: 0.1 }
        );
        
        this.observeElements();
    }
    
    observeElements() {
        document.querySelectorAll('.stagger-in, .product-card, .section').forEach(el => {
            this.observer.observe(el);
        });
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                this.observer.unobserve(entry.target);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new StaggerAnimation();
});

// ============================================
// SMOOTH SCROLLING
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ============================================
// ADD GLOW EFFECT TO CURSOR POSITION
// ============================================
document.addEventListener('mousemove', (e) => {
    const elements = document.querySelectorAll('.glass');
    elements.forEach(el => {
        const rect = el.getBoundingClientRect();
        const distance = Math.sqrt(
            Math.pow(e.clientX - (rect.left + rect.width / 2), 2) +
            Math.pow(e.clientY - (rect.top + rect.height / 2), 2)
        );
        
        if (distance < 200) {
            const opacity = 1 - (distance / 200);
            el.style.boxShadow = `
                0 0 30px rgba(124, 58, 237, ${opacity * 0.5}),
                inset 0 0 20px rgba(124, 58, 237, ${opacity * 0.2})
            `;
        } else {
            el.style.boxShadow = '0 0 0px transparent';
        }
    });
});

// ============================================
// FORM FIELD ANIMATION
// ============================================
document.querySelectorAll('input, textarea').forEach(field => {
    field.addEventListener('focus', function() {
        this.style.background = 'rgba(255, 255, 255, 0.08)';
    });
    
    field.addEventListener('blur', function() {
        if (!this.value) {
            this.style.background = 'rgba(255, 255, 255, 0.05)';
        }
    });
});
