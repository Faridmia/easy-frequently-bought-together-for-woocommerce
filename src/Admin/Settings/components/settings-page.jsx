import apiFetch from "@wordpress/api-fetch";
import {
  Button,
  Card,
  CardBody,
  CardHeader,
  SelectControl,
  TextControl,
  ToggleControl,
} from "@wordpress/components";
import { useDispatch } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";
import { Notices } from "./notices";

apiFetch.use((options, next) => {
  options.headers = {
    ...options.headers,
    "X-WP-Nonce": efbtwSettings_object.nonce,
  };
  return next(options);
});

const SettingsPage = () => {
  const queryParams = new URLSearchParams(window.location.search);
  const defaultTab = queryParams.get("settingstab") || "general";

  const [activeTab, setActiveTab] = useState(defaultTab);

  const [boxTitle, setBoxTitle] = useState("Frequently Bought Together");
  const [totalLabel, setTotalLabel] = useState("Price for all");
  const [buttonLabel, setButtonLabel] = useState("Add all to Cart");

  const [currencyType, setCurrencyType] = useState("auto");
  const [enableProductPrice, setEnableProductPrice] = useState(true);
  const [enableCartPrice, setEnableCartPrice] = useState(true);
  const [enableDiscountPercentage, setEnableDiscountPercentage] =
    useState(true);

  const { createNotice } = useDispatch(noticesStore);

  useEffect(() => {
    apiFetch({ path: "/efbtw/v1/settings" }).then((settings) => {
      if (settings) {
        setBoxTitle(settings.boxTitle || "Frequently Bought Together");
        setTotalLabel(settings.totalLabel || "Price for all");
        setButtonLabel(settings.buttonLabel || "Add all to Cart");
        setCurrencyType(settings.currencyType || "auto");
        setEnableProductPrice(settings.enableProductPrice ?? true);
        setEnableCartPrice(settings.enableCartPrice ?? true);
        setEnableDiscountPercentage(settings.enableDiscountPercentage ?? true);
      }
    });
  }, []);

  const handleTabClick = (tabName) => {
    setActiveTab(tabName);
    const url = new URL(window.location.href);
    url.searchParams.set("settingstab", tabName);
    window.history.replaceState(null, "", url.toString());
  };

  const saveSettings = () => {
    apiFetch({
      path: "/efbtw/v1/settings",
      method: "POST",
      data: {
        boxTitle,
        totalLabel,
        buttonLabel,
        currencyType,
        enableProductPrice,
        enableCartPrice,
        enableDiscountPercentage,
      },
    })
      .then(() => {
        createNotice("success", "✅ Settings saved successfully!", {
          isDismissible: true,
          duration: 3000,
        });
      })
      .catch((err) => {
        createNotice("error", "❌ Failed to save settings!", {
          isDismissible: true,
          duration: 3000,
        });
        console.error("Save error:", err);
      });
  };

  return (
    <>
      <div className="easy-efbtw-notice-wrapper">
        <Notices />
      </div>
      <div className="efbtw-settings-admin-layout">
        <div className="efbtw-settings-admin-sidebar">
          <ul className="efbtw-settings-sidebar-nav">
            <li
              className={activeTab === "general" ? "active" : ""}
              onClick={() => handleTabClick("general")}
            >
              ⚙ General
            </li>
            <li
              className={activeTab === "approximate_price" ? "active" : ""}
              onClick={() => handleTabClick("approximate_price")}
            >
              💲 Approximate Price
            </li>
          </ul>
        </div>

        <div className="efbtw-admin-right-settings-content" style={{ flex: 1 }}>
          {activeTab === "general" && (
            <Card>
              <CardHeader>
                <h2>
                  {__(
                    "General Settings",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                </h2>
              </CardHeader>
              <CardBody>
                <TextControl
                  label={__(
                    "Box Title",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  value={boxTitle}
                  onChange={setBoxTitle}
                  help={__(
                    "The title displayed on the 'Frequently Bought Together' box.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  className="efbtw-admin-settings-control"
                />
                <TextControl
                  label={__(
                    "Total Label",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  value={totalLabel}
                  onChange={setTotalLabel}
                  help={__(
                    "This label will show the total price for all selected products.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  className="efbtw-admin-settings-control"
                />
                <TextControl
                  label={__(
                    "Button Label",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  value={buttonLabel}
                  onChange={setButtonLabel}
                  help={__(
                    "This label appears on the 'Add to Cart' button in the box.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  className="efbtw-admin-settings-control"
                />
                <ToggleControl
                  label={__(
                    "Enable Discount Percentage",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  checked={enableDiscountPercentage}
                  onChange={setEnableDiscountPercentage}
                  help={__(
                    "Toggle the visibility of discount percentages in the 'Frequently Bought Together' box.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  className="efbtw-admin-settings-control"
                />
              </CardBody>
            </Card>
          )}

          {activeTab === "approximate_price" && (
            <Card>
              <CardHeader>
                <h2>
                  {__(
                    "Approximate Price Settings",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                </h2>
                <p>
                  {__(
                    "Configure approximate pricing based on the user's preferred currency.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                </p>
              </CardHeader>
              <CardBody>
                <SelectControl
                  label={__(
                    "Approximate Currency Type",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  value={currencyType}
                  options={[
                    { label: "Auto (Based on user location)", value: "auto" },
                    { label: "Base Currency", value: "base" },
                  ]}
                  onChange={setCurrencyType}
                  help={__(
                    "Select the currency type for approximate pricing: 'Auto' based on user's location or 'Base Currency'.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                />
                <ToggleControl
                  label={__(
                    "Enable Approximate Product Prices",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  checked={enableProductPrice}
                  onChange={setEnableProductPrice}
                  help={__(
                    "Toggle to enable or disable approximate prices for individual products.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                />
                <ToggleControl
                  label={__(
                    "Enable Approximate Cart and Checkout Prices",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  checked={enableCartPrice}
                  onChange={setEnableCartPrice}
                  help={__(
                    "Enable approximate prices to be shown for the cart and checkout pages.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                />
              </CardBody>
            </Card>
          )}

          <div className="efbtw-settings-save-button">
            <Button isPrimary onClick={saveSettings}>
              {__(
                "Save Changes",
                "easy-frequently-bought-together-for-woocommerce"
              )}
            </Button>
          </div>
        </div>
      </div>
    </>
  );
};

export { SettingsPage };
