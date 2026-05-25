<?php
/**
 * Author (E-E-A-T) — data provider + JSON-LD Person + cross-template injection
 *
 * Drives the template-author.php page and exposes the Person node to every
 * other template that should declare authorship: landings (Service.author),
 * blog posts (Article.author), outils (WebPage.author), legal (WebPage.author).
 *
 * Storage:
 *   - One or more WordPress Pages using template-author.php hold author data
 *     in post meta (`_brio_author_*`).
 *   - The "default author" for cross-page injection is set via the
 *     `brio_default_author_page_id` option (configurable in Customizer or
 *     via filter), so the wiring stays dynamic.
 *   - Per-page override: any page can set `_brio_author_page` meta to point
 *     to a specific author page ID.
 *
 * @package Brio_Guiseppe
 * @since   1.4.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_AUTHOR_TEMPLATE = 'template-author.php';

/**
 * Read author data from a page using template-author.php.
 *
 * @param int $post_id Author page ID.
 * @return array{name:string,role:string,photo:string,short_bio:string,long_bio:string,credentials:string[],sectors:string[],years_experience:string,email:string,phone:string,social:array<string,string>}
 */
function brio_get_author_data( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return brio_author_data_defaults();
	}

	$credentials_raw = get_post_meta( $post_id, '_brio_author_credentials', true );
	$sectors_raw     = get_post_meta( $post_id, '_brio_author_sectors', true );

	$credentials = $credentials_raw
		? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $credentials_raw ) ) ) )
		: [];
	$sectors = $sectors_raw
		? array_values( array_filter( array_map( 'trim', explode( ',', $sectors_raw ) ) ) )
		: [];

	$social = [];
	foreach ( [ 'linkedin', 'twitter', 'website', 'github' ] as $key ) {
		$val = get_post_meta( $post_id, '_brio_author_social_' . $key, true );
		if ( $val ) {
			$label = ucfirst( $key );
			$social[ $label ] = $val;
		}
	}

	$name = get_post_meta( $post_id, '_brio_author_name', true );
	if ( ! $name ) {
		$name = get_the_title( $post_id );
	}

	$data = [
		'page_id'          => $post_id,
		'permalink'        => get_permalink( $post_id ),
		'name'             => (string) $name,
		'role'             => (string) get_post_meta( $post_id, '_brio_author_role', true ),
		'photo'            => (string) get_post_meta( $post_id, '_brio_author_photo', true ),
		'short_bio'        => (string) get_post_meta( $post_id, '_brio_author_short_bio', true ),
		'long_bio'         => (string) get_post_meta( $post_id, '_brio_author_long_bio', true ),
		'credentials'      => $credentials,
		'sectors'          => $sectors,
		'years_experience' => (string) get_post_meta( $post_id, '_brio_author_years', true ),
		'email'            => (string) get_post_meta( $post_id, '_brio_author_email', true ),
		'phone'            => (string) get_post_meta( $post_id, '_brio_author_phone', true ),
		'social'           => $social,
	];

	return apply_filters( 'brio_author_data', $data, $post_id );
}

function brio_author_data_defaults() {
	return [
		'page_id'          => 0,
		'permalink'        => '',
		'name'             => '',
		'role'             => '',
		'photo'            => '',
		'short_bio'        => '',
		'long_bio'         => '',
		'credentials'      => [],
		'sectors'          => [],
		'years_experience' => '',
		'email'            => '',
		'phone'            => '',
		'social'           => [],
	];
}

/**
 * Resolve the author page ID to use for a given content page.
 *
 *   1. Per-page meta `_brio_author_page` (explicit override)
 *   2. Site-wide default option `brio_default_author_page_id`
 *   3. First published page using template-author.php (auto-discovery)
 *
 * @param int $context_post_id Page where the author should be attributed.
 * @return int Author page ID or 0 if none found.
 */
function brio_resolve_author_id( $context_post_id = 0 ) {
	if ( $context_post_id ) {
		$explicit = (int) get_post_meta( $context_post_id, '_brio_author_page', true );
		if ( $explicit && brio_is_author_page( $explicit ) ) {
			return $explicit;
		}
	}

	$default = (int) get_option( 'brio_default_author_page_id', 0 );
	if ( $default && brio_is_author_page( $default ) ) {
		return $default;
	}

	$found = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => BRIO_AUTHOR_TEMPLATE,
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'ASC',
	] );
	return ! empty( $found ) ? (int) $found[0] : 0;
}

