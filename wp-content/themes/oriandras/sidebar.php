<?php
/**
 * Sidebar template
 *
 * Displays widgets assigned to the Primary Sidebar.
 *
 * @package Oriandras\Theme
 * @since 0.1.0
 */
?>
<aside id="primary-sidebar-area" role="complementary" aria-label="<?php echo esc_attr__( 'Primary Sidebar', 'oriandras' ); ?>" class="space-y-6">
    <?php if (is_active_sidebar('primary-sidebar')) : ?>
        <?php dynamic_sidebar('primary-sidebar'); ?>
    <?php else : ?>
        <section class="widget">
            <h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-2"><?php echo esc_html__( 'Sidebar', 'oriandras' ); ?></h2>
            <p class="text-sm text-slate-600"><?php echo esc_html__( 'Add widgets to the Primary Sidebar in Appearance → Widgets.', 'oriandras' ); ?></p>
        </section>
    <?php endif; ?>
</aside>
