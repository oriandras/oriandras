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
