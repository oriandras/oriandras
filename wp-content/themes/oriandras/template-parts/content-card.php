<?php
/**
 * Template Part: Post Card
 *
 * Reusable card layout for listing posts.
 *
 * Shows:
 * - Cover image (or placeholder)
 * - Title
 * - Excerpt
 * - Author with avatar
 * - First category
 * - Right-aligned translatable More button
 *
 * Usage:
 * Inside The Loop:
 *   get_template_part('template-parts/content', 'card');
 *
 * @package Oriandras\Theme
 */

$author_id   = get_the_author_meta('ID');
$author_url  = get_author_posts_url($author_id);
$author_name = get_the_author();
$avatar      = get_avatar($author_id, 32, '', $author_name, ['class' => 'rounded-full']);
$cats        = get_the_category();
$cat         = !empty($cats) ? $cats[0] : null;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('bg-white/60 rounded-lg shadow-sm ring-1 ring-slate-200 overflow-hidden flex flex-col'); ?> itemscope itemtype="https://schema.org/Article">
    <!-- Media -->
    <a href="<?php the_permalink(); ?>" class="block" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <div class="bg-slate-100">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', ['class' => 'w-full h-auto block', 'itemprop' => 'image', 'loading' => 'lazy']); ?>
            <?php else : ?>
                <div class="w-full flex items-center justify-center text-slate-400 text-sm py-12">No cover image</div>
            <?php endif; ?>
        </div>
    </a>

    <!-- Content -->
    <div class="p-4 flex-1 flex flex-col">
        <h2 class="text-xl font-semibold leading-snug" itemprop="headline">
            <a class="hover:underline" href="<?php the_permalink(); ?>" rel="bookmark" itemprop="url"><?php the_title(); ?></a>
        </h2>
        <div class="mt-2 text-slate-700 line-clamp-3" itemprop="description">
            <?php echo esc_html(get_the_excerpt()); ?>
        </div>

        <div class="mt-4 flex items-center gap-3 text-sm text-slate-600 flex-wrap">
            <?php echo $avatar; ?>
            <a href="<?php echo esc_url($author_url); ?>" class="hover:underline" rel="author" itemprop="author"><?php echo esc_html($author_name); ?></a>
            <?php if ($cat) : ?>
                <span class="text-slate-400">•</span>
                <a class="hover:underline" href="<?php echo esc_url(get_category_link($cat)); ?>" rel="category tag"><?php echo esc_html($cat->name); ?></a>
            <?php endif; ?>
        </div>

        <div class="mt-3 flex justify-end">
            <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-white bg-blue-600 hover:opacity-90" style="background: <?php echo esc_attr('var(--ori-accent, #2563eb)'); ?>;">
                <span><?php echo esc_html__( 'More', 'oriandras' ); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </a>
        </div>
    </div>
</article>
