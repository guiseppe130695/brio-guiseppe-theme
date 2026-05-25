<?php
/**
 * Anti scaled-content abuse — Landing pages
 *
 * Google's spam updates (Dec 2024, Aug 2025, Mar 2026) explicitly target
 * pages generated at scale with thin/duplicated content. This module:
 *
 *  1. Computes a per-landing "uniqueness score" (word count of fields that
 *     are expected to vary between cities/niches).
 *  2. Detects exact duplicates of hero_title across landings.
 *  3. Forces robots noindex,follow when the landing is below threshold or
 *     duplicates another. follow is kept so internal links still pass.
 *  4. Exposes a column in the admin Pages list to audit at a glance.
 *
 * Threshold filterable via `brio_landing_min_unique_words` (default: 300).
 *
 * @package Brio_Guiseppe
 * @since   1.3.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_LANDING_MIN_WORDS_DEFAULT = 300;

/**
 * Fields that should vary between landings. Stable copy (CTAs, pricing
 * labels, fun-fact numbers) is intentionally excluded — counting it would
 * mask real thinness.
 *
 * @return array<int,array{0:string,1:string}> List of [section, field] tuples.
 */
function brio_landing_unique_fields() {
	$fields = [
		[ 'hero', 'title' ],
		[ 'hero', 'subtitle' ],
		[ 'about', 'heading' ],
		[ 'about', 'description' ],
		[ 'philosophy', 'heading' ],
		[ 'philosophy', 'description' ],
		[ 'unique', 'content' ], // free-form per-landing copy (see CSV importer)
	];
	for ( $n = 1; $n <= 6; $n++ ) {
		$fields[] = [ "programs_item{$n}", 'content' ];
	}
	for ( $n = 1; $n <= 8; $n++ ) {
		$fields[] = [ "faqs_item{$n}", 'answer' ];
	}
	return apply_filters( 'brio_landing_unique_fields', $fields );
}

/**
 * Count unique-content words for a landing.
 *
 * @param int $post_id Landing page ID.
 * @return int Word count.
 */
function brio_landing_unique_word_count( $post_id ) {
	$words = 0;
	foreach ( brio_landing_unique_fields() as $f ) {
		$val = brio_meta_get( $post_id, 'landing', $f[0], $f[1], '' );
		if ( '' === $val ) {
			continue;
		}
		$words += str_word_count( wp_strip_all_tags( (string) $val ), 0, 'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇœŒ' );
	}
	return $words;
}

/**
 * Find landings whose hero_title matches the given title verbatim. Used to
 * detect copy/paste across cities ("Site web pour hôtel à X" repeated as-is).
 *
 * @param int    $post_id  Current landing (excluded from results).
 * @param string $title    Hero title to match.
 * @return int[]           Conflicting post IDs.
 */
function brio_landing_title_duplicates( $post_id, $title ) {
	$title = trim( (string) $title );
	if ( '' === $title ) {
		return [];
	}
	$cache_key = 'brio_landing_dup_' . md5( $title );
	$cached    = wp_cache_get( $cache_key, 'brio_seo' );
	if ( false !== $cached ) {
		return array_values( array_diff( (array) $cached, [ (int) $post_id ] ) );
	}

	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta}
		 WHERE meta_key = %s AND meta_value = %s LIMIT 50",
		'_brio_landing_hero_title',
		$title
	) );
	$ids = array_map( 'intval', $ids );
	wp_cache_set( $cache_key, $ids, 'brio_seo', 5 * MINUTE_IN_SECONDS );
	return array_values( array_diff( $ids, [ (int) $post_id ] ) );
}

/**
 * Compute the full audit for one landing.
 *
 * @param int $post_id Landing page ID.
 * @return array{words:int,threshold:int,duplicates:int[],noindex:bool,reasons:string[]}
 */
