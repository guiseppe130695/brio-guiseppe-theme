<?php
/**
 * Landing Section — Features + Vidéo
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$home = brio_get_hero_data();
?>
<section class="landing-features">
	<div class="container">

		<ul class="home-hero__features">
			<?php foreach ( $home['features'] as $feature ) : ?>
				<li>
					<strong><?php echo esc_html( $feature['title'] ); ?></strong>
					<span><?php echo esc_html( $feature['desc'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="home-hero__media">
			<video class="home-hero__video" autoplay loop muted playsinline preload="metadata"
			       poster="<?php echo esc_url( brio_asset( 'hero', 'poster' ) ); ?>"
			       aria-hidden="true">
				<source src="<?php echo esc_url( brio_asset( 'hero', 'video' ) ); ?>" type="video/mp4" />
			</video>
			<img class="home-hero__suitcase"
			     src="<?php echo esc_url( brio_asset( 'hero', 'suitcase' ) ); ?>"
			     alt="" width="250" height="250" decoding="async" />
		</div>

	</div>
</section>
