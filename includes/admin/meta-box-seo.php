<?php
/**
 * Meta Box — SEO (title + description override)
 *
 * Yoast-style controls for every page/post: custom SEO title with variable
 * substitution and meta description. A live "Google SERP" preview at the top
 * shows how the result will appear in search results.
 *
 * Supported variables (case-sensitive, replaced at render time via
 * brio_seo_resolve_variables()):
 *
 *   %%title%%        Post/page title
 *   %%sitename%%     Site name (Settings → General)
 *   %%tagline%%      Site tagline
 *   %%sep%%          Separator (default: " · ", filterable via brio_seo_separator)
 *   %%page%%         Current page number for paginated archives (else empty)
 *   %%category%%     First post category name (single posts only)
 *   %%date%%         Post publish date in site format
 *   %%author%%       Post author display name
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_seo_register_meta_box() {
	foreach ( [ 'page', 'post' ] as $type ) {
		add_meta_box(
			'brio_seo_meta',
			__( 'SEO — Titre & Méta description', 'brio-guiseppe' ),
			'brio_seo_render_meta_box',
			$type,
			'normal',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'brio_seo_register_meta_box' );

function brio_seo_render_meta_box( $post ) {
	wp_nonce_field( 'brio_seo_save', 'brio_seo_nonce' );
	$seo_title = get_post_meta( $post->ID, '_brio_seo_title', true );
	$seo_desc  = get_post_meta( $post->ID, '_brio_seo_description', true );

	// Resolve preview values right now so the editor sees the final result.
	$resolved_title = $seo_title
		? brio_seo_resolve_variables( $seo_title, $post )
		: get_the_title( $post ) . ' ' . brio_seo_separator() . ' ' . get_bloginfo( 'name' );
	$resolved_desc = $seo_desc ?: __( '(Extrait automatique — remplissez le champ pour personnaliser.)', 'brio-guiseppe' );
	$permalink     = get_permalink( $post );
	?>
	<style>
	.brio-seo-preview { border: 1px solid #dadce0; border-radius: 10px; padding: 16px 20px; margin-bottom: 18px; background: #fff; font-family: arial, sans-serif; max-width: 600px; }
	.brio-seo-preview__url { color: #202124; font-size: 14px; line-height: 1.3; }
	.brio-seo-preview__url small { color: #5f6368; font-size: 12px; }
	.brio-seo-preview__title { color: #1a0dab; font-size: 20px; line-height: 1.3; margin: 2px 0 4px; font-weight: 400; cursor: text; }
	.brio-seo-preview__title:visited { color: #6c00a2; }
	.brio-seo-preview__desc { color: #4d5156; font-size: 14px; line-height: 1.58; }
	.brio-seo-field { margin: 14px 0; }
	.brio-seo-field label { display: block; font-weight: 600; margin-bottom: 4px; }
	.brio-seo-field input[type=text], .brio-seo-field textarea { width: 100%; max-width: 700px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
	.brio-seo-field .brio-seo-count { font-size: 12px; color: #64748b; float: right; }
	.brio-seo-field .brio-seo-count.over { color: #dc2626; font-weight: 600; }
	.brio-seo-vars { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; margin: 6px 0 0; font-size: 12px; color: #475569; }
	.brio-seo-vars strong { color: #0f172a; }
	.brio-seo-vars code { background: #fff; border: 1px solid #e5e7eb; padding: 1px 6px; border-radius: 4px; cursor: pointer; }
	.brio-seo-vars code:hover { background: #fef3c7; border-color: #fcd34d; }
	</style>

	<div class="brio-seo-preview" id="brio-seo-preview">
		<div class="brio-seo-preview__url">
			<small><?php echo esc_html( wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ); ?> &nbsp;›&nbsp; <?php echo esc_html( ltrim( wp_make_link_relative( $permalink ), '/' ) ); ?></small>
		</div>
		<div class="brio-seo-preview__title" id="brio-seo-preview-title"><?php echo esc_html( $resolved_title ); ?></div>
		<div class="brio-seo-preview__desc" id="brio-seo-preview-desc"><?php echo esc_html( $resolved_desc ); ?></div>
	</div>

	<div class="brio-seo-field">
		<label for="brio_seo_title">
			<?php esc_html_e( 'Titre SEO', 'brio-guiseppe' ); ?>
			<span class="brio-seo-count" id="brio-seo-title-count">0</span>
		</label>
		<input type="text" id="brio_seo_title" name="brio_seo_title" value="<?php echo esc_attr( $seo_title ); ?>"
		       placeholder="<?php echo esc_attr( '%%title%% ' . brio_seo_separator() . ' %%sitename%%' ); ?>"
		       data-target="brio-seo-preview-title" />
		<p class="description" style="margin:4px 0 0;color:#64748b;font-size:12px">
			<?php esc_html_e( 'Laissez vide pour utiliser : Titre de la page · Nom du site. Idéal : 50–60 caractères.', 'brio-guiseppe' ); ?>
		</p>
	</div>

	<div class="brio-seo-field">
		<label for="brio_seo_description">
			<?php esc_html_e( 'Méta description', 'brio-guiseppe' ); ?>
			<span class="brio-seo-count" id="brio-seo-desc-count">0</span>
		</label>
		<textarea id="brio_seo_description" name="brio_seo_description" rows="3" maxlength="320"
		          data-target="brio-seo-preview-desc"
		          placeholder="<?php esc_attr_e( 'Description courte qui apparaît sous le titre dans Google. 155–160 caractères recommandés.', 'brio-guiseppe' ); ?>"><?php echo esc_textarea( $seo_desc ); ?></textarea>
		<p class="description" style="margin:4px 0 0;color:#64748b;font-size:12px">
			<?php esc_html_e( 'Laissez vide pour utiliser un extrait automatique du contenu. Idéal : 155–160 caractères.', 'brio-guiseppe' ); ?>
		</p>
	</div>

	<div class="brio-seo-vars">
		<strong><?php esc_html_e( 'Variables disponibles', 'brio-guiseppe' ); ?>:</strong>
		<?php
		$vars = [
			'%%title%%'    => __( 'Titre de la page', 'brio-guiseppe' ),
			'%%sitename%%' => __( 'Nom du site', 'brio-guiseppe' ),
			'%%tagline%%'  => __( 'Slogan du site', 'brio-guiseppe' ),
			'%%sep%%'      => __( 'Séparateur', 'brio-guiseppe' ),
			'%%category%%' => __( '1ère catégorie (posts)', 'brio-guiseppe' ),
			'%%date%%'     => __( 'Date de publication', 'brio-guiseppe' ),
			'%%author%%'   => __( 'Auteur', 'brio-guiseppe' ),
		];
		$parts = [];
		foreach ( $vars as $tag => $desc ) {
			$parts[] = '<code data-var="' . esc_attr( $tag ) . '" title="' . esc_attr( $desc ) . '">' . esc_html( $tag ) . '</code>';
		}
		echo implode( ' &nbsp; ', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<br><em style="color:#94a3b8;font-size:11px">
			<?php esc_html_e( 'Cliquez sur une variable pour l\'insérer dans le titre.', 'brio-guiseppe' ); ?>
		</em>
	</div>

	<script>
	( function () {
		var titleInput = document.getElementById( 'brio_seo_title' );
		var descInput  = document.getElementById( 'brio_seo_description' );
		var previewT   = document.getElementById( 'brio-seo-preview-title' );
		var previewD   = document.getElementById( 'brio-seo-preview-desc' );
		var countT     = document.getElementById( 'brio-seo-title-count' );
		var countD     = document.getElementById( 'brio-seo-desc-count' );

		// Variable substitutions for live preview (mirror of PHP resolver).
		var DATA = <?php echo wp_json_encode( [
			'title'    => get_the_title( $post ),
			'sitename' => get_bloginfo( 'name' ),
			'tagline'  => get_bloginfo( 'description' ),
			'sep'      => brio_seo_separator(),
			'category' => brio_seo_first_category_name( $post ),
			'date'     => get_the_date( '', $post ),
			'author'   => get_the_author_meta( 'display_name', $post->post_author ),
		] ); ?>;

		function resolve( str ) {
			return str
				.replace( /%%title%%/g,    DATA.title )
				.replace( /%%sitename%%/g, DATA.sitename )
				.replace( /%%tagline%%/g,  DATA.tagline )
				.replace( /%%sep%%/g,      DATA.sep )
				.replace( /%%category%%/g, DATA.category )
				.replace( /%%date%%/g,     DATA.date )
				.replace( /%%author%%/g,   DATA.author );
		}

		function updateTitle() {
			var v = titleInput.value.trim();
			var resolved = v ? resolve( v ) : ( DATA.title + ' ' + DATA.sep + ' ' + DATA.sitename );
			previewT.textContent = resolved;
			var len = resolved.length;
			countT.textContent = len + ' / 60';
			countT.classList.toggle( 'over', len > 60 );
		}
		function updateDesc() {
			var v = descInput.value.trim();
			previewD.textContent = v || '<?php echo esc_js( __( '(Extrait automatique — remplissez le champ pour personnaliser.)', 'brio-guiseppe' ) ); ?>';
			var len = v.length;
			countD.textContent = len + ' / 160';
			countD.classList.toggle( 'over', len > 160 );
		}

		titleInput.addEventListener( 'input', updateTitle );
		descInput.addEventListener( 'input', updateDesc );
		updateTitle();
		updateDesc();

		// Click-to-insert variables.
		document.querySelectorAll( '.brio-seo-vars code[data-var]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				var v = el.dataset.var;
				var pos = titleInput.selectionStart || titleInput.value.length;
				titleInput.value = titleInput.value.slice( 0, pos ) + v + titleInput.value.slice( pos );
				titleInput.focus();
				titleInput.dispatchEvent( new Event( 'input' ) );
			} );
		} );
	} )();
	</script>
	<?php
}

function brio_seo_save_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['brio_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_seo_nonce'] ) ), 'brio_seo_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$title = isset( $_POST['brio_seo_title'] )
		? sanitize_text_field( wp_unslash( $_POST['brio_seo_title'] ) )
		: '';
	$desc = isset( $_POST['brio_seo_description'] )
		? sanitize_textarea_field( wp_unslash( $_POST['brio_seo_description'] ) )
		: '';

	if ( '' === $title ) {
		delete_post_meta( $post_id, '_brio_seo_title' );
	} else {
		update_post_meta( $post_id, '_brio_seo_title', $title );
	}
	update_post_meta( $post_id, '_brio_seo_description', $desc );
}
add_action( 'save_post', 'brio_seo_save_meta_box' );

/* ─────────────────────────────────────────────────────────────────────
 * Variable resolution helpers
 * ────────────────────────────────────────────────────────────────── */

