const { __ } = wp.i18n;

export const panelFields = [
  {
    name: "_panel_archived",
    label: __("Show archived panel", "justice"),
    type: "boolean",
  },
  {
    name: "_panel_brand",
    label: __("Show brand panel", "justice"),
    type: "boolean",
  },
  {
    name: "_panel_direct_gov",
    label: __("Show directgov panel", "justice"),
    type: "boolean",
  },
  {
    name: "_panel_email_alerts",
    label: __("Show email alerts panel", "justice"),
    type: "boolean",
  },
  {
    name: "_panel_search",
    label: __("Show search panel", "justice"),
    type: "boolean",
  },
];

export const metaFields = [
  {
    name: "_short_title",
    label: __("Short title.", "justice"),
    type: "text",
  },
];
