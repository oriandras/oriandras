<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white text-slate-800 antialiased'); ?>>
<a class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-white focus:text-blue-700 focus:px-3 focus:py-2 focus:rounded" href="#primary">Skip to content</a>
<header class="border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4 py-6 flex items-center justify-between">
        <a class="font-bold text-xl tracking-tight" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <?php if (has_nav_menu('primary')) : ?>
            <nav aria-label="Primary" class="">
                <?php wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex gap-6 text-sm font-medium',
                    'fallback_cb'    => false,
                ]); ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
