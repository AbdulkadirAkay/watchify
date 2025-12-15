var ProfileService = {
  _getCurrentUser: function () {
    const token = localStorage.getItem("user_token");
    if (!token) return null;
    const payload = Utils.parseJwt(token);
    return payload ? payload.user : null;
  },

  _showError: function (msg) {
    Utils.showError(msg);
  },

  _showSuccess: function (msg) {
    Utils.showSuccess(msg);
  },

  init: function () {
    const user = ProfileService._getCurrentUser();
    if (!user) {
      window.location.hash = "#login";
      return;
    }

    // Populate sidebar header (use first + last if available)
    const displayName = (user.name || "") + (user.last_name ? " " + user.last_name : "");
    $("#profileName").text(displayName.trim() || "");
    $("#profileEmail").text(user.email || "");

    // Prefill form from JWT (basic) and then refresh from backend
    $("#firstName").val(user.name || "");
    $("#lastName").val(user.last_name || "");
    $("#email").val(user.email || "");
    $("#phone").val(user.phone || "");
    $("#address").val(user.address || "");
    $("#city").val(user.city || "");
    $("#zipCode").val(user.zip_code || "");

    ProfileService.loadUser(user.id);
    ProfileService.loadOrders(user.id);
    ProfileService.bindForms(user.id);
  },

  loadUser: function (userId) {
    RestClient.get(
      "api/users/" + userId,
      function (res) {
        if (!res || !res.success || !res.data) return;
        const u = res.data;

        $("#firstName").val(u.name || "");
        $("#lastName").val(u.last_name || "");
        $("#email").val(u.email || "");
        $("#phone").val(u.phone || "");
        $("#address").val(u.address || "");
        $("#city").val(u.city || "");
        $("#zipCode").val(u.zip_code || "");

        const displayName =
          (u.name || "") + (u.last_name ? " " + u.last_name : "");
        $("#profileName").text(displayName.trim() || "");
        $("#profileEmail").text(u.email || "");
      },
      function (jqXHR) {
        const res = jqXHR && jqXHR.responseJSON;
        ProfileService._showError(
          (res && (res.message || res.error)) ||
            "Failed to load profile information"
        );
      }
    );
  },

  loadOrders: function (userId) {
    RestClient.get(
      "api/orders/user/" + userId,
      function (res) {
        const $container = $("#ordersContainer");
        if (!$container.length) return;

        $container.empty();

        if (!res.success || !res.data || res.data.length === 0) {
          $container.append(
            '<p class="text-muted">You have no orders yet.</p>'
          );
          return;
        }

        res.data.forEach(function (order) {
          const statusClass =
            order.status === "delivered"
              ? "status-delivered"
              : order.status === "shipped"
              ? "status-shipped"
              : "status-pending";

          const created =
            order.created_at ||
            (order.createdAt || "").toString().replace("T", " ").split(".")[0];

          const html = `
          <div class="order-item">
            <div class="row align-items-center">
              <div class="col-md-4">
                <h6 class="mb-1">Order #${order.id}</h6>
                <small class="text-muted">Created: ${created || ""}</small>
              </div>
              <div class="col-md-2">
                <span class="order-status ${statusClass}">${order.status}</span>
              </div>
              <div class="col-md-3">
                <h6 class="mb-0">$${order.total_price || order.total || 0}</h6>
              </div>
              <div class="col-md-3 text-end">
                <small class="text-muted">ID: ${order.id}</small>
              </div>
            </div>
          </div>`;

          $container.append(html);
        });
      },
      function (jqXHR) {
        const res = jqXHR && jqXHR.responseJSON;
        ProfileService._showError(
          (res && (res.message || res.error)) ||
            "Failed to load orders"
        );
      }
    );
  },

  bindForms: function (userId) {
    // User info form
    $(document).off("submit", "#userInfoForm");
    $(document).on("submit", "#userInfoForm", function (e) {
      e.preventDefault();
      const firstName = $("#firstName").val() || "";
      const lastName = $("#lastName").val() || "";

      const payload = {
        name: firstName,
        last_name: lastName,
        email: $("#email").val(),
        phone: $("#phone").val(),
        address: $("#address").val(),
        city: $("#city").val(),
        zip_code: $("#zipCode").val(),
      };

      RestClient.put(
        "api/users/" + userId,
        payload,
        function (res) {
          if (!res || !res.success) {
            ProfileService._showError(
              (res && (res.message || res.error)) ||
                "Failed to update profile information"
            );
            return;
          }

          const displayName =
            (payload.name || "") +
            (payload.last_name ? " " + payload.last_name : "");
          $("#profileName").text(displayName.trim() || "");
          $("#profileEmail").text(payload.email || "");

          ProfileService._showSuccess("Profile updated successfully");
        },
        function (jqXHR) {
          const res = jqXHR && jqXHR.responseJSON;
          let msg =
            (res && (res.message || res.error)) ||
            jqXHR.responseText ||
            "Failed to update profile information";

          if (res && res.errors) {
            const fieldErrors = [];
            Object.values(res.errors).forEach((val) => {
              if (Array.isArray(val)) {
                fieldErrors.push(...val);
              } else if (typeof val === "string") {
                fieldErrors.push(val);
              }
            });
            if (fieldErrors.length > 0) {
              msg += " - " + fieldErrors.join(", ");
            }
          }

          ProfileService._showError(msg);
        }
      );
    });

    // Change password form
    $(document).off("submit", "#changePasswordForm");
    $(document).on("submit", "#changePasswordForm", function (e) {
      e.preventDefault();
      const newPassword = $("#newPassword").val();
      const confirmNewPassword = $("#confirmNewPassword").val();

      if (newPassword !== confirmNewPassword) {
        ProfileService._showError("New password and confirmation do not match.");
        return;
      }

      if (!newPassword || newPassword.length < 6) {
        ProfileService._showError(
          "Password must be at least 6 characters long."
        );
        return;
      }

      RestClient.patch(
        "api/users/" + userId + "/password",
        { password: newPassword },
        function (res) {
          if (!res || !res.success) {
            ProfileService._showError(
              (res && (res.message || res.error)) ||
                "Failed to update password"
            );
            return;
          }

          ProfileService._showSuccess("Password updated successfully");
          $("#currentPassword").val("");
          $("#newPassword").val("");
          $("#confirmNewPassword").val("");
        },
        function (jqXHR) {
          const res = jqXHR && jqXHR.responseJSON;
          let msg =
            (res && (res.message || res.error)) ||
            jqXHR.responseText ||
            "Failed to update password";

          if (res && res.errors) {
            const fieldErrors = [];
            Object.values(res.errors).forEach((val) => {
              if (Array.isArray(val)) {
                fieldErrors.push(...val);
              } else if (typeof val === "string") {
                fieldErrors.push(val);
              }
            });
            if (fieldErrors.length > 0) {
              msg += " - " + fieldErrors.join(", ");
            }
          }

          ProfileService._showError(msg);
        }
      );
    });
  },
};


