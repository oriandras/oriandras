<?php
/**
 * Content Card template for Oriandras Audio
 *
 * Variables available in scope:
 * - $track (array): [id, title, desc, url, cta_label, cta_url, start]
 * - $card_index (int)
 */
if ( ! isset( $track ) || ! is_array( $track ) ) { return; }
?>
<li class="ori-card">
    <div class="ori-card__body">
        <h3 class="ori-card__title"><?php echo esc_html( $track['title'] ); ?></h3>
        <?php if ( ! empty( $track['desc'] ) ) : ?>
            <p class="ori-card__desc"><?php echo esc_html( $track['desc'] ); ?></p>
        <?php endif; ?>
        <audio class="ori-card__audio" preload="metadata" controls src="<?php echo esc_url( $track['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Audio for %s', 'oriandras-audio' ), $track['title'] ) ); ?>">
            <?php // Browsers without audio support ?>
            <?php echo esc_html__( 'Your browser does not support the audio element.', 'oriandras-audio' ); ?>
        </audio>
        <?php if ( ! empty( $track['cta_label'] ) && ! empty( $track['cta_url'] ) ) : ?>
            <p class="ori-card__cta">
                <a class="ori-card__btn" href="<?php echo esc_url( $track['cta_url'] ); ?>"><?php echo esc_html( $track['cta_label'] ); ?></a>
            </p>
        <?php endif; ?>
    </div>
</li>
