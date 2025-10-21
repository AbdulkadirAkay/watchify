var app = $.spapp({
    defaultView: "#home",
    templateDir: "./views/"

});

// Hide/show navigation and footer based on current page
function toggleNavigation(show) {
    const navbar = $('.navbar');
    const footer = $('footer');
    
    if (show) {
        navbar.show();
        footer.show();
    } else {
        navbar.hide();
        footer.hide();
    }
}

// Check current page on load and navigation
function checkCurrentPage() {
    const currentHash = window.location.hash;
    
    if (currentHash === '#login' || currentHash === '#register' || currentHash === '#order-success') {
        toggleNavigation(false);
    } else {
        toggleNavigation(true);
    }
}

// Listen for hash changes
$(window).on('hashchange', function() {
    // Handle empty hash or just "#" to show home page
    const currentHash = window.location.hash;
    if (!currentHash || currentHash === '#') {
        window.location.hash = '#home';
        return;
    }
    
    // Handle category-products and brand-products with query parameters
    if (currentHash.includes('category-products?id=') || currentHash.includes('brand-products?id=')) {
        // Store the full hash with parameters BEFORE redirecting
        // Always update the stored params for the current page type
        window.currentHashWithParams = currentHash;
        
        // Extract the base route and redirect to it
        const baseRoute = currentHash.split('?')[0];
        window.location.hash = baseRoute;
        return;
    }
    
    // Store the full hash with parameters for pages that need it
    // Only store if we don't already have parameters stored (to avoid overwriting)
    if (!window.currentHashWithParams || !window.currentHashWithParams.includes('?')) {
        window.currentHashWithParams = currentHash;
    }
    
    checkCurrentPage();
});

// Check on initial load
$(document).ready(function() {
    // Handle empty hash or just "#" to show home page
    const currentHash = window.location.hash;
    if (!currentHash || currentHash === '#') {
        window.location.hash = '#home';
        return;
    }
    
    // Handle category-products and brand-products with query parameters on initial load
    if (currentHash.includes('category-products?id=') || currentHash.includes('brand-products?id=')) {
        // Store the full hash with parameters BEFORE redirecting
        // Always update the stored params for the current page type
        window.currentHashWithParams = currentHash;
        
        // Extract the base route and redirect to it
        const baseRoute = currentHash.split('?')[0];
        window.location.hash = baseRoute;
        return;
    }
    
    // Store the full hash with parameters
    // Only store if we don't already have parameters stored (to avoid overwriting)
    if (!window.currentHashWithParams || !window.currentHashWithParams.includes('?')) {
        window.currentHashWithParams = currentHash;
    }
    
    checkCurrentPage();
});

// Handle login form submission
$(document).on('submit', '#loginForm', function(e) {
    e.preventDefault();
    
    const email = $('#email').val();
    const password = $('#password').val();
    
    // Basic validation
    if (!email || !password) {
        alert('Please fill in all fields');
        return;
    }
    
    // Here you would typically send the data to your backend
    
    
    window.location.hash = '#home';
    
    // Clear the form
    this.reset();
});

// Handle registration form submission
$(document).on('submit', '#registerForm', function(e) {
    e.preventDefault();
    
    const firstName = $('#firstName').val();
    const lastName = $('#lastName').val();
    const email = $('#email').val();
    const phone = $('#phone').val();
    const password = $('#password').val();
    const confirmPassword = $('#confirmPassword').val();
    
    // Basic validation
    if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
        alert('Please fill in all fields');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    // Here you would typically send the data to your backend
    
    // For demo purposes, show success message and redirect to login
    alert('Registration successful! Please login with your credentials.');
    window.location.hash = '#login';
    
    // Clear the form
    this.reset();
});

app.run();