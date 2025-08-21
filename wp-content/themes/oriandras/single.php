<?php
/**
 * Single Post Template
 *
 * Implements a 3-column layout:
 * - Column 1 (narrow, hidden on mobile): published/updated dates and author info.
 * - Column 2 (wide): cover image/video, title, categories, mobile author/date block, excerpt, content, tags.
 * - Column 3: sidebar widgets.
 *
 * This template is Gutenberg-ready and uses Tailwind utility classes. It also
 * outputs basic schema.org Article microdata via itemprop attributes.
 *
 * Accessibility: Provides proper landmarks, list semantics for categories/tags,
 * and ARIA labels for assistive technologies.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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

        /**
         * Runtime variables used by the template
         *
         * @var int         $author_id    Current post author's user ID.
         * @var string      $author_name  Current post author's display name.
         * @var string|null $author_bio   Author bio/description (may be empty).
         * @var string      $author_url   URL to the author's archive page.
         * @var string      $avatar       HTML <img> tag for the author's avatar.
         * @var string      $published    Human-readable published date of the post.
         * @var string      $modified     Human-readable modified date of the post.
         * @var bool        $show_updated Whether to display the modified date (true when different from published).
         */
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('grid grid-cols-1 lg:grid-cols-12 gap-8'); ?> itemscope itemtype="https://schema.org/Article">
            <!-- Column 1: Meta + Author (hidden on small, visible on lg) -->
            <div class="hidden lg:block lg:col-span-2 text-sm text-slate-600">
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

            <!-- Column 2: Main content -->
            <div class="lg:col-span-7">
                <?php if (has_post_thumbnail()) : ?>
                    <figure class="mb-6">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-auto rounded', 'itemprop' => 'image']); ?>
                    </figure>
                <?php endif; ?>

                <header class="mb-4">
                    <h1 class="text-3xl font-extrabold tracking-tight !mb-2" itemprop="headline"><?php the_title(); ?></h1>
                    <?php
                    $cats = get_the_category();
                    if (!empty($cats)) : ?>
                        <nav class="mb-2" aria-label="Categories">
                            <ul class="text-sm text-slate-600 flex flex-wrap gap-2">
                                <?php foreach ($cats as $cat) : ?>
                                    <li>
                                        <a rel="category tag" class="inline-flex items-center rounded-full border border-slate-300 px-2.5 py-0.5 hover:bg-slate-50" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

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

                <?php if (has_excerpt()) : ?>
                    <p class="text-lg text-slate-700 mb-6" itemprop="description"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>

                <div class="entry-content prose prose-slate max-w-none" itemprop="articleBody">
                    <?php the_content(); ?>
                </div>

                <?php
                // Pagination for multi-page posts
                wp_link_pages([
                    'before' => '<nav class="page-links mt-6">',
                    'after'  => '</nav>',
                ]);
                ?>

                <?php $post_tags = get_the_tags(); ?>
                <?php if ($post_tags) : ?>
                    <nav class="mt-8" aria-label="Tags">
                        <ul class="flex flex-wrap gap-2">
                            <?php foreach ($post_tags as $tag) : ?>
                                <li>
                                    <a rel="tag" href="<?php echo esc_url(get_tag_link($tag)); ?>" class="inline-flex items-center rounded-full bg-slate-100 text-slate-800 text-xs px-3 py-1 hover:bg-slate-200">#<?php echo esc_html($tag->name); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Column 3: Sidebar -->
            <div class="lg:col-span-3">
                <?php get_sidebar(); ?>
            </div>
        </article>

        <?php
        /**
         * Load the comments template in a padded container when comments are open or present.
         *
         * @see comments_template()
         */
        if (comments_open() || get_comments_number()) {
            echo '<div class="mt-10 lg:mt-12">';
            comments_template();
            echo '</div>';
        }
        ?>

    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
