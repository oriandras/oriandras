<?php
/**
 * Plugin Name: Same Day Archive (Previous Years)
 * Description: Lists posts whose publication month and day match a given date (defaults to today) from previous years only, ordered by date. Provides a shortcode and PHP functions.
 * Version: 1.0.0
 * Author: oriandras site helpers
 * License: GPLv2 or later
 * Text Domain: same-day-archive
 */

if (!defined('ABSPATH')) { exit; }

if (!function_exists('ori_sameday_arrayify_post_types')) {
    /**
     * Normalize post_type input (string|array) to a sanitized array.
     */
    function ori_sameday_arrayify_post_types($post_type) {
        if (empty($post_type)) { return ['post']; }
        if (is_string($post_type)) {
            // Allow comma separated list
            $parts = array_map('trim', explode(',', $post_type));
        } else {
            $parts = (array) $post_type;
        }
        $parts = array_map(function($pt){ return sanitize_key($pt); }, $parts);
        $parts = array_values(array_filter($parts));
        return $parts ?: ['post'];
    }
}

if (!function_exists('ori_sameday_build_query_args')) {
    /**
     * Build a WP_Query arg array for same-day-archive.
     *
     * @param array $args
     *  - month (1-12) optional, default: today in site timezone
     *  - day (1-31) optional, default: today in site timezone
     *  - post_type (string|array) default: post
     *  - limit (int) posts_per_page, default: -1 (all)
     *  - order (ASC|DESC) default: DESC
     *  - orderby default: date
     */
    function ori_sameday_build_query_args($args = []) {
        $ts = current_time('timestamp'); // site timezone aware
        $month = isset($args['month']) ? (int) $args['month'] : (int) date('n', $ts);
        $day   = isset($args['day'])   ? (int) $args['day']   : (int) date('j', $ts);
        $current_year = (int) date('Y', $ts);

        // Ensure valid ranges
        if ($month < 1 || $month > 12) { $month = (int) date('n', $ts); }
        if ($day < 1 || $day > 31) { $day = (int) date('j', $ts); }

        $post_types = ori_sameday_arrayify_post_types($args['post_type'] ?? 'post');
        $limit = isset($args['limit']) ? (int) $args['limit'] : -1;
        $order = isset($args['order']) ? strtoupper($args['order']) : 'DESC';
        if ($order !== 'ASC' && $order !== 'DESC') { $order = 'DESC'; }
        $orderby = $args['orderby'] ?? 'date';

        $query_args = [
            'post_type'           => $post_types,
            'posts_per_page'      => $limit,
            'post_status'         => 'publish',
            'orderby'             => $orderby,
            'order'               => $order,
            'ignore_sticky_posts' => true,
            'date_query'          => [
                'relation' => 'AND',
                [ 'monthnum' => $month, 'day' => $day ],
                [ 'year' => $current_year, 'compare' => '<' ], // strictly previous years
            ],
        ];

        // Allow external customization
        return apply_filters('ori_sameday_query_args', $query_args, $args);
    }
}

if (!function_exists('ori_sameday_render')) {
    /**
     * Build HTML for the same-day archive list.
     *
     * @param array $args See ori_sameday_build_query_args
     * @return string HTML
     */
    function ori_sameday_render($args = []) {
        $query_args = ori_sameday_build_query_args($args);
        $q = new WP_Query($query_args);

        ob_start();
        ?>
        <div class="sda-archive" data-month="<?php echo esc_attr($query_args['date_query'][0]['monthnum'] ?? ''); ?>" data-day="<?php echo esc_attr($query_args['date_query'][0]['day'] ?? ''); ?>">
        <?php if ($q->have_posts()) : ?>
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php
                $title = get_the_title();
                $permalink = get_permalink();
                $date_iso = get_the_date('c');
                $date_human = get_the_date(get_option('date_format'));
                ?>
                <article <?php post_class('sda-item'); ?>>
                    <h2 class="sda-title"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h2>
                    <div class="sda-meta">
                        <time class="sda-date" datetime="<?php echo esc_attr($date_iso); ?>"><?php echo esc_html($date_human); ?></time>
                    </div>
                    <div class="sda-content">
                        <?php
                        // Full content
                        $content = apply_filters('the_content', get_the_content());
                        // the_content filter outputs HTML; escaping would break it
                        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </div>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="sda-empty"><?php echo esc_html(apply_filters('ori_sameday_no_results_text', __('No entries found for this day from previous years.', 'same-day-archive'), $args)); ?></div>
        <?php endif; ?>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }
}

if (!function_exists('ori_sameday_shortcode')) {
    /**
     * Shortcode handler: [same_day_archive]
     */
    function ori_sameday_shortcode($atts = []) {
        $atts = shortcode_atts([
            'month'     => '',
            'day'       => '',
            'post_type' => 'post',
            'limit'     => -1,
            'order'     => 'DESC',
            'orderby'   => 'date',
        ], $atts, 'same_day_archive');

        // Normalize numeric strings to ints when provided
        if ($atts['month'] !== '') { $atts['month'] = (int) $atts['month']; }
        if ($atts['day']   !== '') { $atts['day']   = (int) $atts['day']; }
        $atts['limit'] = (int) $atts['limit'];

        return ori_sameday_render($atts);
    }
    add_shortcode('same_day_archive', 'ori_sameday_shortcode');
}

if (!function_exists('ori_sameday_archive')) {
    /**
     * PHP API: returns HTML string for same-day archive. Usable in theme files.
     *
     * Example:
     *   echo ori_sameday_archive(['month' => 8, 'day' => 21, 'post_type' => ['post','page'], 'limit' => -1]);
     */
    function ori_sameday_archive($args = []) {
        return ori_sameday_render($args);
    }
}

if (!function_exists('ori_sameday_archive_echo')) {
    /**
     * PHP API: echoes the HTML directly.
     */
    function ori_sameday_archive_echo($args = []) {
        echo ori_sameday_render($args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

// Load textdomain for translations
if (!function_exists('ori_sameday_load_textdomain')) {
    function ori_sameday_load_textdomain() {
        load_plugin_textdomain('same-day-archive', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    add_action('init', 'ori_sameday_load_textdomain');
}
