<?php
/**
 * Plugin Name: Disable Comments
 * Plugin URI:  https://github.com/wp-fuse/disable-comments
 * Description: Completely removes the WordPress comment system.
 * Version:     1.0.1
 * Author:      WP Fuse
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// Global teardown.
add_action('init', static function (): void {
    foreach (get_post_types([], 'names') as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }

        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    add_filter('comments_array', '__return_empty_array', 10, 2);
    add_filter('feed_links_show_comments_feed', '__return_false');
    add_filter('pre_option_default_pingback_flag', '__return_zero');
}, 9999);

// Widgets.
add_action('widgets_init', static function (): void {
    unregister_widget('WP_Widget_Recent_Comments');
    add_filter('show_recent_comments_widget_style', '__return_false');
});

// Headers / feeds / XML-RPC.
add_filter('wp_headers', static function (array $headers): array {
    unset($headers['X-Pingback'], $headers['x-pingback']);
    return $headers;
});

add_filter('xmlrpc_methods', static function (array $methods): array {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

remove_action('wp_head', 'feed_links_extra', 3);

add_action('template_redirect', static function (): void {
    if (! is_comment_feed()) {
        return;
    }

    wp_die( esc_html__('Comments are disabled.', 'disable-comments'), '', ['response' => 403] );
}, 9);

// Admin bar.
add_action('admin_bar_menu', static function ($wp_admin_bar): void {
    $wp_admin_bar->remove_node('comments');

    if (! is_multisite() || ! is_user_logged_in() || empty($wp_admin_bar->user->blogs)) {
        return;
    }

    foreach ($wp_admin_bar->user->blogs as $blog) {
        $wp_admin_bar->remove_node('blog-' . $blog->userblog_id . '-c');
    }
}, 999);

// Admin.
if (is_admin()) {
    add_action('admin_menu', static function (): void {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }, 9999);

    add_action('admin_init', static function (): void {
        global $pagenow;

        if (in_array($pagenow, ['comment.php', 'edit-comments.php', 'options-discussion.php'], true)) {
            wp_die( esc_html__('Comments are disabled.', 'disable-comments'), '', ['response' => 403] );
        }
    });

    add_action('wp_dashboard_setup', static function (): void {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    });

    add_action('admin_print_styles-index.php', static function (): void {
        echo '<style>'
            . '#dashboard_right_now .comment-count,'
            . '#dashboard_right_now .comment-mod-count,'
            . '#welcome-panel .welcome-comments'
            . '{display:none!important}'
            . '</style>';
    });

    add_action('admin_print_styles-profile.php', static function (): void {
        echo '<style>.user-comment-shortcuts-wrap{display:none!important}</style>';
    });
} else {
    add_filter('comments_template', static function (): string {
        return __DIR__ . '/comments-template.php';
    }, 20);
}

// Front-end.
add_action('wp_enqueue_scripts', static function (): void {
    wp_dequeue_script('comment-reply');
    wp_deregister_script('comment-reply');
}, 9999);
