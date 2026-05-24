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
		'hero'       => [ 'label' => __( 'Hero',         'brio-guiseppe' ), 'icon' => '⬛' ],
		'about'      => [ 'label' => __( 'À propos',     'brio-guiseppe' ), 'icon' => '👤' ],
		'partners'   => [ 'label' => __( 'Partenaires',  'brio-guiseppe' ), 'icon' => '🤝' ],
		'programs'   => [ 'label' => __( 'Programmes',   'brio-guiseppe' ), 'icon' => '📋' ],
		'philosophy' => [ 'label' => __( 'Philosophie',  'brio-guiseppe' ), 'icon' => '💡' ],
		'showcase'   => [ 'label' => __( 'Showcase',     'brio-guiseppe' ), 'icon' => '🖼' ],
		'funfacts'   => [ 'label' => __( 'Chiffres',     'brio-guiseppe' ), 'icon' => '📊' ],
		'pricing'    => [ 'label' => __( 'Tarifs',       'brio-guiseppe' ), 'icon' => '💶' ],
		'faqs'       => [ 'label' => __( 'FAQ',          'brio-guiseppe' ), 'icon' => '❓' ],
		'cta'        => [ 'label' => __( 'CTA final',    'brio-guiseppe' ), 'icon' => '🎯' ],
	];
	?>
	<div class="brio-tabs">
		<nav class="brio-tabs__nav" role="tablist">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<button type="button"
				        class="brio-tabs__btn<?php echo $slug === 'hero' ? ' is-active' : ''; ?>"
				        data-tab="brio-tab-<?php echo esc_attr( $slug ); ?>"
				        role="tab"
				        aria-selected="<?php echo $slug === 'hero' ? 'true' : 'false'; ?>"
				        aria-controls="brio-tab-<?php echo esc_attr( $slug ); ?>">
					<span class="brio-tabs__icon"><?php echo $tab['icon']; ?></span>
					<?php echo esc_html( $tab['label'] ); ?>
				</button>
			<?php endforeach; ?>
		</nav>
		<?php foreach ( $tabs as $slug => $tab ) : ?>
			<div id="brio-tab-<?php echo esc_attr( $slug ); ?>"
			     class="brio-tabs__panel<?php echo $slug === 'hero' ? ' is-active' : ''; ?>"
			     role="tabpanel"
			     hidden="<?php echo $slug !== 'hero' ? 'hidden' : ''; ?>">
				<h3 class="brio-tabs__heading"><?php echo esc_html( $tab['label'] ); ?></h3>
				<?php call_user_func( 'brio_landing_render_' . $slug, $post ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/* ── Hero ── */
function brio_landing_render_hero( $post ) {
	brio_field_text(     'brio_landing_hero_title',    __( 'Titre H1',   'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'title' ) );
	brio_field_textarea( 'brio_landing_hero_subtitle', __( 'Sous-titre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'subtitle' ) );
}

/* ── About ── */
function brio_landing_render_about( $post ) {
	brio_field_text(     'brio_landing_about_overline',    __( 'Surtitre',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'overline' ) );
	brio_field_text(     'brio_landing_about_heading',     __( 'Titre',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'heading' ) );
	brio_field_textarea( 'brio_landing_about_description', __( 'Description',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'description' ) );
	brio_field_text(     'brio_landing_about_cta_label',   __( 'Libellé CTA',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'cta_label' ) );
	brio_field_url(      'brio_landing_about_cta_url',     __( 'URL CTA',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'cta_url' ) );
	brio_field_image(    'brio_landing_about_image',       __( 'Image',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'image' ) );
}

/* ── Partners ── */
function brio_landing_render_partners( $post ) {
	$id = $post->ID;
	brio_field_text( 'brio_landing_partners_label', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'partners', 'label' ) );
	for ( $n = 1; $n <= 6; $n++ ) {
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Logo %d', 'brio-guiseppe' ), $n ) . '</p>';
		echo '<div class="brio-row brio-row--2">';
		brio_field_image( "brio_landing_partners_logo{$n}_url", __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "partners_logo{$n}", 'url' ) );
		brio_field_text(  "brio_landing_partners_logo{$n}_alt", __( 'Texte alternatif', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "partners_logo{$n}", 'alt' ) );
		echo '</div></div>';
	}
}

/* ── Programs ── */
function brio_landing_render_programs( $post ) {
	$id = $post->ID;
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_programs_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'overline' ) );
	brio_field_text( 'brio_landing_programs_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'heading' ) );
	echo '</div>';
	for ( $n = 1; $n <= 6; $n++ ) {
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Programme %d', 'brio-guiseppe' ), $n ) . '</p>';
		brio_field_text(     "brio_landing_programs_item{$n}_title",   __( 'Titre',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', "programs_item{$n}", 'title' ) );
		brio_field_textarea( "brio_landing_programs_item{$n}_content", __( 'Contenu', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "programs_item{$n}", 'content' ), 3 );
		echo '</div>';
	}
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_programs_cta_label', __( 'Libellé bouton', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'cta_label' ) );
	brio_field_url(  'brio_landing_programs_cta_url',   __( 'URL bouton',     'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'cta_url' ) );
	echo '</div>';
	brio_field_text( 'brio_landing_programs_note', __( 'Note de bas de page', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'programs', 'note' ) );
}

/* ── Philosophy ── */
function brio_landing_render_philosophy( $post ) {
	$id = $post->ID;
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_philosophy_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'philosophy', 'overline' ) );
	brio_field_text( 'brio_landing_philosophy_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'philosophy', 'heading' ) );
	echo '</div>';
	brio_field_textarea( 'brio_landing_philosophy_description', __( 'Description', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'philosophy', 'description' ) );
	brio_field_image(    'brio_landing_philosophy_visual',      __( 'Image',       'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'philosophy', 'visual' ) );
	for ( $n = 1; $n <= 3; $n++ ) {
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Point fort %d', 'brio-guiseppe' ), $n ) . '</p>';
		echo '<div class="brio-row brio-row--3">';
		brio_field_text( "brio_landing_philosophy_feature{$n}_icon",  __( 'Icône',       'brio-guiseppe' ), brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'icon' ) );
		brio_field_text( "brio_landing_philosophy_feature{$n}_title", __( 'Titre',       'brio-guiseppe' ), brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'title' ) );
		brio_field_text( "brio_landing_philosophy_feature{$n}_text",  __( 'Description', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "philosophy_feature{$n}", 'text' ) );
		echo '</div></div>';
	}
}

/* ── Showcase ── */
function brio_landing_render_showcase( $post ) {
	brio_field_image( 'brio_landing_showcase_bg', __( 'Image de fond', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'showcase', 'bg' ) );
	brio_field_json(
		'brio_landing_showcase_images',
		__( 'Images flottantes', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'showcase', 'images' ),
		'[{"url":"https://…/img.jpg","alt":"Description","position":"top-left"}]'
	);
}

/* ── Fun Facts ── */
function brio_landing_render_funfacts( $post ) {
	$id = $post->ID;
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_funfacts_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'funfacts', 'overline' ) );
	brio_field_text( 'brio_landing_funfacts_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'funfacts', 'heading' ) );
	echo '</div>';
	for ( $n = 1; $n <= 4; $n++ ) {
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Carte %d', 'brio-guiseppe' ), $n ) . '</p>';
		echo '<div class="brio-row brio-row--4">';
		brio_field_image( "brio_landing_funfacts_card{$n}_icon",   __( 'Icône',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'icon' ) );
		brio_field_text(  "brio_landing_funfacts_card{$n}_number", __( 'Nombre',  'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'number' ) );
		brio_field_text(  "brio_landing_funfacts_card{$n}_suffix", __( 'Suffixe', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'suffix' ) );
		brio_field_text(  "brio_landing_funfacts_card{$n}_title",  __( 'Titre',   'brio-guiseppe' ), brio_meta_get( $id, 'landing', "funfacts_card{$n}", 'title' ) );
		echo '</div></div>';
	}
}

/* ── Pricing ── */
function brio_landing_render_pricing( $post ) {
	$id = $post->ID;
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_pricing_overline',  __( 'Surtitre',       'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'pricing', 'overline' ) );
	brio_field_text( 'brio_landing_pricing_heading',   __( 'Titre',          'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'pricing', 'heading' ) );
	echo '</div>';
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_pricing_cta_label', __( 'Libellé bouton', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'pricing', 'cta_label' ) );
	brio_field_url(  'brio_landing_pricing_cta_url',   __( 'URL bouton',     'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'pricing', 'cta_url' ) );
	echo '</div>';
	foreach ( [ 1, 2, 3 ] as $n ) {
		$p = "plan{$n}";
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Plan %d', 'brio-guiseppe' ), $n ) . '</p>';
		echo '<div class="brio-row brio-row--3">';
		brio_field_text( "brio_landing_pricing_{$p}_title",        __( 'Nom du plan', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'title' ) );
		brio_field_text( "brio_landing_pricing_{$p}_price",        __( 'Prix',        'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'price' ) );
		brio_field_text( "brio_landing_pricing_{$p}_price_prefix", __( 'Devise',      'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'price_prefix' ) );
		echo '</div>';
		echo '<div class="brio-row brio-row--2">';
		brio_field_text( "brio_landing_pricing_{$p}_rooms",     __( 'Chambres (ex: 1–10)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'rooms' ) );
		brio_field_text( "brio_landing_pricing_{$p}_tagline",   __( 'Sous-titre',          'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'tagline' ) );
		echo '</div>';
		brio_field_text( "brio_landing_pricing_{$p}_ideal", __( 'Profil idéal', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'ideal' ) );
		echo '<div class="brio-row brio-row--2">';
		brio_field_text( "brio_landing_pricing_{$p}_cta_label", __( 'Libellé bouton', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'cta_label' ) );
		brio_field_url(  "brio_landing_pricing_{$p}_cta_url",   __( 'URL bouton',     'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'cta_url' ) );
		echo '</div>';
		brio_field_textarea( "brio_landing_pricing_{$p}_includes", __( 'Inclus (1 par ligne)', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "pricing_{$p}", 'includes' ), 4 );
		echo '</div>';
	}
}

/* ── FAQs ── */
function brio_landing_render_faqs( $post ) {
	$id = $post->ID;
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_faqs_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'faqs', 'overline' ) );
	brio_field_text( 'brio_landing_faqs_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'faqs', 'heading' ) );
	echo '</div>';
	brio_field_image( 'brio_landing_faqs_visual', __( 'Image', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'faqs', 'visual' ) );
	for ( $n = 1; $n <= 8; $n++ ) {
		echo '<div class="brio-card"><p class="brio-card__title">' . sprintf( esc_html__( 'Question %d', 'brio-guiseppe' ), $n ) . '</p>';
		brio_field_text(     "brio_landing_faqs_item{$n}_question", __( 'Question', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', "faqs_item{$n}", 'question' ) );
		brio_field_textarea( "brio_landing_faqs_item{$n}_answer",   __( 'Réponse',  'brio-guiseppe' ), brio_meta_get( $id, 'landing', "faqs_item{$n}", 'answer' ), 3 );
		echo '</div>';
	}
}

/* ── CTA final ── */
function brio_landing_render_cta( $post ) {
	$id = $post->ID;
	brio_field_text( 'brio_landing_cta_heading', __( 'Titre principal', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'heading' ) );
	echo '<div class="brio-row brio-row--3">';
	brio_field_text( 'brio_landing_cta_tagline1', __( 'Accroche 1', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline1' ) );
	brio_field_text( 'brio_landing_cta_tagline2', __( 'Accroche 2', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline2' ) );
	brio_field_text( 'brio_landing_cta_tagline3', __( 'Accroche 3', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'tagline3' ) );
	echo '</div>';
	echo '<div class="brio-row brio-row--2">';
	brio_field_text( 'brio_landing_cta_label', __( 'Libellé bouton', 'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'label' ) );
	brio_field_url(  'brio_landing_cta_url',   __( 'URL bouton',     'brio-guiseppe' ), brio_meta_get( $id, 'landing', 'cta', 'url' ) );
	echo '</div>';
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
