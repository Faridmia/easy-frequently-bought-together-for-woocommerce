(function (window, document, $, undefined) {
  "use strict";

  $(document).ready(function ($) {
    function loadProductsDropdown(selectElement) {
      $.ajax({
        url: efbtw_localize_obj.ajax_url,
        type: "POST",
        data: {
          action: "efbtw_get_products",
        },
        success: function (response) {
          $(selectElement).html(response);
          $(selectElement).select2({
            placeholder: "Select Product",
            width: "100%",
          });
        },
      });
    }

    $("#fbt-bought-together-add-row").on("click", function (e) {
      e.preventDefault();

      var newRow = $(".empty-row")
        .clone()
        .removeClass("empty-row screen-reader-text")
        .insertBefore(".empty-row");

      var newSelect = newRow.find(".fbt-select-product2");
      loadProductsDropdown(newSelect);

      newRow.find("select, input").each(function () {
        var oldName = $(this).attr("name");
        if (oldName) {
          $(this).attr("name", oldName.replace("[]", "") + "[]");
        }
      });

      newRow.find('input[type="number"]').val("");
    });

    // Remove row button
    $(document).on("click", ".fbt-bought-together-remove-row", function (e) {
      e.preventDefault();
      $(this).closest("tr").remove();
    });
  });

  function loadbundleProductsDropdown(selectElement) {
    $.ajax({
      url: efbtw_localize_obj.ajax_url,
      type: "POST",
      data: {
        action: "tab_bundle_get_products_callback",
      },
      success: function (response) {
        console.log(response);
        $(selectElement).html(response);
        $(selectElement).select2({
          placeholder: "Select bundle Product",
          width: "100%",
        });
      },
    });
  }

  var bundleProductSelect = $(".efbtw-bought-together-controls").find(
    ".woobufbt-select-bundle-product"
  );
  loadbundleProductsDropdown(bundleProductSelect);
})(window, document, jQuery);
