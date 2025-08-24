(function (window, document, $, undefined) {
  "use strict";

  var EfbtwfbtFrontend = {
    init: function () {
      this.frequentlyBoughtTogether();
      this.handleCheckboxChange();
      this.handleVariationChange();
      this.disablePrimaryCheckbox();
      this.updateVariationPricesOnLoad();
      this.bindVariationImageChange();
      this.toggleAddToCartButton();
      this.handleAddToCartButtonClick();
    },

    updateFBTTotalPrice: function ($form) {
      let products = [];
      let $formContext = $form || $(this).closest(".easyefbtw-fbt-form");

      $formContext.find('input[type="checkbox"]:checked').each(function () {
        let $checkbox = $(this);
        let $variationSelect = $formContext.find(
          '.efbtw-variation-select[data-product-id="' + $checkbox.val() + '"]'
        );

        if ($variationSelect.length && $variationSelect.val()) {
          products.push({
            id: $variationSelect.val(),
            discount_value: $variationSelect.attr("discount_value") || 0,
            discount_type: $variationSelect.attr("discount_type") || "",
          });
        } else {
          products.push({
            id: $checkbox.val(),
            discount_value: $checkbox.attr("discount_value") || 0,
            discount_type: $checkbox.attr("discount_type") || "",
          });
        }
      });

      // Ajax Call
      $.ajax({
        type: "POST",
        url: efbtw_fbt_ajax.ajax_url,
        data: {
          action: "efbtw_update_total_price",
          products: products,
          nonce: efbtw_fbt_ajax.cart_price_nonce,
        },
        success: function (response) {
          if (response.fragments) {
            $.each(response.fragments, function (key, value) {
              $formContext.find(key).replaceWith(value);
            });
          }
        },
      });
    },

    handleCheckboxChange: function () {
      var self = this;
      $(".easyefbtw-fbt-form").on(
        "change",
        'input[type="checkbox"]',
        function () {
          self.updateFBTTotalPrice($(this).closest(".easyefbtw-fbt-form"));
        }
      );
    },

    handleVariationChange: function () {
      var self = this;
      $(".easyefbtw-fbt-form").on(
        "change",
        ".efbtw-variation-select",
        function () {
          self.updateFBTTotalPrice($(this).closest(".easyefbtw-fbt-form"));
        }
      );
    },

    disablePrimaryCheckbox: function () {
      var $primaryCheckboxes = $(
        '.efbtw-product-item.product-primary input[type="checkbox"]'
      );
      $primaryCheckboxes
        .prop("checked", true)
        .prop("disabled", true)
        .on("click", function (e) {
          e.preventDefault();
          return false;
        });
    },

    updateVariationPrice: function (selectElement) {
      let discount_value = selectElement.attr("discount_value") || 0;
      let discount_type = selectElement.attr("discount_type") || "";
      var selectedOption = selectElement.find("option:selected");
      var regularPrice = parseFloat(selectedOption.data("regular-price"));
      var salePrice = parseFloat(selectedOption.data("sale-price"));

      if (!salePrice || salePrice >= regularPrice) {
        regularPrice = this.applyDiscount(
          regularPrice,
          discount_value,
          discount_type
        );
      } else {
        salePrice = this.applyDiscount(
          salePrice,
          discount_value,
          discount_type
        );
      }

      var priceHtml = "";

      if (salePrice && salePrice < regularPrice) {
        priceHtml =
          "<ins>" +
          this.formatPrice(salePrice) +
          "</ins> <del>" +
          this.formatPrice(regularPrice) +
          "</del>";
      } else {
        priceHtml = "<ins>" + this.formatPrice(regularPrice) + "</ins>";
      }

      selectElement
        .closest(".efbtw-product-details")
        .find(".efbtw-product-price")
        .html(priceHtml);
    },

    updateVariationPricesOnLoad: function () {
      var self = this;
      $(".efbtw-variation-select").each(function () {
        self.updateVariationPrice($(this));
      });
      self.updateTotalSavings();
    },

    bindVariationImageChange: function () {
      $(".efbtw-variation-select").on("change", function () {
        let selectedData = $(this).find("option:selected");
        let newImageUrl = selectedData.data("image");
        let productIdWithPrefix = $(this).attr("data-productId");
        let $form = $(this).closest(".easyefbtw-fbt-form");
        let productImageDiv = $form.find(
          '.efbtw-product-images .efbtw-product-image[data-productId="' +
            productIdWithPrefix +
            '"] img'
        );

        if (newImageUrl && productImageDiv.length > 0) {
          productImageDiv.attr("src", newImageUrl);
        }
      });
    },

    applyDiscount: function (price, discount_value, discount_type) {
      switch (discount_type) {
        case "percentage_discount":
          return Math.max(price - price * (discount_value / 100), 0);
        case "fixed_discount":
          return Math.max(price - discount_value, 0);
        case "fixed_price":
          return Math.max(discount_value, 0);
        default:
          return price;
      }
    },

    updateTotalSavings: function () {
      var self = this;
      $(".easyefbtw-fbt-form").each(function () {
        let $form = $(this);
        let totalRegular = 0;
        let totalDiscounted = 0;

        // $form.find(".efbtw-product-item").each(function () {
        //   let $item = $(this);
        //   let $checkbox = $item.find('input[type="checkbox"]');

        //   if (!$checkbox.is(":checked")) return;

        //   let discountValue, discountType;
        //   let regularPrice = 0;
        //   let salePrice = 0;
        //   let finalPrice = 0;

        //   let $select = $item.find(".efbtw-variation-select");

        //   if ($select.length > 0) {
        //     let selectedOption = $select.find("option:selected");
        //     regularPrice =
        //       parseFloat(selectedOption.data("regular-price")) || 0;
        //     salePrice = parseFloat(selectedOption.data("sale-price")) || 0;
        //     discountValue = parseFloat($select.attr("discount_value")) || 0;
        //     discountType = $select.attr("discount_type") || "";
        //   } else {
        //     regularPrice = parseFloat($checkbox.data("regular-price")) || 0;
        //     salePrice = parseFloat($checkbox.data("sale-price")) || 0;
        //     discountValue = parseFloat($checkbox.attr("discount_value")) || 0;
        //     discountType = $checkbox.attr("discount_type") || "";
        //   }

        //   if (!salePrice || salePrice >= regularPrice) {
        //     finalPrice = self.applyDiscount(
        //       regularPrice,
        //       discountValue,
        //       discountType
        //     );
        //   } else {
        //     finalPrice = self.applyDiscount(
        //       salePrice,
        //       discountValue,
        //       discountType
        //     );
        //   }

        //   totalRegular += regularPrice;
        //   totalDiscounted += finalPrice;
        // });

        $form.find(".efbtw-product-item").each(function () {
          let $item = $(this);
          let $checkbox = $item.find('input[type="checkbox"]');
          let $hidden = $item.find('input[type="hidden"]');

          // hidden থাকলে সবসময় select ধরে নিবে
          if ($checkbox.length && !$checkbox.is(":checked")) return;
          if ($hidden.length && !$hidden.val()) return;

          let discountValue, discountType;
          let regularPrice = 0;
          let salePrice = 0;
          let finalPrice = 0;

          let $select = $item.find(".efbtw-variation-select");

          if ($select.length > 0) {
            let selectedOption = $select.find("option:selected");
            regularPrice =
              parseFloat(selectedOption.data("regular-price")) || 0;
            salePrice = parseFloat(selectedOption.data("sale-price")) || 0;
            discountValue = parseFloat($select.attr("discount_value")) || 0;
            discountType = $select.attr("discount_type") || "";
          } else {
            let $input = $checkbox.length ? $checkbox : $hidden;
            regularPrice = parseFloat($input.data("regular-price")) || 0;
            salePrice = parseFloat($input.data("sale-price")) || 0;
            discountValue = parseFloat($input.attr("discount_value")) || 0;
            discountType = $input.attr("discount_type") || "";
          }

          if (!salePrice || salePrice >= regularPrice) {
            finalPrice = self.applyDiscount(
              regularPrice,
              discountValue,
              discountType
            );
          } else {
            finalPrice = self.applyDiscount(
              salePrice,
              discountValue,
              discountType
            );
          }

          totalRegular += regularPrice;
          totalDiscounted += finalPrice;
        });

        let totalSavings = Math.max(totalRegular - totalDiscounted, 0);
        if (totalSavings > 0) {
          let savingsHTML =
            'Save <span class="woocommerce-Price-amount amount">' +
            self.formatPrice(totalSavings) +
            "</span>";
          $form.find(".efbtw-savings").html(savingsHTML);
        } else {
          $form.find(".efbtw-savings").html("");
        }
        $form
          .find(".efbtw-total-price ins")
          .html(self.formatPrice(totalDiscounted));
        $form
          .find(".efbtw-total-price del")
          .html(self.formatPrice(totalRegular));
      });
    },

    formatPrice: function (price) {
      var currency = efbtw_fbt_ajax.currency_symbol;
      var position = efbtw_fbt_ajax.currency_position;
      var formatted_price = parseFloat(price).toFixed(2);

      switch (position) {
        case "left":
          return currency + formatted_price;
        case "right":
          return formatted_price + currency;
        case "left_space":
          return currency + " " + formatted_price;
        case "right_space":
          return formatted_price + " " + currency;
        default:
          return currency + formatted_price;
      }
    },

    toggleAddToCartButton: function () {
      var self = this;
      $(".easyefbtw-fbt-form").each(function () {
        let $form = $(this);

        function checkBundleProducts() {
          var allUnchecked = true;

          $form
            .find(".efbtw-product-item:not(.product-primary)")
            .each(function () {
              var $checkbox = $(this).find('input[type="checkbox"]');
              var $details = $(this).find(".efbtw-product-details");
              var $hidden = $(this).find('input[type="hidden"]');

              if ($checkbox.length) {
                if ($checkbox.is(":checked")) {
                  allUnchecked = false;
                  $details.removeClass("buttonwoofbt-disabled");
                } else {
                  $details.addClass("buttonwoofbt-disabled");
                }
              } else if ($hidden.length) {
                allUnchecked = false;
                $details.removeClass("buttonwoofbt-disabled");
              }
            });

          var $button = $form.find(".easyefbtw-add-to-cart-btn");

          if (allUnchecked) {
            $button.addClass("disabled").css({
              opacity: "0.4",
              "pointer-events": "none",
            });
          } else {
            $button.removeClass("disabled buttonwoofbt-disabled").css({
              opacity: "",
              "pointer-events": "",
            });
          }
        }

        checkBundleProducts();

        $form.on(
          "change",
          '.efbtw-product-item:not(.product-primary) input[type="checkbox"]',
          function () {
            checkBundleProducts();
            self.updateTotalSavings();
          }
        );
      });
    },

    handleAddToCartButtonClick: function () {
      $(document).on("click", ".easyefbtw-add-to-cart-btn", function (e) {
        var $form = $(this).closest(".easyefbtw-fbt-form");

        let customizationClass = $(
          ".efbtw-product-bundle-wrapper .efbtw-allow-customization-off"
        );

        if (customizationClass.length > 0) {
          return true;
        }

        var allUnchecked = true;

        $form
          .find(
            '.efbtw-product-item:not(.product-primary) input[type="checkbox"]'
          )
          .each(function () {
            if ($(this).is(":checked")) {
              allUnchecked = false;
              return false;
            }
          });

        if (allUnchecked) {
          e.preventDefault();
          alert("Please select at least one product before adding to cart.");
        }
      });
    },

    frequentlyBoughtTogether: function () {
      var self = this;
      $("form.easyefbtw-fbt-form").each(function () {
        var $form = $(this);

        $form.on("click", ".easyefbtw-add-to-cart-btn", function (e) {
          e.preventDefault();

          const $button = $(this);

          if ($button.hasClass("buttonwoofbt-disabled")) return;

          const $currentForm = $button.closest("form.easyefbtw-fbt-form");

          const productsID = self.getProductsId($currentForm);
          const mainProduct = $currentForm
            .find('input[name="easyefbtw-fbt-main-product"]')
            .val();
          const bundleId = $currentForm
            .find('input[name="efbtw-fbt-bundle-id"]')
            .val();

          if (!productsID || typeof productsID[mainProduct] === "undefined")
            return;

          $.ajax({
            url: efbtw_fbt_ajax.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
              action: "efbtw_purchasable_fbt_products",
              products_id: productsID,
              main_product: mainProduct,
              bundle_id: bundleId,
              nonce: efbtw_fbt_ajax.cart_price_nonce,
            },
            beforeSend: function () {
              $button.addClass("loading");
            },
            success(response) {
              const $noticeWrapper = $(".woocommerce-notices-wrapper").empty();

              var priceSummaryHTML = $currentForm
                .find(".efbtw-price-info")
                .html();
              $button.addClass("added");
              // Show errors
              console.log(response.notices);
              $noticeWrapper.append(response.notices);
              if (response.notices && response.notices.indexOf("error") > -1) {
                $noticeWrapper.append(response.notices);
                $("html, body").animate(
                  {
                    scrollTop:
                      $noticeWrapper.offset().top -
                      efbtw_fbt_ajax.ajax_scroll_offset,
                  },
                  400
                );
                return;
              }

              // block mini cart fragment updated
              if (typeof wp !== "undefined" && wp.data && wp.data.dispatch) {
                wp.data
                  .dispatch("wc/store/cart")
                  .invalidateResolutionForStore();

                const miniCart = document.querySelector(".wc-block-mini-cart");
                if (miniCart) {
                  miniCart.setAttribute(
                    "data-cart-items-count",
                    response.cart_data.cart_count
                  );
                  const badge = miniCart.querySelector(
                    ".wc-block-mini-cart__badge"
                  );
                  if (badge) {
                    badge.textContent = response.cart_data.cart_count;
                  }
                }
              }

              var this_page = window.location.toString();
              this_page.replace("add-to-cart", "added-to-cart");

              if (response.success) {
                var fragments = response.fragments;
                var cart_hash = response.cart_hash;

                if (fragments) {
                  $.each(fragments, function (key) {
                    $(key).addClass("updating");
                  });
                }

                if (fragments) {
                  $.each(fragments, function (key, value) {
                    $(key).replaceWith(value);
                  });
                }

                $(document.body).trigger("added_to_cart", [
                  fragments,
                  cart_hash,
                ]);
                $(document.body).trigger("added_to_cart", [
                  fragments,
                  cart_hash,
                  $button,
                ]);

                if (response.success) {
                  $currentForm.find(".efbtw-price-info").html(priceSummaryHTML);
                }

                $(document.body).trigger("wc_fragment_refresh");
                $(document.body).trigger("wc_fragments_refreshed");

                $button.addClass("added");
              }
            },
            error(xhr, status, error) {
              console.error("AJAX Error:", error);
            },
            complete() {
              $button.removeClass("loading buttonwoofbt-disabled");
              $form.removeClass("efbtw-checkbox-uncheck");
              $button.removeClass("loading");
            },
          });
        });
      });
    },

    getProductsId: function ($form) {
      var productsID = {};

      $form.find(".efbtw-product-item").each(function () {
        var $this = $(this);
        var $checkbox = $this.find('input[type="checkbox"]');
        var $hidden = $this.find('input[type="hidden"]');
        var $input = $checkbox.length ? $checkbox : $hidden;
        var productId = $this.data("productid");

        // checkbox হলে checked কিনা দেখবে, hidden হলে সবসময় include করবে
        if (($checkbox.length && $checkbox.is(":checked")) || $hidden.length) {
          if ($this.find(".efbtw-fbt-product-variation").length) {
            productsID[productId] = $this
              .find(".efbtw-fbt-product-variation select")
              .val();
          } else {
            productsID[productId] = "";
          }
        }
      });

      return productsID;
    },
  };

  $(document).ready(function () {
    EfbtwfbtFrontend.init();
  });
})(window, document, jQuery);