function brio_is_author_page( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	if ( 'publish' !== get_post_status( $post_id ) ) {
		return false;
	}
	return BRIO_AUTHOR_TEMPLATE === get_page_template_slug( $post_id );
}

/**
 * Build the Person JSON-LD node for an author page.
 *
 * @param int $author_page_id Page ID.
 * @return array|null         schema.org/Person node, or null if no data.
 */
function brio_author_person_node( $author_page_id ) {
	$author_page_id = (int) $author_page_id;
	if ( ! $author_page_id || ! brio_is_author_page( $author_page_id ) ) {
		return null;
	}
	$a = brio_get_author_data( $author_page_id );
	if ( empty( $a['name'] ) ) {
		return null;
	}

	$node = [
		'@type' => 'Person',
		'@id'   => $a['permalink'] . '#person',
		'name'  => $a['name'],
		'url'   => $a['permalink'],
	];
	if ( $a['role'] ) {
		$node['jobTitle'] = $a['role'];
	}
	if ( $a['short_bio'] ) {
		$node['description'] = $a['short_bio'];
	}
	if ( $a['photo'] ) {
		$node['image'] = $a['photo'];
	}
	if ( ! empty( $a['social'] ) ) {
		$node['sameAs'] = array_values( $a['social'] );
	}
	// Tie the person to the org (works as an employee/founder signal).
	$node['worksFor'] = [ '@id' => home_url( '/#organization' ) ];

	if ( ! empty( $a['credentials'] ) ) {
		$node['hasCredential'] = array_map(
			fn( $c ) => [
				'@type'           => 'EducationalOccupationalCredential',
				'credentialCategory' => 'certification',
				'name'            => $c,
			],
			$a['credentials']
		);
	}
	if ( $a['email'] ) {
		$node['email'] = $a['email'];
	}
	if ( $a['phone'] ) {
		$node['telephone'] = $a['phone'];
	}
	return $node;
}

/**
 * Inject the Person node into the @graph for every covered template
 * (landings, blog posts, outils, legal). Always declares the Person node
 * AND references it as the `author` of the relevant content node when one
 * already exists in the graph.
 */
function brio_author_extend_graph( $graph ) {
	$context_id = is_singular() ? get_queried_object_id() : 0;
	$author_id  = brio_resolve_author_id( $context_id );
	if ( ! $author_id ) {
		return $graph;
	}

	$person = brio_author_person_node( $author_id );
	if ( ! $person ) {
		return $graph;
	}

	// Avoid duplicates: replace existing Person with same @id.
	foreach ( $graph as $key => $node ) {
		if ( isset( $node['@id'] ) && $node['@id'] === $person['@id'] ) {
			$graph[ $key ] = $person;
			$person_already_in = true;
			break;
		}
	}
	if ( empty( $person_already_in ) ) {
		$graph[] = $person;
	}

	// Wire as author on landing Service / blog Article / generic WebPage nodes.
	$attach_to_types = [ 'Service', 'Article', 'NewsArticle', 'BlogPosting', 'WebPage' ];
	foreach ( $graph as $key => $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
			continue;
		}
		$type = $node['@type'];
		if ( in_array( $type, $attach_to_types, true ) && empty( $node['author'] ) ) {
			$graph[ $key ]['author'] = [ '@id' => $person['@id'] ];
		}
	}

	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_author_extend_graph', 20 );

/* ─────────────────────────────────────────────────────────────────────
 * Admin: meta box on author pages
 * ────────────────────────────────────────────────────────────────── */

