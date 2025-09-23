import sidebarBlock from '../components/sidebar-block'

// GA4 - Enable the gtag.js API
window.dataLayer = window.dataLayer || []
window.gtag = () => dataLayer.push(arguments);

document.addEventListener('DOMContentLoaded', () => {
    sidebarBlock();
});
