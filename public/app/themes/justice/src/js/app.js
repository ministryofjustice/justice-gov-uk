import './legacy/scripts'

// GA4 - Enable the gtag.js API
window.dataLayer = window.dataLayer || []
window.gtag = () => dataLayer.push(arguments);
