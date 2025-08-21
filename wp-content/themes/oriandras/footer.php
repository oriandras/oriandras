<?php
/**
 * Site Footer Template
 *
 * Outputs the site footer with role="contentinfo" and enqueues footer hooks.
 *
 * @package Oriandras\Theme
 */
?>
<footer class="border-t border-slate-200 mt-12 mt-auto" role="contentinfo">
    <?php
    // Footer widgetized area (1–4 columns)
    $footer_sidebars = ['footer-1', 'footer-2', 'footer-3', 'footer-4'];
    $active = array_filter($footer_sidebars, function ($id) { return is_active_sidebar($id); });
    $count = count($active);

    if ($count > 0) :
        $grid = 'grid grid-cols-1 gap-8';
        if ($count === 2) {
            $grid .= ' md:grid-cols-2';
        } elseif ($count === 3) {
            $grid .= ' md:grid-cols-2 lg:grid-cols-3';
        } elseif ($count >= 4) {
            $grid .= ' md:grid-cols-2 lg:grid-cols-4';
        }
    ?>
    <div class="max-w-5xl mx-auto px-4 py-10 <?php echo esc_attr($grid); ?>">
        <?php foreach ($footer_sidebars as $id) : if (is_active_sidebar($id)) : ?>
            <div class="footer-widget-col space-y-4">
                <?php dynamic_sidebar($id); ?>
            </div>
        <?php endif; endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="border-t border-slate-200">
        <div class="max-w-5xl mx-auto px-4 py-8 text-sm text-center">
            <?php
            $current_year = (int) date('Y');
            $start_year   = 2014;
            $host         = parse_url(home_url('/'), PHP_URL_HOST);
            ?>
            <p>&copy; <?php echo esc_html($start_year); ?> - <?php echo esc_html($current_year); ?> <?php echo esc_html($host ?: get_bloginfo('name')); ?>. <?php echo esc_html(__('All rights reserved!', 'oriandras')); ?></p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