function brio_author_register_meta_box() {
	global $post;
	if ( ! $post || BRIO_AUTHOR_TEMPLATE !== get_page_template_slug( $post->ID ) ) {
		return;
	}
	add_meta_box(
		'brio_author_box',
		__( 'Données de l\'auteur (Person schema)', 'brio-guiseppe' ),
		'brio_author_render_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'brio_author_register_meta_box' );

function brio_author_render_meta_box( $post ) {
	wp_nonce_field( 'brio_author_save', 'brio_author_nonce' );
	$id = $post->ID;
	$g  = fn( $k ) => get_post_meta( $id, '_brio_author_' . $k, true );
	?>
	<style>
		.brio-author-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; }
		.brio-author-grid label { display:block; font-weight:600; font-size:13px; margin-bottom:4px; }
		.brio-author-grid .full { grid-column: 1 / -1; }
		.brio-author-grid input[type=text], .brio-author-grid input[type=url], .brio-author-grid input[type=email], .brio-author-grid textarea {
			width: 100%; padding: 6px 8px; border:1px solid #d1d5db; border-radius:6px;
		}
		.brio-author-grid p.description { margin: 4px 0 0; font-size:12px; color:#64748b; }
	</style>
	<div class="brio-author-grid">

		<div>
			<label><?php esc_html_e( 'Nom complet', 'brio-guiseppe' ); ?></label>
			<input type="text" name="brio_author_name" value="<?php echo esc_attr( $g( 'name' ) ); ?>" placeholder="<?php esc_attr_e( 'Laissé vide = titre de la page', 'brio-guiseppe' ); ?>" />
		</div>
		<div>
			<label><?php esc_html_e( 'Rôle / Titre', 'brio-guiseppe' ); ?></label>
			<input type="text" name="brio_author_role" value="<?php echo esc_attr( $g( 'role' ) ); ?>" placeholder="Fondateur, expert hôtellerie digitale" />
		</div>

		<div class="full">
			<label><?php esc_html_e( 'Photo (URL)', 'brio-guiseppe' ); ?></label>
			<input type="url" name="brio_author_photo" value="<?php echo esc_attr( $g( 'photo' ) ); ?>" placeholder="https://…/photo.jpg" />
		</div>

		<div class="full">
			<label><?php esc_html_e( 'Bio courte (1-2 phrases)', 'brio-guiseppe' ); ?></label>
			<textarea name="brio_author_short_bio" rows="2"><?php echo esc_textarea( $g( 'short_bio' ) ); ?></textarea>
		</div>

		<div class="full">
			<label><?php esc_html_e( 'Bio longue (parcours, expertise)', 'brio-guiseppe' ); ?></label>
			<textarea name="brio_author_long_bio" rows="8"><?php echo esc_textarea( $g( 'long_bio' ) ); ?></textarea>
		</div>

		<div>
			<label><?php esc_html_e( 'Années d\'expérience', 'brio-guiseppe' ); ?></label>
			<input type="text" name="brio_author_years" value="<?php echo esc_attr( $g( 'years' ) ); ?>" placeholder="10" />
		</div>
		<div>
			<label><?php esc_html_e( 'Secteurs (séparés par virgule)', 'brio-guiseppe' ); ?></label>
			<input type="text" name="brio_author_sectors" value="<?php echo esc_attr( $g( 'sectors' ) ); ?>" placeholder="Hôtellerie, Riads, Maisons d'hôtes" />
		</div>

		<div class="full">
			<label><?php esc_html_e( 'Credentials / Certifications (un par ligne)', 'brio-guiseppe' ); ?></label>
			<textarea name="brio_author_credentials" rows="4" placeholder="Google Ads Certified&#10;Booking.com Partner&#10;…"><?php echo esc_textarea( $g( 'credentials' ) ); ?></textarea>
		</div>

		<div>
			<label>LinkedIn</label>
			<input type="url" name="brio_author_social_linkedin" value="<?php echo esc_attr( $g( 'social_linkedin' ) ); ?>" />
		</div>
		<div>
			<label>Twitter / X</label>
			<input type="url" name="brio_author_social_twitter" value="<?php echo esc_attr( $g( 'social_twitter' ) ); ?>" />
		</div>
		<div>
			<label><?php esc_html_e( 'Site personnel', 'brio-guiseppe' ); ?></label>
			<input type="url" name="brio_author_social_website" value="<?php echo esc_attr( $g( 'social_website' ) ); ?>" />
		</div>
		<div>
			<label>GitHub</label>
			<input type="url" name="brio_author_social_github" value="<?php echo esc_attr( $g( 'social_github' ) ); ?>" />
		</div>

		<div>
			<label>Email</label>
			<input type="email" name="brio_author_email" value="<?php echo esc_attr( $g( 'email' ) ); ?>" />
		</div>
		<div>
			<label><?php esc_html_e( 'Téléphone', 'brio-guiseppe' ); ?></label>
			<input type="text" name="brio_author_phone" value="<?php echo esc_attr( $g( 'phone' ) ); ?>" />
		</div>

		<div class="full" style="background:#f0fdf4;border:1px solid #86efac;padding:10px 12px;border-radius:8px">
			<label style="display:flex;align-items:center;gap:8px;margin:0">
				<input type="checkbox" name="brio_author_set_default" value="1" <?php checked( (int) get_option( 'brio_default_author_page_id', 0 ), $id ); ?> />
				<?php esc_html_e( 'Désigner comme auteur par défaut du site (utilisé pour les landings, blog, outils, legal)', 'brio-guiseppe' ); ?>
			</label>
		</div>

	</div>
	<?php
}

function brio_author_save_meta( $post_id ) {
	if ( ! isset( $_POST['brio_author_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_author_nonce'] ) ), 'brio_author_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$text_fields = [ 'name', 'role', 'short_bio', 'long_bio', 'credentials', 'sectors', 'years', 'phone' ];
	foreach ( $text_fields as $f ) {
		$key = 'brio_author_' . $f;
		if ( isset( $_POST[ $key ] ) ) {
			$val = sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) );
			update_post_meta( $post_id, '_brio_author_' . $f, $val );
		}
	}

	if ( isset( $_POST['brio_author_photo'] ) ) {
		update_post_meta( $post_id, '_brio_author_photo', esc_url_raw( wp_unslash( $_POST['brio_author_photo'] ) ) );
	}
	if ( isset( $_POST['brio_author_email'] ) ) {
		update_post_meta( $post_id, '_brio_author_email', sanitize_email( wp_unslash( $_POST['brio_author_email'] ) ) );
	}
	foreach ( [ 'linkedin', 'twitter', 'website', 'github' ] as $net ) {
		$key = 'brio_author_social_' . $net;
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, '_brio_author_social_' . $net, esc_url_raw( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// Default-author toggle.
	if ( ! empty( $_POST['brio_author_set_default'] ) ) {
		update_option( 'brio_default_author_page_id', (int) $post_id, false );
	} elseif ( (int) get_option( 'brio_default_author_page_id', 0 ) === (int) $post_id ) {
		delete_option( 'brio_default_author_page_id' );
	}
}
add_action( 'save_post_page', 'brio_author_save_meta' );

/* ─────────────────────────────────────────────────────────────────────
 * Optional per-page override: which author owns this page?
 * Useful when one landing should attribute a different author than the default.
 * ────────────────────────────────────────────────────────────────── */

function brio_author_per_page_meta_box() {
	global $post;
	if ( ! $post ) {
		return;
	}
	// Don't show on author pages themselves.
	if ( BRIO_AUTHOR_TEMPLATE === get_page_template_slug( $post->ID ) ) {
		return;
	}
	add_meta_box(
		'brio_author_assign_box',
		__( 'Auteur de la page (E-E-A-T)', 'brio-guiseppe' ),
		'brio_author_per_page_render',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'brio_author_per_page_meta_box' );

function brio_author_per_page_render( $post ) {
	wp_nonce_field( 'brio_author_assign_save', 'brio_author_assign_nonce' );
	$current = (int) get_post_meta( $post->ID, '_brio_author_page', true );
	$authors = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => BRIO_AUTHOR_TEMPLATE,
		'posts_per_page' => 50,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );
	$default = (int) get_option( 'brio_default_author_page_id', 0 );
	?>
	<p style="margin:0 0 8px;color:#64748b;font-size:12px">
		<?php esc_html_e( 'Laisser sur "Auteur par défaut du site" sauf raison spécifique.', 'brio-guiseppe' ); ?>
	</p>
	<select name="brio_author_page" style="width:100%">
		<option value="0"><?php
			if ( $default ) {
				printf(
					/* translators: %s: author name */
					esc_html__( '— Auteur par défaut (%s) —', 'brio-guiseppe' ),
					esc_html( get_the_title( $default ) )
				);
			} else {
				esc_html_e( '— Aucun (pas d\'auteur déclaré) —', 'brio-guiseppe' );
			}
		?></option>
		<?php foreach ( $authors as $a ) : ?>
			<option value="<?php echo (int) $a->ID; ?>" <?php selected( $current, $a->ID ); ?>>
				<?php echo esc_html( $a->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

function brio_author_per_page_save( $post_id ) {
	if ( ! isset( $_POST['brio_author_assign_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_author_assign_nonce'] ) ), 'brio_author_assign_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$val = isset( $_POST['brio_author_page'] ) ? (int) $_POST['brio_author_page'] : 0;
	if ( $val ) {
		update_post_meta( $post_id, '_brio_author_page', $val );
	} else {
		delete_post_meta( $post_id, '_brio_author_page' );
	}
}
add_action( 'save_post_page', 'brio_author_per_page_save' );
