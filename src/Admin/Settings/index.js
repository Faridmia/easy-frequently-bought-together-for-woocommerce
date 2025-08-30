import { createRoot } from "@wordpress/element";
import { SettingsPage } from "./components";
import "./index.scss";

const container = document.getElementById("easy-bought-together-settings-app");
if (container) {
  const root = createRoot(container);
  root.render(<SettingsPage />);
}
