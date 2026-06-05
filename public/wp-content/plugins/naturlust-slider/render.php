<?php
/**
 * Server-Render des Tagebuch-Sliders.
 *
 * @var array    $attributes Block-Attribute (category, count).
 * @var string   $content    Innerer Inhalt (ungenutzt).
 * @var WP_Block $block      Block-Instanz.
 *
 * @package NaturlustSlider
 */

defined( 'ABSPATH' ) || exit;

$nl_category = isset( $attributes['category'] ) ? sanitize_title( (string) $attributes['category'] ) : 'tagebuch';
$nl_count    = isset( $attributes['count'] ) ? (int) $attributes['count'] : 4;
$nl_count    = max( 1, min( 12, $nl_count ) );

$nl_query = new WP_Query(
	array(
		'category_name'       => $nl_category,
		'posts_per_page'      => $nl_count,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $nl_query->have_posts() ) {
	wp_reset_postdata();
	return '';
}

$nl_wrapper = get_block_wrapper_attributes( array( 'class' => 'naturlust-slider' ) );
?>
<div <?php echo $nl_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes. ?> aria-roledescription="<?php esc_attr_e( 'Karussell', 'naturlust-slider' ); ?>" aria-label="<?php esc_attr_e( 'Neueste Tagebuch-Beiträge', 'naturlust-slider' ); ?>">
	<div class="naturlust-slider__viewport">
		<ul class="naturlust-slider__track">
			<?php
			while ( $nl_query->have_posts() ) :
				$nl_query->the_post();
				?>
				<li class="naturlust-slider__slide">
					<a class="naturlust-slider__link" href="<?php the_permalink(); ?>">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'large',
								array(
									'class'    => 'naturlust-slider__img',
									'loading'  => 'lazy',
									'decoding' => 'async',
									'alt'      => esc_attr( get_the_title() ),
								)
							);
						} else {
							echo '<span class="naturlust-slider__img naturlust-slider__img--empty" aria-hidden="true"></span>';
						}
						?>
						<span class="naturlust-slider__caption">
							<span class="naturlust-slider__date"><?php echo esc_html( get_the_date() ); ?></span>
							<span class="naturlust-slider__title"><?php the_title(); ?></span>
						</span>
					</a>
				</li>
				<?php
			endwhile;
			?>
		</ul>
	</div>

	<button class="naturlust-slider__nav naturlust-slider__nav--prev" type="button" aria-label="<?php esc_attr_e( 'Vorheriger Beitrag', 'naturlust-slider' ); ?>">
		<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" /></svg>
	</button>
	<button class="naturlust-slider__nav naturlust-slider__nav--next" type="button" aria-label="<?php esc_attr_e( 'Nächster Beitrag', 'naturlust-slider' ); ?>">
		<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" /></svg>
	</button>

	<div class="naturlust-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Beitrag wählen', 'naturlust-slider' ); ?>"></div>
</div>
<?php
wp_reset_postdata();
