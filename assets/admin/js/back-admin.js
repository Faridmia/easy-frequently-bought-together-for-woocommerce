jQuery(document).ready(function ($) {
  $(".fbt-bought-together-remove-row").on("click", function () {
    $(this).parents("tr").remove();
    return false;
  });

  $(document).ready(function ($) {
    $(".fbt-select-product").removeAttr("multiple").select2({
      placeholder: "Select Product",
      width: "100%",
    });
  });

  // bundle  product select2 js
  $(document).ready(function ($) {
    $(".woobufbt-select-bundle-product").select2({
      placeholder: "Select bundle product",
      width: "100%",
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  function woofbtHandleCustomizationState() {
    const customizationToggle = document.querySelector(
      'input[name="efbtw_allow_customization"]'
    );
    document.querySelectorAll(".efbtw-settings-show-hidden").forEach((el) => {
      el.classList.toggle("woofbt-hidden", !customizationToggle.checked);
    });
  }

  document.querySelectorAll(".efbtw-toggle-button input").forEach((toggle) => {
    toggle.addEventListener("change", function () {
      const toggleThumb = this.parentNode.querySelector(".efbtw-toggle-thumb");
      toggleThumb.textContent = this.checked ? "YES" : "NO";

      if (this.name === "efbtw_allow_customization") {
        document
          .querySelectorAll(".efbtw-settings-show-hidden")
          .forEach((el) => {
            el.classList.toggle("woofbt-hidden", !this.checked);
          });

        if (this.checked) {
          woofbtBindDefaultCheckboxButtonEvents();
        }
      }
    });
  });

  function woofbtBindDefaultCheckboxButtonEvents() {
    const buttonGroup = document.querySelector(".efbtw-checked-unchecked");

    if (buttonGroup) {
      const checkButton = buttonGroup.querySelector(".efbtw-check-button");
      const unCheckButton = buttonGroup.querySelector(".efbtw-uncheck-button");
      const hiddenInput = buttonGroup.querySelector(
        'input[name="efbtw_fbtcheckboxstate"]'
      );

      checkButton.addEventListener("click", function (e) {
        e.preventDefault();
        hiddenInput.value = "check";
        checkButton.classList.add("efbtw-btn-primary");
        checkButton.classList.remove("efbtw-btn-default");
        unCheckButton.classList.add("efbtw-btn-default");
        unCheckButton.classList.remove("efbtw-btn-primary");
      });

      unCheckButton.addEventListener("click", function (e) {
        e.preventDefault();
        hiddenInput.value = "uncheck";
        unCheckButton.classList.add("efbtw-btn-primary");
        unCheckButton.classList.remove("efbtw-btn-default");
        checkButton.classList.add("efbtw-btn-default");
        checkButton.classList.remove("efbtw-btn-primary");
      });
    }
  }

  woofbtBindDefaultCheckboxButtonEvents();
  woofbtHandleCustomizationState();
});
