<?php
/**
 * Meta Boxes — Landing Page template
 *
 * One meta box per section of template-landing.php. Each section maps to its
 * equivalent homepage section so landing pages can have fully independent copy.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_landing_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-landing.php' ) ) {
		return;
	}
	add_meta_box(
		'brio_landing_sections',
		__( 'Contenu de la Landing Page', 'brio-guiseppe' ),
		'brio_landing_render_tabs',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'brio_landing_register_meta_boxes' );

/**
 * Main tabbed renderer — one meta box, 10 tabs.
 */
function brio_landing_render_tabs( $post ) {
	wp_nonce_field( 'brio_landing_save', 'brio_landing_nonce' );

	$tabs = [
		'hero'       => [ 'label' => __( 'Hero',         'brio-guiseppe' ), 'icon' => 'dashicons-cover-image',   'hint' => __( 'Titre H1 & sous-titre',          'brio-guiseppe' ), 'prefixes' => [ 'hero' ] ],
		'rating'     => [ 'label' => __( 'Note & avis',  'brio-guiseppe' ), 'icon' => 'dashicons-star-filled',   'hint' => __( 'Étoiles SERP (AggregateRating)', 'brio-guiseppe' ), 'prefixes' => [ 'rating' ] ],
		'unique'     => [ 'label' => __( 'Contenu unique','brio-guiseppe' ), 'icon' => 'dashicons-edit-large',    'hint' => __( 'Texte propre à la ville/niche',  'brio-guiseppe' ), 'prefixes' => [ 'unique' ] ],
		'about'      => [ 'label' => __( 'À propos',     'brio-guiseppe' ), 'icon' => 'dashicons-admin-users',   'hint' => __( 'Présentation + visuel',          'brio-guiseppe' ), 'prefixes' => [ 'about' ] ],
		'partners'   => [ 'label' => __( 'Partenaires',  'brio-guiseppe' ), 'icon' => 'dashicons-groups',        'hint' => __( 'Jusqu’à 6 logos',                'brio-guiseppe' ), 'prefixes' => [ 'partners' ] ],
		'programs'   => [ 'label' => __( 'Programmes',   'brio-guiseppe' ), 'icon' => 'dashicons-list-view',     'hint' => __( '6 cartes éditoriales',           'brio-guiseppe' ), 'prefixes' => [ 'programs' ] ],
		'philosophy' => [ 'label' => __( 'Philosophie',  'brio-guiseppe' ), 'icon' => 'dashicons-lightbulb',     'hint' => __( 'Valeurs & 3 points forts',       'brio-guiseppe' ), 'prefixes' => [ 'philosophy' ] ],
		'showcase'   => [ 'label' => __( 'Showcase',     'brio-guiseppe' ), 'icon' => 'dashicons-format-gallery','hint' => __( 'Fond + images flottantes',       'brio-guiseppe' ), 'prefixes' => [ 'showcase' ] ],
		'funfacts'   => [ 'label' => __( 'Chiffres',     'brio-guiseppe' ), 'icon' => 'dashicons-chart-bar',     'hint' => __( '4 chiffres-clés',                'brio-guiseppe' ), 'prefixes' => [ 'funfacts' ] ],
		'pricing'    => [ 'label' => __( 'Tarifs',       'brio-guiseppe' ), 'icon' => 'dashicons-money-alt',     'hint' => __( '3 plans tarifaires',             'brio-guiseppe' ), 'prefixes' => [ 'pricing' ] ],
		'faqs'       => [ 'label' => __( 'FAQ',          'brio-guiseppe' ), 'icon' => 'dashicons-editor-help',   'hint' => __( 'Jusqu’à 8 Q/R',                  'brio-guiseppe' ), 'prefixes' => [ 'faqs' ] ],
		'cta'        => [ 'label' => __( 'CTA final',    'brio-guiseppe' ), 'icon' => 'dashicons-megaphone',     'hint' => __( 'Bandeau de conversion',          'brio-guiseppe' ), 'prefixes' => [ 'cta' ] ],
	];
	?>
	<div class="brio-tabs brio-tabs--vertical">
		<aside class="brio-tabs__nav" role="tablist" aria-orientation="vertical">
			<div class="brio-tabs__nav-head">
				<span class="brio-tabs__nav-title"><?php esc_html_e( 'Sections', 'brio-guiseppe' ); ?></span>
				<span class="brio-tabs__nav-count"><?php echo (int) count( $tabs ); ?></span>
			</div>
			<?php foreach ( $tabs as $slug => $tab ) :
				$filled = brio_landing_section_has_content( $post->ID, $tab['prefixes'] );
				?>
				<button type="button"
				        class="brio-tabs__btn<?php echo $slug === 'hero' ? ' is-active' : ''; ?>"
				        data-tab="brio-tab-<?php echo esc_attr( $slug ); ?>"
				        role="tab"
				        aria-selected="<?php echo $slug === 'hero' ? 'true' : 'false'; ?>"
				        aria-controls="brio-tab-<?php echo esc_attr( $slug ); ?>">
					<span class="brio-tabs__icon-wrap" aria-hidden="true">
						<span class="brio-tabs__icon dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
					</span>
					<span class="brio-tabs__text">
						<span class="brio-tabs__label"><?php echo esc_html( $tab['label'] ); ?></span>
						<span class="brio-tabs__hint"><?php echo esc_html( $tab['hint'] ); ?></span>
					</span>
					<?php if ( $filled ) : ?>
						<span class="brio-tabs__dot" aria-label="<?php esc_attr_e( 'Section remplie', 'brio-guiseppe' ); ?>"></span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</aside>
		<div class="brio-tabs__body">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<div id="brio-tab-<?php echo esc_attr( $slug ); ?>"
				     class="brio-tabs__panel<?php echo $slug === 'hero' ? ' is-active' : ''; ?>"
				     role="tabpanel"
				     <?php echo $slug !== 'hero' ? 'hidden' : ''; ?>>
					<header class="brio-panel-head">
						<span class="brio-panel-head__icon dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
						<div>
							<h3 class="brio-panel-head__title"><?php echo esc_html( $tab['label'] ); ?></h3>
							<p class="brio-panel-head__hint"><?php echo esc_html( $tab['hint'] ); ?></p>
						</div>
					</header>
					<div class="brio-panel-body">
						<?php call_user_func( 'brio_landing_render_' . $slug, $post ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Quick check whether any meta key beginning with one of $prefixes exists for
 * this post. Used to flash a "filled" dot next to nav items.
 */
function brio_landing_section_has_content( $post_id, $prefixes ) {
	foreach ( (array) $prefixes as $p ) {
		global $wpdb;
		$like = $wpdb->esc_like( '_brio_landing_' . $p ) . '%';
		$row  = $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s AND meta_value <> '' LIMIT 1",
			$post_id,
			$like
		) );
		if ( $row ) {
			return true;
		}
	}
	return false;
}

