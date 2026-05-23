<?php
/**
 * Single post — Hero
 *
 * Structure HTML sémantique :
 *   <article> (microformat hentry, Schema.org Article)
 *     <header>
 *       <h1>        — titre principal (entry-title)
 *       <nav>       — fil d'Ariane
 *       <address>   — auteur (sémantique HTML5 pour les infos de contact/auteur)
 *       <p>         — meta (date, catégorie, temps de lecture)
 *       <figure>    — image à la une
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

	<h1 class="post-hero__title entry-title"><?php the_title(); ?></h1>

	<nav class="post-hero__crumbs" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'brio-guiseppe' ); ?>">
		<ol itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span itemprop="name"><?php esc_html_e( 'Accueil', 'brio-guiseppe' ); ?></span>
				</a>
				<meta itemprop="position" content="1" />
			</li>
			<?php if ( $first_cat ) : ?>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a itemprop="item" href="<?php echo esc_url( get_category_link( $first_cat->term_id ) ); ?>">
						<span itemprop="name"><?php echo esc_html( $first_cat->name ); ?></span>
					</a>
					<meta itemprop="position" content="2" />
				</li>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<span itemprop="name" aria-current="page"><?php the_title(); ?></span>
					<meta itemprop="position" content="3" />
				</li>
			<?php else : ?>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<span itemprop="name" aria-current="page"><?php the_title(); ?></span>
					<meta itemprop="position" content="2" />
				</li>
			<?php endif; ?>
		</ol>
	</nav>

	<div class="post-hero__meta">

		<address class="post-hero__author">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
				<?php the_author(); ?>
			</a>
		</address>

		<span class="post-hero__meta-sep" aria-hidden="true">/</span>

		<span class="post-hero__meta-item">
			<time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
			<?php if ( get_the_modified_date( 'U' ) > get_the_date( 'U' ) + DAY_IN_SECONDS ) : ?>
				<span class="post-hero__meta-updated">
					— <?php esc_html_e( 'Mis à jour le', 'brio-guiseppe' ); ?>
					<time class="updated" datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>">
						<?php echo esc_html( get_the_modified_date() ); ?>
					</time>
				</span>
			<?php endif; ?>
		</span>

		<?php if ( $first_cat ) : ?>
			<span class="post-hero__meta-sep" aria-hidden="true">/</span>
			<span class="post-hero__meta-item">
				<a href="<?php echo esc_url( get_category_link( $first_cat->term_id ) ); ?>" rel="category tag">
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

	<figure class="post-hero__image">
		<img
			src="<?php echo esc_url( brio_post_thumbnail_url( $post_id, 'large' ) ); ?>"
			alt="<?php the_title_attribute(); ?>"
			loading="eager"
			decoding="async"
			width="1200"
		/>
	</figure>

</header>
