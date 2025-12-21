const AdminService = {
    currentOrderId: null,
    currentProductId: null,
    categories: [],
    productImageFile: null,  // Store the file object instead of base64
    uploadedImagePath: null, // Store the uploaded image path

    /**
     * Initialize the admin page
     */
    init: function() {
        // Check if user is authenticated and is admin
        const token = localStorage.getItem("user_token");
        if (!token) {
            window.location.hash = "#login";
            return;
        }

        const payload = Utils.parseJwt(token);
        const user = payload ? payload.user : null;
        
        if (!user || user.role !== Constants.ADMIN_ROLE) {
            Utils.showError("Access denied. Admin privileges required.");
            window.location.hash = "#home";
            return;
        }

        // Load categories first, then dashboard
        this.loadCategories(() => {
            this.loadDashboardData();
        });
        
        // Setup image upload handler
        this.setupImageUpload();
    },

    /**
     * Setup image upload handler
     */
    setupImageUpload: function() {
        $(document).on('change', '#productImage', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Utils.showError("Image size should be less than 5MB");
                    e.target.value = '';
                    return;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    Utils.showError("Please select a valid image file");
                    e.target.value = '';
                    return;
                }
                
                // Store the file object
                AdminService.productImageFile = file;
                
                // Show preview using FileReader
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreviewContainer').show();
                };
                reader.onerror = function() {
                    Utils.showError("Failed to read image file");
                };
                reader.readAsDataURL(file);
            }
        });
    },

    /**
     * Clear uploaded image
     */
    clearImage: function() {
        this.productImageFile = null;
        this.uploadedImagePath = null;
        $('#productImage').val('');
        $('#imagePreview').attr('src', '');
        $('#imagePreviewContainer').hide();
    },

    /**
     * Load categories for dropdowns
     */
    loadCategories: function(callback) {
        RestClient.get(
            "api/categories",
            (response) => {
                if (response.success && response.data) {
                    this.categories = response.data;
                    // Populate category dropdown
                    const categorySelect = $("#productCategory");
                    categorySelect.html('<option value="">Select Category</option>');
                    this.categories.forEach((cat) => {
                        categorySelect.append(
                            `<option value="${cat.id}">${cat.name}</option>`
                        );
                    });
                }
                if (callback) callback();
            },
            (error) => {
                console.error("Failed to load categories:", error);
                if (callback) callback();
            }
        );
    },

    /**
     * Show specific admin section
     */
    showSection: function(sectionId) {
        // Hide all sections
        $(".admin-section").removeClass("active");

        // Remove active class from all nav links
        $(".admin-sidebar .nav-link").removeClass("active");

        // Show selected section
        $("#" + sectionId).addClass("active");

        // Add active class to clicked nav link
        event.target.classList.add("active");

        // Load section data
        this.loadSectionData(sectionId);
    },

    /**
     * Load data for specific section
     */
    loadSectionData: function(sectionId) {
        switch (sectionId) {
            case "dashboard":
                this.loadDashboardData();
                break;
            case "products":
                this.loadProductsData();
                break;
            case "orders":
                this.loadOrdersData();
                break;
            case "customers":
                this.loadCustomersData();
                break;
        }
    },

    /**
     * Load dashboard data (stats and recent orders)
     */
    loadDashboardData: function() {
        // Load dashboard statistics from the new statistics endpoint
        RestClient.get("api/statistics/dashboard", (response) => {
            if (response.success && response.data) {
                const stats = response.data;
                
                // Update stat cards
                $("#totalProducts").text(stats.total_products);
                $("#totalOrders").text(stats.total_orders);
                $("#totalRevenue").text("$" + stats.total_revenue.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $("#totalCustomers").text(stats.total_customers);

                // Load recent orders
                this.renderRecentOrders(stats.recent_orders);
            }
        }, (error) => {
            Utils.showError("Failed to load dashboard statistics");
        });
    },

    /**
     * Render recent orders table
     */
    renderRecentOrders: function(orders) {
        const table = $("#recentOrdersTable");
        table.empty();

        orders.forEach((order) => {
            const row = `
                <tr>
                    <td>#${order.id}</td>
                    <td>${order.user_name || "N/A"}</td>
                    <td>$${parseFloat(order.total_price).toFixed(2)}</td>
                    <td><span class="status-badge status-${order.status}">${this.formatStatus(
                order.status
            )}</span></td>
                    <td>${this.formatDate(order.created_at)}</td>
                    <td>
                        <button class="btn btn-action btn-view" onclick="AdminService.viewOrder(${
                            order.id
                        })">
                            <i class="bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            table.append(row);
        });
    },

    /**
     * Load products data
     */
    loadProductsData: function() {
        RestClient.get(
            "api/products",
            (response) => {
                if (response.success && response.data) {
                    this.renderProducts(response.data);
                }
            },
            (error) => {
                Utils.showError("Failed to load products");
            }
        );
    },

    /**
     * Render products table
     */
    renderProducts: function(products) {
        const table = $("#productsTable");
        table.empty();

        products.forEach((product) => {
            const categoryName =
                this.categories.find((c) => c.id === product.category_id)
                    ?.name || "N/A";
            const imageUrl = product.image_url || "images/no-image-placeholder.png";

            const row = `
                <tr>
                    <td><img src="${imageUrl}" alt="${
                product.name
            }" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                    <td>${product.name}</td>
                    <td>${categoryName}</td>
                    <td>${product.brand || "N/A"}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${product.quantity}</td>
                    <td><span class="status-badge ${
                        product.quantity > 0 ? "status-delivered" : "status-cancelled"
                    }">${
                product.quantity > 0 ? "In Stock" : "Out of Stock"
            }</span></td>
                    <td>
                        <button class="btn btn-action btn-edit" onclick="AdminService.editProduct(${
                            product.id
                        })">
                            <i class="bi-pencil"></i>
                        </button>
                        <button class="btn btn-action btn-delete" onclick="AdminService.deleteProduct(${
                            product.id
                        })">
                            <i class="bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            table.append(row);
        });
    },

    /**
     * Load orders data
     */
    loadOrdersData: function() {
        RestClient.get(
            "api/orders",
            (response) => {
                if (response.success && response.data) {
                    this.renderOrders(response.data);
                }
            },
            (error) => {
                Utils.showError("Failed to load orders");
            }
        );
    },

    /**
     * Render orders table
     */
    renderOrders: function(orders) {
        const table = $("#ordersTable");
        table.empty();

        orders.forEach((order) => {
            const productCount = order.products ? order.products.length : 0;
            const row = `
                <tr>
                    <td>#${order.id}</td>
                    <td>${order.user_name || "N/A"}</td>
                    <td>${productCount} item(s)</td>
                    <td>$${parseFloat(order.total_price).toFixed(2)}</td>
                    <td><span class="status-badge status-${order.status}">${this.formatStatus(
                order.status
            )}</span></td>
                    <td>${this.formatDate(order.created_at)}</td>
                    <td>
                        <button class="btn btn-action btn-view" onclick="AdminService.viewOrder(${
                            order.id
                        })">
                            <i class="bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            table.append(row);
        });
    },

    /**
     * Load customers data
     */
    loadCustomersData: function() {
        RestClient.get(
            "api/users",
            (response) => {
                if (response.success && response.data) {
                    this.renderCustomers(response.data);
                }
            },
            (error) => {
                Utils.showError("Failed to load customers");
            }
        );
    },

    /**
     * Render customers table
     */
    renderCustomers: function(customers) {
        const table = $("#customersTable");
        table.empty();

        customers.forEach((customer) => {
            // Fetch customer orders to calculate stats
            RestClient.get(
                `api/orders/user/${customer.id}`,
                (orderResponse) => {
                    const orders = orderResponse.success ? orderResponse.data : [];
                    const orderCount = orders.length;
                    const totalSpent = orders.reduce(
                        (sum, order) => sum + parseFloat(order.total_price || 0),
                        0
                    );

                    const row = `
                        <tr>
                            <td>${customer.id}</td>
                            <td>${customer.name} ${customer.last_name || ""}</td>
                            <td>${customer.email}</td>
                            <td>${customer.phone || "N/A"}</td>
                            <td>${orderCount}</td>
                            <td>$${totalSpent.toFixed(2)}</td>
                            <td>
                                <button class="btn btn-action btn-view" onclick="AdminService.viewCustomer(${
                                    customer.id
                                })">
                                    <i class="bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    table.append(row);
                },
                (error) => {
                    // If order fetch fails, show customer without order info
                    const row = `
                        <tr>
                            <td>${customer.id}</td>
                            <td>${customer.name} ${customer.last_name || ""}</td>
                            <td>${customer.email}</td>
                            <td>${customer.phone || "N/A"}</td>
                            <td>0</td>
                            <td>$0.00</td>
                            <td>
                                <button class="btn btn-action btn-view" onclick="AdminService.viewCustomer(${
                                    customer.id
                                })">
                                    <i class="bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    table.append(row);
                }
            );
        });
    },

    /**
     * Show product modal for add/edit
     */
    showProductModal: function(productId = null) {
        const modal = new bootstrap.Modal(document.getElementById("productModal"));
        const modalTitle = document.getElementById("productModalTitle");
        const form = document.getElementById("productForm");

        // Clear previous image
        this.clearImage();

        if (productId) {
            modalTitle.textContent = "Edit Product";
            this.currentProductId = productId;

            // Load product data
            RestClient.get(
                `api/products/${productId}`,
                (response) => {
                    if (response.success && response.data) {
                        const product = response.data;
                        $("#productName").val(product.name);
                        $("#productBrand").val(product.brand);
                        $("#productCategory").val(product.category_id);
                        $("#productPrice").val(product.price);
                        $("#productStock").val(product.quantity);
                        $("#productDescription").val(product.description);
                        
                        // Show existing image if available
                        if (product.image_url) {
                            this.uploadedImagePath = product.image_url;
                            $('#imagePreview').attr('src', product.image_url);
                            $('#imagePreviewContainer').show();
                        }
                    }
                },
                (error) => {
                    Utils.showError("Failed to load product details");
                }
            );
        } else {
            modalTitle.textContent = "Add Product";
            this.currentProductId = null;
            form.reset();
        }

        modal.show();
    },

    /**
     * Save product (create or update)
     */
    saveProduct: function() {
        const name = $("#productName").val();
        const brand = $("#productBrand").val();
        const category_id = $("#productCategory").val();
        const price = $("#productPrice").val();
        const quantity = $("#productStock").val();
        const description = $("#productDescription").val();

        if (!name || !brand || !category_id || !price || !quantity) {
            Utils.showError("Please fill in all required fields");
            return;
        }

        // If there's a new image file, upload it first
        if (this.productImageFile) {
            this.uploadImageAndSaveProduct(name, brand, category_id, price, quantity, description);
        } else {
            // No new image, save product with existing image path
            this.saveProductData(name, brand, category_id, price, quantity, description, this.uploadedImagePath);
        }
    },

    /**
     * Upload image and then save product
     */
    uploadImageAndSaveProduct: function(name, brand, category_id, price, quantity, description) {
        const formData = new FormData();
        formData.append('image', this.productImageFile);

        $.ajax({
            url: Constants.PROJECT_BASE_URL + 'api/upload/image',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(xhr) {
                const token = localStorage.getItem("user_token");
                if (token) {
                    xhr.setRequestHeader("Authentication", "Bearer " + token);
                }
            },
            success: (response) => {
                if (response.success && response.data) {
                    // Save product with the uploaded image path
                    this.saveProductData(name, brand, category_id, price, quantity, description, response.data.url);
                } else {
                    Utils.showError(response.message || "Failed to upload image");
                }
            },
            error: (jqXHR) => {
                Utils.showError(jqXHR.responseJSON?.message || "Failed to upload image");
            }
        });
    },

    /**
     * Save product data to backend
     */
    saveProductData: function(name, brand, category_id, price, quantity, description, image_url) {
        const productData = {
            name,
            brand,
            category_id: parseInt(category_id),
            price: parseFloat(price),
            quantity: parseInt(quantity),
            description,
        };

        // Add image URL if provided
        if (image_url) {
            productData.image_url = image_url;
        }

        if (this.currentProductId) {
            // Update existing product
            RestClient.put(
                `api/products/${this.currentProductId}`,
                productData,
                (response) => {
                    if (response.success) {
                        Utils.showSuccess("Product updated successfully");
                        bootstrap.Modal.getInstance(
                            document.getElementById("productModal")
                        ).hide();
                        this.loadProductsData();
                        this.clearImage();
                    }
                },
                (error) => {
                    Utils.showError(
                        error.responseJSON?.message || "Failed to update product"
                    );
                }
            );
        } else {
            // Create new product
            RestClient.post(
                "api/products",
                productData,
                (response) => {
                    if (response.success) {
                        Utils.showSuccess("Product created successfully");
                        bootstrap.Modal.getInstance(
                            document.getElementById("productModal")
                        ).hide();
                        this.loadProductsData();
                        this.clearImage();
                    }
                },
                (error) => {
                    Utils.showError(
                        error.responseJSON?.message || "Failed to create product"
                    );
                }
            );
        }
    },

    /**
     * Edit product
     */
    editProduct: function(productId) {
        this.showProductModal(productId);
    },

    /**
     * Delete product
     */
    deleteProduct: function(productId) {
        if (confirm("Are you sure you want to delete this product?")) {
            RestClient.delete(
                `api/products/${productId}`,
                null,
                (response) => {
                    if (response.success) {
                        Utils.showSuccess("Product deleted successfully");
                        this.loadProductsData();
                    }
                },
                (error) => {
                    Utils.showError(
                        error.responseJSON?.message || "Failed to delete product"
                    );
                }
            );
        }
    },

    /**
     * View order details
     */
    viewOrder: function(orderId) {
        this.currentOrderId = orderId;

        RestClient.get(
            `api/orders/${orderId}`,
            (response) => {
                if (response.success && response.data) {
                    this.populateOrderModal(response.data);
                }
            },
            (error) => {
                Utils.showError("Failed to load order details");
            }
        );
    },

    /**
     * Populate order modal with data
     */
    populateOrderModal: function(order) {
        // Order information
        $("#orderId").text("#" + order.id);
        $("#orderDate").text(this.formatDate(order.created_at));
        $("#orderAmount").text("$" + parseFloat(order.total_price).toFixed(2));

        // Status badge
        const statusElement = $("#orderStatus");
        statusElement.text(this.formatStatus(order.status));
        statusElement.attr("class", "status-badge status-" + order.status);

        // Customer information - fetch user details
        if (order.user_id) {
            RestClient.get(`api/users/${order.user_id}`, (userResponse) => {
                if (userResponse.success && userResponse.data) {
                    const user = userResponse.data;
                    $("#customerName").text(`${user.name} ${user.last_name || ""}`);
                    $("#customerEmail").text(user.email || "N/A");
                    $("#customerPhone").text(user.phone || "N/A");
                } else {
                    $("#customerName").text("N/A");
                    $("#customerEmail").text("N/A");
                    $("#customerPhone").text("N/A");
                }
            }, (error) => {
                $("#customerName").text("N/A");
                $("#customerEmail").text("N/A");
                $("#customerPhone").text("N/A");
            });
        } else {
            $("#customerName").text("N/A");
            $("#customerEmail").text("N/A");
            $("#customerPhone").text("N/A");
        }

        // Shipping address
        if (order.address) {
            // The address is stored as a simple string: "address, city, state zipCode"
            // Try to parse it
            const addressParts = order.address.split(', ');
            if (addressParts.length >= 3) {
                $("#shippingAddress").text(addressParts[0] || "N/A");
                $("#shippingCity").text(addressParts[1] || "N/A");
                // State and zip are in the last part
                const stateZip = addressParts[2].split(' ');
                $("#shippingState").text(stateZip[0] || "N/A");
                $("#shippingZip").text(stateZip[1] || "N/A");
            } else {
                // Fallback: just show the whole address
                $("#shippingAddress").text(order.address);
                $("#shippingCity").text("N/A");
                $("#shippingState").text("N/A");
                $("#shippingZip").text("N/A");
            }
        } else {
            $("#shippingAddress").text("N/A");
            $("#shippingCity").text("N/A");
            $("#shippingState").text("N/A");
            $("#shippingZip").text("N/A");
        }

        // Order items
        const itemsTable = $("#orderItemsTable");
        itemsTable.empty();

        if (order.products && order.products.length > 0) {
            order.products.forEach((item) => {
                const row = `
                    <tr>
                        <td>${item.name || "N/A"}</td>
                        <td>${item.quantity}</td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>$${(item.quantity * parseFloat(item.price)).toFixed(2)}</td>
                    </tr>
                `;
                itemsTable.append(row);
            });
        }

        // Order totals
        const shippingCost = parseFloat(order.shipping_cost || 0);
        const totalPrice = parseFloat(order.total_price);
        const subtotal = totalPrice - shippingCost;

        $("#orderSubtotal").text("$" + subtotal.toFixed(2));
        $("#orderShipping").text("$" + shippingCost.toFixed(2));
        $("#orderTax").text("$0.00");
        $("#orderTotal").text("$" + totalPrice.toFixed(2));

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById("orderModal"));
        modal.show();
    },

    /**
     * Show status change modal
     */
    showStatusModal: function() {
        if (!this.currentOrderId) {
            Utils.showError("No order selected");
            return;
        }

        // Get current order to set default status
        RestClient.get(`api/orders/${this.currentOrderId}`, (response) => {
            if (response.success && response.data) {
                $("#newStatus").val(response.data.status);
                $("#statusNote").val("");

                // Show status modal
                const modal = new bootstrap.Modal(
                    document.getElementById("statusModal")
                );
                modal.show();
            }
        });
    },

    /**
     * Update order status
     */
    updateOrderStatus: function() {
        const newStatus = $("#newStatus").val();
        const statusNote = $("#statusNote").val();

        if (!newStatus) {
            Utils.showError("Please select a new status");
            return;
        }

        RestClient.patch(
            `api/orders/${this.currentOrderId}/status`,
            { status: newStatus },
            (response) => {
                if (response.success) {
                    Utils.showSuccess("Order status updated successfully");

                    // Close modals
                    bootstrap.Modal.getInstance(
                        document.getElementById("statusModal")
                    ).hide();
                    bootstrap.Modal.getInstance(
                        document.getElementById("orderModal")
                    ).hide();

                    // Check which section is currently active and refresh it
                    const activeSection = $(".admin-section.active").attr("id");
                    
                    if (activeSection === "dashboard") {
                        this.loadDashboardData();
                    } else if (activeSection === "orders") {
                        this.loadOrdersData();
                    }
                }
            },
            (error) => {
                Utils.showError(
                    error.responseJSON?.message || "Failed to update order status"
                );
            }
        );
    },

    /**
     * View customer details
     */
    viewCustomer: function(customerId) {
        Utils.showSuccess(`Viewing customer: ${customerId}`);
        // In future: Show customer details modal
    },

    /**
     * Format date
     */
    formatDate: function(dateString) {
        if (!dateString) return "N/A";
        const date = new Date(dateString);
        return date.toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    },

    /**
     * Format status text
     */
    formatStatus: function(status) {
        if (!status) return "N/A";
        return status.charAt(0).toUpperCase() + status.slice(1);
    },
};

// Make showAdminSection available globally
window.showAdminSection = function(sectionId) {
    AdminService.showSection(sectionId);
};

// Make functions available globally for onclick handlers
window.showProductModal = function(productId) {
    AdminService.showProductModal(productId);
};

window.saveProduct = function() {
    AdminService.saveProduct();
};

window.showStatusModal = function() {
    AdminService.showStatusModal();
};

window.updateOrderStatus = function() {
    AdminService.updateOrderStatus();
};

