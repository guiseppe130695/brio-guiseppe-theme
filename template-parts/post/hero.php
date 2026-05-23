<?php
/**
 * Single post — Hero
 *
 * Fond primary, titre H1, breadcrumb auto, meta (auteur / date / catégorie /
 * temps de lecture), image featured avec grand border-radius.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_id    = get_the_ID();
$categories = get_the_category( $post_id );
$first_cat  = ! empty( $categories ) ? $categories[0] : null;

/* Temps de lecture estimé (~200 mots/min) */
$word_count   = str_word_count( wp_strip_all_tags( get_the_content() ) );
$reading_time = max( 1, (int) ceil( $word_count / 200 ) );
?>
<header class="post-hero">

	<h1 class="post-hero__title"><?php the_title(); ?></h1>

	<nav class="post-hero__crumbs" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'brio-guiseppe' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'brio-guiseppe' ); ?></a></li>
			<?php if ( $first_cat ) : ?>
				<li>
					<a href="<?php echo esc_url( get_category_link( $first_cat->term_id ) ); ?>">
						<?php echo esc_html( $first_cat->name ); ?>
					</a>
				</li>
			<?php endif; ?>
			<li><span aria-current="page"><?php the_title(); ?></span></li>
		</ol>
	</nav>

	<div class="post-hero__meta">
		<span class="post-hero__meta-item">
			<?php esc_html_e( 'Conseils partagés par', 'brio-guiseppe' ); ?>
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
				<?php the_author(); ?>
			</a>
		</span>
		<span class="post-hero__meta-sep" aria-hidden="true">/</span>
		<span class="post-hero__meta-item">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		</span>
		<?php if ( $first_cat ) : ?>
			<span class="post-hero__meta-sep" aria-hidden="true">/</span>
			<span class="post-hero__meta-item">
				<a href="<?php echo esc_url( get_category_link( $first_cat->term_id ) ); ?>">
					<?php echo esc_html( $first_cat->name ); ?>
				</a>
			</span>
		<?php endif; ?>
		<span class="post-hero__meta-sep" aria-hidden="true">/</span>
		<span class="post-hero__meta-item">
			<?php
			printf(
				/* translators: %d: reading time in minutes */
				esc_html( _n( '%d min de lecture', '%d min de lecture', $reading_time, 'brio-guiseppe' ) ),
				(int) $reading_time
			);
			?>
		</span>
	</div>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-hero__image">
			<?php the_post_thumbnail( 'large', [ 'loading' => 'eager' ] ); ?>
		</div>
	<?php endif; ?>

</header>
