<?php
/**
 * Homepage Section — Hero
 *
 * Source: Elementor export page #60, container[0].
 * Layout: avatar stack + 5-star rating + caption · headline · subtitle ·
 * 2 CTAs · 4 feature checkmarks · trailing suitcase visual.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$brio_hero_cdn = 'https://www.brioguiseppe.fr/wp-content/uploads/2026/04/';

$brio_hero_avatars = [
	'collage-of-happy-multiracial-people-avatars-on-var1-QXZQJRJ.jpg',
	'collage-of-happy-multiracial-people-avatars-on-var2-QXZQJRJ.jpg',
	'collage-of-happy-multiracial-people-avatars-on-var3-QXZQJRJ.jpg',
	'collage-of-happy-multiracial-people-avatars-on-var4-QXZQJRJ.jpg',
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
				<?php foreach ( $brio_hero_avatars as $avatar_file ) : ?>
					<img
						src="<?php echo esc_url( $brio_hero_cdn . $avatar_file ); ?>"
						alt=""
						class="home-hero__avatar"
						loading="eager"
						width="48"
						height="48"
					/>
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

		<div class="home-hero__subtitle">
			<p>
				<?php esc_html_e( 'Je construis des sites qui convertissent les visiteurs en réservations directes pour les hôtels indépendants, riads et maisons d\'hôtes.', 'brio-guiseppe' ); ?>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: bold inline phrase "jusqu'à 25 000 €/an de commissions". */
					esc_html__( 'Résultat : %s récupérées.', 'brio-guiseppe' ),
					'<strong>' . esc_html__( 'jusqu\'à 25 000 €/an de commissions', 'brio-guiseppe' ) . '</strong>'
				);
				?>
			</p>
		</div>

		<div class="home-hero__actions">
			<a href="#audit" class="btn btn-primary btn-lg">
				<?php esc_html_e( 'Réserver mon audit gratuit', 'brio-guiseppe' ); ?>
			</a>
			<a href="#calculateur" class="btn btn-secondary btn-lg">
				<?php esc_html_e( 'Calculer mes revenus perdus', 'brio-guiseppe' ); ?>
			</a>
		</div>

		<ul class="home-hero__features">
			<?php foreach ( $brio_hero_features as $feature ) : ?>
				<li class="home-hero__feature">
					<i class="fas fa-check home-hero__feature-icon" aria-hidden="true"></i>
					<div class="home-hero__feature-body">
						<span class="home-hero__feature-title"><?php echo esc_html( $feature['title'] ); ?></span>
						<span class="home-hero__feature-desc"><?php echo esc_html( $feature['desc'] ); ?></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="home-hero__suitcase" aria-hidden="true">
			<img
				src="<?php echo esc_url( $brio_hero_cdn . 'Travel-Suitcase-2.svg' ); ?>"
				alt=""
				loading="lazy"
				width="120"
				height="120"
			/>
		</div>

	</div>
</section>
