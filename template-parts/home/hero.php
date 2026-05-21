<?php
/**
 * Homepage Section — Hero
 *
 * Pure renderer. All content lives in brio_get_hero_data(), all assets in
 * brio_get_assets()['hero']. To edit copy or swap visuals, touch theme-data.php
 * (or apply the brio_hero_data / brio_theme_assets filters from a plugin).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero    = brio_get_hero_data();
$rating  = $hero['rating'];
$avatars = [ 'avatar_1', 'avatar_2', 'avatar_3', 'avatar_4' ];
?>
<section class="home-hero" aria-labelledby="hero-title">
	<div class="container">

		<div class="home-hero__social-proof">
			<div class="home-hero__avatars" aria-hidden="true">
				<?php foreach ( $avatars as $key ) : ?>
					<img src="<?php echo esc_url( brio_asset( 'hero', $key ) ); ?>" alt="" class="home-hero__avatar" width="48" height="48" decoding="async" />
				<?php endforeach; ?>
			</div>
			<div class="home-hero__stars" role="img" aria-label="<?php
				/* translators: 1: rating value, 2: max rating. */
				echo esc_attr( sprintf( __( '%1$d étoiles sur %2$d', 'brio-guiseppe' ), $rating['value'], $rating['max'] ) );
			?>"></div>
			<p class="home-hero__rating-caption">
				<a href="<?php echo esc_url( $rating['href'] ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( $rating['caption'] ); ?>
				</a>
			</p>
		</div>

		<h1 id="hero-title" class="home-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>

		<?php foreach ( $hero['lead'] as $paragraph ) : ?>
			<p class="home-hero__lead">
				<?php
				if ( is_array( $paragraph ) ) {
					printf(
						esc_html( $paragraph['template'] ),
						'<strong>' . esc_html( $paragraph['highlight'] ) . '</strong>'
					);
				} else {
					echo esc_html( $paragraph );
				}
				?>
			</p>
		<?php endforeach; ?>

		<div class="home-hero__actions">
			<?php foreach ( $hero['cta'] as $cta ) : ?>
				<a href="<?php echo esc_url( $cta['href'] ); ?>" class="btn btn-<?php echo esc_attr( $cta['variant'] ); ?>">
					<?php echo esc_html( $cta['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<ul class="home-hero__features">
			<?php foreach ( $hero['features'] as $feature ) : ?>
				<li>
					<strong><?php echo esc_html( $feature['title'] ); ?></strong>
					<span><?php echo esc_html( $feature['desc'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="home-hero__media">
			<video class="home-hero__video" autoplay loop muted playsinline preload="metadata" poster="<?php echo esc_url( brio_asset( 'hero', 'poster' ) ); ?>" aria-hidden="true">
				<source src="<?php echo esc_url( brio_asset( 'hero', 'video' ) ); ?>" type="video/mp4" />
			</video>
			<img class="home-hero__suitcase" src="<?php echo esc_url( brio_asset( 'hero', 'suitcase' ) ); ?>" alt="" width="250" height="250" decoding="async" />
		</div>

	</div>
</section>