/**
 * Title separator. Filterable via `brio_seo_separator`.
 *
 * @return string
 */
function brio_seo_separator() {
	return apply_filters( 'brio_seo_separator', '·' );
}

function brio_seo_first_category_name( $post ) {
	if ( ! $post || 'post' !== get_post_type( $post ) ) {
		return '';
	}
	$cats = get_the_category( $post->ID );
	return ! empty( $cats ) ? $cats[0]->name : '';
}

/**
 * Replace %%variables%% in a string. Returns the same string if no variables.
 *
 * @param string       $template The pattern with %%variables%%.
 * @param WP_Post|null $post     Post context.
 * @return string
 */
function brio_seo_resolve_variables( $template, $post = null ) {
	if ( false === strpos( $template, '%%' ) ) {
		return $template;
	}
	if ( ! $post && is_singular() ) {
		$post = get_queried_object();
	}
	$replacements = [
		'%%title%%'    => $post ? get_the_title( $post ) : wp_get_document_title(),
		'%%sitename%%' => get_bloginfo( 'name' ),
		'%%tagline%%'  => get_bloginfo( 'description' ),
		'%%sep%%'      => brio_seo_separator(),
		'%%page%%'     => (int) get_query_var( 'paged' ) > 1 ? (int) get_query_var( 'paged' ) : '',
		'%%category%%' => $post ? brio_seo_first_category_name( $post ) : '',
		'%%date%%'     => $post ? get_the_date( '', $post ) : '',
		'%%author%%'   => $post ? get_the_author_meta( 'display_name', $post->post_author ) : '',
	];
	$out = strtr( $template, $replacements );
	// Collapse leftover double-separators and trim them off the edges.
	$sep = preg_quote( brio_seo_separator(), '/' );
	$out = preg_replace( '/(\s*' . $sep . '\s*){2,}/', ' ' . brio_seo_separator() . ' ', $out );
	$out = trim( $out, " \t\n\r\0\x0B" . brio_seo_separator() );
	return $out;
}

