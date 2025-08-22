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

// Print lightweight CSS for skeleton once per request to avoid duplication
static $ori_card_skeleton_css_printed = false;
if (!$ori_card_skeleton_css_printed) {
    $ori_card_skeleton_css_printed = true;
    echo '<style id="ori-card-skeleton-css">'
        .'.ori-card{position:relative}'
        .'.ori-card .ori-card__skeleton{display:none}'
        .'.ori-card .ori-card__content{opacity:1}'
        .'.ori-card.is-lazy .ori-card__skeleton{display:block}'
        .'.ori-card.is-lazy .ori-card__content{opacity:0}'
        .'.ori-card.is-loaded .ori-card__skeleton{display:none}'
        .'.ori-card.is-loaded .ori-card__content{opacity:1;transition:opacity .2s ease}'
        .'</style>';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('ori-card bg-white/60 shadow-sm hover:shadow-xl transition-shadow duration-200 overflow-hidden flex flex-col border-0'); ?> itemscope itemtype="https://schema.org/Article" aria-busy="false" style="border-left: 0.25rem solid <?php echo esc_attr('var(--ori-accent, #2563eb)'); ?>;">
    <!-- Skeleton Preloader -->
    <div class="ori-card__skeleton">
        <div class="bg-slate-200 animate-pulse w-full" style="aspect-ratio: 16/9;"></div>
        <div class="p-4">
            <div class="h-5 bg-slate-200 rounded w-3/4 mb-3 animate-pulse"></div>
            <div class="space-y-2">
                <div class="h-4 bg-slate-200 rounded w-full animate-pulse"></div>
                <div class="h-4 bg-slate-200 rounded w-11/12 animate-pulse"></div>
                <div class="h-4 bg-slate-200 rounded w-10/12 animate-pulse"></div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <div class="h-8 w-8 bg-slate-200 rounded-full animate-pulse"></div>
                <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
            </div>
            <div class="mt-3 flex justify-end">
                <div class="h-9 w-24 bg-slate-200 rounded-md animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Real Content -->
    <div class="ori-card__content">
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
    </div>
</article>
