<?php

/**
 * Render the frontend stories for the Justice UK theme.
 *
 * This renders a single page that contains all the components and their stories.
 * It is used for development and testing purposes, and is not accessible in production.
 * For local, development, and staging environments, visit /stories.php
 *
 * Since we need to declare functions in the global namespace,
 * disable the PSR-1 rule that requires a namespace declaration.
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

// Unlike WordPress theme files, let's exit if ABSPATH *is* defined.
defined('ABSPATH') && exit;

class Stories
{
    const COMPONENT_SLUGS = [
        'common/header',
        'common/footer',
        'nav/navigation-secondary',
        'common/file-download',
        'common/hero',
        'common/horizontal-rule',
        'common/image',
        'common/image-with-text',
        'common/rich-text',
        'common/updated-date',
        'search/pagination',
        'search/search-bar-block',
        'search/search-result-card',
        'search/search-result-list',
        'common/selection-input',
        'common/text-input-form',
    ];

    public function __construct()
    {
        if (self::isProduction()) {
            // Do not run in production
            return;
        }

        // Required for template parts.
        define('ABSPATH', true);
        // Required for execution of this file, and for *.stories.php files to run.
        define('IN_STORIES', true);

        // Similar to get_template_part in WordPress, but without the WordPress context.
        self::customGetTemplatePart();

        // Load approximations of WordPress's escaping functions
        self::approximateEscapingFunctions();
    }


    /**
     * Check if the environment is production
     *
     * @return bool
     */
    private static function isProduction()
    {
        // Play things safe, and assume production if WP_ENV is not set or is empty.
        return in_array($_ENV['WP_ENV'] ?? '', ['production', 'prod', ''], true);
    }


    /**
     * A customised version of WordPress's get_template_part function.
     *
     * This function simulates the behavior of WordPress's get_template_part
     * function, allowing for the inclusion of template parts without the full
     * WordPress context. It supports both slug and name parameters, and allows
     * for passing additional arguments to the template part.
     *
     * @return void
     */
    public static function customGetTemplatePart()
    {
        if (!function_exists('get_template_part')) {
            function get_template_part($slug, $name = null, $args = [])
            {
                // Simulate the get_template_part function
                if ($name) {
                    $file = __DIR__ . "/app/themes/justice/{$slug}-{$name}.php";
                } else {
                    $file = __DIR__ . "/app/themes/justice/{$slug}.php";
                }

                if (file_exists($file)) {
                    try {
                        require $file;
                    } catch (Exception $e) {
                        echo "Error loading template part {$slug}. The error has been logged.";
                        error_log($e->getMessage());
                    }
                } else {
                    echo "Template part {$slug} not found.";
                }
                unset($args); // Unset args to avoid unused variable warning
            }
        }
    }


    /**
     * Approximate WordPress escaping functions
     *
     * Required for the template parts to run without WordPress.
     *
     * @return void
     */
    public static function approximateEscapingFunctions()
    {
        if (!function_exists('esc_attr')) {
            function esc_attr($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!function_exists('esc_url')) {
            function esc_url($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return filter_var($value, FILTER_SANITIZE_URL);
            }
        }

        if (!function_exists('esc_html')) {
            function esc_html($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!function_exists('wp_kses')) {
            function wp_kses($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return $value;
            }
        }

        if (!function_exists('wp_kses_post')) {
            function wp_kses_post($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return $value;
            }
        }

        if (!function_exists('_n')) {
            function _n($single, $plural, $number, $domain = 'default')
            {
                if (is_null($single) || is_null($plural) || is_null($number)) {
                    throw new InvalidArgumentException('Arguments cannot be null');
                }
                return ($number === 1) ? $single : $plural;
            }
        }

        if (!function_exists('sanitize_title')) {
            function sanitize_title($value)
            {
                if (is_null($value)) {
                    throw new InvalidArgumentException('Value cannot be null');
                }
                return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $value)));
            }
        }
    }


    /**
     * Get all components and their stories
     *
     * @return array
     */
    public static function getComponents()
    {
        $return_array = [];

        foreach (self::COMPONENT_SLUGS as $slug) {
            $return_array[$slug] = require_once __DIR__ . '/app/themes/justice/template-parts/' . $slug . '.stories.php';
        }

        return $return_array;
    }
};

new Stories();

if (!defined('IN_STORIES')) {
    // Redirect to the site homepage
    header('Location: /');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en-GB">

<head>
    <title>Justice UK Components</title>
    <link rel="stylesheet" href="/app/themes/justice/dist/css/v2-app.min.css?ver=2">
    <style>
        body {
            max-width: 1200px;
            margin: 0 auto;
            height: unset;
            padding: 20px;
            background: #f9f9f9;
        }

        .component-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        .story-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        @media screen and (min-width: 1024px) {
            .story-wrapper--sidebar {
                /* On large screen */
                max-width: 400px;
            }
        }
    </style>
</head>

<body>

    <h1>Justice UK Components</h1>

    <?php foreach (Stories::getComponents() as $slug => $component) : ?>
        <div class="component-wrapper">

            <h2><?= $component['title'] ?></h2>

            <?php foreach ($component['stories'] as $name => $args) : ?>
                <h3><?= $name ?></h3>

                <div class="story-wrapper story-wrapper--<?= esc_attr($component['layout'] ?? '') ?>">

                    <?php if ($args['decorator'] ?? false) : ?>
                        <?php
                        $decorator_parts = explode('__Story__', $args['decorator']);
                        unset($args['decorator']); // Remove decorator from args to avoid passing it to the
                        foreach ($decorator_parts as $i => $part) {
                            if ($i) {
                                get_template_part('template-parts/' . $slug, null, $args);
                            }
                            echo $part;
                        }
                        ?>

                    <?php else : ?>
                        <?php get_template_part('template-parts/' . $slug, null, $args); ?>
                    <?php endif; ?>

                </div>
                <!-- /.story-wrapper -->

            <?php endforeach; ?>

        </div>
        <!-- /.component-wrapper -->

    <?php endforeach; ?>

    <script src="/app/themes/justice/dist/v2-app.min.js"></script>
</body>

</html>