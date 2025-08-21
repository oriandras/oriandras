<?php
/**
 * Plugin Name: Oriandras – Stale Content Alert
 * Description: Shows an informational alert on single posts and pages when the content is older than a configurable threshold (default: 3 years, based on the most recent of publish or modified date).
 * Version: 1.0.0
 * Author: Őri András
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oriandras-stale-content-alert
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Determine whether the alert should be shown for the current post.
 *
 * Logic: use the most recent timestamp of post_date_gmt and post_modified_gmt.
 * If that timestamp is older than the configured threshold (default 3 years), show the alert.
 *
 * @param WP_Post $post The post object.
 * @return bool
 */
function oriandras_sca_should_show_alert($post)
{
    if (!$post instanceof WP_Post) {
        return false;
    }

    // Allow disabling via filter
    $enabled = apply_filters('oriandras_sca_enabled', true, $post);
    if (!$enabled) {
        return false;
    }

    // Threshold in seconds (default 3 years). Allow filtering in years or seconds.
    $years_threshold = apply_filters('oriandras_sca_threshold_years', 3, $post);
    $seconds_threshold = (int) apply_filters('oriandras_sca_threshold_seconds', $years_threshold * YEAR_IN_SECONDS, $post);

    // Choose the most recent of publish or modified date, in GMT.
    $published_gmt = strtotime(get_gmt_from_date($post->post_date_gmt ?: get_post_time('c', true, $post)));
    $modified_gmt  = strtotime(get_gmt_from_date($post->post_modified_gmt ?: get_post_modified_time('c', true, $post)));

    // Fallbacks if parsing failed
    if (!$published_gmt) {
        $published_gmt = get_post_time('U', true, $post);
    }
    if (!$modified_gmt) {
        $modified_gmt = get_post_modified_time('U', true, $post);
    }

    $reference = max((int)$published_gmt, (int)$modified_gmt);
    $age = time() - $reference;

    return $age >= $seconds_threshold;
}

/**
 * Build the alert HTML.
 *
 * @param WP_Post $post
 * @return string
 */
function oriandras_sca_build_alert_markup($post)
{
    // Human-readable dates
    $published_disp = get_the_date('', $post);
    $modified_disp  = get_the_modified_date('', $post);

    $use_modified = (get_the_modified_time('U', false, $post) !== get_the_time('U', false, $post));

    $label = esc_attr__('Content age notice', 'oriandras-stale-content-alert');

    $default_classes = 'oriandras-sca-notice rounded-md border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm';
    $classes = apply_filters('oriandras_sca_notice_classes', $default_classes, $post);

    // Core message
    if ($use_modified) {
        /* translators: 1: modified date, 2: published date */
        $message = sprintf(
            esc_html__('This content was last updated on %1$s (originally published on %2$s). It may be out of date or no longer relevant.', 'oriandras-stale-content-alert'),
            esc_html($modified_disp),
            esc_html($published_disp)
        );
    } else {
        /* translators: 1: published date */
        $message = sprintf(
            esc_html__('This content was published on %1$s. It may be out of date or no longer relevant.', 'oriandras-stale-content-alert'),
            esc_html($published_disp)
        );
    }

    $message = apply_filters('oriandras_sca_notice_message', $message, $post);

    $html = '<div role="status" aria-live="polite" aria-label="' . $label . '" class="' . esc_attr($classes) . '">'
        . '<p>' . $message . '</p>'
        . '</div>';

    return $html;
}

/**
 * Filter the content to prepend the alert where appropriate.
 *
 * @param string $content
 * @return string
 */
function oriandras_sca_maybe_prepend_alert($content)
{
    if (is_admin() || is_feed() || is_search()) {
        return $content;
    }

    if (!is_singular(['post', 'page'])) {
        return $content;
    }

    global $post;
    if (!$post instanceof WP_Post) {
        return $content;
    }

    if (post_password_required($post)) {
        return $content;
    }

    if (!oriandras_sca_should_show_alert($post)) {
        return $content;
    }

    // Enqueue styles only when we actually output the notice.
    add_action('wp_enqueue_scripts', 'oriandras_sca_enqueue_styles');

    $notice = oriandras_sca_build_alert_markup($post);

    // Place notice before the content. Allow filtering of placement.
    $placement = apply_filters('oriandras_sca_notice_placement', 'before', $post);

    if ($placement === 'after') {
        return $content . $notice;
    }

    // default: before
    return $notice . $content;
}
add_filter('the_content', 'oriandras_sca_maybe_prepend_alert', 12);

/**
 * Enqueue a tiny bit of CSS to make the notice readable even without theme styles.
 */
function oriandras_sca_enqueue_styles()
{
    // Register an inline stylesheet.
    $css = '
.oriandras-sca-notice {\n'
        . '  border: 1px solid #F59E0B;\n'
        . '  background: #FFFBEB;\n'
        . '  color: #7C2D12;\n'
        . '  padding: 0.75rem 1rem;\n'
        . '  border-radius: .375rem;\n'
        . '  margin: 1rem 0 1.25rem;\n'
        . '  font-size: 0.95rem;\n'
        . '}\n'
        . '.oriandras-sca-notice p { margin: 0; line-height: 1.5; }'
        ;

    // Use wp_add_inline_style by attaching to a registered handle if possible; otherwise print in head.
    // We don't know the theme handle, so we'll print directly in head via wp_head.
    add_action('wp_head', function () use ($css) {
        echo "\n<style id=\"oriandras-sca\">$css</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    });
}

/**
 * Load plugin textdomain (placeholder for future translations).
 */
function oriandras_sca_load_textdomain()
{
    load_plugin_textdomain('oriandras-stale-content-alert', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'oriandras_sca_load_textdomain');
