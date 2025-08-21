<?php
/**
 * Site Header Template
 *
 * Outputs the global document structure head and the site header with:
 * - Skip link, proper landmark roles and ARIA labelling.
 * - Primary navigation (desktop) and accessible mobile off-canvas menu.
 *
 * @package Oriandras\Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('antialiased overflow-x-hidden min-h-screen flex flex-col'); ?>>
<a class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-white focus:text-blue-700 focus:px-3 focus:py-2 focus:rounded" href="#primary">Skip to content</a>
<header id="site-header" class="border-b border-slate-200" role="banner">
    <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 flex-1 md:flex-none">
            <button id="nav-toggle" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600" aria-controls="mobile-nav" aria-expanded="false" aria-label="Open menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center ml-auto md:ml-0">
                <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
                    <?php
                    $logo_id = get_theme_mod('custom_logo');
                    $home_url = esc_url(home_url('/'));
                    $site_name = esc_attr(get_bloginfo('name'));
                    if ($logo_id) {
                        $logo_img = wp_get_attachment_image($logo_id, 'oriandras-logo', false, [
                            'class' => 'custom-logo h-auto max-h-20',
                            'alt'   => $site_name,
                        ]);
                        echo '<a href="' . $home_url . '" class="custom-logo-link" rel="home">' . $logo_img . '</a>';
                    }
                    ?>
                <?php else : ?>
                    <a class="font-bold text-xl tracking-tight" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (has_nav_menu('primary')) : ?>
            <nav aria-label="Primary" class="hidden md:block">
                <?php wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'menu flex items-center gap-6 text-sm font-medium',
                    'fallback_cb'    => false,
                    'walker'         => new Oriandras_Nav_Walker(),
                    'depth'          => 3,
                ]); ?>
            </nav>
        <?php endif; ?>
    </div>
</header>

<!-- Mobile off-canvas menu -->
<div id="nav-overlay" class="fixed inset-0 bg-black/40 hidden z-40" aria-hidden="true"></div>
<aside id="mobile-nav" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full transform bg-white shadow-xl transition-transform duration-300 ease-in-out will-change-transform overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="mobile-nav-title" aria-hidden="true">
    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
        <span id="mobile-nav-title" class="font-semibold"><?php bloginfo('name'); ?></span>
        <button id="nav-close" class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600" aria-label="Close menu">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav aria-label="Mobile" class="p-2">
        <?php if (has_nav_menu('primary')) : ?>
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'space-y-1 text-slate-800',
                'fallback_cb'    => false,
                'walker'         => new Oriandras_Nav_Walker(),
                'depth'          => 3,
            ]); ?>
        <?php endif; ?>
    </nav>
</aside>
