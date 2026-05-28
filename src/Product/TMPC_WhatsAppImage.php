<?php

namespace TMProductConfigurator\Product;

/**
 * WhatsApp image + share + composite handling.
 */
class TMPC_WhatsAppImage {

    /**
     * Init hooks.
     */
    public static function init() {

        add_action('rest_api_init', function () {
            register_rest_route('tmpc/v1', '/whatsapp-image/(?P<filename>[a-z0-9\-]+)\.(?P<ext>jpg|png)', [
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'serve_whatsapp_image'],
                'permission_callback' => '__return_true',
            ]);
        });

        add_action('init', [__CLASS__, 'add_rewrites']);
        add_filter('query_vars', [__CLASS__, 'add_query_vars']);
        add_action('template_redirect', [__CLASS__, 'handle_requests']);

    }

    /**
     * Return image URL.
     */
    public static function serve_whatsapp_image($request) {

        $filename = $request['filename'];
        $ext      = $request['ext'];

        $base_dir = get_stylesheet_directory() . "/assets/layers/composites";
        $base_url = get_stylesheet_directory_uri() . "/assets/layers/composites";

        $file_path = "{$base_dir}/{$filename}.{$ext}";
        $file_url  = "{$base_url}/{$filename}.{$ext}";

        if (!file_exists($file_path)) {
            return new \WP_REST_Response(['error' => 'not_found'], 404);
        }

        return [
            'hash' => $filename,
            'image_url' => $file_url,
        ];
    }

    /**
     * Rewrites.
     */
    public static function add_rewrites() {

        add_rewrite_rule(
            '^share/([a-f0-9\-]+)/?$',
            'index.php?share_hash=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^composite/([a-f0-9\-]+)\.jpg$',
            'index.php?composite_hash=$matches[1]',
            'top'
        );
    }

    /**
     * Query vars.
     */
    public static function add_query_vars($vars) {
           error_log('ADDING QUERY VARS');
        $vars[] = 'composite_hash';
        $vars[] = 'share_hash';
        return $vars;
    }

    /**
     * Handle requests.
     */
     public static function handle_requests() {

        $uri = $_SERVER['REQUEST_URI'] ?? '';

        if (!preg_match('#^/share/([a-z0-9\-]+)/?$#', $uri, $m)) {
            return;
        }

        $share_hash = $m[1];

        $file_path = get_stylesheet_directory() . "/assets/layers/composites/{$share_hash}.jpg";

        if (!file_exists($file_path)) {
            status_header(404);
            exit;
        }

        $image_url = get_stylesheet_directory_uri() . "/assets/layers/composites/{$share_hash}.jpg";
        $page_url  = home_url($uri);

        status_header(200);
        header('Content-Type: text/html; charset=utf-8');

        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">

            <meta property="og:title" content="Your Custom Design">
            <meta property="og:description" content="View your configuration">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo esc_url($page_url); ?>">
            <meta property="og:image" content="<?php echo esc_url($image_url); ?>">

            <meta name="twitter:card" content="summary_large_image">
        </head>
        <body></body>
        </html>
        <?php

        exit;
    }
}