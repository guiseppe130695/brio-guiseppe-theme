<?php
/**
 * Blog Section — Latest Posts
 *
 * Yellow section: centered overline + h2 + a 3-card grid of the most
 * recent published posts pulled live via WP_Query.
 *
 * Query is optimised (no_found_rows + skipped meta/term caches) since
 * the cards only need core post fields + thumbnail + author. Falls
 * back to a friendly placeholder when no posts are published yet.
 *
 * Outputs JSON-LD ItemList → BlogPosting structured data for SEO.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data  = brio_get_blog_data();
$query = new WP_Query( brio_get_blog_query_args( $data['posts_per_page'] ) );
?>

<section class="home-blog" id="blog">
	<div class="container">

		<?php if ( ! empty( $data['overline'] ) ) : ?>
			<p class="home-blog__overline">
				<?php echo esc_html( $data['overline'] ); ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $data['heading'] ) ) : ?>
			<h2 class="home-blog__heading">
				<?php echo esc_html( $data['heading'] ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( $query->have_posts() ) : ?>

			<ul class="home-blog__grid">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<li class="home-blog__card">
						<article class="home-blog__article">
							<a href="<?php the_permalink(); ?>" class="home-blog__link" aria-label="<?php echo esc_attr( get_the_title() ); ?>">

								<?php if ( has_post_thumbnail() ) : ?>
									<figure class="home-blog__media">
										<?php
										the_post_thumbnail(
											'medium_large',
											[
												'class'   => 'home-blog__image',
												'loading' => 'lazy',
												'alt'     => the_title_attribute( [ 'echo' => false ] ),
											]
										);
										?>
									</figure>
								<?php else : ?>
									<div class="home-blog__media home-blog__media--empty" aria-hidden="true"></div>
								<?php endif; ?>

								<div class="home-blog__body">
									<h3 class="home-blog__title"><?php the_title(); ?></h3>

									<p class="home-blog__meta">
										<span class="home-blog__meta-item">
											<i class="fas fa-user home-blog__meta-icon" aria-hidden="true"></i>
											<?php the_author(); ?>
										</span>
										<span class="home-blog__meta-item">
											<i class="fas fa-calendar home-blog__meta-icon" aria-hidden="true"></i>
											<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
												<?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
											</time>
										</span>
									</p>

									<p class="home-blog__excerpt">
										<?php echo esc_html( wp_trim_words( get_the_excerpt(), (int) $data['excerpt_words'], '…' ) ); ?>
									</p>
								</div>

							</a>
						</article>
					</li>
				<?php endwhile; ?>
			</ul>

		<?php else : ?>

			<p class="home-blog__empty">
				<?php echo esc_html( $data['empty_message'] ); ?>
			</p>

		<?php endif; ?>

	</div>
</section>

<?php
/* JSON-LD ItemList → BlogPosting · Schema.org structured data for SEO. */
if ( $query->have_posts() ) :
	$items = [];
	$pos   = 1;
	while ( $query->have_posts() ) :
		$query->the_post();
		$item = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'item'     => [
				'@type'         => 'BlogPosting',
				'headline'      => wp_strip_all_tags( get_the_title() ),
				'url'           => get_permalink(),
				'datePublished' => get_the_date( DATE_W3C ),
				'dateModified'  => get_the_modified_date( DATE_W3C ),
				'author'        => [
					'@type' => 'Person',
					'name'  => get_the_author(),
				],
			],
		];
		if ( has_post_thumbnail() ) {
			$item['item']['image'] = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		}
		$items[] = $item;
	endwhile;

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'ItemList',
		'itemListElement' => $items,
	];
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php
endif;

wp_reset_postdata();
