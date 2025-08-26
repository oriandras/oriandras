<?php
/**
 * Archive Template
 *
 * Implements the new design for Category and Tag views.
 * Layout mirrors front-page.php: content (8/12) + primary sidebar (4/12).
 *
 * Left column:
 *  - First block: taxonomy title (category or tag) and taxonomy description.
 *  - Posts loop: each post rendered with content-card.php template part.
 *  - Pagination at the end.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Oriandras\Theme
 */

get_header();
?>

<main id="primary" role="main" class="max-w-5xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Archive content -->
        <div id="archive-col-content" class="lg:col-span-8">
            <?php
            // First block: Taxonomy title + description (only for term archives)
            if (is_category() || is_tag() || is_tax()) :
                $term = get_queried_object();
                $term_title = $term ? $term->name : get_the_archive_title();
                $term_desc  = term_description();
                ?>
                <section class="mb-6 bg-white/60 shadow-sm p-4">
                    <h1 class="text-2xl font-extrabold tracking-tight">
                        <?php echo esc_html($term_title); ?>
                    </h1>
                    <?php if (!empty($term_desc)) : ?>
                        <div class="mt-2 prose prose-slate max-w-none">
                            <?php echo wp_kses_post($term_desc); ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php else : ?>
                <header class="mb-4">
                    <h1 class="text-2xl font-extrabold tracking-tight"><?php echo esc_html(get_the_archive_title()); ?></h1>
                    <?php if ($desc = get_the_archive_description()) : ?>
                        <div class="mt-2 prose prose-slate max-w-none"><?php echo wp_kses_post($desc); ?></div>
                    <?php endif; ?>
                </header>
            <?php endif; ?>

            <?php if (have_posts()) : ?>
                <div class="grid gap-6">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/content', 'card'); ?>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <nav class="mt-8" aria-label="Posts Navigation">
                    <?php
                    the_posts_pagination([
                        'mid_size'  => 1,
                        'prev_text' => __('Newer', 'oriandras'),
                        'next_text' => __('Older', 'oriandras'),
                        'screen_reader_text' => __('Posts navigation', 'oriandras'),
                    ]);
                    ?>
                </nav>

            <?php else : ?>
                <p class="text-slate-700"><?php echo esc_html__('No posts found.', 'oriandras'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Right: Primary Sidebar (same sizing as front-page.php) -->
        <div id="archive-col-sidebar" class="lg:col-span-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
