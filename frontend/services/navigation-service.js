const NavigationService = {
    /**
     * Initialize navigation - load categories and brands
     */
    init: function() {
        this.loadCategories();
        this.loadBrands();
    },

    /**
     * Load categories from backend and populate dropdown
     */
    loadCategories: function() {
        RestClient.get(
            "api/categories",
            (response) => {
                if (response.success && response.data) {
                    this.populateCategoriesDropdown(response.data);
                }
            },
            (error) => {
                console.error("Failed to load categories:", error);
            }
        );
    },

    /**
     * Populate categories dropdown
     */
    populateCategoriesDropdown: function(categories) {
        const $dropdown = $("#categoriesDropdown").next(".dropdown-menu");
        $dropdown.empty();

        categories.forEach((category) => {
            const item = `
                <li>
                    <a class="dropdown-item" href="#category-products?id=${category.id}">${category.name}</a>
                </li>
            `;
            $dropdown.append(item);
        });
    },

    /**
     * Load brands from backend and populate dropdown
     */
    loadBrands: function() {
        RestClient.get(
            "api/products/brands",
            (response) => {
                if (response.success && response.data) {
                    this.populateBrandsDropdown(response.data);
                }
            },
            (error) => {
                console.error("Failed to load brands:", error);
            }
        );
    },

    /**
     * Populate brands dropdown
     */
    populateBrandsDropdown: function(brands) {
        const $dropdown = $("#brandsDropdown").next(".dropdown-menu");
        $dropdown.empty();

        brands.forEach((brandItem) => {
            const brandName = brandItem.brand;
            // URL encode the brand name for the link
            const encodedBrand = encodeURIComponent(brandName);
            const item = `
                <li>
                    <a class="dropdown-item" href="#brand-products?brand=${encodedBrand}">${brandName}</a>
                </li>
            `;
            $dropdown.append(item);
        });
    }
};


