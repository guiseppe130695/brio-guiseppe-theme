<?php
/**
 * Theme Setup & SEO Defaults
 *
 * Registers theme support flags and outputs the SEO meta tags that WordPress
 * does not provide out of the box (meta description, OpenGraph basics).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme-support features.
 *
 * `title-tag` lets WordPress emit the <title> element via wp_head() — required
 * for Lighthouse SEO/Accessibility scoring. `html5` switches the search form,
 * comment list and gallery markup to semantic HTML5.
 *
 * @since 1.0.0
 */
function brio_setup_theme() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );

	register_nav_menu( 'primary',   __( 'Primary Menu', 'brio-guiseppe' ) );
	register_nav_menu( 'secondary', __( 'Secondary Menu', 'brio-guiseppe' ) );
}

/**
 * Rename the post_tag archive slug from /tag/{slug}/ to /definition/{slug}/.
 *
 * Uses `register_taxonomy_args` to patch only the rewrite slug, keeping every
 * other default WordPress sets up for post_tag (labels, capabilities, REST
 * exposure, query var "tag", etc.). Re-registering via `register_taxonomy()`
 * with incomplete args would break the admin UI and 404 the new archives.
 *
 * Filter `brio_tag_slug` if you ever want to change it again.
 *
 * @since 1.6.0
 */
function brio_rewrite_post_tag_slug( $args, $taxonomy ) {
	if ( 'post_tag' !== $taxonomy ) {
		return $args;
	}
	$slug = apply_filters( 'brio_tag_slug', 'definition' );

	// Only override rewrite — leave everything else (labels, caps, REST…) alone.
	$args['rewrite'] = [
		'slug'         => $slug,
		'with_front'   => false,
		'hierarchical' => false,
		'ep_mask'      => isset( $args['rewrite']['ep_mask'] ) ? $args['rewrite']['ep_mask'] : 0,
	];
	return $args;
}
add_filter( 'register_taxonomy_args', 'brio_rewrite_post_tag_slug', 10, 2 );

/**
 * Flush rewrite rules when our slug doesn't match what's currently registered.
 * Runs late on `init` (priority 99) so every taxonomy/CPT is registered first,
 * then we check the actual taxonomy object's rewrite slug — more reliable than
 * a stored option flag that can drift from reality.
 *
 * @since 1.6.0
 */
function brio_maybe_flush_tag_rewrites() {
	$target = apply_filters( 'brio_tag_slug', 'definition' );
	$tax    = get_taxonomy( 'post_tag' );
	if ( ! $tax ) {
		return;
	}
	$current = is_array( $tax->rewrite ) ? ( $tax->rewrite['slug'] ?? '' ) : '';
	if ( $current !== $target ) {
		// Mismatch in this request — nothing to do, the filter should have
		// applied. Bail to avoid loops.
		return;
	}
	if ( get_option( 'brio_tag_slug_flushed_v2' ) !== $target ) {
		flush_rewrite_rules( false );
		update_option( 'brio_tag_slug_flushed_v2', $target, false );
	}
}
add_action( 'init', 'brio_maybe_flush_tag_rewrites', 99 );

/**
 * 301-redirect legacy /tag/{slug}/ URLs to /definition/{slug}/ so existing
 * inbound links + Google's index transfer cleanly.
 *
 * Only fires when the legacy /tag/ prefix is requested AND a tag with that
 * slug actually exists — anything else falls through to the normal 404.
 *
 * @since 1.6.0
 */
function brio_redirect_old_tag_urls() {
	if ( is_admin() ) {
		return;
	}
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '';
	if ( ! preg_match( '#^/tag/([^/]+)/?$#', $path, $m ) ) {
		return;
	}
	$tag = get_term_by( 'slug', $m[1], 'post_tag' );
	if ( ! $tag || is_wp_error( $tag ) ) {
		return;
	}
	wp_safe_redirect( get_term_link( $tag ), 301 );
	exit;
}
add_action( 'template_redirect', 'brio_redirect_old_tag_urls' );

/**
 * Emit the <meta name="description"> tag in <head>.
 *
 * For singular content with an excerpt, uses the excerpt. Otherwise falls
 * back to the company tagline. Filterable via brio_meta_description.
 *
 * @since 1.0.0
 */
function brio_render_meta_description() {
	if ( is_singular() && has_excerpt() ) {
		$description = wp_strip_all_tags( get_the_excerpt() );
	} else {
		$company     = brio_get_company_data();
		$description = sprintf(
			/* translators: %s: company tagline. */
			__( '%s Sites web qui convertissent, SEO tourisme et stratégie de vente directe pour Riads, maisons d\'hôtes et hôtels indépendants.', 'brio-guiseppe' ),
			$company['tagline']
		);
	}

	$description = apply_filters( 'brio_meta_description', $description );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( wp_trim_words( $description, 30, '…' ) ) );
	}
}
add_action( 'wp_head', 'brio_render_meta_description', 1 );

/**
 * Add a custom LinkedIn field to the WordPress user profile.
 *
 * @since 1.0.0
 */
function brio_add_linkedin_profile_field( $user ) {
	?>
	<h3><?php esc_html_e( 'Réseaux sociaux (Brio)', 'brio-guiseppe' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="linkedin"><?php esc_html_e( 'URL LinkedIn', 'brio-guiseppe' ); ?></label></th>
			<td>
				<input type="url"
				       name="linkedin"
				       id="linkedin"
				       value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>"
				       class="regular-text"
				       placeholder="https://www.linkedin.com/in/votre-profil" />
				<p class="description"><?php esc_html_e( 'Affiché sur le bloc auteur des articles.', 'brio-guiseppe' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile',  'brio_add_linkedin_profile_field' );
add_action( 'edit_user_profile',  'brio_add_linkedin_profile_field' );

/**
 * Save the LinkedIn field.
 *
 * @since 1.0.0
 */
function brio_save_linkedin_profile_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	$linkedin = isset( $_POST['linkedin'] ) ? esc_url_raw( $_POST['linkedin'] ) : '';
	update_user_meta( $user_id, 'linkedin', $linkedin );
}
add_action( 'personal_options_update',  'brio_save_linkedin_profile_field' );
add_action( 'edit_user_profile_update', 'brio_save_linkedin_profile_field' );