function brio_landing_audit( $post_id ) {
	$threshold  = (int) apply_filters( 'brio_landing_min_unique_words', BRIO_LANDING_MIN_WORDS_DEFAULT );
	$words      = brio_landing_unique_word_count( $post_id );
	$title      = brio_meta_get( $post_id, 'landing', 'hero', 'title', '' );
	$duplicates = brio_landing_title_duplicates( $post_id, $title );

	$reasons = [];
	if ( $words < $threshold ) {
		$reasons[] = sprintf( /* translators: 1: words 2: threshold */
			__( 'Contenu unique insuffisant : %1$d / %2$d mots.', 'brio-guiseppe' ),
			$words,
			$threshold
		);
	}
	if ( ! empty( $duplicates ) ) {
		$reasons[] = sprintf( /* translators: %s: list of IDs */
			__( 'Titre H1 dupliqué avec landings : #%s.', 'brio-guiseppe' ),
			implode( ', #', $duplicates )
		);
	}

	$noindex = ! empty( $reasons );

	// Admin override: a manual checkbox on the page can flip noindex on/off.
	$manual = get_post_meta( $post_id, '_brio_landing_index_override', true );
	if ( 'force_index' === $manual ) {
		$noindex   = false;
		$reasons[] = __( 'Override admin : indexation forcée.', 'brio-guiseppe' );
	} elseif ( 'force_noindex' === $manual ) {
		$noindex   = true;
		$reasons[] = __( 'Override admin : noindex forcé.', 'brio-guiseppe' );
	}

	return [
		'words'      => $words,
		'threshold'  => $threshold,
		'duplicates' => $duplicates,
		'noindex'    => $noindex,
		'reasons'    => $reasons,
	];
}

/**
 * Emit robots meta for landings. Replaces WordPress's own implicit "index,follow"
 * with our computed value. Hooked before wp_robots so we override cleanly.
 */
function brio_landing_robots( $robots ) {
	if ( ! is_page() ) {
		return $robots;
	}
	$post_id = get_queried_object_id();
	if ( 'template-landing.php' !== get_page_template_slug( $post_id ) ) {
		return $robots;
	}

	$audit = brio_landing_audit( $post_id );
	if ( $audit['noindex'] ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	// Boost SERP image preview for landings that DO get indexed.
	$robots['max-image-preview'] = 'large';
	return $robots;
}
add_filter( 'wp_robots', 'brio_landing_robots' );

/**
 * Strip the landing from the theme sitemap when noindex applies. Defensive —
 * a noindex page in the sitemap is a contradictory signal and Search Console
 * flags it as "Excluded by 'noindex' tag".
 */
function brio_landing_sitemap_exclude( $entries ) {
	if ( ! is_array( $entries ) ) {
		return $entries;
	}
	foreach ( $entries as $key => $entry ) {
		if ( empty( $entry['loc'] ) ) {
			continue;
		}
		$id = url_to_postid( $entry['loc'] );
		if ( ! $id ) {
			continue;
		}
		if ( 'template-landing.php' !== get_page_template_slug( $id ) ) {
			continue;
		}
		$audit = brio_landing_audit( $id );
		if ( $audit['noindex'] ) {
			unset( $entries[ $key ] );
		}
	}
	return array_values( $entries );
}
add_filter( 'brio_sitemap_entries', 'brio_landing_sitemap_exclude' );

/* ─────────────────────────────────────────────────────────────────────
 * Admin: column in Pages list + meta box override toggle
 * ────────────────────────────────────────────────────────────────── */

function brio_landing_admin_columns( $columns ) {
	$new = [];
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['brio_landing_audit'] = __( 'SEO Landing', 'brio-guiseppe' );
		}
	}
	return $new;
}
add_filter( 'manage_pages_columns', 'brio_landing_admin_columns' );

function brio_landing_admin_column_content( $column, $post_id ) {
	if ( 'brio_landing_audit' !== $column ) {
		return;
	}
	if ( 'template-landing.php' !== get_page_template_slug( $post_id ) ) {
		echo '<span style="color:#94a3b8">—</span>';
		return;
	}
	$audit = brio_landing_audit( $post_id );
	$color = $audit['noindex'] ? '#dc2626' : '#16a34a';
	$icon  = $audit['noindex'] ? '⊘' : '✓';
	$label = $audit['noindex'] ? __( 'noindex', 'brio-guiseppe' ) : __( 'indexable', 'brio-guiseppe' );
	printf(
		'<strong style="color:%1$s">%2$s %3$s</strong><br><small style="color:#64748b">%4$d / %5$d mots</small>',
		esc_attr( $color ),
		esc_html( $icon ),
		esc_html( $label ),
		(int) $audit['words'],
		(int) $audit['threshold']
	);
	if ( ! empty( $audit['duplicates'] ) ) {
		echo '<br><small style="color:#dc2626">' . esc_html( sprintf(
			_n( '%d doublon de H1', '%d doublons de H1', count( $audit['duplicates'] ), 'brio-guiseppe' ),
			count( $audit['duplicates'] )
		) ) . '</small>';
	}
}
add_action( 'manage_pages_custom_column', 'brio_landing_admin_column_content', 10, 2 );

