<?php

// Theme setup
add_action('after_setup_theme', function () {
    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'oriandras'),
    ]);
});

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', function () {
    $theme_dir    = get_template_directory();
    $theme_uri    = get_template_directory_uri();
    $css_rel_path = '/dist/app.css';

    $css_file = $theme_dir . $css_rel_path;
    $css_uri  = $theme_uri . $css_rel_path;

    $version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');

    // Main stylesheet compiled by Tailwind CLI
    wp_enqueue_style('oriandras-app', $css_uri, [], $version);
});
