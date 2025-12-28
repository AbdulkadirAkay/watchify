var app = $.spapp({
    defaultView: "#home",
    templateDir: "./views/"

});


app.route({
    view: 'category-products',
    onReady: function(params) {
        console.log('Category products route ready with params:', params);
        if (typeof initCategoryProducts === 'function') {
            initCategoryProducts(params);
        }
    }
});


app.route({
    view: 'brand-products',
    onReady: function(params) {
        console.log('Brand products route ready with params:', params);
        if (typeof initBrandProducts === 'function') {
            initBrandProducts(params);
        }
    }
});


app.route({
    view: 'admin',
    onReady: function(params) {
        console.log('Admin route ready');
        if (typeof AdminService !== 'undefined' && AdminService.init) {
            AdminService.init();
        }
    }
});


function updateAuthButtons() {
    const token = localStorage.getItem("user_token");
    const loggedIn = !!token;

    const $loginBtn = $("#loginBtn");
    const $logoutBtn = $("#logoutBtn");
    const $profileBtn = $("#profileBtn");
    const $adminBtn = $("#adminBtn");

    if (loggedIn) {
        const payload = Utils.parseJwt(token);
        const user = payload ? payload.user : null;

        $loginBtn.addClass("d-none");
        $logoutBtn.removeClass("d-none");
        $profileBtn.removeClass("d-none");


        if (user && user.role === Constants.ADMIN_ROLE) {
            $adminBtn.removeClass("d-none");
        } else {
            $adminBtn.addClass("d-none");
        }
    } else {
        $loginBtn.removeClass("d-none");
        $logoutBtn.addClass("d-none");
        $profileBtn.addClass("d-none");
        $adminBtn.addClass("d-none");
    }
    

    updateFooterVisibility(loggedIn);
}


function updateFooterVisibility(loggedIn) {
    const $footerCustomerService = $("#footerCustomerService");
    const $footerMyAccount = $("#footerMyAccount");
    const $footerShoppingCart = $("#footerShoppingCart");
    const $footerOrderHistory = $("#footerOrderHistory");
    
    if (loggedIn) {

        $footerCustomerService.show();
        $footerMyAccount.show();
        $footerShoppingCart.show();
        $footerOrderHistory.show();
    } else {
        
        $footerCustomerService.hide();
        $footerMyAccount.hide();
        $footerShoppingCart.hide();
        $footerOrderHistory.hide();
    }
}


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


function checkCurrentPage() {
    const currentHash = window.location.hash;
    
    const baseRoute = currentHash.split('?')[0];
    
    if (baseRoute === '#login' || baseRoute === '#register' || baseRoute === '#order-success' || baseRoute === '#admin') {
        toggleNavigation(false);
    } else {
        toggleNavigation(true);
    }

    
    updateAuthButtons();
}


$(window).on('hashchange', function() {
    
    const currentHash = window.location.hash;
    if (!currentHash || currentHash === '#') {
        window.location.hash = '#home';
        return;
    }
    

    
    checkCurrentPage();
    
    
    if (typeof CartService !== "undefined" && CartService.updateCartBadge) {
        CartService.updateCartBadge();
    }
});


$(document).ready(function() {
    // Set current year in footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    const currentHash = window.location.hash;
    if (!currentHash || currentHash === '#') {
        window.location.hash = '#home';
        return;
    }
    
    checkCurrentPage();
    
    updateAuthButtons();
    
    
    if (typeof CartService !== "undefined" && CartService.init) {
        CartService.init();
    }
    if (typeof NavigationService !== 'undefined' && NavigationService.init) {
        NavigationService.init();
    }
});


$(document).on('submit', '#registerForm', function(e) {
    e.preventDefault();
    
    const entity = Object.fromEntries(new FormData(e.target).entries());
    const { firstName, lastName, email, phone, password, confirmPassword } = entity;
    
    
    if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
        alert('Please fill in all fields');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    
    UserService.register(entity);
});

app.run();