/**
 * Show the audit verdict inside the landing edit screen.
 */
function brio_landing_audit_meta_box() {
	global $post;
	if ( ! $post || 'template-landing.php' !== get_page_template_slug( $post->ID ) ) {
		return;
	}
	add_meta_box(
		'brio_landing_audit_box',
		__( 'SEO — Anti scaled-content', 'brio-guiseppe' ),
		'brio_landing_audit_meta_box_render',
		'page',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'brio_landing_audit_meta_box' );

function brio_landing_audit_meta_box_render( $post ) {
	wp_nonce_field( 'brio_landing_audit_save', 'brio_landing_audit_nonce' );
	$audit    = brio_landing_audit( $post->ID );
	$override = get_post_meta( $post->ID, '_brio_landing_index_override', true );
	$status   = $audit['noindex']
		? '<span style="color:#dc2626;font-weight:600">⊘ ' . esc_html__( 'noindex', 'brio-guiseppe' ) . '</span>'
		: '<span style="color:#16a34a;font-weight:600">✓ ' . esc_html__( 'indexable', 'brio-guiseppe' ) . '</span>';
	?>
	<p style="margin:6px 0 10px"><?php echo $status; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<p style="margin:0 0 6px"><strong><?php esc_html_e( 'Mots uniques', 'brio-guiseppe' ); ?> :</strong>
		<?php echo (int) $audit['words']; ?> / <?php echo (int) $audit['threshold']; ?>
	</p>
	<div style="height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-bottom:12px">
		<?php
		$pct = $audit['threshold'] ? min( 100, round( $audit['words'] / $audit['threshold'] * 100 ) ) : 0;
		$bg  = $audit['words'] >= $audit['threshold'] ? '#16a34a' : '#dc2626';
		?>
		<div style="height:100%;width:<?php echo (int) $pct; ?>%;background:<?php echo esc_attr( $bg ); ?>"></div>
	</div>
	<?php if ( ! empty( $audit['reasons'] ) ) : ?>
		<ul style="margin:0 0 12px;padding:0;list-style:none;font-size:12px;color:#475569">
			<?php foreach ( $audit['reasons'] as $r ) : ?>
				<li style="padding:4px 0;border-top:1px solid #e2e8f0">• <?php echo esc_html( $r ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p style="margin:10px 0 4px;font-weight:600"><?php esc_html_e( 'Override manuel', 'brio-guiseppe' ); ?></p>
	<label style="display:block;margin:3px 0"><input type="radio" name="brio_landing_index_override" value="" <?php checked( $override, '' ); ?> /> <?php esc_html_e( 'Automatique (recommandé)', 'brio-guiseppe' ); ?></label>
	<label style="display:block;margin:3px 0"><input type="radio" name="brio_landing_index_override" value="force_index" <?php checked( $override, 'force_index' ); ?> /> <?php esc_html_e( 'Forcer indexable', 'brio-guiseppe' ); ?></label>
	<label style="display:block;margin:3px 0"><input type="radio" name="brio_landing_index_override" value="force_noindex" <?php checked( $override, 'force_noindex' ); ?> /> <?php esc_html_e( 'Forcer noindex', 'brio-guiseppe' ); ?></label>
	<?php
}

function brio_landing_audit_save( $post_id ) {
	if ( ! isset( $_POST['brio_landing_audit_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_landing_audit_nonce'] ) ), 'brio_landing_audit_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$val   = isset( $_POST['brio_landing_index_override'] ) ? sanitize_text_field( wp_unslash( $_POST['brio_landing_index_override'] ) ) : '';
	$valid = [ '', 'force_index', 'force_noindex' ];
	if ( ! in_array( $val, $valid, true ) ) {
		$val = '';
	}
	if ( '' === $val ) {
		delete_post_meta( $post_id, '_brio_landing_index_override' );
	} else {
		update_post_meta( $post_id, '_brio_landing_index_override', $val );
	}
}
add_action( 'save_post_page', 'brio_landing_audit_save' );
