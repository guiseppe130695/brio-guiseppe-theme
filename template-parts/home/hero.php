<?php
/**
 * Homepage Section — Hero
 *
 * Source: Elementor export page #60, container[0].
 * Layout: avatar stack + 5-star rating + caption · headline · lead paragraph ·
 * 2 CTAs · 4 feature checkmarks · trailing suitcase visual.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$brio_hero_avatars = [
	brio_asset( 'hero', 'avatar_1' ),
	brio_asset( 'hero', 'avatar_2' ),
	brio_asset( 'hero', 'avatar_3' ),
	brio_asset( 'hero', 'avatar_4' ),
];

$brio_hero_features = [
	[ 'title' => __( 'Site Qui Vend', 'brio-guiseppe' ),                 'desc' => __( 'Réservation directe. Zéro commission.', 'brio-guiseppe' ) ],
	[ 'title' => __( 'SEO Tourisme & Destination', 'brio-guiseppe' ),    'desc' => __( 'Vos hôtes vous trouvent avant les OTA.', 'brio-guiseppe' ) ],
	[ 'title' => __( 'Revenue Management Custom', 'brio-guiseppe' ),     'desc' => __( 'Chaque nuit vendue au meilleur prix.', 'brio-guiseppe' ) ],
	[ 'title' => __( 'Audit Distribution OTA', 'brio-guiseppe' ),        'desc' => __( 'Découvrez combien Booking vous coûte.', 'brio-guiseppe' ) ],
];
?>
<section class="home-hero" aria-label="<?php esc_attr_e( 'Hero', 'brio-guiseppe' ); ?>">
	<div class="container">

		<div class="home-hero__social-proof">
			<div class="home-hero__avatars" aria-hidden="true">
				<?php foreach ( $brio_hero_avatars as $avatar_url ) : ?>
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="home-hero__avatar" loading="eager" width="48" height="48" />
				<?php endforeach; ?>
			</div>
			<div class="home-hero__stars" role="img" aria-label="<?php esc_attr_e( '5 étoiles sur 5', 'brio-guiseppe' ); ?>">
				<i class="fas fa-star" aria-hidden="true"></i>
				<i class="fas fa-star" aria-hidden="true"></i>
				<i class="fas fa-star" aria-hidden="true"></i>
				<i class="fas fa-star" aria-hidden="true"></i>
				<i class="fas fa-star" aria-hidden="true"></i>
			</div>
			<p class="home-hero__rating-caption">
				<a href="https://www.linkedin.com/in/brioguiseppe/" target="_blank" rel="noopener">
					<?php esc_html_e( 'Noté par les hôteliers accompagnés', 'brio-guiseppe' ); ?>
				</a>
			</p>
		</div>

		<h1 class="home-hero__title">
			<?php esc_html_e( 'Libérez votre Hôtel des commissions OTA', 'brio-guiseppe' ); ?>
		</h1>

		<p class="home-hero__lead">
			<?php esc_html_e( 'Je construis des sites qui convertissent les visiteurs en réservations directes pour les hôtels indépendants, riads et maisons d\'hôtes.', 'brio-guiseppe' ); ?>
			<br><br>
			<?php
			printf(
				/* translators: %s: bold inline phrase "jusqu'à 25 000 €/an de commissions". */
				esc_html__( 'Résultat : %s récupérées.', 'brio-guiseppe' ),
				'<strong>' . esc_html__( 'jusqu\'à 25 000 €/an de commissions', 'brio-guiseppe' ) . '</strong>'
			);
			?>
		</p>

		<div class="home-hero__actions">
			<a href="#audit" class="btn btn-primary"><?php esc_html_e( 'Réserver mon audit gratuit', 'brio-guiseppe' ); ?></a>
			<a href="#calculateur" class="btn btn-secondary"><?php esc_html_e( 'Calculer mes revenus perdus', 'brio-guiseppe' ); ?></a>
		</div>

		<ul class="home-hero__features">
			<?php foreach ( $brio_hero_features as $feature ) : ?>
				<li class="home-hero__feature">
					<i class="fas fa-check home-hero__feature-icon" aria-hidden="true"></i>
					<span class="home-hero__feature-title"><?php echo esc_html( $feature['title'] ); ?></span>
					<span class="home-hero__feature-desc"><?php echo esc_html( $feature['desc'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="home-hero__media">
			<video
				class="home-hero__video"
				autoplay
				loop
				muted
				playsinline
				preload="metadata"
				poster="<?php echo esc_url( brio_asset( 'hero', 'poster' ) ); ?>"
				aria-hidden="true"
			>
				<source src="<?php echo esc_url( brio_asset( 'hero', 'video' ) ); ?>" type="video/mp4" />
			</video>
			<img class="home-hero__suitcase" src="<?php echo esc_url( brio_asset( 'hero', 'suitcase' ) ); ?>" alt="" aria-hidden="true" loading="lazy" width="250" height="250" />
		</div>

	</div>
</section>
