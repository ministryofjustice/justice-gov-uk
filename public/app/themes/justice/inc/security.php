<?php

namespace MOJ\Justice;

use WP_Error;
use Roots\WPConfig\Config;
use MOJ\ClusterHelper;

/**
 * Add a little security for WordPress
 */
class Security
{

    private $wp_version;
    private $hashed_wp_version;

    /**
     * A list of known hosts.
     */
    private array $known_hosts = [
        'api.deliciousbrains.com'
    ];

    /**
     * The application host e.g. intranet.docker or intranet.justice.gov.uk
     */
    private string $home_host;

    /**
     * Set properties and run actions.
     */
    public function __construct()
    {
        // Get the WordPress version.
        $this->wp_version = get_bloginfo('version');
        // Hash the WP version number with a salt - let's borrow AUTH_SALT for this.
        // This way a we get a unique hash per WP version but it's not reversible.
        $this->hashed_wp_version = substr(hash('sha256', $this->wp_version . AUTH_SALT), 0, 6);

        $this->home_host = parse_url(get_home_url(), PHP_URL_HOST);

        $this->actions();

        // Push the application host to known_hosts.
        array_push($this->known_hosts, $this->home_host);

        // Push the S3 bucket host to known_hosts.
        if ($s3_bucket = env('AWS_S3_BUCKET')) {
            array_push($this->known_hosts, $s3_bucket . ".s3.eu-west-2.amazonaws.com");
        }

        if ($custom_s3_host = env('AWS_S3_CUSTOM_HOST')) {
            array_push($this->known_hosts, $custom_s3_host);
        }

        if ($loopback_url = Config::get('WP_LOOPBACK')) {
            // Push the loopback URL host to known_hosts.
            array_push($this->known_hosts, parse_url($loopback_url, PHP_URL_HOST));
        }

        // Push the Nginx hosts to known_hosts.
        $nginx_urls = ClusterHelper::getNginxHosts('hosts');
        $nginx_hosts = array_map(fn($host) => parse_url($host, PHP_URL_HOST), $nginx_urls);
        array_push($this->known_hosts, ...$nginx_hosts);
    }

    /**
     * Loads up actions that are called when WordPress initialises
     *
     * @return void
     */
    public function actions(): void
    {
        // No generator meta tag in the head
        remove_action('wp_head', 'wp_generator');

        add_filter('redirect_canonical', [$this, 'noRedirect404']);
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', [$this, 'headerMods']);
        add_filter('auth_cookie_expiration', [$this, 'setLoginPeriod'], 10, 0);

        // Prevent username enumeration via the login error message.
        add_filter('login_errors', [__class__, 'secureLoginErrors']);

        // Prevent username enumeration via the lost password error message.
        add_filter('lostpassword_errors', [__class__, 'secureLostpasswordErrors'], 20, 0);

        // Filter the password reset confirm text.
        add_filter('gettext', [$this, 'filterPasswordResetConfirmText'], 10, 3);

        // Remove emoji support.
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Strip the WP version number from enqueued asset URLs.
        add_filter('style_loader_tag', [$this, 'filterAssetQueryString'], 10, 1);
        // change the url with script_loader_tag
        add_filter('script_loader_tag', [$this, 'filterAssetQueryString'], 10, 1);

        // Hide the WP version number from the feeds.
        add_filter('the_generator', '__return_empty_string');

        // Disable REST API for non-logged in users.
        add_filter('rest_authentication_errors', [$this, 'restAuth']);

        // Return 404 for all author pages.
        add_action('template_redirect', [$this, 'disableAuthorPages'], 1);
        // Remove the "View" link from user admin screen, since these will 404.
        add_filter('user_row_actions', [$this, 'removeViewLinkOnUsersScreen'], 100);

        // Log requests to unknown hosts.
        add_filter('pre_http_request', [$this, 'logUnknownHostRequests'], 20, 3);

        // Remove the inline script that WordPress adds to the head for post previews.
        // This is because we use a CSP to block inline scripts.
        // The functionality has been replicated in the app.js file.
        remove_action('wp_head', 'wp_post_preview_js', 1);

        // Since we removed the inline script for post previews,
        // we need to add the post ID to the HTML tag for the app.js to use.
        add_filter('moj_safe_localization_data', function ($data) {
            global $post;

            if (! is_preview() || empty($post)) {
                return $data;
            }

            $data['preview-post-id'] = get_the_ID() ?: 0;
            return $data;
        });

        // PublishPress Revisions (formerly called Revisionary) uses inline scripts
        // to fix the revision preview in the admin bar. Remove the inline script
        // because it violates the CSP.
        add_filter('revisionary_admin_bar_absolute', '__return_false');
    }

    /**
     * Prevent WordPress from trying to guess and redirect a 404 page
     *
     * https://developer.wordpress.org/reference/functions/redirect_canonical/
     *
     * @param $redirect_url
     * @return false|mixed
     */
    public function noRedirect404($redirect_url): mixed
    {
        if (is_404()) {
            return false;
        }

        return $redirect_url;
    }

    /**
     * @param $headers
     * @return mixed
     */
    public function headerMods($headers): mixed
    {
        unset($headers['X-Pingback']);

        return $headers;
    }

