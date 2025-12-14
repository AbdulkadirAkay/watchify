var CartService = {
  CART_STORAGE_KEY: "watchify_cart",

  /**
   * Get cart from localStorage
   * Returns array of cart items: [{ productId, quantity, ...productData }]
   */
  getCart: function () {
    try {
      var cartJson = localStorage.getItem(CartService.CART_STORAGE_KEY);
      return cartJson ? JSON.parse(cartJson) : [];
    } catch (err) {
      console.error("Failed to parse cart from localStorage:", err);
      return [];
    }
  },

  /**
   * Save cart to localStorage
   */
  saveCart: function (cart) {
    try {
      localStorage.setItem(CartService.CART_STORAGE_KEY, JSON.stringify(cart));
      CartService.updateCartBadge();
    } catch (err) {
      console.error("Failed to save cart to localStorage:", err);
    }
  },

  /**
   * Add product to cart
   * If product already exists, increment quantity
   * Otherwise, fetch product details and add new item
   */
  addToCart: function (productId, quantity) {
    quantity = quantity || 1;

    // Fetch product details from backend
    RestClient.get(
      "api/products/" + productId,
      function (res) {
        if (!res || !res.success || !res.data) {
          if (typeof Utils !== "undefined" && Utils.showError) {
            Utils.showError("Failed to load product details.");
          }
          return;
        }

        var product = res.data;
        var cart = CartService.getCart();

        // Check if product already in cart
        var existingItem = cart.find(function (item) {
          return item.productId === productId;
        });

        if (existingItem) {
          // Increment quantity
          existingItem.quantity += quantity;
        } else {
          // Add new item to cart
          cart.push({
            productId: product.id,
            name: product.name,
            brand: product.brand,
            price: product.price,
            image_url: product.image_url,
            quantity: quantity,
          });
        }

        CartService.saveCart(cart);

        if (typeof Utils !== "undefined" && Utils.showSuccess) {
          Utils.showSuccess("Product added to cart!");
        }
      },
      function (jqXHR) {
        var res = jqXHR && jqXHR.responseJSON;
        var msg =
          (res && (res.message || res.error)) ||
          jqXHR.responseText ||
          "Failed to add product to cart";
        if (typeof Utils !== "undefined" && Utils.showError) {
          Utils.showError(msg);
        }
      }
    );
  },

  /**
   * Remove product from cart
   */
  removeFromCart: function (productId) {
    var cart = CartService.getCart();
    cart = cart.filter(function (item) {
      return item.productId !== productId;
    });
    CartService.saveCart(cart);
  },

  /**
   * Update quantity for a cart item
   */
  updateQuantity: function (productId, quantity) {
    var cart = CartService.getCart();
    var item = cart.find(function (i) {
      return i.productId === productId;
    });

    if (item) {
      if (quantity <= 0) {
        CartService.removeFromCart(productId);
      } else {
        item.quantity = quantity;
        CartService.saveCart(cart);
      }
    }
  },

  /**
   * Clear entire cart
   */
  clearCart: function () {
    localStorage.removeItem(CartService.CART_STORAGE_KEY);
    CartService.updateCartBadge();
  },

  /**
   * Get total number of items in cart
   */
  getCartItemCount: function () {
    var cart = CartService.getCart();
    return cart.reduce(function (total, item) {
      return total + (item.quantity || 0);
    }, 0);
  },

  /**
   * Get total price of cart
   */
  getCartTotal: function () {
    var cart = CartService.getCart();
    return cart.reduce(function (total, item) {
      var price = parseFloat(item.price) || 0;
      var quantity = parseInt(item.quantity) || 0;
      return total + price * quantity;
    }, 0);
  },

  /**
   * Update the cart badge in navigation
   */
  updateCartBadge: function () {
    var count = CartService.getCartItemCount();
    var $badge = $(".btn-outline-dark .badge");
    if ($badge.length) {
      $badge.text(count);
    }
  },

  /**
   * Initialize cart (update badge on page load)
   */
  init: function () {
    CartService.updateCartBadge();
  },
};

