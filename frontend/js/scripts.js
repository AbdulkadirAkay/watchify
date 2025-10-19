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
    checkCurrentPage();
});

// Check on initial load
$(document).ready(function() {
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
    console.log('Login attempt:', { email, password });
    
    
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
    console.log('Registration attempt:', { firstName, lastName, email, phone, password });
    
    // For demo purposes, show success message and redirect to login
    alert('Registration successful! Please login with your credentials.');
    window.location.hash = '#login';
    
    // Clear the form
    this.reset();
});

app.run();