/* ─────────────────────────────────────────────────────────────────────
 * Front-end : override <title> via document_title_parts + pre_get_document_title
 * ────────────────────────────────────────────────────────────────── */

/**
 * Highest-priority filter: if a custom SEO title is set on this singular OR
 * on the queried taxonomy term (category, tag), we return it verbatim
 * (after variable resolution) and short-circuit WordPress' own title pipeline.
 */
function brio_seo_override_document_title( $title ) {
	// Singular pages/posts.
	if ( is_singular() ) {
		$post_id   = get_queried_object_id();
		$seo_title = get_post_meta( $post_id, '_brio_seo_title', true );
		if ( $seo_title ) {
			return brio_seo_resolve_variables( $seo_title, get_post( $post_id ) );
		}
		return $title;
	}

	// Category / tag archives.
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->term_id ) ) {
			$seo_title = get_term_meta( $term->term_id, '_brio_seo_title', true );
			if ( $seo_title ) {
				return brio_seo_resolve_term_variables( $seo_title, $term );
			}
		}
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'brio_seo_override_document_title', 999 );

/* ─────────────────────────────────────────────────────────────────────
 * Taxonomy SEO fields (categories + tags)
 * ────────────────────────────────────────────────────────────────── */

/**
 * Variables resolver for term contexts (category/tag). Replaces only the
 * variables that make sense without a post.
 *
 *   %%term%%      Term name
 *   %%sitename%%  Site name
 *   %%tagline%%   Site tagline
 *   %%sep%%       Separator
 *   %%page%%      Pagination
 *
 * @param string  $template Template string with %%vars%%.
 * @param WP_Term $term     Queried term.
 * @return string
 */
