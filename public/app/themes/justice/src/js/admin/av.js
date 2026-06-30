class MediaProgressWatcher {
  /**
   * Prime internal state for tracking upload progress elements.
   * @returns {void}
   */
  constructor() {
    this.selector = "#media-items .progress";
    this.trackedProgress = new WeakSet();
    this.observer = null;
    this.started = false;
  }

  /**
   * Begin observing the DOM for matching progress nodes.
   * @returns {void}
   */
  start() {
    // Only start if AV is enabled. Checks both the iframe parent and the current
    // document in case this script is running inside an iframe.
    if (
      !window.parent?.document.body.classList.contains("av-enabled") &&
      !document.body.classList.contains("av-enabled")
    ) {
      return;
    }

    if (this.started) {
      return;
    }

    this.started = true;

    this.observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => this.visitNode(node));
      });
    });

    this.observer.observe(document.body, { childList: true, subtree: true });

    // Bind to any progress bars that already exist at start-up.
    document
      .querySelectorAll(this.selector)
      .forEach((element) => this.watchProgress(element));
  }

  /**
   * Tear down timers and observers when no longer needed.
   * @returns {void}
   */
  stop() {
    if (!this.started) {
      return;
    }

    this.started = false;
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }
  }

  /**
   * Inspect added nodes for progress elements to track.
   * @param {Node} node
   * @returns {void}
   */
  visitNode(node) {
    if (node.nodeType !== Node.ELEMENT_NODE) {
      return;
    }

    const element = node;
    if (element.matches?.(this.selector)) {
      this.watchProgress(element);
    }

    element
      .querySelectorAll?.(this.selector)
      .forEach((match) => this.watchProgress(match));
  }

  /**
   * Parse a percentage value from the element text.
   * @param {Element} element
   * @returns {number|null}
   */
  extractPercent(element) {
    const text = element.textContent ?? "";
    const textMatch = text.match(/(\d+)(?=\s*%)/);

    return textMatch ? parseInt(textMatch[1], 10) : null;
  }

  /**
   * Attach mutation listeners so we can respond to updates.
   * @param {Element} element
   * @returns {void}
   */
  watchProgress(element) {
    if (this.trackedProgress.has(element)) {
      return;
    }

    this.trackedProgress.add(element);

    const report = () => {
      const percent = this.extractPercent(element);
      if (percent !== null) {
        this.handleProgress(percent, element);
      }
    };

    report();

    const progressObserver = new MutationObserver(report);
    progressObserver.observe(element, {
      childList: true,
      characterData: true,
      subtree: true,
    });
  }

  /**
   * Switch to the processing state once 100% is reached.
   * @param {number} percent
   * @param {Element} element
   * @returns {void}
   */
  handleProgress(percent, element) {
    const percentElement = element.querySelector(".percent");

    if (percentElement && percent === 100) {
      // If we are at 100%, switch to processing state
      percentElement.textContent = "Scanning for malware…";
    }
  }
}

export default MediaProgressWatcher;
