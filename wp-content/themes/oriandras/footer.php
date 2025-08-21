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
    <div class="max-w-5xl mx-auto px-4 py-10 text-sm">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
