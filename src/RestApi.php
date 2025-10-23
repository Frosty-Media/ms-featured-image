<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Server;
use function esc_html__;
use function explode;
use function is_multisite;
use function is_numeric;
use function register_rest_route;
use function rest_ensure_response;

/**
 * Class RestApi
 * @package FrostyMedia\MSFeaturedImage
 */
class RestApi implements WpHooksInterface
{

    use AllBlogs;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    /**
     * Register REST API routes.
     */
    public function registerRestRoutes(): void
    {
        $args = [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getSites'],
            'permission_callback' => '__return_true',
            'args' => [
                'exclude' => [
                    'description' => esc_html__('Blog ID\'s to ignore.', 'ms-featured-image'),
                ],
            ],
        ];

        // /ms-featured-image/v1/sites/?exclude=1,2
        register_rest_route('ms-featured-image/v1', '/sites', $args);

        // /ms-featured-image/v1/sites/1
        register_rest_route('ms-featured-image/v1', '/sites/(?P<exclude>\s+)', $args);

        $args = [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getBlogs'],
            'permission_callback' => '__return_true',
            'args' => [
                'blog_id' => [
                    'description' => esc_html__('Blog ID.', 'ms-featured-image'),
                    'type' => 'integer',
                    'required' => true,
                    'validate_callback' => static fn($param): bool => is_numeric($param),
                ],
            ],
        ];

        // /ms-featured-image/v1/blog/?blog_id=1
        register_rest_route('ms-featured-image/v1', '/blog)', $args);

        // /ms-featured-image/v1/blog/1
        register_rest_route('ms-featured-image/v1', '/blog/(?P<blog_id>\d+)', $args);
    }

    public function getSites(WP_REST_Request $request)
    {
        if (!is_multisite()) {
            return rest_ensure_response(
                new WP_Error(
                    'multisite_not_enabled',
                    esc_html__('Multisite not enabled.', 'ms-featured-image'),
                    ['status' => WP_Http::NOT_ACCEPTABLE]
                )
            );
        }

        $exclude = $request->get_param('exclude');
        return rest_ensure_response($this->getAllBlogs($exclude ? explode(',', $exclude) : []));
    }

    public function getBlogs(WP_REST_Request $request)
    {
        if (!is_multisite()) {
            return rest_ensure_response(
                new WP_Error(
                    'multisite_not_enabled',
                    esc_html__('Multisite not enabled.', 'ms-featured-image'),
                    ['status' => WP_Http::NOT_ACCEPTABLE]
                )
            );
        }

        $blog_id = $request->get_param('blog_id');
        return rest_ensure_response($this->getBlogNames($blog_id));
    }
}