    /**
     * Sets the expiration time of the login session cookie
     *
     * Nb. if we can harden access to the login page this value
     * can be extended to a much longer period
     *
     * @return float|int
     */
    public function setLoginPeriod(): float|int
    {
        return 7 * DAY_IN_SECONDS; // Cookies set to expire in 7 days.
    }

    /**
     * Prevent username enumeration via the login error message.
     *
     * @see https://developer.wordpress.org/reference/hooks/login_errors/
     *
     * @param string $error
     * @return string
     */
    public static function secureLoginErrors(string $errors): string
    {
        // Add a random delay between 20ms to 200ms to hinder timing attacks.
        usleep(random_int(20000, 200000));

        // Send error to Sentry, so that we can assist in debugging genuine login issues.
        $sanitized_errors = wp_strip_all_tags($errors);
        $severity = class_exists('Sentry\Severity') ? \Sentry\Severity::info() : null;
        do_action('sentry/captureMessage', 'Login error: ' . $sanitized_errors, $severity);

        // Generic error message regardless of the actual error.
        return 'The login information you entered is incorrect. Please check your username and password.';
    }

    /**
     * Prevent username enumeration via the lost password error message.
     *
     * @see https://developer.wordpress.org/reference/hooks/lostpassword_errors/
     *
     * @return void
     */
    public static function secureLostpasswordErrors(): void
    {
        // Add a random delay between 20ms to 200ms to hinder timing attacks.
        usleep(random_int(20000, 200000));

        // Always do the same redirect, regardless of the actual error.
        wp_safe_redirect('wp-login.php?checkemail=confirm');
        exit;
    }

    /**
     * Filter the password reset confirm text.
     *
     * Since all password resets will see the same message, then update it to avoid confusion.
     *
     * @see https://developer.wordpress.org/reference/hooks/gettext/
     *
     * @param string $translated_text
     * @param string $text
     * @param string $domain
     * @return string
     */
    public function filterPasswordResetConfirmText(string $translated_text, string $text, string $domain): string
    {
        if ($text === 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.' && $domain === 'default') {
            $translated_text = 'If you entered a valid email address or username, you will receive an email with a link to reset your password.';
        }
        return $translated_text;
    }

    /**
     * Change the URL of the script or style tags.
     *
     * @see https://developer.wordpress.org/reference/hooks/style_loader_tag/
     *
     * @param $tag string The HTML string of a link or script tag.
     * @return string The modified HTML string.
     */
    public function filterAssetQueryString(string $tag): string
    {
        return str_replace('ver=' . $this->wp_version, 'ver=' . $this->hashed_wp_version, $tag);
    }

    /**
     * Disable REST API for non-logged in users.
     *
     * @see https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
     *
     * @param WP_Error|null|true $result
     * @return WP_Error|null|true
     */
    public function restAuth(WP_Error|null|true $result): WP_Error|null|true
    {
        // If a previous authentication check was applied,
        // pass that result along without modification.
        if (true === $result || is_wp_error($result)) {
            return $result;
        }

        // No authentication has been performed yet.
        // Return an error if user is not logged in.
        if (! is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('You are not currently logged in.'),
                // Return 403, since 401 can result in a redirect loop to Entra.
                array('status' => 403)
            );
        }

        // Our custom authentication check should have no effect
        // on logged-in requests
        return $result;
    }

    /**
     * Disable author pages.
     *
     * Return status code 404 for existing and non-existing author archives.
     *
     * @see https://developer.wordpress.org/reference/hooks/template_redirect/
     * @return void
     */
    public function disableAuthorPages(): void
    {
        if (isset($_GET['author']) || is_author()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }

    /**
     * Remove the "View" link from user admin screen.
     *
     * @param string[] $actions An array of action links to be displayed.
     * @return string[] $actions The modified array of action links.
     */
    public function removeViewLinkOnUsersScreen(array $actions): array
    {
        if (isset($actions['view'])) {
            unset($actions['view']);
        }
        return $actions;
    }


    /**
     * Log the urls of requests to unknown hosts.
     *
     * This could be useful in identifying requests to malicious URLs.
     *
     * @param false|array|\WP_Error $response
     * @param array $parsed_args
     * @param string $url
     * @return false|array|\WP_Error
     */
    public function logUnknownHostRequests(false|array|\WP_Error $response, array $parsed_args, string $url): false|array|\WP_Error
    {
        if (!in_array(parse_url($url, PHP_URL_HOST), $this->known_hosts)) {
            // Log the request url.
            error_log('pre_http_request url: ' . $url);
        }

        return $response;
    }


    /**
     * Safely localize a script by adding data attributes to the html tag.
     *
     * Similar to `wp_localize_script`, but it does not use a <script> tag, it adds data attributes to the html tag.
     * This is useful for passing data to the app.js script without violating the Content Security Policy (CSP).
     *
     * @return void
     */
    public static function safeLocalizeScript(): void
    {
        $data = apply_filters('moj_safe_localization_data', []);

        foreach ($data as $key => $value) {
            echo sprintf(' data-%s="%s"', esc_attr($key), esc_attr($value));
        }
    }
}
