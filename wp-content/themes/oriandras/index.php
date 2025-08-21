<?php
get_header();
?>

<main id="primary" role="main" class="prose prose-slate max-w-3xl mx-auto px-4 py-10">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('mb-12'); ?>>
                <header class="mb-6">
                    <h1 class="!mb-2 text-3xl font-extrabold tracking-tight">
                        <a href="<?php the_permalink(); ?>" class="no-underline hover:underline"><?php the_title(); ?></a>
                    </h1>
                    <p class="text-sm text-slate-500">
                        <?php echo get_the_date(); ?>
                        <?php if (get_the_category()) : ?>
                            · <?php the_category(', '); ?>
                        <?php endif; ?>
                    </p>
                </header>
                <div class="entry-content">
                    <?php if (is_singular()) : ?>
                        <?php the_content(); ?>
                    <?php else : ?>
                        <?php the_excerpt(); ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endwhile; ?>

        <nav class="flex items-center justify-between py-8">
            <div class="">
                <?php previous_posts_link('&larr; Newer Posts'); ?>
            </div>
            <div class="">
                <?php next_posts_link('Older Posts &rarr;'); ?>
            </div>
        </nav>

    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
