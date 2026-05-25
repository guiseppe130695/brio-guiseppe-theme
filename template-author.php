<?php
/**
 * Template Name: Page Auteur
 *
 * Page dédiée à un auteur/expert. Les données structurées Person sont
 * injectées dans le @graph JSON-LD via includes/front/author.php et
 * référencées comme `author` par les autres templates (landing, blog,
 * outils, legal).
 *
 * @package Brio_Guiseppe
 * @since   1.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$post_id = get_queried_object_id();
$author  = brio_get_author_data( $post_id );
?>
<main id="main" class="site-main site-main--author" role="main">

	<section class="author-hero" aria-labelledby="author-hero-title">
		<div class="container author-hero__inner">

			<?php if ( ! empty( $author['photo'] ) ) : ?>
				<div class="author-hero__photo">
					<img src="<?php echo esc_url( $author['photo'] ); ?>"
					     alt="<?php echo esc_attr( $author['name'] ); ?>"
					     width="200" height="200" loading="eager" fetchpriority="high" />
				</div>
			<?php endif; ?>

			<div class="author-hero__content">
				<?php if ( ! empty( $author['role'] ) ) : ?>
					<p class="author-hero__role"><?php echo esc_html( $author['role'] ); ?></p>
				<?php endif; ?>

				<h1 id="author-hero-title" class="author-hero__name">
					<?php echo esc_html( $author['name'] ); ?>
				</h1>

				<?php if ( ! empty( $author['short_bio'] ) ) : ?>
					<p class="author-hero__lead"><?php echo esc_html( $author['short_bio'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $author['social'] ) ) : ?>
					<ul class="author-hero__social">
						<?php foreach ( $author['social'] as $label => $url ) : ?>
							<li>
								<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener me">
									<?php echo esc_html( $label ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $author['long_bio'] ) ) : ?>
		<section class="author-bio" aria-labelledby="author-bio-title">
			<div class="container author-bio__inner">
				<h2 id="author-bio-title" class="author-bio__title">
					<?php esc_html_e( 'Mon parcours', 'brio-guiseppe' ); ?>
				</h2>
				<div class="author-bio__body">
					<?php echo wp_kses_post( wpautop( $author['long_bio'] ) ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $author['credentials'] ) ) : ?>
		<section class="author-credentials" aria-labelledby="author-credentials-title">
			<div class="container author-credentials__inner">
				<h2 id="author-credentials-title" class="author-credentials__title">
					<?php esc_html_e( 'Crédibilité & expertise', 'brio-guiseppe' ); ?>
				</h2>

				<div class="author-credentials__grid">
					<?php if ( ! empty( $author['years_experience'] ) ) : ?>
						<div class="author-credentials__stat">
							<span class="author-credentials__stat-num"><?php echo esc_html( $author['years_experience'] ); ?>+</span>
							<span class="author-credentials__stat-label"><?php esc_html_e( 'années d\'expérience', 'brio-guiseppe' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $author['sectors'] ) ) : ?>
						<div class="author-credentials__stat">
							<span class="author-credentials__stat-num"><?php echo count( $author['sectors'] ); ?></span>
							<span class="author-credentials__stat-label"><?php esc_html_e( 'secteurs couverts', 'brio-guiseppe' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $author['credentials'] ) ) : ?>
					<ul class="author-credentials__list">
						<?php foreach ( $author['credentials'] as $c ) : ?>
							<li><?php echo esc_html( $c ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

</main>
<?php get_footer();
