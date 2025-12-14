var UserService = {
  _showError: function (msg) {
    Utils.showError(msg);
  },

  _showSuccess: function (msg) {
    Utils.showSuccess(msg);
  },
  init: function () {
    const token = localStorage.getItem("user_token");
    if (token) {
      // If already authenticated, send user to home view
      window.location.hash = "#home";
      return;
    }

    // Attach submit handler for login form
    $(document).off("submit", "#loginForm");
    $(document).on("submit", "#loginForm", function (e) {
      e.preventDefault();
      const entity = Object.fromEntries(new FormData(e.target).entries());
      UserService.login(entity);
    });
  },
  login: function (entity) {
    $.ajax({
      url: Constants.PROJECT_BASE_URL + "auth/login",
      type: "POST",
      data: JSON.stringify(entity),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        localStorage.setItem("user_token", result.data.token);
        UserService._showSuccess("Logged in successfully.");
        window.location.hash = "#home";
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        const res = XMLHttpRequest && XMLHttpRequest.responseJSON;
        const baseMsg =
          (res && (res.error || res.message)) ||
          XMLHttpRequest.responseText ||
          "Login failed";

        // Show inline alert above login form
        const $alert = $("#loginAlert");
        if ($alert.length) {
          $alert.removeClass("d-none").text(baseMsg);
        }

        UserService._showError(baseMsg);
      },
    });
  },

  register: function (entity) {
    // Map frontend fields to backend expectations
    const payload = {
      name: entity.firstName || "",
      last_name: entity.lastName || "",
      email: entity.email,
      password: entity.password,
      phone: entity.phone,
    };

    $.ajax({
      url: Constants.PROJECT_BASE_URL + "auth/register",
      type: "POST",
      data: JSON.stringify(payload),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        UserService._showSuccess("Registration successful! Logging you in...");
        // Automatically log the user in with the same credentials
        UserService.login({
          email: payload.email,
          password: payload.password,
        });
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        const res = XMLHttpRequest && XMLHttpRequest.responseJSON;
        let msg =
          (res && (res.error || res.message)) ||
          XMLHttpRequest.responseText ||
          "Registration failed";

        // Append field-level validation errors if present
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

        UserService._showError(msg);
      },
    });
  },

  logout: function () {
    localStorage.clear();
    UserService._showSuccess("Logged out successfully.");
    window.location.hash = "#login";
  },

  generateMenuItems: function () {
    const token = localStorage.getItem("user_token");
    if (!token) return window.location.replace("login.html");

    const user = Utils.parseJwt(token).user;
    const nav = document.getElementById("nav-menu");

    nav.innerHTML = ""; // clear old menu

    // HOME (everyone sees)
    nav.innerHTML += `
            <li class="nav-item mx-0 mx-lg-1">
                <a class="nav-link py-3 px-0 px-lg-3 rounded" href="#home">Home</a>
            </li>
        `;

    if (user.role === Constants.ADMIN_ROLE) {
      // ADMIN MENU
      nav.innerHTML += `
                <li class="nav-item mx-0 mx-lg-1">
                    <a class="nav-link py-3 px-0 px-lg-3 rounded" href="#students">Students</a>
                </li>
            `;
    }

    if (user.role === Constants.USER_ROLE) {
      // NORMAL USER MENU
      nav.innerHTML += `
                <li class="nav-item mx-0 mx-lg-1">
                    <a class="nav-link py-3 px-0 px-lg-3 rounded" href="#about">About</a>
                </li>
                <li class="nav-item mx-0 mx-lg-1">
                    <a class="nav-link py-3 px-0 px-lg-3 rounded" href="#contact">Contact</a>
                </li>
            `;
    }

    // LOGOUT (everyone)
    nav.innerHTML += `
            <li class="nav-item mx-0 mx-lg-1">
                <button class="btn btn-danger ms-3" onclick="UserService.logout()">Logout</button>
            </li>
        `;
  },
};
