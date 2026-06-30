import MediaProgressWatcher from "./av.js";
import "./request-errors";
import "../../components/previous-permalinks/admin.js";

/**
 * Initialise and start a MediaProgressWatcher instance.
 * This is used to display "Scanning for malware…" once media upload reaches 100%.
 */
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () =>
    new MediaProgressWatcher().start(),
  );
} else {
  new MediaProgressWatcher().start();
}