function brio_seo_resolve_term_variables( $template, $term ) {
	if ( false === strpos( $template, '%%' ) ) {
		return $template;
	}
	$replacements = [
		'%%term%%'     => $term ? $term->name : '',
		'%%title%%'    => $term ? $term->name : '', // alias
		'%%sitename%%' => get_bloginfo( 'name' ),
		'%%tagline%%'  => get_bloginfo( 'description' ),
		'%%sep%%'      => brio_seo_separator(),
		'%%page%%'     => (int) get_query_var( 'paged' ) > 1 ? (int) get_query_var( 'paged' ) : '',
	];
	$out = strtr( $template, $replacements );
	$sep = preg_quote( brio_seo_separator(), '/' );
	$out = preg_replace( '/(\s*' . $sep . '\s*){2,}/', ' ' . brio_seo_separator() . ' ', $out );
	return trim( $out, " \t\n\r\0\x0B" . brio_seo_separator() );
}

/**
 * Render the SEO fields on the term edit screen.
 */
function brio_seo_term_edit_fields( $term ) {
	$seo_title = get_term_meta( $term->term_id, '_brio_seo_title', true );
	$seo_desc  = get_term_meta( $term->term_id, '_brio_seo_description', true );

	$resolved_title = $seo_title
		? brio_seo_resolve_term_variables( $seo_title, $term )
		: $term->name . ' ' . brio_seo_separator() . ' ' . get_bloginfo( 'name' );
	$resolved_desc = $seo_desc ?: __( '(Description automatique — remplissez pour personnaliser.)', 'brio-guiseppe' );
	$permalink     = get_term_link( $term );
	wp_nonce_field( 'brio_seo_term_save', 'brio_seo_term_nonce' );
	?>
	<tr class="form-field">
		<th colspan="2" style="padding:20px 10px 6px">
			<h2 style="margin:0;font-size:14px;color:#0f172a"><?php esc_html_e( 'SEO — Aperçu Google', 'brio-guiseppe' ); ?></h2>
		</th>
	</tr>
	<tr class="form-field">
		<td colspan="2" style="padding:0 10px 14px">
			<style>
			.brio-term-seo-preview { border: 1px solid #dadce0; border-radius: 10px; padding: 14px 18px; max-width: 600px; font-family: arial, sans-serif; }
			.brio-term-seo-preview small { color: #5f6368; font-size: 12px; }
			.brio-term-seo-preview .t { color: #1a0dab; font-size: 19px; line-height: 1.3; margin: 2px 0 4px; }
			.brio-term-seo-preview .d { color: #4d5156; font-size: 13px; line-height: 1.58; }
			.brio-term-seo-vars { background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;margin:8px 0;font-size:12px;color:#475569;max-width:680px }
			.brio-term-seo-vars code { background:#fff;border:1px solid #e5e7eb;padding:1px 6px;border-radius:4px;cursor:pointer }
			.brio-term-seo-vars code:hover { background:#fef3c7;border-color:#fcd34d }
			</style>
			<div class="brio-term-seo-preview" id="brio-term-preview">
				<small><?php echo esc_html( wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ); ?> › <?php echo esc_html( ltrim( wp_make_link_relative( $permalink ), '/' ) ); ?></small>
				<div class="t" id="brio-term-prev-title"><?php echo esc_html( $resolved_title ); ?></div>
				<div class="d" id="brio-term-prev-desc"><?php echo esc_html( $resolved_desc ); ?></div>
			</div>
		</td>
	</tr>
	<tr class="form-field">
		<th><label for="brio_seo_term_title"><?php esc_html_e( 'Titre SEO', 'brio-guiseppe' ); ?></label></th>
		<td>
			<input type="text" id="brio_seo_term_title" name="brio_seo_term_title"
			       value="<?php echo esc_attr( $seo_title ); ?>" class="regular-text"
			       placeholder="<?php echo esc_attr( '%%term%% ' . brio_seo_separator() . ' %%sitename%%' ); ?>" />
			<p class="description"><?php esc_html_e( 'Laissez vide pour utiliser : Nom de la catégorie · Nom du site. Idéal : 50–60 caractères.', 'brio-guiseppe' ); ?></p>
			<div class="brio-term-seo-vars">
				<strong><?php esc_html_e( 'Variables', 'brio-guiseppe' ); ?>:</strong>
				<code data-var="%%term%%">%%term%%</code>
				<code data-var="%%sitename%%">%%sitename%%</code>
				<code data-var="%%tagline%%">%%tagline%%</code>
				<code data-var="%%sep%%">%%sep%%</code>
				<em style="color:#94a3b8;font-size:11px"> — <?php esc_html_e( 'cliquez pour insérer', 'brio-guiseppe' ); ?></em>
			</div>
		</td>
	</tr>
	<tr class="form-field">
		<th><label for="brio_seo_term_description"><?php esc_html_e( 'Méta description SEO', 'brio-guiseppe' ); ?></label></th>
		<td>
			<textarea id="brio_seo_term_description" name="brio_seo_term_description"
			          rows="3" maxlength="320" class="regular-text"
			          style="width:25em"><?php echo esc_textarea( $seo_desc ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Idéal : 155–160 caractères. Laissez vide pour utiliser la description de la catégorie.', 'brio-guiseppe' ); ?></p>
		</td>
	</tr>
	<script>
	( function () {
		var t  = document.getElementById( 'brio_seo_term_title' );
		var d  = document.getElementById( 'brio_seo_term_description' );
		var pt = document.getElementById( 'brio-term-prev-title' );
		var pd = document.getElementById( 'brio-term-prev-desc' );
		var DATA = <?php echo wp_json_encode( [
			'term'     => $term->name,
			'sitename' => get_bloginfo( 'name' ),
			'tagline'  => get_bloginfo( 'description' ),
			'sep'      => brio_seo_separator(),
		] ); ?>;
		function resolve( s ) {
			return s
				.replace( /%%term%%/g,     DATA.term )
				.replace( /%%title%%/g,    DATA.term )
				.replace( /%%sitename%%/g, DATA.sitename )
				.replace( /%%tagline%%/g,  DATA.tagline )
				.replace( /%%sep%%/g,      DATA.sep );
		}
		function update() {
			var v = t.value.trim();
			pt.textContent = v ? resolve( v ) : ( DATA.term + ' ' + DATA.sep + ' ' + DATA.sitename );
			pd.textContent = d.value.trim() || '<?php echo esc_js( __( '(Description automatique — remplissez pour personnaliser.)', 'brio-guiseppe' ) ); ?>';
		}
		t.addEventListener( 'input', update );
		d.addEventListener( 'input', update );
		document.querySelectorAll( '.brio-term-seo-vars code[data-var]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				var pos = t.selectionStart || t.value.length;
				t.value = t.value.slice( 0, pos ) + el.dataset.var + t.value.slice( pos );
				t.focus();
				update();
			} );
		} );
	} )();
	</script>
	<?php
}

/**
 * Save SEO fields on term update.
 */
function brio_seo_term_save( $term_id ) {
	if ( ! isset( $_POST['brio_seo_term_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_seo_term_nonce'] ) ), 'brio_seo_term_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$title = isset( $_POST['brio_seo_term_title'] )
		? sanitize_text_field( wp_unslash( $_POST['brio_seo_term_title'] ) )
		: '';
	$desc = isset( $_POST['brio_seo_term_description'] )
		? sanitize_textarea_field( wp_unslash( $_POST['brio_seo_term_description'] ) )
		: '';

	if ( '' === $title ) {
		delete_term_meta( $term_id, '_brio_seo_title' );
	} else {
		update_term_meta( $term_id, '_brio_seo_title', $title );
	}
	if ( '' === $desc ) {
		delete_term_meta( $term_id, '_brio_seo_description' );
	} else {
		update_term_meta( $term_id, '_brio_seo_description', $desc );
	}
}

/* Register edit fields + save hook on category + post_tag (extend the
 * taxonomies list via the `brio_seo_taxonomies` filter if you add custom
 * taxonomies later). */
foreach ( apply_filters( 'brio_seo_taxonomies', [ 'category', 'post_tag' ] ) as $tax ) {
	add_action( $tax . '_edit_form_fields', 'brio_seo_term_edit_fields', 20 );
	add_action( 'edited_' . $tax, 'brio_seo_term_save' );
	add_action( 'created_' . $tax, 'brio_seo_term_save' );
}
