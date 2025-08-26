<?php
/**
 * Plugin Name: Oriandras – Audio & Podcast Playlist
 * Description: WCAG-accessible audio/podcast playlists with shortcode. Uses media from the library, supports categories/tags, CTA buttons, and start time. Shortcode: [oriandras-audio]
 * Version: 1.0.0
 * Author: Oriandras
 * License: GPLv2 or later
 * Text Domain: oriandras-audio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Load i18n
function oriandras_audio_load_textdomain() {
    load_plugin_textdomain('oriandras-audio', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'oriandras_audio_load_textdomain');

class Oriandras_Audio_Plugin {
    const CPT = 'oriandras_track';
    const SLUG = 'oriandras-audio';
    const META_URL = '_ori_audio_url';
    const META_ATT = '_ori_audio_attachment_id';
    const META_CTA_LABEL = '_ori_audio_cta_label';
    const META_CTA_URL = '_ori_audio_cta_url';
    const META_START = '_ori_audio_start_time';

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
        add_shortcode( 'oriandras-audio', [ $this, 'shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public function register_cpt() {
        $labels = [
            'name' => __( 'Tracks', 'oriandras-audio' ),
            'singular_name' => __( 'Track', 'oriandras-audio' ),
            'add_new' => __( 'Add New Track', 'oriandras-audio' ),
            'add_new_item' => __( 'Add New Track', 'oriandras-audio' ),
            'edit_item' => __( 'Edit Track', 'oriandras-audio' ),
            'new_item' => __( 'New Track', 'oriandras-audio' ),
            'view_item' => __( 'View Track', 'oriandras-audio' ),
            'search_items' => __( 'Search Tracks', 'oriandras-audio' ),
            'not_found' => __( 'No tracks found', 'oriandras-audio' ),
            'not_found_in_trash' => __( 'No tracks found in Trash', 'oriandras-audio' ),
            'menu_name' => __( 'Audio Tracks', 'oriandras-audio' ),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-controls-volumeon',
            'supports' => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'has_archive' => false,
            'rewrite' => [ 'slug' => 'track' ],
            'taxonomies' => [ 'category', 'post_tag' ],
            'show_in_rest' => true,
        ];

        register_post_type( self::CPT, $args );
    }

    public function add_meta_boxes() {
        add_meta_box( 'ori_audio_source', __( 'Audio Source & Options', 'oriandras-audio' ), [ $this, 'metabox_render' ], self::CPT, 'normal', 'high' );
    }

    public function admin_enqueue( $hook ) {
        global $post_type;
        if ( in_array( $hook, [ 'post-new.php', 'post.php' ], true ) && $post_type === self::CPT ) {
            wp_enqueue_media();
            wp_enqueue_script( self::SLUG . '-admin', plugins_url( 'assets/admin.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
            wp_enqueue_style( self::SLUG . '-admin', plugins_url( 'assets/admin.css', __FILE__ ), [], '1.0.0' );
        }
    }

    public function metabox_render( $post ) {
        wp_nonce_field( 'ori_audio_meta', 'ori_audio_nonce' );
        $url = esc_url( get_post_meta( $post->ID, self::META_URL, true ) );
        $att = absint( get_post_meta( $post->ID, self::META_ATT, true ) );
        $cta_label = esc_html( get_post_meta( $post->ID, self::META_CTA_LABEL, true ) );
        $cta_url = esc_url( get_post_meta( $post->ID, self::META_CTA_URL, true ) );
        $start = esc_html( get_post_meta( $post->ID, self::META_START, true ) );
        ?>
        <p>
            <label for="ori_audio_url"><strong><?php _e( 'Audio URL', 'oriandras-audio' ); ?></strong></label><br>
            <input type="url" id="ori_audio_url" name="ori_audio_url" class="widefat" placeholder="https://example.com/audio.mp3" value="<?php echo $url; ?>" />
        </p>
        <p>
            <button type="button" class="button" id="ori_audio_select"><?php _e( 'Select from Media Library', 'oriandras-audio' ); ?></button>
            <input type="hidden" id="ori_audio_attachment_id" name="ori_audio_attachment_id" value="<?php echo $att; ?>" />
            <span class="description"><?php _e( 'If you pick a media file, its URL will be filled above.', 'oriandras-audio' ); ?></span>
        </p>
        <hr>
        <p>
            <label for="ori_audio_start"><strong><?php _e( 'Start time (optional)', 'oriandras-audio' ); ?></strong></label><br>
            <input type="text" id="ori_audio_start" name="ori_audio_start" class="regular-text" placeholder="e.g., 90 or 1:30 or 00:01:30" value="<?php echo $start; ?>" />
            <span class="description"><?php _e( 'Player will start at this time when this track is loaded.', 'oriandras-audio' ); ?></span>
        </p>
        <hr>
        <p>
            <label for="ori_audio_cta_label"><strong><?php _e( 'CTA Label (optional)', 'oriandras-audio' ); ?></strong></label><br>
            <input type="text" id="ori_audio_cta_label" name="ori_audio_cta_label" class="regular-text" value="<?php echo $cta_label; ?>" />
        </p>
        <p>
            <label for="ori_audio_cta_url"><strong><?php _e( 'CTA URL (optional)', 'oriandras-audio' ); ?></strong></label><br>
            <input type="url" id="ori_audio_cta_url" name="ori_audio_cta_url" class="widefat" placeholder="https://example.com" value="<?php echo $cta_url; ?>" />
        </p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['ori_audio_nonce'] ) || ! wp_verify_nonce( $_POST['ori_audio_nonce'], 'ori_audio_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( isset( $_POST['post_type'] ) && self::CPT === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        }

        $url = isset( $_POST['ori_audio_url'] ) ? esc_url_raw( $_POST['ori_audio_url'] ) : '';
        $att = isset( $_POST['ori_audio_attachment_id'] ) ? absint( $_POST['ori_audio_attachment_id'] ) : 0;
        $cta_label = isset( $_POST['ori_audio_cta_label'] ) ? sanitize_text_field( $_POST['ori_audio_cta_label'] ) : '';
        $cta_url = isset( $_POST['ori_audio_cta_url'] ) ? esc_url_raw( $_POST['ori_audio_cta_url'] ) : '';
        $start = isset( $_POST['ori_audio_start'] ) ? sanitize_text_field( $_POST['ori_audio_start'] ) : '';

        update_post_meta( $post_id, self::META_URL, $url );
        update_post_meta( $post_id, self::META_ATT, $att );
        update_post_meta( $post_id, self::META_CTA_LABEL, $cta_label );
        update_post_meta( $post_id, self::META_CTA_URL, $cta_url );
        update_post_meta( $post_id, self::META_START, $start );
    }

    public function register_assets() {
        wp_register_script( self::SLUG, plugins_url( 'assets/player.js', __FILE__ ), [], '1.0.0', true );
        wp_register_style( self::SLUG, plugins_url( 'assets/player.css', __FILE__ ), [], '1.0.0' );
    }

    private function locate_template( $template_name ) {
        $paths = [
            trailingslashit( get_stylesheet_directory() ) . 'oriandras-audio/' . $template_name,
            trailingslashit( get_template_directory() ) . 'oriandras-audio/' . $template_name,
            plugin_dir_path( __FILE__ ) . 'templates/' . $template_name,
        ];
        foreach ( $paths as $path ) {
            if ( file_exists( $path ) ) return $path;
        }
        return '';
    }

    private function parse_time_to_seconds( $time ) {
        if ( $time === '' ) return 0;
        if ( is_numeric( $time ) ) return max( 0, (int) $time );
        $parts = array_map( 'intval', explode( ':', $time ) );
        $count = count( $parts );
        if ( $count === 3 ) return $parts[0]*3600 + $parts[1]*60 + $parts[2];
        if ( $count === 2 ) return $parts[0]*60 + $parts[1];
        return 0;
    }

    public function shortcode( $atts ) {
        $atts = shortcode_atts( [
            'ids' => '',
            'category' => '',
            'tag' => '',
            'items' => 10,
            'start' => '', // global start time
            'autoplay' => 'false',
            'show_cta' => 'true',
            'class' => '',
            'layout' => 'playlist', // playlist|card
        ], $atts, 'oriandras-audio' );

        $ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', $atts['ids'] ) ) ) );
        $tax_query = [];
        if ( $atts['category'] ) {
            $tax_query[] = [ 'taxonomy' => 'category', 'field' => 'slug', 'terms' => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['category'] ) ) ) ];
        }
        if ( $atts['tag'] ) {
            $tax_query[] = [ 'taxonomy' => 'post_tag', 'field' => 'slug', 'terms' => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['tag'] ) ) ) ];
        }

        $query_args = [
            'post_type' => self::CPT,
            'post_status' => 'publish',
            'posts_per_page' => intval( $atts['items'] ),
        ];
        if ( $ids ) { $query_args['post__in'] = $ids; $query_args['orderby'] = 'post__in'; }
        if ( $tax_query ) { $query_args['tax_query'] = $tax_query; }

        $q = new WP_Query( $query_args );
        if ( ! $q->have_posts() ) {
            return '<div class="ori-audio notice">' . esc_html__( 'No tracks found.', 'oriandras-audio' ) . '</div>';
        }

        wp_enqueue_script( self::SLUG );
        wp_enqueue_style( self::SLUG );

        $global_start = $this->parse_time_to_seconds( $atts['start'] );
        $autoplay = filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );
        $show_cta = filter_var( $atts['show_cta'], FILTER_VALIDATE_BOOLEAN );

        $tracks = [];
        while ( $q->have_posts() ) {
            $q->the_post();
            $pid = get_the_ID();
            $title = get_the_title();
            $desc = wp_strip_all_tags( get_the_excerpt() ?: get_the_content( null, false, $pid ) );
            $url = get_post_meta( $pid, self::META_URL, true );
            $att_id = (int) get_post_meta( $pid, self::META_ATT, true );
            if ( empty( $url ) && $att_id ) {
                $src = wp_get_attachment_url( $att_id );
                if ( $src ) $url = $src;
            }
            if ( ! $url ) continue;
            $cta_label = get_post_meta( $pid, self::META_CTA_LABEL, true );
            $cta_url = get_post_meta( $pid, self::META_CTA_URL, true );
            $start = $this->parse_time_to_seconds( get_post_meta( $pid, self::META_START, true ) );
            $tracks[] = [
                'id' => $pid,
                'title' => $title,
                'desc' => $desc,
                'url' => $url,
                'cta_label' => $cta_label,
                'cta_url' => $cta_url,
                'start' => $start,
            ];
        }
        wp_reset_postdata();

        // Build markup
        $id_attr = 'ori-audio-' . wp_generate_uuid4();
        ob_start();

        // Alternative layout: card(s)
        if ( strtolower( $atts['layout'] ) === 'card' || strtolower( $atts['layout'] ) === 'cards' ) {
            $tpl = $this->locate_template( 'content-card.php' );
            echo '<section class="ori-audio-cards ' . esc_attr( $atts['class'] ) . '" role="region" aria-label="' . esc_attr__( 'Audio cards', 'oriandras-audio' ) . '">';
            echo '<ul class="ori-audio-cards__list" role="list">';
            foreach ( $tracks as $index => $t ) {
                // Provide variables to template scope
                $track = $t; // associative array with title, desc, url, cta_label, cta_url, start, id
                $card_index = $index;
                if ( $tpl ) {
                    include $tpl;
                } else {
                    // Fallback minimal card if template missing
                    echo '<li class="ori-card"><div class="ori-card__body">';
                    echo '<h3 class="ori-card__title">' . esc_html( $track['title'] ) . '</h3>';
                    if ( ! empty( $track['desc'] ) ) {
                        echo '<p class="ori-card__desc">' . esc_html( $track['desc'] ) . '</p>';
                    }
                    echo '<audio class="ori-card__audio" preload="metadata" controls src="' . esc_url( $track['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Audio for %s', 'oriandras-audio' ), $track['title'] ) ) . '"></audio>';
                    if ( ! empty( $track['cta_label'] ) && ! empty( $track['cta_url'] ) ) {
                        echo '<p class="ori-card__cta"><a class="ori-card__btn" href="' . esc_url( $track['cta_url'] ) . '">' . esc_html( $track['cta_label'] ) . '</a></p>';
                    }
                    echo '</div></li>';
                }
            }
            echo '</ul></section>';
            return ob_get_clean();
        }

        ?>
        <section id="<?php echo esc_attr( $id_attr ); ?>" class="ori-audio <?php echo esc_attr( $atts['class'] ); ?>" role="region" aria-label="<?php esc_attr_e( 'Audio playlist', 'oriandras-audio' ); ?>">
            <div class="ori-audio__player" role="group" aria-label="<?php esc_attr_e( 'Player controls', 'oriandras-audio' ); ?>">
                <audio class="ori-audio__element" preload="metadata" <?php echo $autoplay ? 'autoplay' : ''; ?>></audio>
                <div class="ori-audio__controls">
                    <button type="button" class="ori-audio__btn ori-prev" aria-label="<?php esc_attr_e( 'Previous track', 'oriandras-audio' ); ?>">&#9664;</button>
                    <button type="button" class="ori-audio__btn ori-play" aria-label="<?php esc_attr_e( 'Play', 'oriandras-audio' ); ?>" aria-pressed="false">&#9658;</button>
                    <button type="button" class="ori-audio__btn ori-pause" aria-label="<?php esc_attr_e( 'Pause', 'oriandras-audio' ); ?>">&#10073;&#10073;</button>
                    <button type="button" class="ori-audio__btn ori-stop" aria-label="<?php esc_attr_e( 'Stop', 'oriandras-audio' ); ?>">&#9632;</button>
                    <button type="button" class="ori-audio__btn ori-next" aria-label="<?php esc_attr_e( 'Next track', 'oriandras-audio' ); ?>">&#9654;</button>
                </div>
                <div class="ori-audio__now" aria-live="polite"></div>
                <?php if ( $show_cta ) : ?>
                <div class="ori-audio__cta" hidden>
                    <a class="ori-audio__cta-link" href="#"></a>
                </div>
                <?php endif; ?>
            </div>
            <ul class="ori-audio__list" role="list" aria-label="<?php esc_attr_e( 'Track list', 'oriandras-audio' ); ?>">
                <?php foreach ( $tracks as $index => $t ): ?>
                    <li class="ori-audio__item">
                        <button type="button"
                            class="ori-audio__track"
                            role="button"
                            aria-label="<?php echo esc_attr( sprintf( __( 'Play %s', 'oriandras-audio' ), $t['title'] ) ); ?>"
                            data-url="<?php echo esc_url( $t['url'] ); ?>"
                            data-title="<?php echo esc_attr( $t['title'] ); ?>"
                            data-desc="<?php echo esc_attr( $t['desc'] ); ?>"
                            data-start="<?php echo (int) ( $t['start'] ?: $global_start ); ?>"
                            data-cta-label="<?php echo esc_attr( $t['cta_label'] ); ?>"
                            data-cta-url="<?php echo esc_url( $t['cta_url'] ); ?>"
                            data-index="<?php echo (int) $index; ?>"
                        >
                            <span class="ori-audio__title"><?php echo esc_html( $t['title'] ); ?></span>
                            <?php if ( ! empty( $t['desc'] ) ): ?><span class="ori-audio__desc"><?php echo esc_html( $t['desc'] ); ?></span><?php endif; ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <script>window.OriAudioPlaylists = window.OriAudioPlaylists || [];window.OriAudioPlaylists.push({ id: '<?php echo esc_js( $id_attr ); ?>' });</script>
        <?php
        return ob_get_clean();
    }
}

new Oriandras_Audio_Plugin();
