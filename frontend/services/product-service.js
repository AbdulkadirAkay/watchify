var ProductService = {
  _getProductIdFromHash: function () {
    // Prefer stored hash with params if available
    var hash = window.currentHashWithParams || window.location.hash || "";
    // Expect formats like "#product?id=1"
    var qIndex = hash.indexOf("?");
    if (qIndex === -1) return null;

    var query = hash.substring(qIndex + 1);
    var params = new URLSearchParams(query.replace("#", ""));
    var id = params.get("id");
    return id ? parseInt(id, 10) : null;
  },

  init: function () {
    var id = ProductService._getProductIdFromHash();
    var $container = $("#productDetails");

    if (!$container.length) return;

    if (!id || isNaN(id)) {
      $container.html(
        '<div class="col-12 text-center text-muted py-5">Invalid product.</div>'
      );
      return;
    }

    // Show loading state
    $container.html(
      '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
    );

    RestClient.get(
      "api/products/" + id,
      function (res) {
        if (!res || !res.success || !res.data) {
          Utils.showError(
            (res && (res.message || res.error)) || "Failed to load product"
          );
          $container.html(
            '<div class="col-12 text-center text-muted py-5">Product not found.</div>'
          );
          return;
        }

        ProductService.renderProduct(res.data);
      },
      function (jqXHR) {
        var res = jqXHR && jqXHR.responseJSON;
        var msg =
          (res && (res.message || res.error)) ||
          jqXHR.responseText ||
          "Failed to load product";
        Utils.showError(msg);
        $container.html(
          '<div class="col-12 text-center text-muted py-5">Unable to load product.</div>'
        );
      }
    );
  },

  renderProduct: function (p) {
    var $container = $("#productDetails");
    $container.empty();

    var img = p.image_url || "images/no-image-placeholder.png";
    var price = p.price != null ? Number(p.price).toFixed(2) : "0.00";
    var isOutOfStock = !p.quantity || Number(p.quantity) <= 0;

    var html = `
      <div class="col-md-5 mb-4">
        <img src="${img}" class="img-fluid rounded shadow-sm" alt="${p.name || ""}">
      </div>
      <div class="col-md-7">
        <h2 class="fw-bold mb-3">${p.name || "Unnamed product"}</h2>
        <p class="text-muted mb-1">${p.brand || ""}</p>
        <h4 class="text-primary mb-3">$${price}</h4>
        <p class="mb-4">${p.description || ""}</p>
        <p class="mb-2"><strong>Availability:</strong> ${
          isOutOfStock ? "Out of stock" : "In stock (" + p.quantity + ")"
        }</p>
        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-dark" type="button" ${
            isOutOfStock
              ? "disabled"
              : `onclick="ProductService.addToCart(${p.id}, event)"`
          }>
            ${isOutOfStock ? "Out of stock" : "Add to cart"}
          </button>
        </div>
      </div>
    `;

    $container.html(html);
  },

  // Common product card for all product listings (home, category, brand, etc.)
  createProductCard: function (p) {
    var img = p.image_url || "images/no-image-placeholder.png";
    var price = p.price != null ? Number(p.price).toFixed(2) : "0.00";
    var isOutOfStock = !p.quantity || Number(p.quantity) <= 0;``

    var badge = isOutOfStock
      ? '<div class="badge bg-secondary text-white position-absolute" style="top: 0.5rem; right: 0.5rem">Out of stock</div>'
      : "";

    var addBtnAttrs = isOutOfStock
      ? 'disabled'
      : 'onclick="ProductService.addToCart(' + p.id + ', event)"';
    var addBtnLabel = isOutOfStock ? "Out of stock" : "Add to cart";

    return (
      '<div class="col mb-5">' +
      '  <div class="card h-100 product-card position-relative">' +
      badge +
      '    <img class="card-img-top product-image" src="' +
      img +
      '" alt="' +
      (p.name || "") +
      '" />' +
      '    <div class="card-body p-4">' +
      '      <div class="text-center">' +
      '        <h5 class="fw-bolder">' +
      (p.name || "Unnamed product") +
      "</h5>" +
      '        <p class="text-muted mb-2">' +
      (p.brand || "") +
      "</p>" +
      '        <div class="mb-3">' +
      '          <span class="fw-bold">$' +
      price +
      "</span>" +
      "        </div>" +
      "      </div>" +
      "    </div>" +
      '    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">' +
      '      <div class="text-center">' +
      '        <button class="btn btn-add-cart w-100 mb-2" type="button" ' +
      addBtnAttrs +
      ">" +
      '          <i class="bi-cart-plus me-2"></i>' +
      addBtnLabel +
      "        </button>" +
      '        <a class="btn btn-outline-secondary w-100" href="#product?id=' +
      p.id +
      '">' +
      '          <i class="bi-eye me-2"></i>View details' +
      "        </a>" +
      "      </div>" +
      "    </div>" +
      "  </div>" +
      "</div>"
    );
  },

  addToCart: function (productId, e) {
    try {
      console.log("Adding product to cart:", productId);

      // Add visual feedback to button
      if (e && e.target) {
        var btn = e.target.closest("button");
        if (btn) {
          var originalHtml = btn.innerHTML;
          var isAddCartStyle = btn.classList.contains("btn-add-cart");

          btn.innerHTML = '<i class="bi-check me-2"></i>Added!';
          btn.classList.add("btn-success");
          if (isAddCartStyle) {
            btn.classList.remove("btn-add-cart");
          } else {
            btn.classList.remove("btn-dark");
          }

          setTimeout(function () {
            btn.innerHTML = originalHtml;
            btn.classList.remove("btn-success");
            if (isAddCartStyle) {
              btn.classList.add("btn-add-cart");
            } else {
              btn.classList.add("btn-dark");
            }
          }, 2000);
        }
      }

      // Use CartService to add to cart and persist to localStorage
      if (typeof CartService !== "undefined" && CartService.addToCart) {
        CartService.addToCart(productId, 1);
      } else {
        console.error("CartService not available");
        if (typeof Utils !== "undefined" && Utils.showError) {
          Utils.showError("Failed to add product to cart.");
        }
      }
    } catch (err) {
      if (typeof Utils !== "undefined" && Utils.showError) {
        Utils.showError("Failed to add product to cart.");
      } else {
        console.error("Failed to add product to cart:", err);
      }
    }
  },
};