/* ── Hero ── */
function brio_landing_render_hero( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'En-tête de section', 'brio-guiseppe' ) . '</h4>';
	brio_field_text(     'brio_landing_hero_title',    __( 'Titre H1',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'hero', 'title' ) );
	brio_field_textarea( 'brio_landing_hero_subtitle', __( 'Sous-titre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'hero', 'subtitle' ) );
	echo '</div>';
}

/* ── Unique content (anti scaled-content) ── */
function brio_landing_render_unique( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Contenu propre à cette landing', 'brio-guiseppe' ) . '</h4>';
	echo '<p class="description" style="margin:-4px 0 12px">' . esc_html__( 'Rédigez ici 300+ mots vraiment spécifiques à cette ville/niche : contexte local, spécificités du marché, exemples concrets. Sans ce contenu, la page sera mise en noindex pour éviter une pénalité "scaled content abuse".', 'brio-guiseppe' ) . '</p>';
	brio_field_text(     'brio_landing_unique_heading', __( 'Titre de section (optionnel)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'unique', 'heading' ) );
	brio_field_textarea( 'brio_landing_unique_content', __( 'Contenu (HTML simple autorisé)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'unique', 'content' ), 14 );
	echo '</div>';
}

/* ── Rating (AggregateRating SERP) ── */
function brio_landing_render_rating( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Note affichée + Rich Snippet', 'brio-guiseppe' ) . '</h4>';
	echo '<p class="description" style="margin:-4px 0 12px">' . esc_html__( 'Ces valeurs s\'affichent dans le hero ET alimentent le balisage AggregateRating (étoiles Google). Laissez vide pour utiliser les valeurs globales du thème.', 'brio-guiseppe' ) . '</p>';
	brio_field_text( 'brio_landing_rating_value',   __( 'Note (0–5, ex: 4.9)',          'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'rating', 'value' ) );
	brio_field_text( 'brio_landing_rating_count',   __( 'Nombre d\'avis (ex: 12)',      'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'rating', 'count' ) );
	brio_field_text( 'brio_landing_rating_caption', __( 'Légende (ex: « avis LinkedIn »)','brio-guiseppe' ), brio_meta_get( $id, 'landing', 'rating', 'caption' ) );
	brio_field_text( 'brio_landing_rating_href',    __( 'Lien (ex: profil LinkedIn)',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'rating', 'href' ) );
	echo '</div>';
}

/* ── About ── */
function brio_landing_render_about( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'about', [ 'description' => true ] );

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Visuel', 'brio-guiseppe' ) . '</h4>';
	brio_field_image( 'brio_landing_about_image', __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'about', 'image' ) );
	echo '</div>';

	brio_field_cta( $id, 'landing', 'about' );
}

/* ── Partners ── */
function brio_landing_render_partners( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'En-tête de section', 'brio-guiseppe' ) . '</h4>';
	brio_field_text( 'brio_landing_partners_label', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'partners', 'label' ) );
	echo '</div>';

	brio_repeater_open( __( 'Logos partenaires', 'brio-guiseppe' ), 6 );
	for ( $n = 1; $n <= 6; $n++ ) {
		$alt   = brio_meta_get( $id, 'landing', "partners_logo{$n}", 'alt' );
		brio_repeater_item_open( __( 'Logo', 'brio-guiseppe' ), $n, "brio_landing_partners_logo{$n}_alt", $alt );
		brio_field_image( "brio_landing_partners_logo{$n}_url", __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "partners_logo{$n}", 'url' ) );
		brio_field_text(  "brio_landing_partners_logo{$n}_alt", __( 'Texte alternatif', 'brio-guiseppe' ), $alt );
		brio_repeater_item_close();
	}
	brio_repeater_close();
}

/* ── Programs ── */
function brio_landing_render_programs( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'programs' );

	brio_repeater_open( __( 'Programmes', 'brio-guiseppe' ), 6 );
	for ( $n = 1; $n <= 6; $n++ ) {
		$title = brio_meta_get( $id, 'landing', "programs_item{$n}", 'title' );
		brio_repeater_item_open( __( 'Programme', 'brio-guiseppe' ), $n, "brio_landing_programs_item{$n}_title", $title );
		brio_field_text(     "brio_landing_programs_item{$n}_title",   __( 'Titre',   'brio-guiseppe' ), $title );
		brio_field_textarea( "brio_landing_programs_item{$n}_content", __( 'Contenu', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "programs_item{$n}", 'content' ), 3 );
		brio_repeater_item_close();
	}
	brio_repeater_close();

	brio_field_cta( $id, 'landing', 'programs' );

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Note', 'brio-guiseppe' ) . '</h4>';
	brio_field_text( 'brio_landing_programs_note', __( 'Note de bas de section', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'note' ) );
	echo '</div>';
}

/* ── Philosophy ── */
function brio_landing_render_philosophy( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'philosophy', [ 'description' => true ] );

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Visuel', 'brio-guiseppe' ) . '</h4>';
	brio_field_image( 'brio_landing_philosophy_visual', __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'philosophy', 'visual' ) );
	echo '</div>';

	brio_repeater_open( __( 'Points forts', 'brio-guiseppe' ), 3 );
	for ( $n = 1; $n <= 3; $n++ ) {
		$title = brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'title' );
		brio_repeater_item_open( __( 'Point fort', 'brio-guiseppe' ), $n, "brio_landing_philosophy_feature{$n}_title", $title );
		brio_field_text( "brio_landing_philosophy_feature{$n}_title", __( 'Titre',       'brio-guiseppe' ), $title );
		brio_field_text( "brio_landing_philosophy_feature{$n}_text",  __( 'Description', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'text' ) );
		brio_field_text( "brio_landing_philosophy_feature{$n}_icon",  __( 'Icône (slug Dashicon ou URL)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'icon' ) );
		brio_repeater_item_close();
	}
	brio_repeater_close();
}

/* ── Showcase ── */
function brio_landing_render_showcase( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Image de fond', 'brio-guiseppe' ) . '</h4>';
	brio_field_image( 'brio_landing_showcase_bg', __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'showcase', 'bg' ) );
	echo '</div>';

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Images flottantes', 'brio-guiseppe' ) . '</h4>';
	brio_field_json(
		'brio_landing_showcase_images',
		__( 'Liste (JSON)', 'brio-guiseppe' ),
		brio_meta_get( $id, 'landing', 'showcase', 'images' ),
		'[{"url":"https://…/img.jpg","alt":"Description","position":"top-left"}]'
	);
	echo '</div>';
}

/* ── Fun Facts ── */
function brio_landing_render_funfacts( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'funfacts' );

	brio_repeater_open( __( 'Chiffres-clés', 'brio-guiseppe' ), 4 );
	for ( $n = 1; $n <= 4; $n++ ) {
		$title = brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'title' );
		brio_repeater_item_open( __( 'Carte', 'brio-guiseppe' ), $n, "brio_landing_funfacts_card{$n}_title", $title );
		brio_field_text( "brio_landing_funfacts_card{$n}_title",  __( 'Titre',  'brio-guiseppe' ), $title );
		echo '<div class="brio-pair">';
		brio_field_text( "brio_landing_funfacts_card{$n}_number", __( 'Nombre',  'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'number' ) );
		brio_field_text( "brio_landing_funfacts_card{$n}_suffix", __( 'Suffixe', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'suffix' ) );
		echo '</div>';
		brio_field_image( "brio_landing_funfacts_card{$n}_icon", __( 'Icône', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'icon' ) );
		brio_repeater_item_close();
	}
	brio_repeater_close();
}

/* ── Pricing ── */
function brio_landing_render_pricing( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'pricing' );

	brio_repeater_open( __( 'Plans tarifaires', 'brio-guiseppe' ), 3 );
	foreach ( [ 1, 2, 3 ] as $n ) {
		$p     = "plan{$n}";
		$title = brio_meta_get( $id, 'landing', "pricing_{$p}", 'title' );
		brio_repeater_item_open( __( 'Plan', 'brio-guiseppe' ), $n, "brio_landing_pricing_{$p}_title", $title );

		brio_field_text( "brio_landing_pricing_{$p}_title", __( 'Nom du plan', 'brio-guiseppe' ), $title );

		echo '<div class="brio-pair">';
		brio_field_text( "brio_landing_pricing_{$p}_price",        __( 'Prix',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'price' ) );
		brio_field_text( "brio_landing_pricing_{$p}_price_prefix", __( 'Devise', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'price_prefix' ) );
		echo '</div>';

		echo '<div class="brio-pair">';
		brio_field_text( "brio_landing_pricing_{$p}_rooms",   __( 'Chambres (ex: 1–10)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'rooms' ) );
		brio_field_text( "brio_landing_pricing_{$p}_tagline", __( 'Sous-titre',          'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'tagline' ) );
		echo '</div>';

		brio_field_text(     "brio_landing_pricing_{$p}_ideal",    __( 'Profil idéal',         'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'ideal' ) );
		brio_field_textarea( "brio_landing_pricing_{$p}_includes", __( 'Inclus (1 par ligne)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'includes' ), 4 );

		brio_field_cta( $id, 'landing', "pricing_{$p}" );

		brio_repeater_item_close();
	}
	brio_repeater_close();

	brio_field_cta( $id, 'landing', 'pricing', [ 'title' => __( 'Bouton global', 'brio-guiseppe' ) ] );
}

/* ── FAQs ── */
function brio_landing_render_faqs( $post ) {
	$id = $post->ID;
	brio_section_header( $id, 'landing', 'faqs' );

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Visuel', 'brio-guiseppe' ) . '</h4>';
	brio_field_image( 'brio_landing_faqs_visual', __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'faqs', 'visual' ) );
	echo '</div>';

	brio_repeater_open( __( 'Questions / réponses', 'brio-guiseppe' ), 8 );
	for ( $n = 1; $n <= 8; $n++ ) {
		$q = brio_meta_get( $id, 'landing', "faqs_item{$n}", 'question' );
		brio_repeater_item_open( __( 'Question', 'brio-guiseppe' ), $n, "brio_landing_faqs_item{$n}_question", $q );
		brio_field_text(     "brio_landing_faqs_item{$n}_question", __( 'Question', 'brio-guiseppe' ), $q );
		brio_field_textarea( "brio_landing_faqs_item{$n}_answer",   __( 'Réponse',  'brio-guiseppe' ), brio_meta_get( $id, 'landing', "faqs_item{$n}", 'answer' ), 3 );
		brio_repeater_item_close();
	}
	brio_repeater_close();
}

/* ── CTA final ── */
function brio_landing_render_cta( $post ) {
	$id = $post->ID;
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'En-tête de section', 'brio-guiseppe' ) . '</h4>';
	brio_field_text( 'brio_landing_cta_heading', __( 'Titre principal', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'heading' ) );
	echo '</div>';

	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'Accroches', 'brio-guiseppe' ) . '</h4>';
	echo '<div class="brio-pair">';
	brio_field_text( 'brio_landing_cta_tagline1', __( 'Accroche 1', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline1' ) );
	brio_field_text( 'brio_landing_cta_tagline2', __( 'Accroche 2', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline2' ) );
	brio_field_text( 'brio_landing_cta_tagline3', __( 'Accroche 3', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline3' ) );
	echo '</div>';
	echo '</div>';

	brio_field_cta( $id, 'landing', 'cta', [ 'label_field' => 'label', 'url_field' => 'url' ] );
}

/**
 * Persist all Landing fields when the page is saved.
 */
function brio_landing_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_landing_nonce', 'brio_landing_save' ) ) {
		return;
	}

	$map = [
		'hero' => [
			[ 'title',    'text' ],
			[ 'subtitle', 'textarea' ],
		],
		'rating' => [
			[ 'value',   'text' ],
			[ 'count',   'text' ],
			[ 'caption', 'text' ],
			[ 'href',    'url' ],
		],
		'unique' => [
			[ 'heading', 'text' ],
			[ 'content', 'textarea' ],
		],
		'about' => [
			[ 'overline',    'text' ],
			[ 'heading',     'text' ],
			[ 'description', 'textarea' ],
			[ 'cta_label',   'text' ],
			[ 'cta_url',     'url' ],
			[ 'image',       'url' ],
		],
		'partners' => [
			[ 'label', 'text' ],
		],
		'partners_logo1' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'partners_logo2' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'partners_logo3' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'partners_logo4' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'partners_logo5' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'partners_logo6' => [ [ 'url', 'url' ], [ 'alt', 'text' ] ],
		'programs' => [
			[ 'overline',  'text' ],
			[ 'heading',   'text' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
			[ 'note',      'text' ],
		],
		'programs_item1' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'programs_item2' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'programs_item3' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'programs_item4' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'programs_item5' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'programs_item6' => [ [ 'title', 'text' ], [ 'content', 'textarea' ] ],
		'philosophy' => [
			[ 'overline',    'text' ],
			[ 'heading',     'text' ],
			[ 'description', 'textarea' ],
			[ 'visual',      'url' ],
		],
		'philosophy_feature1' => [ [ 'icon', 'text' ], [ 'title', 'text' ], [ 'text', 'text' ] ],
		'philosophy_feature2' => [ [ 'icon', 'text' ], [ 'title', 'text' ], [ 'text', 'text' ] ],
		'philosophy_feature3' => [ [ 'icon', 'text' ], [ 'title', 'text' ], [ 'text', 'text' ] ],
		'showcase' => [
			[ 'bg',     'url' ],
			[ 'images', 'json' ],
		],
		'funfacts' => [
			[ 'overline', 'text' ],
			[ 'heading',  'text' ],
		],
		'funfacts_card1' => [ [ 'icon', 'url' ], [ 'number', 'text' ], [ 'suffix', 'text' ], [ 'title', 'text' ] ],
		'funfacts_card2' => [ [ 'icon', 'url' ], [ 'number', 'text' ], [ 'suffix', 'text' ], [ 'title', 'text' ] ],
		'funfacts_card3' => [ [ 'icon', 'url' ], [ 'number', 'text' ], [ 'suffix', 'text' ], [ 'title', 'text' ] ],
		'funfacts_card4' => [ [ 'icon', 'url' ], [ 'number', 'text' ], [ 'suffix', 'text' ], [ 'title', 'text' ] ],
		'pricing' => [
			[ 'overline',  'text' ],
			[ 'heading',   'text' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
		],
		'pricing_plan1' => [
			[ 'title',        'text' ],
			[ 'rooms',        'text' ],
			[ 'price',        'text' ],
			[ 'price_prefix', 'text' ],
			[ 'tagline',      'text' ],
			[ 'ideal',        'text' ],
			[ 'cta_label',    'text' ],
			[ 'cta_url',      'url' ],
			[ 'includes',     'textarea' ],
		],
		'pricing_plan2' => [
			[ 'title',        'text' ],
			[ 'rooms',        'text' ],
			[ 'price',        'text' ],
			[ 'price_prefix', 'text' ],
			[ 'tagline',      'text' ],
			[ 'ideal',        'text' ],
			[ 'cta_label',    'text' ],
			[ 'cta_url',      'url' ],
			[ 'includes',     'textarea' ],
		],
		'pricing_plan3' => [
			[ 'title',        'text' ],
			[ 'rooms',        'text' ],
			[ 'price',        'text' ],
			[ 'price_prefix', 'text' ],
			[ 'tagline',      'text' ],
			[ 'ideal',        'text' ],
			[ 'cta_label',    'text' ],
			[ 'cta_url',      'url' ],
			[ 'includes',     'textarea' ],
		],
		'faqs' => [
			[ 'overline', 'text' ],
			[ 'heading',  'text' ],
			[ 'visual',   'url' ],
		],
		'faqs_item1' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item2' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item3' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item4' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item5' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item6' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item7' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'faqs_item8' => [ [ 'question', 'text' ], [ 'answer', 'textarea' ] ],
		'cta' => [
			[ 'heading',  'text' ],
			[ 'tagline1', 'text' ],
			[ 'tagline2', 'text' ],
			[ 'tagline3', 'text' ],
			[ 'label',    'text' ],
			[ 'url',      'url' ],
		],
	];

	foreach ( $map as $section => $fields ) {
		foreach ( $fields as $f ) {
			brio_meta_save_field( $post_id, 'landing', $section, $f[0], $f[1] );
		}
	}
}
add_action( 'save_post_page', 'brio_landing_save_meta' );
