<?php
/**
 * Theme Functions for Oriandras
 *
 * This file bootstraps theme features, registers navigation menus, and enqueues
 * front-end assets (styles and scripts). It follows WordPress best practices
 * for initialization via action hooks and uses cache-busting based on the
 * last-modified time of built assets when available.
 *
 * @package Oriandras\Theme
 * @since 1.0.0
 */

// -----------------------------------------------------------------------------
// Includes
// -----------------------------------------------------------------------------

/**
 * Custom Nav Walker include.
 *
 * Loads a custom Walker_Nav_Menu implementation used to render the primary
 * navigation markup with the expected utility classes and structure.
 *
 * Expected class: Oriandras\NavWalker or compatible Walker_Nav_Menu subclass
 * File: /inc/NavWalker.php
 */
require_once get_template_directory() . '/inc/NavWalker.php';

// -----------------------------------------------------------------------------
// Theme setup
// -----------------------------------------------------------------------------

/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * Hook: after_setup_theme
 *
 * - Enables dynamic document titles (title-tag).
 * - Enables post thumbnails for posts and pages.
 * - Switches several core components to valid HTML5 markup
 *   (search form, comment form, comment list, gallery, caption, style, script).
 * - Adds support for responsive embeds.
 * - Registers navigation menus (currently: primary).
 *
 * This callback runs early in the load cycle and should not rely on front-end
 * assets or the query being available.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Custom Logo with cropping support
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 120,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => ['site-title', 'site-description'],
    ]);

    // Logo image size: height-capped at 80px, width auto (no hard crop)
    // Using a very large width and fixed height ensures WordPress generates a constrained-height rendition preserving aspect ratio.
    add_image_size('oriandras-logo', 9999, 80, false);

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Add support for wide and full alignments in Gutenberg
    add_theme_support('align-wide');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'oriandras'),
    ]);
});

// -----------------------------------------------------------------------------
// Widgets / Sidebars
// -----------------------------------------------------------------------------

