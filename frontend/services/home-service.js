var HomeService = {
  init: function () {
    const $container = $("#homeProducts");
    if (!$container.length) return;

    // Clear and show loading state
    $container.html(
      '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
    );


    RestClient.get(
      "api/products/available",
      function (res) {
        if (!res || !res.success || !Array.isArray(res.data)) {
          Utils.showError(
            (res && (res.message || res.error)) ||
              "Failed to load products from server"
          );
          $container.html(
            '<div class="col-12 text-center text-muted py-5">No products found.</div>'
          );
          return;
        }

        HomeService.renderProducts(res.data);
      },
      function (jqXHR) {
        const res = jqXHR && jqXHR.responseJSON;
        const msg =
          (res && (res.message || res.error)) ||
          jqXHR.responseText ||
          "Failed to load products";
        Utils.showError(msg);
        $("#homeProducts").html(
          '<div class="col-12 text-center text-muted py-5">Unable to load products.</div>'
        );
      }
    );
  },

  renderProducts: function (products) {
    const $container = $("#homeProducts");
    $container.empty();

    if (!products.length) {
      $container.html(
        '<div class="col-12 text-center text-muted py-5">No products available.</div>'
      );
      return;
    }

    products.forEach(function (p) {
      if (typeof ProductService !== "undefined" && ProductService.createProductCard) {
        const card = ProductService.createProductCard(p);
        $container.append(card);
      }
    });
  },
};


