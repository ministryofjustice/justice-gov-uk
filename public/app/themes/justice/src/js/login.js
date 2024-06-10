/**
 * Modify the login page
 */

const wp_link = document.querySelector('a[href="https://en-gb.wordpress.org/"]');
const site_link = document.querySelector('#backtoblog a');

if (wp_link && site_link) {
    wp_link.href = site_link.href
}