/**
 * Register theme sidebars.
 */
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => __('Primary Sidebar', 'oriandras'),
        'id'            => 'primary-sidebar',
        'description'   => __('Widgets in this area will be shown in the third column on single posts.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-6">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-2">',
        'after_title'   => '</h2>',
    ]);

    // Comments section sidebar (third column in comments area)
    register_sidebar([
        'name'          => __('Comments Sidebar', 'oriandras'),
        'id'            => 'comments-sidebar',
        'description'   => __('Widgets shown in the third column of the comments section.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-6">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-2">',
        'after_title'   => '</h2>',
    ]);

    // Mega Menu left column widget area
    register_sidebar([
        'name'          => __('Mega Menu Widget', 'oriandras'),
        'id'            => 'mega-menu',
        'description'   => __('Widgets placed here will appear as the first column in the mega-menu. Leave empty to hide.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-4">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">',
        'after_title'   => '</h2>',
    ]);

    // Footer widget areas (1–4 columns)
    register_sidebar([
        'name'          => __('Footer 1', 'oriandras'),
        'id'            => 'footer-1',
        'description'   => __('First column in the footer widget area.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-3">',
        'after_title'   => '</h2>',
    ]);
    register_sidebar([
        'name'          => __('Footer 2', 'oriandras'),
        'id'            => 'footer-2',
        'description'   => __('Second column in the footer widget area.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-3">',
        'after_title'   => '</h2>',
    ]);
    register_sidebar([
        'name'          => __('Footer 3', 'oriandras'),
        'id'            => 'footer-3',
        'description'   => __('Third column in the footer widget area.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-3">',
        'after_title'   => '</h2>',
    ]);
    register_sidebar([
        'name'          => __('Footer 4', 'oriandras'),
        'id'            => 'footer-4',
        'description'   => __('Fourth column in the footer widget area.', 'oriandras'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-3">',
        'after_title'   => '</h2>',
    ]);
});

// -----------------------------------------------------------------------------
// Enqueue styles and scripts
// -----------------------------------------------------------------------------

/**
 * Enqueue front-end styles and scripts with cache-busting.
 *
 * Hook: wp_enqueue_scripts
 *
 * Strategy:
 * - Uses the compiled Tailwind stylesheet found at /dist/app.css.
 *   If the file exists, its filemtime is used as the version for cache-busting.
 *   Otherwise, falls back to the theme version as reported by wp_get_theme().
 * - Enqueues a navigation script (/assets/js/nav.js) for the mobile drawer and
 *   swipe interactions, also versioned by filemtime when available and loaded
 *   in the footer for performance.
 *
 * Handles:
 * - Style: oriandras-app
 * - Script: oriandras-nav
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function () {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // Prefer compiled Tailwind output if present; fall back to source CSS for dev; last resort: style.css
    $dist_rel   = '/dist/app.css';
    $assets_rel = '/assets/css/tailwind.css';

    $dist_file   = $theme_dir . $dist_rel;
    $assets_file = $theme_dir . $assets_rel;

    $theme_version = wp_get_theme()->get('Version');

    if (file_exists($dist_file)) {
        $css_uri  = $theme_uri . $dist_rel;
        $css_ver  = filemtime($dist_file) ?: $theme_version;
        wp_enqueue_style('oriandras-app', $css_uri, [], $css_ver);
    } elseif (file_exists($assets_file)) {
        $css_uri  = $theme_uri . $assets_rel;
        $css_ver  = filemtime($assets_file) ?: $theme_version;
        wp_enqueue_style('oriandras-tailwind', $css_uri, [], $css_ver);
    } else {
        // Fallback to the theme's style.css so basic styles still load
        wp_enqueue_style('oriandras-style', get_stylesheet_uri(), [], $theme_version);
    }

    // Nav JS for mobile drawer and swipe
    $js_rel_path = '/assets/js/nav.js';
    $js_file     = $theme_dir . $js_rel_path;
    $js_uri      = $theme_uri . $js_rel_path;
    $js_version  = file_exists($js_file) ? filemtime($js_file) : $theme_version;
    wp_enqueue_script('oriandras-nav', $js_uri, [], $js_version, true);
});

// -----------------------------------------------------------------------------
// Admin bar adjustment for sticky footer (avoid tiny scroll when admin bar shows)
// -----------------------------------------------------------------------------
add_action('wp_head', function () {
    if (!is_admin_bar_showing()) {
        return;
    }
    // When the admin bar is visible, 100vh includes the area under the admin bar.
    // Because our body uses min-h-screen (100vh), this creates a small overflow.
    // We reduce the body's min-height by the admin bar height (32px desktop, 46px small screens).
    echo "\n<style id=\"oriandras-adminbar-fixer\">\n" .
         "body.admin-bar{min-height:calc(100vh - 32px);}\n" .
         "@media screen and (max-width:782px){body.admin-bar{min-height:calc(100vh - 46px);}}\n" .
         "</style>\n";
});

// -----------------------------------------------------------------------------
// Customizer: Theme Colors (Main & Accent)
// -----------------------------------------------------------------------------

/**
 * Tailwind default color palette (single representative shade per color).
 * We use the 600 shade as a good middle-ground for UI accents.
 * Returns [ slug => [label, hex] ].
 */
function oriandras_tailwind_color_choices() {
    return [
        'white'   => ['White',   '#ffffff'], // tailwind white
        'black'   => ['Black',   '#000000'], // tailwind black
        'slate'   => ['Slate',   '#475569'], // slate-600
        'gray'    => ['Gray',    '#4b5563'], // gray-600
        'zinc'    => ['Zinc',    '#52525b'], // zinc-600
        'neutral' => ['Neutral', '#525252'], // neutral-600
        'stone'   => ['Stone',   '#57534e'], // stone-600
        'red'     => ['Red',     '#dc2626'], // red-600
        'orange'  => ['Orange',  '#ea580c'], // orange-600
        'amber'   => ['Amber',   '#d97706'], // amber-600
        'yellow'  => ['Yellow',  '#ca8a04'], // yellow-600
        'lime'    => ['Lime',    '#65a30d'], // lime-600
        'green'   => ['Green',   '#16a34a'], // green-600
        'emerald' => ['Emerald', '#059669'], // emerald-600
        'teal'    => ['Teal',    '#0d9488'], // teal-600
        'cyan'    => ['Cyan',    '#0891b2'], // cyan-600
        'sky'     => ['Sky',     '#0284c7'], // sky-600
        'blue'    => ['Blue',    '#2563eb'], // blue-600
        'indigo'  => ['Indigo',  '#4f46e5'], // indigo-600
        'violet'  => ['Violet',  '#7c3aed'], // violet-600
        'purple'  => ['Purple',  '#9333ea'], // purple-600
        'fuchsia' => ['Fuchsia', '#c026d3'], // fuchsia-600
        'pink'    => ['Pink',    '#db2777'], // pink-600
        'rose'    => ['Rose',    '#e11d48'], // rose-600
    ];
}

/**
 * Sanitize color choice to allowed slugs only.
 */
function oriandras_sanitize_color_choice($value) {
    $choices = oriandras_tailwind_color_choices();
    return array_key_exists($value, $choices) ? $value : 'blue';
}

/**
 * Sanitize back-to-top background choice. Allows palette slugs or the special 'accent'.
 */
function oriandras_sanitize_btt_bg($value) {
    $choices = oriandras_tailwind_color_choices();
    if ($value === 'accent') return 'accent';
    return array_key_exists($value, $choices) ? $value : 'accent';
}

/**
 * Sanitize back-to-top foreground (text) choice. Allows palette slugs or 'auto'.
 */
function oriandras_sanitize_btt_fg($value) {
    $choices = oriandras_tailwind_color_choices();
    if ($value === 'auto') return 'auto';
    return array_key_exists($value, $choices) ? $value : 'auto';
}

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
    // Section
    $wp_customize->add_section('oriandras_theme_colors', [
        'title'    => __('Theme Colors', 'oriandras'),
        'priority' => 30,
    ]);

    $choices = oriandras_tailwind_color_choices();
    $choices_labels = [];
    foreach ($choices as $slug => $data) {
        $choices_labels[$slug] = $data[0];
    }

    // Main color
    $wp_customize->add_setting('oriandras_main_color', [
        'default'           => 'slate',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_main_color', [
        'label'       => __('Main color', 'oriandras'),
        'description' => __('Used for titles, emphasis, and blockquote border.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Accent color
    $wp_customize->add_setting('oriandras_accent_color', [
        'default'           => 'blue',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_accent_color', [
        'label'       => __('Accent color', 'oriandras'),
        'description' => __('Used for links and accents.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Body background color
    $wp_customize->add_setting('oriandras_body_bg_color', [
        'default'           => 'white',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_body_bg_color', [
        'label'       => __('Body background', 'oriandras'),
        'description' => __('Background color for the site body.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Body text/url color
    $wp_customize->add_setting('oriandras_body_fg_color', [
        'default'           => 'slate',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_body_fg_color', [
        'label'       => __('Body text & links', 'oriandras'),
        'description' => __('Text and link color for the site body.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Header background color
    $wp_customize->add_setting('oriandras_header_bg_color', [
        'default'           => 'white',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_header_bg_color', [
        'label'       => __('Header background', 'oriandras'),
        'description' => __('Background color for the site header (role="banner").', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Header text/url color
    $wp_customize->add_setting('oriandras_header_fg_color', [
        'default'           => 'slate',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_header_fg_color', [
        'label'       => __('Header text & links', 'oriandras'),
        'description' => __('Text and link color in the site header.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Navbar background color (mobile menu)
    $wp_customize->add_setting('oriandras_nav_bg_color', [
        'default'           => 'white',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_nav_bg_color', [
        'label'       => __('Mobile nav background', 'oriandras'),
        'description' => __('Background color for the mobile off-canvas menu.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Navbar text/url color (mobile menu)
    $wp_customize->add_setting('oriandras_nav_fg_color', [
        'default'           => 'slate',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_nav_fg_color', [
        'label'       => __('Mobile nav text & links', 'oriandras'),
        'description' => __('Text and link color in the mobile off-canvas menu.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Footer background color
    $wp_customize->add_setting('oriandras_footer_bg_color', [
        'default'           => 'white',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_footer_bg_color', [
        'label'       => __('Footer background', 'oriandras'),
        'description' => __('Background color for the site footer.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Footer text/url color
    $wp_customize->add_setting('oriandras_footer_fg_color', [
        'default'           => 'slate',
        'sanitize_callback' => 'oriandras_sanitize_color_choice',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('oriandras_footer_fg_color', [
        'label'       => __('Footer text & links', 'oriandras'),
        'description' => __('Text and link color in the footer.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $choices_labels,
    ]);

    // Back-to-top button colors
    $wp_customize->add_setting('oriandras_btt_bg', [
        'default'           => 'accent',
        'sanitize_callback' => 'oriandras_sanitize_btt_bg',
        'transport'         => 'refresh',
    ]);
    $btt_bg_choices = array_merge(['accent' => __('Use Accent color', 'oriandras')], $choices_labels);
    $wp_customize->add_control('oriandras_btt_bg', [
        'label'       => __('Back-to-top background', 'oriandras'),
        'description' => __('Background color for the scroll-to-top button. Default uses the Accent color.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $btt_bg_choices,
    ]);

    $wp_customize->add_setting('oriandras_btt_fg', [
        'default'           => 'auto',
        'sanitize_callback' => 'oriandras_sanitize_btt_fg',
        'transport'         => 'refresh',
    ]);
    $btt_fg_choices = array_merge(['auto' => __('Auto (contrast)', 'oriandras')], $choices_labels);
    $wp_customize->add_control('oriandras_btt_fg', [
        'label'       => __('Back-to-top text color', 'oriandras'),
        'description' => __('Text/icon color for the scroll-to-top button.', 'oriandras'),
        'section'     => 'oriandras_theme_colors',
        'type'        => 'select',
        'choices'     => $btt_fg_choices,
    ]);
});

/**
 * Output inline CSS for main, accent, and navbar colors.
 */
add_action('wp_head', function () {
    $choices = oriandras_tailwind_color_choices();
    $main_slug      = get_theme_mod('oriandras_main_color', 'slate');
    $accent_slug    = get_theme_mod('oriandras_accent_color', 'blue');
    $body_bg_slug   = get_theme_mod('oriandras_body_bg_color', 'white');
    $body_fg_slug   = get_theme_mod('oriandras_body_fg_color', 'slate');
    $header_bg_slug = get_theme_mod('oriandras_header_bg_color', 'white');
    $header_fg_slug = get_theme_mod('oriandras_header_fg_color', 'slate');
    $nav_bg_slug    = get_theme_mod('oriandras_nav_bg_color', 'white');
    $nav_fg_slug    = get_theme_mod('oriandras_nav_fg_color', 'slate');
    $footer_bg_slug = get_theme_mod('oriandras_footer_bg_color', 'white');
    $footer_fg_slug = get_theme_mod('oriandras_footer_fg_color', 'slate');
    $btt_bg_choice  = get_theme_mod('oriandras_btt_bg', 'accent'); // 'accent' or palette slug
    $btt_fg_choice  = get_theme_mod('oriandras_btt_fg', 'auto');   // 'auto' or palette slug

    $main_hex      = $choices[$main_slug][1]       ?? '#475569'; // slate-600
    $accent_hex    = $choices[$accent_slug][1]     ?? '#2563eb'; // blue-600
    $body_bg_hex   = $choices[$body_bg_slug][1]    ?? '#ffffff'; // white
    $body_fg_hex   = $choices[$body_fg_slug][1]    ?? '#475569'; // slate-600
    $header_bg_hex = $choices[$header_bg_slug][1]  ?? '#ffffff'; // white
    $header_fg_hex = $choices[$header_fg_slug][1]  ?? '#475569'; // slate-600
    $nav_bg_hex    = $choices[$nav_bg_slug][1]     ?? '#ffffff'; // white
    $nav_fg_hex    = $choices[$nav_fg_slug][1]     ?? '#475569'; // slate-600
    $footer_bg_hex = $choices[$footer_bg_slug][1]  ?? '#ffffff'; // white
    $footer_fg_hex = $choices[$footer_fg_slug][1]  ?? '#475569'; // slate-600

    // Back-to-top color resolution
    $btt_bg_hex = ($btt_bg_choice === 'accent') ? $accent_hex : ($choices[$btt_bg_choice][1] ?? $accent_hex);
    // Compute auto contrast if needed
    if ($btt_fg_choice === 'auto') {
        $hex = ltrim($btt_bg_hex, '#');
        if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // perceived brightness 0..255
        $brightness = (0.299*$r + 0.587*$g + 0.114*$b);
        $btt_fg_hex = ($brightness > 140) ? '#000000' : '#ffffff';
    } else {
        $btt_fg_hex = $choices[$btt_fg_choice][1] ?? '#ffffff';
    }

    // Inline CSS at a later priority so it overrides base CSS
    echo "\n<style id=\"oriandras-theme-colors\">\n";
    echo ":root{--ori-main: {$main_hex}; --ori-accent: {$accent_hex}; --ori-body-bg: {$body_bg_hex}; --ori-body-fg: {$body_fg_hex}; --ori-header-bg: {$header_bg_hex}; --ori-header-fg: {$header_fg_hex}; --ori-nav-bg: {$nav_bg_hex}; --ori-nav-fg: {$nav_fg_hex}; --ori-footer-bg: {$footer_bg_hex}; --ori-footer-fg: {$footer_fg_hex}; --ori-btt-bg: {$btt_bg_hex}; --ori-btt-fg: {$btt_fg_hex};}\n";

    // Links (accent) - base
    echo "a{color: var(--ori-accent);} a:hover{color: var(--ori-accent);} \n";

    // Titles and emphasis (main)
    echo "h1,h2,h3,h4,h5,h6{color: var(--ori-main);} em{color: var(--ori-main);} \n";

    // Blockquote left border (main)
    echo "blockquote{border-left: 4px solid var(--ori-main); padding-left: 1rem;}\n";

    // Body styles
    echo "body{background-color: var(--ori-body-bg); color: var(--ori-body-fg);}\n";
    echo "body a, body a:hover, body a:focus{color: var(--ori-body-fg);}\n";

    // Header styles (desktop header area)
    echo "header[role=\\\"banner\\\"], #site-header{background-color: var(--ori-header-bg); color: var(--ori-header-fg);}\n";
    echo "header[role=\\\"banner\\\"] *, header[role=\\\"banner\\\"] a, header[role=\\\"banner\\\"] a:hover, header[role=\\\"banner\\\"] a:focus, #site-header *, #site-header a, #site-header a:hover, #site-header a:focus{color: var(--ori-header-fg);}\n";

    // Mobile menu styles
    echo "#mobile-nav{background-color: var(--ori-nav-bg); color: var(--ori-nav-fg);}\n";
    echo "#mobile-nav *, #mobile-nav a, #mobile-nav a:hover, #mobile-nav a:focus{color: var(--ori-nav-fg);}\n";

    // Footer styles
    echo "footer[role=\\\"contentinfo\\\"], footer{background-color: var(--ori-footer-bg); color: var(--ori-footer-fg);}\n";
    echo "footer[role=\\\"contentinfo\\\"] *, footer *, footer a, footer a:hover, footer a:focus{color: var(--ori-footer-fg);}\n";

    // Back-to-top styles
    echo "#back-to-top{background-color: var(--ori-btt-bg); color: var(--ori-btt-fg); --tw-ring-color: var(--ori-btt-bg);}\n";

    echo "</style>\n";
}, 100);

// -----------------------------------------------------------------------------
// Editor Metabox: Header block visibility toggle
// -----------------------------------------------------------------------------

/**
 * Register the post meta in REST (optional, for future extensibility) and add the metabox.
 */
add_action('init', function () {
    // Register post meta so it’s recognized and can be exposed to REST if needed.
    if (function_exists('register_post_meta')) {
        register_post_meta('', '_ori_hide_header_block', [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string', // stores '1' when hidden
            'auth_callback' => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_post', $post_id);
            },
        ]);
    }
});

add_action('add_meta_boxes', function () {
    $screen_types = ['post', 'page'];
    foreach ($screen_types as $screen) {
        add_meta_box(
            'oriandras_header_visibility',
            __('Header Visibility', 'oriandras'),
            'oriandras_render_header_visibility_metabox',
            $screen,
            'side',
            'default'
        );
    }
});

/**
 * Render the checkbox metabox to toggle the header block visibility.
 *
 * @param WP_Post $post
 * @return void
 */
function oriandras_render_header_visibility_metabox($post)
{
    $value = get_post_meta($post->ID, '_ori_hide_header_block', true);
    $checked = ($value === '1');
    wp_nonce_field('oriandras_hide_header_block_nonce', 'oriandras_hide_header_block_nonce_field');
    echo '<p><label for="oriandras_hide_header_block">';
    echo '<input type="checkbox" id="oriandras_hide_header_block" name="oriandras_hide_header_block" value="1"' . checked($checked, true, false) . ' /> ';
    echo esc_html__('Hide header (title, author and dates)', 'oriandras');
    echo '</label></p>';
    echo '<p class="description">' . esc_html__('When checked, the header block is hidden on the front-end for this post/page.', 'oriandras') . '</p>';
}

/**
 * Save handler for the header visibility checkbox.
 * Stores '1' when checked; removes the meta when unchecked.
 */
add_action('save_post', function ($post_id, $post, $update) {
    // Security: nonce check
    if (!isset($_POST['oriandras_hide_header_block_nonce_field']) || !wp_verify_nonce($_POST['oriandras_hide_header_block_nonce_field'], 'oriandras_hide_header_block_nonce')) {
        return;
    }

    // Do not run on autosave/revisions/auto-drafts
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

    // Permission check
    if (!current_user_can('edit_post', $post_id)) return;

    // Only handle for our target post types
    if (!in_array($post->post_type, ['post', 'page'], true)) return;

    // Read checkbox
    $is_checked = isset($_POST['oriandras_hide_header_block']) && $_POST['oriandras_hide_header_block'] === '1';

    if ($is_checked) {
        update_post_meta($post_id, '_ori_hide_header_block', '1');
    } else {
        delete_post_meta($post_id, '_ori_hide_header_block');
    }
}, 10, 3);


// -----------------------------------------------------------------------------
// Admin notice: Required plugins check
// -----------------------------------------------------------------------------
add_action('admin_notices', function () {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // We need the is_plugin_active function.
    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $required_plugins = [
        // slug => [Human Name, main file]
        'same-day-archive/same-day-archive.php' => 'Same Day Archive (Previous Years)',
        'oriandras-stale-content-alert/oriandras-stale-content-alert.php' => 'Stale Content Alert',
    ];

    $inactive = [];
    foreach ($required_plugins as $main_file => $label) {
        if (!is_plugin_active($main_file)) {
            $inactive[] = $label;
        }
    }

    if (!empty($inactive)) {
        $plugins_url = admin_url('plugins.php');
        $theme_name  = wp_get_theme()->get('Name');
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>' . esc_html($theme_name) . ':</strong> ';
        echo esc_html__('The following required plugins are not active:', 'oriandras') . ' ';
        echo esc_html(implode(', ', $inactive)) . '. ';
        echo '<a href="' . esc_url($plugins_url) . '">' . esc_html__('Go to Plugins to activate', 'oriandras') . '</a>.';
        echo '</p></div>';
    }
});
