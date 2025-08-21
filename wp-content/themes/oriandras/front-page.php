<?php
/**
 * Front Page Template
 *
 * Layout:
 *  - Intro Section: Placeholder for optional content (currently shows a coming soon message).
 *  - 3-column grid (on large screens):
 *      left  = empty (lg: 3/12)
 *      middle= latest 10 blog posts (lg: 6/12)
 *      right = widgetized sidebar (same as page.php and single.php) (lg: 3/12)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
 * @package Oriandras\Theme
 * @since 0.1.0
 */

global $wp_query;

get_header();
?>

<main id="primary" role="main" class="max-w-5xl mx-auto px-4 py-10">
    <!-- Intro/Optional content section -->
    <section class="mb-10">
        <div class="rounded border border-slate-200 bg-white/50 p-6">
            <h2 class="text-xl font-semibold mb-2">Coming soon</h2>
            <p class="text-slate-700">This section is reserved for customizable front page content.</p>
        </div>
    </section>

    <!-- 3-column layout: left empty, middle list, right sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left column (empty) -->
        <div class="hidden lg:block lg:col-span-3"></div>

        <!-- Middle column: 10 latest blog posts -->
        <div class="lg:col-span-6">
            <header class="mb-4">
                <h1 class="text-2xl font-extrabold tracking-tight">Latest Posts</h1>
            </header>

            <?php
            $front_query = new WP_Query([
                'post_type'           => 'post',
                'posts_per_page'      => 10,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
            ]);
            ?>

            <?php if ($front_query->have_posts()) : ?>
                <div class="grid gap-6">
                    <?php while ($front_query->have_posts()) : $front_query->the_post(); ?>
                        <?php get_template_part('template-parts/content', 'card'); ?>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p class="text-slate-700">No posts found.</p>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>

        <!-- Right column: Sidebar (same as page.php and single.php) -->
        <div class="lg:col-span-3">
            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
