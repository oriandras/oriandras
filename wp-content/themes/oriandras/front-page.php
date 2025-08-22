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
    <!-- Intro/Optional content section (widgetized) -->
    <?php if (is_active_sidebar('front-coming-soon')) : ?>
        <section id="front-intro" class="mb-10">
            <div id="front-coming-soon-area" class="w-full">
                <?php dynamic_sidebar('front-coming-soon'); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- 3-column layout: content + sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Content column: 10 latest blog posts -->
        <div id="front-col-content" class="lg:col-span-8">
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

            <?php
            // Conditional: On this day (previous years) block — only if the Same Day Archive plugin is active and has content today
            if (function_exists('ori_sameday_build_query_args')) {
                // Build query args for today (site timezone), previous years only; limit to, say, 10 entries for performance
                $sda_args = ori_sameday_build_query_args([
                    'limit'     => 10,
                    'post_type' => 'post',
                    'order'     => 'DESC',
                    'orderby'   => 'date',
                ]);
                // Minor perf: avoid total rows
                $sda_args['no_found_rows'] = true;

                $onthisday_q = new WP_Query($sda_args);
                if ($onthisday_q->have_posts()) {
                    ?>
                    <section class="mt-10">
                        <header class="mb-4">
                            <h2 class="text-2xl font-extrabold tracking-tight"><?php echo esc_html__('On this day', 'oriandras'); ?></h2>
                        </header>
                        <div class="grid gap-6">
                            <?php while ($onthisday_q->have_posts()) : $onthisday_q->the_post(); ?>
                                <?php get_template_part('template-parts/content', 'card'); ?>
                            <?php endwhile; ?>
                        </div>
                    </section>
                    <?php
                }
                wp_reset_postdata();
            }
            ?>
        </div>


        <!-- Right column: Sidebar (same as page.php and single.php) -->
         <div id="front-col-sidebar" class="lg:col-span-4">
             <?php get_sidebar(); ?>
         </div>
     </div>
 </main>
 
 <?php get_footer(); ?>
