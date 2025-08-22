<?php
/**
 * Site Footer Template
 *
 * Outputs the site footer with role="contentinfo" and enqueues footer hooks.
 *
 * @package Oriandras\Theme
 */
?>
<footer id="site-footer" class="border-t border-slate-200 mt-12 mt-auto" role="contentinfo">
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
    <div id="footer-widgets" class="max-w-5xl mx-auto px-4 py-10 <?php echo esc_attr($grid); ?>">
        <?php foreach ($footer_sidebars as $id) : if (is_active_sidebar($id)) : ?>
            <?php $num = (int) filter_var($id, FILTER_SANITIZE_NUMBER_INT); ?>
            <div id="footer-col-<?php echo (int) $num; ?>" class="footer-widget-col space-y-4">
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
</div>

<button id="back-to-top" type="button" aria-label="<?php echo esc_attr(__('Back to top', 'oriandras')); ?>" class="fixed z-50 bottom-6 right-6 inline-flex items-center justify-center rounded-full shadow-lg h-12 w-12 transform transition-all duration-300 ease-out opacity-0 translate-y-2 pointer-events-none hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2" style="--tw-ring-color: var(--ori-btt-bg); background-color: var(--ori-btt-bg); color: var(--ori-btt-fg);">
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
