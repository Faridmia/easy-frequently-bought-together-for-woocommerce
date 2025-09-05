import apiFetch from "@wordpress/api-fetch";
import {
  BoxControl,
  Button,
  Card,
  CardBody,
  CardHeader,
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
  const [totalLabel, setTotalLabel] = useState("Price for %d items:");
  const [buttonLabel, setButtonLabel] = useState("Add all to Cart");

  const [enableDiscountPercentage, setEnableDiscountPercentage] =
    useState(true);

  const [formBgColor, setFormBgColor] = useState("#ffffff");
  const [btnBg, setBtnBg] = useState("#299e8e");
  const [btnBgHover, setBtnBgHover] = useState("#28c81f");
  const [btnText, setBtnText] = useState("#ffffff");
  const [btnTextHover, setBtnTextHover] = useState("#ffffff");

  const [boxSpacing, setBoxSpacing] = useState({
    top: "10px",
    right: "10px",
    bottom: "10px",
    left: "10px",
  });

  const { createNotice } = useDispatch(noticesStore);

  useEffect(() => {
    apiFetch({ path: "/efbtw/v1/settings" }).then((settings) => {
      if (settings) {
        setBoxTitle(settings.boxTitle || "Frequently Bought Together");
        setTotalLabel(settings.totalLabel || "Price for all");
        setButtonLabel(settings.buttonLabel || "Add all to Cart");
        setEnableDiscountPercentage(settings.enableDiscountPercentage ?? true);

        setFormBgColor(settings.formBgColor || "#ffffff");
        setBtnBg(settings.btnBg || "#299e8e");
        setBtnBgHover(settings.btnBgHover || "#28c81f");
        setBtnText(settings.btnText || "#ffffff");
        setBtnTextHover(settings.btnTextHover || "#ffffff");
        setBoxSpacing(settings.box_spacing);
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
        enableDiscountPercentage,
        formBgColor,
        btnBg,
        btnBgHover,
        btnText,
        btnTextHover,
        box_spacing: boxSpacing,
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
              className={activeTab === "style_settings" ? "active" : ""}
              onClick={() => handleTabClick("style_settings")}
            >
              <svg
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                class="edit-site-global-styles-icon-with-current-color"
                aria-hidden="true"
                focusable="false"
              >
                <path d="M17.2 10.9c-.5-1-1.2-2.1-2.1-3.2-.6-.9-1.3-1.7-2.1-2.6L12 4l-1 1.1c-.6.9-1.3 1.7-2 2.6-.8 1.2-1.5 2.3-2 3.2-.6 1.2-1 2.2-1 3 0 3.4 2.7 6.1 6.1 6.1s6.1-2.7 6.1-6.1c0-.8-.3-1.8-1-3zm-5.1 7.6c-2.5 0-4.6-2.1-4.6-4.6 0-.3.1-1 .8-2.3.5-.9 1.1-1.9 2-3.1.7-.9 1.3-1.7 1.8-2.3.7.8 1.3 1.6 1.8 2.3.8 1.1 1.5 2.2 2 3.1.7 1.3.8 2 .8 2.3 0 2.5-2.1 4.6-4.6 4.6z"></path>
              </svg>{" "}
              Color Settings
            </li>
          </ul>
        </div>

        <div className="efbtw-admin-right-settings-content" style={{ flex: 1 }}>
          {activeTab == 'general' && (
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
                <BoxControl
                  label={__(
                    "Box Spacing",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                  values={boxSpacing}
                  onChange={(nextValues) => setBoxSpacing(nextValues)}
                  help={__(
                    "Adjust the spacing (margin or padding) around the 'Frequently Bought Together' box.",
                    "easy-frequently-bought-together-for-woocommerce"
                  )} className="efbtw-admin-settings-control"
                />
              </CardBody>
            </Card>
          )}

          {activeTab === "style_settings" && (
            <Card>
              <CardHeader>
                <h2>
                  {__(
                    "Style Settings",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                </h2>
              </CardHeader>
              <CardBody>
                {/* Form Background Color */}
                <div className="efbtw-color-field efbtw-color-main-box">
                  <label className="efbtw-color-label">
                    {__(
                      "Wrapper Background Color",
                      "easy-frequently-bought-together-for-woocommerce"
                    )}
                  </label>
                  <div className="efbtw-color-input-wrapper">
                    <input
                      type="color"
                      value={formBgColor}
                      onChange={(e) => setFormBgColor(e.target.value)}
                      className="efbtw-color-input"
                    />
                    <input
                      type="text"
                      value={formBgColor}
                      onChange={(e) => setFormBgColor(e.target.value)}
                      className="efbtw-color-text-input"
                      placeholder="#ffffff"
                    />
                  </div>
                  <p className="description">
                    {__(
                      "Select background color for Frequently Bought form",
                      "easy-frequently-bought-together-for-woocommerce"
                    )}
                  </p>
                </div>

                <h3 className="mt-4">
                  {__(
                    "Button Colors",
                    "easy-frequently-bought-together-for-woocommerce"
                  )}
                </h3>
                <div className="efbtw-color-grid">
                  {[
                    { label: "Background", value: btnBg, setter: setBtnBg },
                    {
                      label: "Background Hover",
                      value: btnBgHover,
                      setter: setBtnBgHover,
                    },
                    { label: "Text", value: btnText, setter: setBtnText },
                    {
                      label: "Text Hover",
                      value: btnTextHover,
                      setter: setBtnTextHover,
                    },
                  ].map((item, index) => (
                    <div className="efbtw-color-field" key={index}>
                      <label>
                        {__(
                          item.label,
                          "easy-frequently-bought-together-for-woocommerce"
                        )}
                      </label>
                      <div className="efbtw-color-input-wrapper">
                        <input
                          type="color"
                          value={item.value}
                          onChange={(e) => item.setter(e.target.value)}
                          className="efbtw-color-input"
                        />
                        <input
                          type="text"
                          value={item.value}
                          onChange={(e) => item.setter(e.target.value)}
                          className="efbtw-color-text-input"
                          placeholder="#000000"
                        />
                      </div>
                    </div>
                  ))}
                </div>
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
