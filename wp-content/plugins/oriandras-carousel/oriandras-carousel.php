<?php
/**
 * Plugin Name: Oriandras – Content Carousel
 * Description: Provides a configurable carousel for latest content across all public post types, using the post's featured (cover) image. Shortcode: [oriandras-carousel].
 * Version: 1.1.0
 * Author: Őri András
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oriandras-carousel
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode callback for [oriandras-carousel]
 *
 * Attributes:
 * - width: CSS width (e.g., 100%, 1200px) - default '100%'
 * - height: CSS height (e.g., 300px, 50vh) - default '320px'
 * - items: number of posts to include - default 6
 * - dots: show navigation dots - default true
 * - arrows: show side arrows - default true
 * - show_title: show post/page title overlay - default true
 * - post_type: comma-separated list of post types - default: all public
 * - orderby: WP_Query orderby - default 'date'
 * - order: ASC/DESC - default 'DESC'
 */
function oriandras_carousel_shortcode($atts)
{
    $atts = shortcode_atts([
        'width' => '100%',
        'height' => '320px',
        'items' => 6,
        'dots' => 'true',
        'arrows' => 'true',
        'show_title' => 'true',
        'post_type' => '',
        'orderby' => 'date',
        'order' => 'DESC',
    ], $atts, 'oriandras-carousel');

    // Sanitize and normalize
    $width = trim((string)$atts['width']);
    $height = trim((string)$atts['height']);
    $items = max(1, (int)$atts['items']);
    $dots = filter_var($atts['dots'], FILTER_VALIDATE_BOOLEAN);
    $arrows = filter_var($atts['arrows'], FILTER_VALIDATE_BOOLEAN);
    $show_title = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);

    // Determine post types
    if (is_string($atts['post_type']) && $atts['post_type'] !== '') {
        $post_types = array_filter(array_map('trim', explode(',', (string)$atts['post_type'])));
    } else {
        $post_types = array_values(get_post_types(['public' => true]));
    }

    // Build query – only include posts that actually have a featured image (cover)
    $q = new WP_Query([
        'post_type' => $post_types,
        'posts_per_page' => $items,
        'post_status' => 'publish',
        'orderby' => sanitize_key($atts['orderby']),
        'order' => (strtoupper($atts['order']) === 'ASC') ? 'ASC' : 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'meta_query' => [
            [
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    if (!$q->have_posts()) {
        return '';
    }

    // Ensure assets are enqueued
    add_action('wp_enqueue_scripts', 'oriandras_carousel_enqueue');

    // Unique ID for this instance
    static $instance = 0;
    $instance++;
    $carousel_id = 'oriandras-carousel-' . $instance;
    $viewport_id = $carousel_id . '-viewport';
    $track_id = $carousel_id . '-track';
    $live_id = $carousel_id . '-live';

    // Build slides
    $slides_html = '';
    $count = (int) $q->post_count;
    $i = 0;
    while ($q->have_posts()) {
        $q->the_post();
        $post_id = get_the_ID();
        $title = get_the_title();
        $permalink = get_permalink();
        $i++;

        $img_html = '';
        if (has_post_thumbnail($post_id)) {
            // Use full size src to keep it simple; themes/CDN may handle responsive
            $img_src = get_the_post_thumbnail_url($post_id, 'large');
            if ($img_src) {
                $img_alt = esc_attr($title);
                $img_html = '<img class="oriandras-crsl-img" src="' . esc_url($img_src) . '" alt="' . $img_alt . '" />';
            }
        } else {
            // Safety: if no thumbnail slipped through for any reason, skip this item.
            continue;
        }

        $title_html = '';
        if ($show_title) {
            $title_html = '<div class="oriandras-crsl-title">' . esc_html($title) . '</div>';
        }

        $pos_label = sprintf(esc_html__('Slide %1$d of %2$d', 'oriandras-carousel'), $i, $count);
        $is_active = ($i === 1);
        $aria_hidden = $is_active ? '' : ' aria-hidden="true"';
        $link_tabindex = $is_active ? '' : ' tabindex="-1"';

        $slides_html .= '<div class="oriandras-crsl-slide" role="group" aria-roledescription="slide" aria-label="' . esc_attr($pos_label) . '"' . $aria_hidden . '>'
            . '<a class="oriandras-crsl-link" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '"' . $link_tabindex . '>'
            . $img_html
            . $title_html
            . '</a>'
            . '</div>';
    }
    wp_reset_postdata();

    // Controls
    $arrows_html = '';
    if ($arrows && $count > 1) {
        $arrows_html = '<button class="oriandras-crsl-prev" aria-controls="' . esc_attr($viewport_id) . '" aria-label="' . esc_attr__('Previous slide', 'oriandras-carousel') . '">‹</button>'
            . '<button class="oriandras-crsl-next" aria-controls="' . esc_attr($viewport_id) . '" aria-label="' . esc_attr__('Next slide', 'oriandras-carousel') . '">›</button>';
    }

    $dots_attr = $dots ? 'true' : 'false';

    $style_attr = 'style="width:' . esc_attr($width) . ';height:' . esc_attr($height) . ';"';

    $html = '<div id="' . esc_attr($carousel_id) . '" class="oriandras-crsl" ' . $style_attr . ' data-dots="' . $dots_attr . '">'
        . '<div id="' . esc_attr($viewport_id) . '" class="oriandras-crsl-viewport" role="region" aria-roledescription="carousel" aria-label="' . esc_attr__('Content carousel', 'oriandras-carousel') . '">'
        . '<div class="oriandras-crsl-track" id="' . esc_attr($track_id) . '">' . $slides_html . '</div>'
        . '<div id="' . esc_attr($live_id) . '" class="oriandras-crsl-live sr-only" aria-live="polite" aria-atomic="true">' . esc_html__('Slide 1', 'oriandras-carousel') . '</div>'
        . '</div>'
        . $arrows_html
        . '</div>';

    return $html;
}
add_shortcode('oriandras-carousel', 'oriandras_carousel_shortcode');

/**
 * Enqueue front-end assets (JS + tiny CSS)
 */
function oriandras_carousel_enqueue()
{
    if (is_admin()) {
        return;
    }
    // JS
    wp_enqueue_script(
        'oriandras-carousel',
        plugins_url('oriandras-carousel.js', __FILE__),
        [],
        '1.1.0',
        true
    );

    // Minimal CSS via wp_head to avoid separate file
    $css = '.oriandras-crsl{position:relative;overflow:hidden;max-width:100%;}
.oriandras-crsl-viewport{width:100%;height:100%;overflow:hidden}
.oriandras-crsl-track{display:flex;flex-wrap:nowrap;height:100%;will-change:transform;transition:transform .35s ease}
.oriandras-crsl-slide{flex:0 0 100%;height:100%;position:relative}
.oriandras-crsl-link{display:block;width:100%;height:100%;position:relative;color:inherit;text-decoration:none}
.oriandras-crsl-img{width:100%;height:100%;object-fit:cover;display:block}
.oriandras-crsl-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#111;font-weight:600;padding:1rem;text-align:center}
.oriandras-crsl-title{position:absolute;left:.75rem;right:.75rem;bottom:.75rem;background:rgba(0,0,0,.55);color:#fff;padding:.5rem .75rem;border-radius:.375rem;font-size:.95rem}
.oriandras-crsl-prev,.oriandras-crsl-next{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.45);color:#fff;border:none;width:2.25rem;height:2.25rem;border-radius:999px;display:flex;align-items:center;justify-content:center;cursor:pointer}
.oriandras-crsl-prev{left:.5rem}
.oriandras-crsl-next{right:.5rem}
.oriandras-crsl-prev:hover,.oriandras-crsl-next:hover{background:rgba(0,0,0,.65)}
.oriandras-crsl-dots{position:absolute;left:0;right:0;bottom:.35rem;display:flex;gap:.4rem;align-items:center;justify-content:center}
.oriandras-crsl-dot{width:.45rem;height:.45rem;border-radius:999px;background:#d1d5db;border:0;padding:0;cursor:pointer}
.oriandras-crsl-dot[aria-current="true"]{background:#111827}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,1px,1px);white-space:nowrap;border:0}
';

    add_action('wp_head', function () use ($css) {
        echo "\n<style id=\"oriandras-carousel\">$css</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    });
}
add_action('wp_enqueue_scripts', 'oriandras_carousel_enqueue');

/**
 * Render dots after the track if the container has data-dots="true".
 * We output them via JS on DOM ready to match the number of slides.
 */

// i18n loader placeholder
function oriandras_carousel_load_textdomain()
{
    load_plugin_textdomain('oriandras-carousel', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'oriandras_carousel_load_textdomain');
