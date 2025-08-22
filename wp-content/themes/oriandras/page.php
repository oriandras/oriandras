<?php
/**
 * Page Template
 *
 * Based on single.php but without categories and tags sections.
 * Maintains a similar 3-column layout with author/date, main content, and sidebar.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-page
 * @package Oriandras\Theme
 * @since 0.1.0
 */

get_header();
?>

<main id="primary" role="main" class="max-w-5xl mx-auto px-4 py-10">
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

        // Visibility toggle: set custom field _ori_hide_header_block = '1' to hide the title/author/dates block (incl. left meta column).
        // Default: visible when the meta is absent or not '1'.
        $ori_hide_header_block = get_post_meta(get_the_ID(), '_ori_hide_header_block', true) === '1';
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('grid grid-cols-1 lg:grid-cols-12 gap-8'); ?> itemscope itemtype="https://schema.org/WebPage">
            <!-- Column 1: Meta + Author (hidden on small, visible on lg) -->
            <?php if (!$ori_hide_header_block) : ?>
            <div id="page-col-meta" class="hidden lg:block lg:col-span-2 text-sm text-slate-600">
                <div class="space-y-4 sticky top-6">
                    <div>
                        <div class="font-semibold uppercase tracking-wide text-slate-500">Published</div>
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished"><?php echo esc_html($published); ?></time>
                    </div>
                    <?php if ($show_updated) : ?>
                        <div>
                            <div class="font-semibold uppercase tracking-wide text-slate-500">Updated</div>
                            <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>" itemprop="dateModified"><?php echo esc_html($modified); ?></time>
                        </div>
                    <?php endif; ?>

                    <div class="pt-2 border-t border-slate-200">
                        <div class="flex items-center gap-3">
                            <?php echo $avatar; ?>
                            <div>
                                <div class="font-medium"><a href="<?php echo esc_url($author_url); ?>" class="hover:underline" rel="author" itemprop="author"><?php echo esc_html($author_name); ?></a></div>
                            </div>
                        </div>
                        <?php if (!empty($author_bio)) : ?>
                            <p class="mt-3 text-slate-700"><?php echo esc_html($author_bio); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Column 2: Main content -->
            <div id="page-col-content" class="lg:col-span-7">
                <?php if (has_post_thumbnail()) : ?>
                    <figure class="mb-6">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-auto rounded', 'itemprop' => 'image']); ?>
                    </figure>
                <?php endif; ?>

                <?php if (!$ori_hide_header_block) : ?>
                <header class="mb-4">
                    <h1 class="text-3xl font-extrabold tracking-tight !mb-2" itemprop="headline"><?php the_title(); ?></h1>

                    <!-- Mobile author/date block (shown on small screens only) -->
                    <div class="lg:hidden text-sm text-slate-600 mt-2">
                        <div class="flex items-center gap-3">
                            <?php echo $avatar; ?>
                            <div>
                                <div class="font-medium"><a href="<?php echo esc_url($author_url); ?>" class="hover:underline" rel="author"><?php echo esc_html($author_name); ?></a></div>
                                <div class="text-xs">
                                    <span>Published <?php echo esc_html($published); ?></span>
                                    <?php if ($show_updated) : ?>
                                        <span class="ml-1">· Updated <?php echo esc_html($modified); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($author_bio)) : ?>
                            <p class="mt-2 text-slate-700"><?php echo esc_html($author_bio); ?></p>
                        <?php endif; ?>
                    </div>
                </header>
                <?php endif; ?>

                <?php if (has_excerpt()) : ?>
                    <p class="text-lg text-slate-700 mb-6" itemprop="description"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>

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
            </div>

            <!-- Column 3: Sidebar -->
            <div id="page-col-sidebar" class="lg:col-span-3">
                <?php get_sidebar(); ?>
            </div>
        </article>

        <?php
        // Load comments if open or present (optional for pages)
        echo '<div class="mt-10 lg:mt-12">';
        comments_template();
        echo '</div>';
        ?>

    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
