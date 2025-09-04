<?php
/**
 * Plugin Name: Oriandras – Galleries
 * Description: Provides a Gallery content type that groups uploaded photos with categories and tags, front-end Instagram-like grid, lightbox viewer, and custom fields (location, shot time, camera type selector managed via settings).
 * Version: 1.0.0
 * Author: Őri András
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oriandras-galleries
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constants
const ORI_GAL_OPT_CAMERAS = 'oriandras_galleries_cameras';

function ori_gal_activate() {
    ori_gal_register_cpt();
    // default cameras if none
    if (get_option(ORI_GAL_OPT_CAMERAS, null) === null) {
        add_option(ORI_GAL_OPT_CAMERAS, [ 'iPhone', 'Sony A7', 'Canon EOS', 'Nikon Z6' ]);
    }
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ori_gal_activate');

function ori_gal_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ori_gal_deactivate');

// Register Custom Post Type
function ori_gal_register_cpt() {
    $labels = [
        'name' => __('Galleries', 'oriandras-galleries'),
        'singular_name' => __('Gallery', 'oriandras-galleries'),
        'add_new' => __('Add New', 'oriandras-galleries'),
        'add_new_item' => __('Add New Photo', 'oriandras-galleries'),
        'edit_item' => __('Edit Photo', 'oriandras-galleries'),
        'new_item' => __('New Photo', 'oriandras-galleries'),
        'view_item' => __('View Photo', 'oriandras-galleries'),
        'search_items' => __('Search Photos', 'oriandras-galleries'),
        'not_found' => __('No photos found', 'oriandras-galleries'),
        'not_found_in_trash' => __('No photos found in Trash', 'oriandras-galleries'),
        'all_items' => __('All Photos', 'oriandras-galleries'),
        'archives' => __('Gallery Archives', 'oriandras-galleries'),
        'menu_name' => __('Galleries', 'oriandras-galleries'),
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => [ 'slug' => 'galleries' ],
        'show_in_rest' => true,
        'supports' => [ 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ],
        'taxonomies' => [ 'category', 'post_tag' ],
        'menu_icon' => 'dashicons-format-image',
    ];

    register_post_type('galleries', $args);
}
add_action('init', 'ori_gal_register_cpt');

// Settings: Camera Types list
function ori_gal_register_settings() {
    register_setting('ori_gal_settings', ORI_GAL_OPT_CAMERAS, [
        'type' => 'array',
        'sanitize_callback' => 'ori_gal_sanitize_cameras',
        'default' => [],
    ]);
}
add_action('admin_init', 'ori_gal_register_settings');

function ori_gal_sanitize_cameras($value) {
    if (!is_array($value)) {
        $value = explode("\n", (string)$value);
    }
    $value = array_map('trim', $value);
    $value = array_filter($value, function($v){ return $v !== ''; });
    $value = array_values(array_unique($value));
    return $value;
}

function ori_gal_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=galleries',
        __('Camera Types', 'oriandras-galleries'),
        __('Camera Types', 'oriandras-galleries'),
        'manage_options',
        'ori-gal-cameras',
        'ori_gal_render_settings_page'
    );
}
add_action('admin_menu', 'ori_gal_add_settings_page');

function ori_gal_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    // If posted, options API handles saving. Provide textarea for list.
    $cameras = get_option(ORI_GAL_OPT_CAMERAS, []);
    $text = is_array($cameras) ? implode("\n", $cameras) : (string)$cameras;
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Camera Types', 'oriandras-galleries') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('ori_gal_settings');
    echo '<p>' . esc_html__('Enter one camera type per line. These will appear in the camera selector for photos.', 'oriandras-galleries') . '</p>';
    echo '<textarea name="' . esc_attr(ORI_GAL_OPT_CAMERAS) . '" rows="10" cols="50" class="large-text code">' . esc_textarea($text) . '</textarea>';
    submit_button();
    echo '</form>';
    echo '</div>';
}

// Meta boxes for custom fields
function ori_gal_add_meta_boxes() {
    add_meta_box('ori_gal_photo_meta', __('Photo Details', 'oriandras-galleries'), 'ori_gal_render_meta_box', 'galleries', 'normal', 'default');
}
add_action('add_meta_boxes', 'ori_gal_add_meta_boxes');

function ori_gal_render_meta_box($post) {
    wp_nonce_field('ori_gal_save_meta', 'ori_gal_meta_nonce');
    $location = get_post_meta($post->ID, '_ori_gal_location', true);
    $shot_time = get_post_meta($post->ID, '_ori_gal_shot_time', true);
    $camera = get_post_meta($post->ID, '_ori_gal_camera', true);
    $cameras = get_option(ORI_GAL_OPT_CAMERAS, []);
    if (!is_array($cameras)) { $cameras = []; }
    echo '<p><label for="ori_gal_location"><strong>' . esc_html__('Location', 'oriandras-galleries') . '</strong></label><br />';
    echo '<input type="text" id="ori_gal_location" name="ori_gal_location" class="regular-text" value="' . esc_attr((string)$location) . '" /></p>';

    echo '<p><label for="ori_gal_shot_time"><strong>' . esc_html__('Shot time', 'oriandras-galleries') . '</strong></label><br />';
    echo '<input type="datetime-local" id="ori_gal_shot_time" name="ori_gal_shot_time" value="' . esc_attr((string)$shot_time) . '" /></p>';

    echo '<p><label for="ori_gal_camera"><strong>' . esc_html__('Camera type', 'oriandras-galleries') . '</strong></label><br />';
    echo '<select id="ori_gal_camera" name="ori_gal_camera">';
    echo '<option value="">' . esc_html__('— Select —', 'oriandras-galleries') . '</option>';
    foreach ($cameras as $cam) {
        $sel = selected($camera, $cam, false);
        echo '<option value="' . esc_attr($cam) . '" ' . $sel . '>' . esc_html($cam) . '</option>';
    }
    echo '</select></p>';
}

function ori_gal_save_meta($post_id) {
    if (!isset($_POST['ori_gal_meta_nonce']) || !wp_verify_nonce($_POST['ori_gal_meta_nonce'], 'ori_gal_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $location = isset($_POST['ori_gal_location']) ? sanitize_text_field((string)$_POST['ori_gal_location']) : '';
    $shot_time = isset($_POST['ori_gal_shot_time']) ? sanitize_text_field((string)$_POST['ori_gal_shot_time']) : '';
    $camera = isset($_POST['ori_gal_camera']) ? sanitize_text_field((string)$_POST['ori_gal_camera']) : '';

    update_post_meta($post_id, '_ori_gal_location', $location);
    update_post_meta($post_id, '_ori_gal_shot_time', $shot_time);
    update_post_meta($post_id, '_ori_gal_camera', $camera);
}
add_action('save_post_galleries', 'ori_gal_save_meta');

// Front-end assets and shortcode for grid
function ori_gal_enqueue() {
    if (is_admin()) return;
    $css = '.ori-gal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}.ori-gal-item{position:relative;aspect-ratio:1/1;overflow:hidden;border-radius:.25rem;background:#f3f4f6}.ori-gal-item .ori-gal-link{position:absolute;inset:0;display:block}.ori-gal-item img{width:100%;height:100%;object-fit:cover;display:block;margin:0!important}.ori-gal-open{position:absolute;top:.4rem;right:.4rem;width:2.25rem;height:2.25rem;border-radius:999px;border:0;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--ori-accent,#2563eb);color:#fff;box-shadow:0 2px 6px rgba(0,0,0,.15);z-index:2}.ori-gal-open:hover{filter:brightness(.95)}.ori-gal-open:focus-visible{outline:2px solid #fff;outline-offset:2px;box-shadow:0 0 0 3px var(--ori-accent,#2563eb)}.ori-gal-open svg{width:1.1rem;height:1.1rem;display:block}.ori-gal-open .ori-gal-ico{font-size:1.1rem;line-height:1}.ori-gal-lightbox{position:fixed;inset:0;background:rgba(0,0,0,.9);display:none;align-items:center;justify-content:center;z-index:9999}.ori-gal-lightbox[aria-hidden="false"]{display:flex}.ori-gal-lightbox img{max-width:90vw;max-height:85vh;object-fit:contain}.ori-gal-lightbox .ori-gal-close,.ori-gal-lightbox .ori-gal-prev,.ori-gal-lightbox .ori-gal-next{position:absolute;background:var(--ori-accent,#2563eb);border:0;color:#fff;width:2.5rem;height:2.5rem;border-radius:999px;display:flex;align-items:center;justify-content:center;cursor:pointer}
.ori-gal-lightbox .ori-gal-close{top:1rem;right:1rem;background:#111}
.ori-gal-lightbox .ori-gal-prev{left:1rem;top:50%;transform:translateY(-50%)}
.ori-gal-lightbox .ori-gal-next{right:1rem;top:50%;transform:translateY(-50%)}
.ori-gal-lightbox .ori-gal-caption{position:absolute;left:0;right:0;bottom:0;color:#fff;background:rgba(0,0,0,.5);padding:.5rem 1rem}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,1px,1px);white-space:nowrap;border:0}';
    add_action('wp_head', function () use ($css) { echo "\n<style id=\"oriandras-galleries\">$css</style>\n"; });
}
add_action('wp_enqueue_scripts', 'ori_gal_enqueue');

function ori_gal_shortcode($atts) {
    $atts = shortcode_atts([
        'items' => 12,
        'category' => '',
        'tag' => '',
    ], $atts, 'oriandras-galleries');

    $args = [
        'post_type' => 'galleries',
        'posts_per_page' => max(1, (int)$atts['items']),
        'post_status' => 'publish',
        'no_found_rows' => true,
    ];
    if (!empty($atts['category'])) {
        $args['category_name'] = sanitize_title((string)$atts['category']);
    }
    if (!empty($atts['tag'])) {
        $args['tag'] = sanitize_title((string)$atts['tag']);
    }

    $q = new WP_Query($args);
    if (!$q->have_posts()) return '';

    // Lightbox container (single instance)
    $lightbox = '<div class="ori-gal-lightbox" role="dialog" aria-hidden="true" aria-label="Gallery viewer"><button class="ori-gal-close" aria-label="Close">×</button><button class="ori-gal-prev" aria-label="Previous">‹</button><img alt="" /><button class="ori-gal-next" aria-label="Next">›</button><div class="ori-gal-caption"></div></div>';

    $items_html = '';
    while ($q->have_posts()) {
        $q->the_post();
        $id = get_the_ID();
        $title = get_the_title();
        $img = get_the_post_thumbnail_url($id, 'large');
        if (!$img) continue;
        $permalink = get_permalink($id);
        $caption = wp_kses_post(get_the_excerpt() ?: '');
        $items_html .= '<div class="ori-gal-item">'
            . '<button type="button" class="ori-gal-open" aria-label="Open in viewer" data-src="' . esc_url($img) . '" data-title="' . esc_attr($title) . '" data-permalink="' . esc_url($permalink) . '" data-caption="' . esc_attr(wp_strip_all_tags($caption)) . '"><svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" focusable="false"><path d="M12 5c3.86 0 7.16 2.5 8.47 6-1.31 3.5-4.61 6-8.47 6s-7.16-2.5-8.47-6C4.84 7.5 8.14 5 12 5zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg></button>'
            . '<a href="' . esc_url($permalink) . '" class="ori-gal-link" aria-label="' . esc_attr($title) . '"><img src="' . esc_url($img) . '" alt="' . esc_attr($title) . '" /></a>'
            . '</div>';
    }
    wp_reset_postdata();

    // Inline JS (tiny, no dependency)
    $js = "(function(){var items=[];var current=-1;function collect(){items=[];document.querySelectorAll('.ori-gal-grid .ori-gal-item .ori-gal-open').forEach(function(btn){items.push({src:btn.dataset.src,title:btn.dataset.title||'',caption:btn.dataset.caption||'',permalink:btn.dataset.permalink||''});});}
function openAt(i){if(!items.length)return;current=(i+items.length)%items.length;var lb=document.querySelector('.ori-gal-lightbox');if(!lb)return;lb.setAttribute('aria-hidden','false');var img=lb.querySelector('img');if(img)img.src=items[current].src;var cap=lb.querySelector('.ori-gal-caption');if(cap)cap.textContent=items[current].title;}
function close(){var lb=document.querySelector('.ori-gal-lightbox');if(!lb)return;lb.setAttribute('aria-hidden','true');var img=lb.querySelector('img');if(img)img.src='';current=-1;}
function next(){if(items.length)openAt(current+1);}function prev(){if(items.length)openAt(current-1);} 
// Open button
 document.addEventListener('click',function(e){var b=e.target.closest('.ori-gal-open');if(!b)return;collect();var idx=Array.prototype.indexOf.call(document.querySelectorAll('.ori-gal-grid .ori-gal-item .ori-gal-open'),b);if(idx<0)idx=0;openAt(idx);});
 // Close
 document.addEventListener('click',function(e){if(e.target.closest('.ori-gal-close')){close();}});
 // Arrows
 document.addEventListener('click',function(e){if(e.target.closest('.ori-gal-next')){next();} else if(e.target.closest('.ori-gal-prev')){prev();}});
 // Keyboard
 document.addEventListener('keydown',function(e){var lb=document.querySelector('.ori-gal-lightbox');if(!lb||lb.getAttribute('aria-hidden')!=='false')return; if(e.key==='Escape'){close();} else if(e.key==='ArrowRight'){next();} else if(e.key==='ArrowLeft'){prev();}});
})();";

    add_action('wp_footer', function () use ($js) { echo "\n<script id=\"oriandras-galleries\">$js</script>\n"; });

    return $lightbox . '<div class="ori-gal-grid">' . $items_html . '</div>';
}
add_shortcode('oriandras-galleries', 'ori_gal_shortcode');

// Single template filter: provide simple template if theme lacks one
function ori_gal_template_include($template) {
    if (is_singular('galleries')) {
        // Build minimal HTML output via buffer
        add_filter('the_content', 'ori_gal_single_content_append');
    }
    return $template;
}
add_filter('template_include', 'ori_gal_template_include');

function ori_gal_single_content_append($content) {
    if (!is_singular('galleries')) return $content;
    global $post;
    $location = get_post_meta($post->ID, '_ori_gal_location', true);
    $shot_time = get_post_meta($post->ID, '_ori_gal_shot_time', true);
    $camera = get_post_meta($post->ID, '_ori_gal_camera', true);

    $meta_html = '<ul class="ori-gal-meta">';
    if ($location) $meta_html .= '<li><strong>' . esc_html__('Location:', 'oriandras-galleries') . '</strong> ' . esc_html($location) . '</li>';
    if ($shot_time) $meta_html .= '<li><strong>' . esc_html__('Shot time:', 'oriandras-galleries') . '</strong> ' . esc_html($shot_time) . '</li>';
    if ($camera) $meta_html .= '<li><strong>' . esc_html__('Camera:', 'oriandras-galleries') . '</strong> ' . esc_html($camera) . '</li>';
    $meta_html .= '</ul>';

    return $content . $meta_html;
}

// Load textdomain
add_action('init', function(){ load_plugin_textdomain('oriandras-galleries', false, dirname(plugin_basename(__FILE__)) . '/languages'); });
