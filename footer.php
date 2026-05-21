<?php
/**
 * Site Footer Template
 *
 * Three-row layout:
 *   Row 1: Brand + Newsletter
 *   Row 2: Navigation columns (Explorer, Contact, Services, Social)
 *   Row 3: Legal links and company identifiers
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$company = brio_get_company_data();
$columns = brio_get_footer_columns();
$legal   = brio_get_legal_data();
?>

<footer class="site-footer" role="contentinfo">

	<?php /* ============================================
		Row 1 — Brand + Newsletter
		============================================ */ ?>
	<div class="footer-inner footer-row-1">

		<div class="footer-col-left">
			<div class="footer-logo-block">
				<img
					src="<?php echo esc_url( brio_asset( 'footer', 'logo' ) ); ?>"
					alt="<?php echo esc_attr( $company['name'] ); ?>"
					class="footer-logo"
					loading="lazy"
				/>
				<p class="footer-desc">
					<?php esc_html_e( 'Votre site hôtel ne devrait pas être une vitrine. Il devrait réserver. Booking engine intégré, design mobile-first, SEO tourisme.', 'brio-guiseppe' ); ?>
				</p>
			</div>

			<h2 class="footer-tagline"><?php echo esc_html( $company['tagline'] ); ?></h2>

			<img
				src="<?php echo esc_url( brio_asset( 'footer', 'decoration' ) ); ?>"
				alt=""
				class="footer-asset-image"
				aria-hidden="true"
				loading="lazy"
			/>
		</div>

		<div class="footer-newsletter">
			<div class="footer-newsletter-content">
				<h2 class="footer-newsletter-title">
					<?php esc_html_e( 'Recevez nos actualités directement par email', 'brio-guiseppe' ); ?>
				</h2>

				<form class="newsletter-form" action="#" method="post">
					<label for="newsletter-email" class="screen-reader-text">
						<?php esc_html_e( 'Adresse email', 'brio-guiseppe' ); ?>
					</label>
					<input
						type="email"
						id="newsletter-email"
						name="email"
						placeholder="<?php esc_attr_e( 'Votre adresse email', 'brio-guiseppe' ); ?>"
						required
					/>
					<button type="submit" aria-label="<?php esc_attr_e( 'S\'inscrire à la newsletter', 'brio-guiseppe' ); ?>">→</button>
				</form>

				<p class="footer-newsletter-desc">
					<?php esc_html_e( 'Recevez chaque mois les tendances du direct booking, des données OTA exclusives, et des conseils SEO tourisme.', 'brio-guiseppe' ); ?>
				</p>
			</div>

			<img
				src="<?php echo esc_url( brio_asset( 'newsletter', 'background' ) ); ?>"
				alt=""
				class="footer-newsletter-image"
				aria-hidden="true"
				loading="lazy"
			/>
		</div>

	</div>

	<?php /* ============================================
		Row 2 — Navigation Columns
		============================================ */ ?>
	<div class="footer-inner footer-row-2">

		<?php /* Column 1 — Explorer */ ?>
		<div class="footer-col-1">
			<h2 class="footer-col-title"><?php echo esc_html( $columns['explorer']['title'] ); ?></h2>
			<ul class="footer-links">
				<?php foreach ( $columns['explorer']['links'] as $link ) : ?>
					<li>
						<a href="<?php echo esc_url( $link['url'] ); ?>">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php /* Column 2 — Contact (built inline because it uses company data) */ ?>
		<div class="footer-col-2">
			<h2 class="footer-col-title"><?php esc_html_e( 'Contact', 'brio-guiseppe' ); ?></h2>
			<ul class="footer-links">
				<li>
					<a href="#">
						<i class="fas fa-map-marker-alt" aria-hidden="true"></i>
						<?php echo esc_html( $company['address'] ); ?>
					</a>
				</li>
				<?php foreach ( $company['phones'] as $phone ) : ?>
					<li>
						<a href="tel:<?php echo esc_attr( $phone['tel'] ); ?>">
							<i class="fas fa-phone" aria-hidden="true"></i>
							<?php echo esc_html( $phone['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<a href="mailto:<?php echo esc_attr( $company['email'] ); ?>">
						<i class="fas fa-envelope" aria-hidden="true"></i>
						<?php echo esc_html( $company['email'] ); ?>
					</a>
				</li>
			</ul>
		</div>

		<?php /* Column 3 — Services */ ?>
		<div class="footer-col-3">
			<h2 class="footer-col-title"><?php echo esc_html( $columns['services']['title'] ); ?></h2>
			<ul class="footer-links">
				<?php foreach ( $columns['services']['links'] as $link ) : ?>
					<li>
						<a href="<?php echo esc_url( $link['url'] ); ?>">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php /* Column 4 — Social */ ?>
		<div class="footer-col-4">
			<h2 class="footer-col-title"><?php esc_html_e( 'Suivez-moi', 'brio-guiseppe' ); ?></h2>
			<ul class="footer-links">
				<?php if ( ! empty( $company['social']['linkedin'] ) ) : ?>
					<li>
						<a href="<?php echo esc_url( $company['social']['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer">
							<i class="fab fa-linkedin-in" aria-hidden="true"></i>
							<?php esc_html_e( 'LinkedIn', 'brio-guiseppe' ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>

	</div>

	<?php /* ============================================
		Row 3 — Legal Identifiers + Policy Links
		============================================ */ ?>
	<div class="footer-inner footer-row-3">
		<a href="<?php echo esc_url( $legal['pages']['privacy']['url'] ); ?>" class="footer-bottom-link">
			<?php echo esc_html( $legal['pages']['privacy']['label'] ); ?>
		</a>
		<span class="footer-bottom-sep">
			<?php
			/* translators: %s: Moroccan business identifier (ICE). */
			printf( esc_html__( 'Identifiant Commun de l\'entreprise (ICE): %s', 'brio-guiseppe' ), esc_html( $legal['ice'] ) );
			?>
		</span>
		<span class="footer-bottom-sep">
			<?php
			/* translators: %s: French fiscal identifier. */
			printf( esc_html__( 'Identifiant fiscal: %s', 'brio-guiseppe' ), esc_html( $legal['fiscal_id'] ) );
			?>
		</span>
		<a href="<?php echo esc_url( $legal['pages']['legal']['url'] ); ?>" class="footer-bottom-link">
			<?php echo esc_html( $legal['pages']['legal']['label'] ); ?>
		</a>
	</div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
