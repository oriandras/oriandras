<?php
/**
 * Template Name: Full Width Content
 * Template Post Type: page
 *
 * A full-width page template where the header (title, author, dates) is shown first,
 * then the page content, and after that the comments section. No sidebar, and no
 * max-width constraint on the main container.
 *
 * @package Oriandras\Theme
 * @since 0.1.0
 */

global $post;

get_header();
?>

<main id="primary" role="main" class="w-full px-4 py-10">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php
        // Author data
        $author_id   = get_the_author_meta('ID');
        $author_name = get_the_author();
        $author_bio  = get_the_author_meta('description');
        $author_url  = get_author_posts_url($author_id);
        $avatar      = get_avatar($author_id, 64, '', $author_name, ['class' => 'rounded-full']);

        // Dates
        $published = get_the_date();
        $modified  = get_the_modified_date();
        $show_updated = (get_the_modified_time('U') !== get_the_time('U'));
        ?>

        <?php
        // Visibility toggle: set custom field _ori_hide_header_block = '1' to hide the title/author/dates header block.
        // Default: visible when the meta is absent or not '1'.
        $ori_hide_header_block = get_post_meta(get_the_ID(), '_ori_hide_header_block', true) === '1';
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('space-y-10'); ?> itemscope itemtype="https://schema.org/WebPage">
            <!-- Header block: Title, Author, Dates -->
            <?php if (!$ori_hide_header_block) : ?>
            <header class="border-b border-slate-200 pb-6">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3" itemprop="headline"><?php the_title(); ?></h1>
                <div class="flex items-start gap-3 text-sm text-slate-600">
                    <?php echo $avatar; ?>
                    <div>
                        <div class="font-medium">
                            <a href="<?php echo esc_url($author_url); ?>" class="hover:underline" rel="author" itemprop="author"><?php echo esc_html($author_name); ?></a>
                        </div>
                        <div class="text-xs mt-0.5">
                            <span><?php echo esc_html__('Published', 'oriandras'); ?> <?php echo esc_html($published); ?></span>
                            <?php if ($show_updated) : ?>
                                <span class="ml-1">· <?php echo esc_html__('Updated', 'oriandras'); ?> <?php echo esc_html($modified); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($author_bio)) : ?>
                            <p class="mt-2 text-slate-700"><?php echo esc_html($author_bio); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            <?php endif; ?>

            <!-- Content -->
            <div class="entry-content prose prose-slate max-w-none" itemprop="mainEntityOfPage">
                <?php the_content(); ?>
            </div>

            <?php
            // Pagination for multi-page content
            wp_link_pages([
                'before' => '<nav class="page-links mt-6">',
                'after'  => '</nav>',
            ]);
            ?>

            <!-- Comments section (after content as requested) -->
            <section>
                <?php
                echo '<div class="mt-2">';
                comments_template();
                echo '</div>';
                ?>
            </section>
        </article>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
