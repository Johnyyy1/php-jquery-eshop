// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        let isMenuOpen = false;
        
        mobileMenuBtn.addEventListener('click', function() {
            isMenuOpen = !isMenuOpen;
            mobileMenu.style.transform = isMenuOpen ? 'translateY(0)' : 'translateY(-100%)';
            mobileMenuBtn.setAttribute('aria-expanded', isMenuOpen);
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (isMenuOpen && !mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                isMenuOpen = false;
                mobileMenu.style.transform = 'translateY(-100%)';
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
});

// Cart quantity controls with touch support
document.addEventListener('DOMContentLoaded', function() {
    const quantityControls = document.querySelectorAll('.quantity-controls');
    
    if (quantityControls) {
        quantityControls.forEach(control => {
            const decreaseBtn = control.querySelector('.decrease-quantity');
            const increaseBtn = control.querySelector('.increase-quantity');
            
            if (decreaseBtn && increaseBtn) {
                // Add touch feedback
                const addTouchFeedback = (button) => {
                    button.addEventListener('touchstart', () => {
                        button.style.transform = 'scale(0.95)';
                    }, { passive: true });
                    
                    button.addEventListener('touchend', () => {
                        button.style.transform = 'scale(1)';
                    }, { passive: true });
                };
                
                addTouchFeedback(decreaseBtn);
                addTouchFeedback(increaseBtn);
            }
        });
    }
});

// Prevent double-tap zoom on mobile devices
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button, .cyber-btn');
    buttons.forEach(button => {
        button.addEventListener('touchend', (e) => {
            e.preventDefault();
        }, { passive: false });
    });
});

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Responsive image loading
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
});

// Form validation with better mobile UX
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            // Prevent zoom on focus in iOS
            input.addEventListener('focus', () => {
                if (window.innerWidth < 768) {
                    document.documentElement.style.fontSize = '16px';
                }
            });
            
            input.addEventListener('blur', () => {
                document.documentElement.style.fontSize = '';
            });
            
            // Show validation messages on touch devices
            if ('ontouchstart' in window) {
                input.addEventListener('invalid', (e) => {
                    e.preventDefault();
                    input.classList.add('error');
                    
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message text-red-500 text-sm mt-1';
                    errorMessage.textContent = input.validationMessage;
                    
                    const existingError = input.parentNode.querySelector('.error-message');
                    if (!existingError) {
                        input.parentNode.appendChild(errorMessage);
                    }
                });
                
                input.addEventListener('input', () => {
                    input.classList.remove('error');
                    const errorMessage = input.parentNode.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            }
        });
    });
});

// Performance optimizations for animations
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Optimize Three.js background animation
if (typeof THREE !== 'undefined') {
    const optimizeThreeJS = debounce(() => {
        const canvas = document.getElementById('canvas-bg');
        if (canvas) {
            const pixelRatio = Math.min(window.devicePixelRatio, 2);
            const renderer = new THREE.WebGLRenderer({ canvas, antialias: false });
            renderer.setPixelRatio(pixelRatio);
        }
    }, 250);
    
    window.addEventListener('resize', optimizeThreeJS);
}

// Add touch ripple effect
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.cyber-btn');
    
    buttons.forEach(button => {
        button.addEventListener('touchstart', (e) => {
            const rect = button.getBoundingClientRect();
            const ripple = document.createElement('div');
            const x = e.touches[0].clientX - rect.left;
            const y = e.touches[0].clientY - rect.top;
            
            ripple.className = 'ripple';
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 1000);
        }, { passive: true });
    });
});
