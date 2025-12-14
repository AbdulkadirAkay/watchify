let Utils = {
  datatable: function (table_id, columns, data, pageLength = 15) {
    if ($.fn.dataTable.isDataTable("#" + table_id)) {
      $("#" + table_id)
        .DataTable()
        .destroy();
    }

    $("#" + table_id).DataTable({
      data: data,
      columns: columns,
      pageLength: pageLength,
      lengthMenu: [2, 5, 10, 15, 25, 50, 100, "All"],
    });
  },

  parseJwt: function (token) {
    if (!token) return null;
    try {
      const payload = token.split(".")[1];
      const decoded = atob(payload);
      return JSON.parse(decoded);
    } catch (e) {
      console.error("Invalid JWT token", e);
      return null;
    }
  },

  showToast: function (type, msg) {
    if (window.toastr) {
      if (type === "error") toastr.error(msg);
      else toastr.success(msg);
      return;
    }

  
    var $container = $("#appToastContainer");
    if (!$container.length) {
      $container = $(
        '<div id="appToastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>'
      );
      $("body").append($container);
    }

    var alertClass = type === "error" ? "alert-danger" : "alert-success";
    var $alert = $(
      '<div class="alert ' +
        alertClass +
        ' shadow mb-2" role="alert">' +
        msg +
        "</div>"
    );

    $container.append($alert);
    setTimeout(function () {
      $alert.fadeOut(400, function () {
        $(this).remove();
      });
    }, 3000);
  },

  showError: function (msg) {
    Utils.showToast("error", msg);
  },

  showSuccess: function (msg) {
    Utils.showToast("success", msg);
  },
};
