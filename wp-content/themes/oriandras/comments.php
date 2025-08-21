<?php
/**
 * Comments Template
 *
 * Implements a 3-column structure for the comments area:
 * - Form section: uses only the middle column.
 * - Listing section: left column shows comment meta (date, author), middle column shows comment content.
 * - Right column: dedicated widget area (Comments Sidebar).
 *
 * @link https://developer.wordpress.org/reference/functions/wp_list_comments/
 * @package Oriandras\Theme
 * @since 0.1.0
 */

if (post_password_required()) {
    return;
}

// Custom callback to render each comment in a 2-column layout within the listing area.
if (!function_exists('oriandras_render_comment')) {
    /**
     * Render a single comment in a 2-column layout (meta + content) for large screens
     * and a stacked layout on small screens. Used as a callback for wp_list_comments().
     *
     * @param WP_Comment $comment The comment object.
     * @param array      $args    An array of arguments passed to wp_list_comments().
     * @param int        $depth   Depth of the current comment in a threaded list.
     *
     * @return void Outputs HTML directly.
     */
    function oriandras_render_comment($comment, $args, $depth)
    {
        $tag       = ($args['style'] === 'div') ? 'div' : 'li';
        $author    = get_comment_author($comment);
        $avatar    = get_avatar($comment, 48, '', $author, ['class' => 'rounded-full']);
        $permalink = esc_url(get_comment_link($comment));
        $date_iso  = esc_attr(get_comment_time('c'));
        $date_disp = esc_html(get_comment_date('', $comment)) . ' ' . esc_html(get_comment_time());
        $author_url = get_comment_author_url($comment);
        $is_pending = $comment->comment_approved == '0';

        echo '<' . $tag . ' id="comment-' . esc_attr($comment->comment_ID) . '" ' . comment_class('border-b border-slate-200 pb-6', $comment, null, false) . '>';

        echo '<article class="grid gap-4 lg:grid-cols-9">';

        // Column 1: Meta (hidden on mobile)
        echo '<div class="hidden lg:block lg:col-span-2 text-sm text-slate-600">';
        echo '<div class="space-y-3">';
        echo '<div class="flex items-center gap-2">' . $avatar . '<span class="font-medium">' . esc_html($author) . '</span></div>';
        echo '<div><time datetime="' . $date_iso . '"><a class="hover:underline" href="' . $permalink . '">' . $date_disp . '</a></time></div>';
        if (current_user_can('edit_comment', $comment->comment_ID)) {
            echo '<div>';
            edit_comment_link(__('Edit', 'oriandras'), '<span class="text-xs text-slate-500">', '</span>', $comment);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        // Column 2: Content
        echo '<div class="lg:col-span-7">';
        // Mobile meta
        echo '<div class="lg:hidden text-sm text-slate-600 mb-2">';
        echo '<div class="flex items-center gap-2">' . $avatar . '<span class="font-medium">' . esc_html($author) . '</span></div>';
        echo '<div class="text-xs"><time datetime="' . $date_iso . '"><a class="hover:underline" href="' . $permalink . '">' . $date_disp . '</a></time></div>';
        echo '</div>';

        if ($is_pending) {
            echo '<em role="status" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded">' . esc_html__('Your comment is awaiting moderation.', 'oriandras') . '</em>';
        }

        echo '<div class="prose prose-slate max-w-none">';
        comment_text($comment);
        echo '</div>';

        echo '<div class="mt-2 flex items-center gap-4 text-sm">';
        comment_reply_link(array_merge($args, [
            'depth'     => $depth,
            'max_depth' => $args['max_depth'],
            'reply_text'=> __('Reply', 'oriandras'),
            'before'    => '<span class="inline-flex items-center">',
            'after'     => '</span>',
        ]));
        if (current_user_can('edit_comment', $comment->comment_ID)) {
            edit_comment_link(__('Edit', 'oriandras'), '<span class="text-slate-500">', '</span>', $comment);
        }
        echo '</div>';

        echo '</div>';

        echo '</article>';

        echo '</' . $tag . '>';
    }
}

?>
<section id="comments" class="mt-10 lg:mt-12" role="region" aria-labelledby="comments-title">
    <?php if (comments_open()) : ?>
        <!-- Form section: only middle column used -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="hidden lg:block lg:col-span-2"></div>
            <div class="lg:col-span-7">
                <?php
                $req = get_option('require_name_email');
                $aria_req = ($req ? [ 'aria-required' => 'true' ] : []);

                comment_form([
                    'title_reply_before' => '<h2 id="reply-title" class="text-xl font-bold tracking-tight mb-4">',
                    'title_reply_after'  => '</h2>',
                    'class_form'         => 'space-y-4',
                    'class_submit'       => 'inline-flex items-center px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700',
                    'comment_field'      => '<p class="comment-form-comment"><label for="comment" class="block text-sm font-medium mb-1">' . _x('Comment', 'noun', 'oriandras') . '</label><textarea id="comment" name="comment" rows="5" class="w-full rounded border-slate-300 focus:border-blue-600 focus:ring-blue-600" required></textarea></p>',
                    'fields'             => [
                        'author' => '<p class="comment-form-author"><label for="author" class="block text-sm font-medium mb-1">' . __('Name', 'oriandras') . ($req ? ' *' : '') . '</label><input id="author" name="author" type="text" value="' . esc_attr(wp_get_current_commenter()['comment_author']) . '" class="w-full rounded border-slate-300 focus:border-blue-600 focus:ring-blue-600"' . ($req ? ' required' : '') . '></p>',
                        'email'  => '<p class="comment-form-email"><label for="email" class="block text-sm font-medium mb-1">' . __('Email', 'oriandras') . ($req ? ' *' : '') . '</label><input id="email" name="email" type="email" value="' . esc_attr(wp_get_current_commenter()['comment_author_email']) . '" class="w-full rounded border-slate-300 focus:border-blue-600 focus:ring-blue-600"' . ($req ? ' required' : '') . '></p>',
                        'url'    => '<p class="comment-form-url"><label for="url" class="block text-sm font-medium mb-1">' . __('Website', 'oriandras') . '</label><input id="url" name="url" type="url" value="' . esc_attr(wp_get_current_commenter()['comment_author_url']) . '" class="w-full rounded border-slate-300 focus:border-blue-600 focus:ring-blue-600"></p>',
                    ],
                    'logged_in_as'       => null,
                    'comment_notes_before' => '',
                    'comment_notes_after'  => '',
                ]);
                ?>
            </div>
            <div class="hidden lg:block lg:col-span-3"></div>
        </div>
    <?php endif; ?>

    <?php if (have_comments()) : ?>
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Listing area (left 9 cols) -->
            <div class="lg:col-span-9">
                <h2 id="comments-title" class="text-xl font-bold tracking-tight mb-4">
                    <?php
                    $count = get_comments_number();
                    printf( _n('%s Comment', '%s Comments', $count, 'oriandras'), number_format_i18n($count) );
                    ?>
                </h2>

                <ol class="space-y-6">
                    <?php
                    wp_list_comments([
                        'style'       => 'ol',
                        'short_ping'  => true,
                        'avatar_size' => 48,
                        'callback'    => 'oriandras_render_comment',
                        'max_depth'   => 3,
                    ]);
                    ?>
                </ol>

                <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
                    <nav class="mt-6 flex items-center justify-between" aria-label="Comments Pagination">
                        <div><?php previous_comments_link(__('← Older Comments', 'oriandras')); ?></div>
                        <div><?php next_comments_link(__('Newer Comments →', 'oriandras')); ?></div>
                    </nav>
                <?php endif; ?>

                <?php if (!comments_open() && $count) : ?>
                    <p class="mt-6 text-sm text-slate-600"><?php esc_html_e('Comments are closed.', 'oriandras'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Right column: Comments Sidebar -->
            <aside class="lg:col-span-3 space-y-6" aria-label="Comments Widgets">
                <?php if (is_active_sidebar('comments-sidebar')) : ?>
                    <?php dynamic_sidebar('comments-sidebar'); ?>
                <?php else : ?>
                    <section class="widget">
                        <h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-slate-600 mb-2"><?php esc_html_e('Comments Widgets', 'oriandras'); ?></h2>
                        <p class="text-sm text-slate-600"><?php esc_html_e('Add widgets to the Comments Sidebar in Appearance → Widgets.', 'oriandras'); ?></p>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>

    <?php if (!comments_open() && !have_comments()) : ?>
        <p class="mt-6 text-sm text-slate-600"><?php esc_html_e('Comments are closed.', 'oriandras'); ?></p>
    <?php endif; ?>
</section>
