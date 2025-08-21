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
<aside role="complementary" aria-label="Primary Sidebar" class="space-y-6">
    <?php if (is_active_sidebar('primary-sidebar')) : ?>
        <?php dynamic_sidebar('primary-sidebar'); ?>
    <?php else : ?>
        <section class="widget">
            <h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-2">Sidebar</h2>
            <p class="text-sm text-slate-600">Add widgets to the Primary Sidebar in Appearance → Widgets.</p>
        </section>
    <?php endif; ?>
</aside>
