<?php
/**
 * Plugin Name: Oriandras – Featured Video
 * Description: Use a video (YouTube or Media Library) as a replacement for the featured image (cover). Adds a meta box for YouTube URL or selecting a video from the media library, and automatically swaps the featured image output with the video when set.
 * Version: 1.0.0
 * Author: Őri András
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oriandras-featured-video
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Meta keys
const ORI_FV_META_YT_URL = '_ori_fv_youtube_url';
const ORI_FV_META_ATTACHMENT = '_ori_fv_attachment_id';

/**
 * Add meta box to posts and pages.
 */
function oriandras_fv_add_meta_box()
{
    $screens = apply_filters('oriandras_fv_screens', ['post', 'page']);
    foreach ($screens as $screen) {
        add_meta_box(
            'oriandras_featured_video',
            __('Featured Video', 'oriandras-featured-video'),
            'oriandras_fv_render_meta_box',
            $screen,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'oriandras_fv_add_meta_box');

/**
 * Enqueue admin assets for media modal.
 */
function oriandras_fv_admin_assets($hook)
{
    // Only on post editor
    if ($hook !== 'post-new.php' && $hook !== 'post.php') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script(
        'oriandras-featured-video-admin',
        plugins_url('oriandras-featured-video-admin.js', __FILE__),
        ['jquery'],
        '1.0.0',
        true
    );
    wp_localize_script('oriandras-featured-video-admin', 'oriFvAdmin', [
        'pickVideo' => __('Select a video', 'oriandras-featured-video'),
        'useVideo' => __('Use this video', 'oriandras-featured-video'),
        'noVideoSelected' => __('No video selected.', 'oriandras-featured-video'),
    ]);
}
add_action('admin_enqueue_scripts', 'oriandras_fv_admin_assets');

/**
 * Render the meta box UI.
 */
function oriandras_fv_render_meta_box($post)
{
    wp_nonce_field('ori_fv_save_meta', 'ori_fv_nonce');

    $yt_url = esc_url(get_post_meta($post->ID, ORI_FV_META_YT_URL, true));
    $attach_id = (int) get_post_meta($post->ID, ORI_FV_META_ATTACHMENT, true);
    $attach_url = $attach_id ? wp_get_attachment_url($attach_id) : '';

    echo '<p>' . esc_html__('Provide a YouTube URL or select a video from the Media Library. If both are set, the YouTube URL takes precedence.', 'oriandras-featured-video') . '</p>';

    echo '<label for="ori_fv_youtube_url" style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html__('YouTube URL', 'oriandras-featured-video') . '</label>';
    echo '<input type="url" id="ori_fv_youtube_url" name="ori_fv_youtube_url" value="' . esc_attr($yt_url) . '" placeholder="https://www.youtube.com/watch?v=..." style="width:100%;" />';

    echo '<hr style="margin:10px 0;" />';

    echo '<label style="display:block;font-weight:600;margin-bottom:6px;">' . esc_html__('Media Library Video', 'oriandras-featured-video') . '</label>';
    echo '<div id="ori_fv_media_wrap">';
    echo '<input type="hidden" id="ori_fv_attachment_id" name="ori_fv_attachment_id" value="' . esc_attr($attach_id) . '" />';
    echo '<div id="ori_fv_attachment_preview" style="font-size:12px;color:#555;margin-bottom:6px;">' . ($attach_url ? esc_html($attach_url) : esc_html__('No video selected.', 'oriandras-featured-video')) . '</div>';
    echo '<button type="button" class="button" id="ori_fv_select_video">' . esc_html__('Select video', 'oriandras-featured-video') . '</button> ';
    echo '<button type="button" class="button" id="ori_fv_clear_video" ' . ($attach_id ? '' : 'disabled') . '>' . esc_html__('Clear', 'oriandras-featured-video') . '</button>';
    echo '</div>';

    echo '<p style="margin-top:10px;color:#666;font-size:12px;">' . esc_html__('When a featured video is provided, it will be displayed in place of the featured image on the front-end.', 'oriandras-featured-video') . '</p>';
}

/**
 * Save meta box data.
 */
function oriandras_fv_save_post($post_id)
{
    if (!isset($_POST['ori_fv_nonce']) || !wp_verify_nonce($_POST['ori_fv_nonce'], 'ori_fv_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save YouTube URL
    $yt_url = isset($_POST['ori_fv_youtube_url']) ? trim((string) $_POST['ori_fv_youtube_url']) : '';
    if ($yt_url !== '') {
        // Basic validation: must be a valid URL and likely a YouTube domain
        if (filter_var($yt_url, FILTER_VALIDATE_URL)) {
            update_post_meta($post_id, ORI_FV_META_YT_URL, esc_url_raw($yt_url));
        }
    } else {
        delete_post_meta($post_id, ORI_FV_META_YT_URL);
    }

    // Save attachment ID (must be a video)
    $attach_id = isset($_POST['ori_fv_attachment_id']) ? (int) $_POST['ori_fv_attachment_id'] : 0;
    if ($attach_id > 0) {
        $mime = get_post_mime_type($attach_id);
        if (is_string($mime) && strpos($mime, 'video/') === 0) {
            update_post_meta($post_id, ORI_FV_META_ATTACHMENT, $attach_id);
        } else {
            // Not a video, ignore
            delete_post_meta($post_id, ORI_FV_META_ATTACHMENT);
        }
    } else {
        delete_post_meta($post_id, ORI_FV_META_ATTACHMENT);
    }
}
add_action('save_post', 'oriandras_fv_save_post');

/**
 * Build the featured video HTML if configured for a post.
 * Returns empty string if not set or cannot be rendered.
 *
 * @param int|WP_Post $post
 * @return string
 */
function oriandras_fv_get_featured_video_html($post = null)
{
    $post = get_post($post);
    if (!$post instanceof WP_Post) {
        return '';
    }

    if (post_password_required($post)) {
        return '';
    }

    // Priority: YouTube URL
    $yt = trim((string) get_post_meta($post->ID, ORI_FV_META_YT_URL, true));
    if ($yt !== '') {
        $embed = wp_oembed_get($yt, [
            'width' => apply_filters('ori_fv_embed_width', 1200, $post),
            'height' => apply_filters('ori_fv_embed_height', 675, $post),
        ]);
        if ($embed) {
            $classes = apply_filters('ori_fv_container_classes', 'ori-fv ori-fv-youtube', $post);
            return '<div class="' . esc_attr($classes) . '">' . $embed . '</div>';
        }
    }

    // Fallback to Media Library video
    $attach_id = (int) get_post_meta($post->ID, ORI_FV_META_ATTACHMENT, true);
    if ($attach_id > 0) {
        $src = wp_get_attachment_url($attach_id);
        if ($src) {
            // Optionally use featured image as poster
            $poster = '';
            if (has_post_thumbnail($post)) {
                $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'full');
                if (!empty($thumb[0])) {
                    $poster = $thumb[0];
                }
            }
            $shortcode_atts = [
                'src' => esc_url($src),
            ];
            if ($poster) {
                $shortcode_atts['poster'] = esc_url($poster);
            }
            $html = wp_video_shortcode($shortcode_atts);
            if ($html) {
                $classes = apply_filters('ori_fv_container_classes', 'ori-fv ori-fv-media', $post);
                return '<div class="' . esc_attr($classes) . '">' . $html . '</div>';
            }
        }
    }

    return '';
}

/**
 * Filter the featured image HTML to output the video when available.
 */
function oriandras_fv_filter_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    $video_html = oriandras_fv_get_featured_video_html($post_id);
    if ($video_html !== '') {
        // Provide a filter to completely override or modify the output
        $video_html = apply_filters('ori_fv_output_html', $video_html, get_post($post_id));
        return $video_html;
    }
    return $html;
}
add_filter('post_thumbnail_html', 'oriandras_fv_filter_post_thumbnail_html', 10, 5);

/**
 * Ensure themes that conditionally display thumbnails still render our video.
 */
function oriandras_fv_filter_has_post_thumbnail($has_thumbnail, $post)
{
    $post = get_post($post);
    if ($post instanceof WP_Post) {
        $yt = trim((string) get_post_meta($post->ID, ORI_FV_META_YT_URL, true));
        $attach_id = (int) get_post_meta($post->ID, ORI_FV_META_ATTACHMENT, true);
        if ($yt !== '' || $attach_id > 0) {
            return true;
        }
    }
    return $has_thumbnail;
}
add_filter('has_post_thumbnail', 'oriandras_fv_filter_has_post_thumbnail', 10, 2);

/**
 * Provide a shortcode so users can place the featured video manually in templates or content.
 * Usage: [ori_featured_video]
 */
function oriandras_fv_shortcode($atts, $content = '', $tag = '')
{
    $post = get_post();
    if (!$post) {
        return '';
    }
    $html = oriandras_fv_get_featured_video_html($post);
    if ($html === '') {
        return '';
    }
    return $html;
}
add_shortcode('ori_featured_video', 'oriandras_fv_shortcode');

/**
 * Minimal inline styles to ensure embeds are responsive-ish by default.
 */
function oriandras_fv_enqueue_styles()
{
    if (is_admin()) {
        return;
    }
    $css = '.ori-fv{margin:0 0 1rem 0}.ori-fv iframe{max-width:100%;width:100%;aspect-ratio:16/9;height:auto}.ori-fv video{max-width:100%;width:100%;height:auto}';
    add_action('wp_head', function () use ($css) {
        echo "\n<style id=\"oriandras-fv\">$css</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    });
}
add_action('wp_enqueue_scripts', 'oriandras_fv_enqueue_styles');

/**
 * Load textdomain placeholder.
 */
function oriandras_fv_load_textdomain()
{
    load_plugin_textdomain('oriandras-featured-video', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'oriandras_fv_load_textdomain');
