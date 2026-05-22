<?php
/**
 * Theme Data Provider
 *
 * Centralized configuration for company contact information, footer columns,
 * legal data, and other site-wide constants. Provides a single source of truth
 * for content that appears in multiple template locations (header, footer, etc.).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get company contact information.
 *
 * Used by header (phones, demo CTA) and footer (contact column, branding).
 * Filterable to allow plugins/child themes to override values.
 *
 * @since 1.0.0
 *
 * @return array {
 *     Company contact data.
 *
 *     @type string $name    Company display name.
 *     @type string $tagline Marketing tagline.
 *     @type string $address Postal address.
 *     @type array  $phones  List of phone numbers (label + tel: href).
 *     @type string $email   Primary contact email.
 *     @type array  $social  Social media URLs keyed by network.
 * }
 */
function brio_get_company_data() {
	$data = [
		'name'    => 'Brio Guiseppe',
		'tagline' => __( 'Votre hôtel mérite mieux qu\'une page Booking.', 'brio-guiseppe' ),
		'address' => __( '5º étage N°19, Res Moulay Ismail Av. Moulay Ismail, Tanger 90000', 'brio-guiseppe' ),
		'phones'  => [
			[ 'label' => '+33 6 16 97 58 44',  'tel' => '+33616975844' ],
			[ 'label' => '+212 7 70 74 03 11', 'tel' => '+212770740311' ],
		],
		'email'   => 'contact@brioguiseppe.fr',
		'social'  => [
			'linkedin' => 'https://www.linkedin.com/in/brioguiseppe/',
		],
	];

	return apply_filters( 'brio_company_data', $data );
}

/**
 * Get footer navigation columns (Explorer + Services).
 *
 * The Contact and Social columns are built inline in the footer template
 * because they use specific data (phones, social URLs) from company data.
 *
 * @since 1.0.0
 *
 * @return array Footer navigation columns with titles and links.
 */
function brio_get_footer_columns() {
	$columns = [
		'explorer' => [
			'title' => __( 'Explorer', 'brio-guiseppe' ),
			'links' => [
				[ 'label' => __( 'Accueil', 'brio-guiseppe' ),   'url' => '#' ],
				[ 'label' => __( 'Expertise', 'brio-guiseppe' ), 'url' => '#' ],
				[ 'label' => __( 'Services', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'Blog', 'brio-guiseppe' ),      'url' => '#' ],
				[ 'label' => __( 'Contact', 'brio-guiseppe' ),   'url' => '#' ],
			],
		],
		'services' => [
			'title' => __( 'Services', 'brio-guiseppe' ),
			'links' => [
				[ 'label' => __( 'Site Web Hôtel Conversion', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'SEO Tourisme & Destination', 'brio-guiseppe' ), 'url' => '#' ],
				[ 'label' => __( 'Revenue Management System', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'Audit Distribution OTA', 'brio-guiseppe' ),     'url' => '#' ],
				[ 'label' => __( 'Optimisation Conversion', 'brio-guiseppe' ),    'url' => '#' ],
			],
		],
	];

	return apply_filters( 'brio_footer_columns', $columns );
}

/**
 * Get company legal information shown in the footer bottom row.
 *
 * @since 1.0.0
 *
 * @return array {
 *     Legal data.
 *
 *     @type string $ice       Identifiant Commun de l'Entreprise (Morocco).
 *     @type string $fiscal_id French tax identification number.
 *     @type array  $pages     Legal pages (privacy policy, legal mentions).
 * }
 */
function brio_get_legal_data() {
	$data = [
		'ice'       => '002333763000084',
		'fiscal_id' => '37690548',
		'pages'     => [
			'privacy' => [ 'label' => __( 'Politique de confidentialité', 'brio-guiseppe' ), 'url' => '#' ],
			'legal'   => [ 'label' => __( 'Mentions légales', 'brio-guiseppe' ),             'url' => '#' ],
		],
	];

	return apply_filters( 'brio_legal_data', $data );
}

/**
 * Get static asset URLs, grouped by section.
 *
 * All assets are served from the theme's assets/images/{section}/ folder so
 * the site has zero runtime dependency on the brioguiseppe.fr CDN. Add new
 * sections as a new key and drop the files in assets/images/{key}/.
 *
 * @since 1.0.0
 *
 * @return array<string, array<string, string>> Section => [ slug => URL ].
 */
function brio_get_assets() {
	$base = get_theme_file_uri( 'assets/images/' );

	$assets = [
		'hero'   => [
			'avatar_1' => $base . 'hero/avatar-1.jpg',
			'avatar_2' => $base . 'hero/avatar-2.jpg',
			'avatar_3' => $base . 'hero/avatar-3.jpg',
			'avatar_4' => $base . 'hero/avatar-4.jpg',
			'suitcase' => $base . 'hero/suitcase.svg',
			'video'    => $base . 'hero/marrakech.mp4',
			'poster'   => $base . 'hero/poster.webp',
		],
		'about' => [
			'commissions' => $base . 'about/Stop-aux-commissions-invisibles.webp',
		],
		'philosophy' => [
			'visual' => $base . 'philosophy/Creez-un-site-qui-remplace-les-OTA.webp',
		],
		'faqs' => [
			'visual' => $base . 'faqs/Un-outil-puissant-pour-votre-croissance.webp',
		],
		'fun_facts' => [
			'bg'      => $base . 'fun-facts/Concu-pour-generer-des-reservations.webp',
			'asset_1' => $base . 'fun-facts/asset-1.png',
			'asset_6' => $base . 'fun-facts/asset-6.png',
			'asset_5' => $base . 'fun-facts/asset-5.png',
			'asset_2' => $base . 'fun-facts/asset-2.png',
		],
		'showcase' => [
			'bg'           => $base . 'showcase/Un-site-concu-pour-les-hoteliers-ambitieux.webp',
			'top_left'     => $base . 'showcase/Plan-de-travail-1.png',
			'bottom_right' => $base . 'showcase/La-solution-pour-reduire-votre-dependance-OTA.webp',
		],
		'partners' => [
			'ota-marge'      => $base . 'partners/Ne-laissez-plus-les-OTA-prendre-votre-marge.webp',
			'controle'       => $base . 'partners/Reprenez-le-controle-des-maintenant.webp',
			'direct-vs-ota'  => $base . 'partners/Vos-concurrents-passent-au-direct-et-vous.webp',
			'strategie'      => $base . 'partners/Passez-a-une-strategie-rentable-1.webp',
			'hotel-en-avant' => $base . 'partners/Mettez-votre-hotel-en-avant-pas-les-OTA.webp',
			'chaque-jour'    => $base . 'partners/Chaque-jour-sans-site-optimise-vous-coute.webp',
			'ota-marge-2'    => $base . 'partners/Ne-laissez-plus-les-OTA-prendre-votre-marge-1.webp',
		],
		'footer' => [
			'logo'       => $base . 'footer/logo.webp',
			'decoration' => $base . 'footer/decoration.png',
		],
		'newsletter' => [
			'background' => $base . 'newsletter/background.webp',
		],
	];

	return apply_filters( 'brio_theme_assets', $assets );
}

/**
 * Get homepage Hero section content.
 *
 * Pure data — the template part is responsible for rendering only.
 * Filterable so the content can be A/B-tested or overridden without
 * editing the template.
 *
 * @since 1.0.0
 *
 * @return array {
 *     Hero content.
 *
 *     @type array $rating   { value, max, caption, href } — social proof block.
 *     @type array $title    Headline string.
 *     @type array $lead     Two-paragraph lead (string array, second may contain a %s placeholder).
 *     @type array $cta      List of CTAs ({ label, href, variant }).
 *     @type array $features List of feature pills ({ title, desc }).
 * }
 */
function brio_get_hero_data() {
	$company = brio_get_company_data();

	$data = [
		'rating'   => [
			'value'   => 5,
			'max'     => 5,
			'caption' => __( 'Noté par les hôteliers accompagnés', 'brio-guiseppe' ),
			'href'    => $company['social']['linkedin'] ?? '#',
		],
		'title'    => __( 'Libérez votre Hôtel des commissions OTA', 'brio-guiseppe' ),
		'lead'     => [
			__( 'Je construis des sites qui convertissent les visiteurs en réservations directes pour les hôtels indépendants, riads et maisons d\'hôtes.', 'brio-guiseppe' ),
			[
				/* translators: %s: bold inline phrase "jusqu'à 25 000 €/an de commissions". */
				'template'  => __( 'Résultat : %s récupérées.', 'brio-guiseppe' ),
				'highlight' => __( 'jusqu\'à 25 000 €/an de commissions', 'brio-guiseppe' ),
			],
		],
		'cta'      => [
			[ 'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),  'href' => '#audit',        'variant' => 'primary' ],
			[ 'label' => __( 'Calculer mes revenus perdus', 'brio-guiseppe' ), 'href' => '#calculateur',  'variant' => 'secondary' ],
		],
		'features' => [
			[ 'title' => __( 'Site Qui Vend', 'brio-guiseppe' ),              'desc' => __( 'Réservation directe. Zéro commission.', 'brio-guiseppe' ) ],
			[ 'title' => __( 'SEO Tourisme & Destination', 'brio-guiseppe' ), 'desc' => __( 'Vos hôtes vous trouvent avant les OTA.', 'brio-guiseppe' ) ],
			[ 'title' => __( 'Revenue Management Custom', 'brio-guiseppe' ),  'desc' => __( 'Chaque nuit vendue au meilleur prix.', 'brio-guiseppe' ) ],
			[ 'title' => __( 'Audit Distribution OTA', 'brio-guiseppe' ),     'desc' => __( 'Découvrez combien Booking vous coûte.', 'brio-guiseppe' ) ],
		],
	];

	return apply_filters( 'brio_hero_data', $data );
}

/**
 * Get homepage About section content.
 *
 * Two-column layout: text pitch (left 60%) + visual asset (right 35%).
 * Pure data — the template part is responsible for rendering only.
 *
 * @since 1.0.0
 *
 * @return array {
 *     About content.
 *
 *     @type string $overline       Small accent text above heading.
 *     @type string $heading        Main headline (multiline).
 *     @type string $description    Body text with context.
 *     @type array  $cta            Call-to-action button ({ label, href }).
 *     @type string $image          Featured image URL (right column).
 * }
 */
function brio_get_about_data() {
	$data = [
		'overline'    => __( 'Ce que je fais vraiment', 'brio-guiseppe' ),
		'heading'     => __( 'Je ne crée pas de sites web. Je construis des canaux de vente directe pour hôtels indépendants.', 'brio-guiseppe' ),
		'description' => __( 'Chaque nuit sur une OTA, 15 à 25% de votre revenu s\'envole et vos clients avec. Je construis le système qui les ramène : site qui convertit, SEO qui attire, pricing qui optimise. Pensé pour votre établissement, votre marché, votre histoire.', 'brio-guiseppe' ),
		'image'       => brio_asset( 'about', 'commissions' ),
		'cta'         => [
			'label' => __( 'Calculer mes revenus perdus', 'brio-guiseppe' ),
			'href'  => 'https://wa.me/212770740311?text=' . urlencode( __( 'Bonjour, je souhaite analyser les revenus perdus à cause des OTA et découvrir des solutions concrètes pour augmenter mes réservations directes. Peut-on planifier un échange ?', 'brio-guiseppe' ) ),
		],
	];

	return apply_filters( 'brio_about_data', $data );
}

/**
 * Get homepage Philosophy ("Approche") section content.
 *
 * Two-column block: visual (with floating yellow "mission" card) on the
 * left, and a text stack with 3 feature icon-boxes on the right.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $visual    Background image URL for the left column.
 *     @type array   $mission   { label, text } — yellow card content.
 *     @type string  $overline  Small uppercase label.
 *     @type string  $heading   Section title.
 *     @type string  $description Lead paragraph.
 *     @type array[] $features  Each item: { icon (FA class), title, text }.
 * }
 */
function brio_get_philosophy_data() {
	$data = [
		'visual'      => brio_asset( 'philosophy', 'visual' ),
		'mission'     => [
			'label' => __( 'Ma mission :', 'brio-guiseppe' ),
			'text'  => __( 'Donner aux hôteliers indépendants les armes digitales que seules les grandes chaînes possèdent — pour que chaque nuit soit vendue au bon prix, au bon client, sans intermédiaire.', 'brio-guiseppe' ),
		],
		'overline'    => __( 'Pourquoi Les Hôteliers Me Choisissent', 'brio-guiseppe' ),
		'heading'     => __( 'Une approche technique, humaine et orientée résultats mesurables', 'brio-guiseppe' ),
		'description' => __( 'Pas d\'agence. Pas de sous-traitance. Un seul interlocuteur qui maîtrise le code, le SEO et la réalité du terrain hôtelier.', 'brio-guiseppe' ),
		'features'    => [
			[
				'icon'  => 'fa-solid fa-pencil',
				'title' => __( 'Expertise Technique Profonde', 'brio-guiseppe' ),
				'text'  => __( 'Développeur web, expert SEO et spécialiste UX conversion — je maîtrise l\'ensemble de la chaîne technique. Pas de sous-traitance, pas de template générique. Chaque solution est construite sur-mesure pour maximiser vos réservations directes.', 'brio-guiseppe' ),
			],
			[
				'icon'  => 'fa-solid fa-chart-column',
				'title' => __( 'Connaissance du Terrain', 'brio-guiseppe' ),
				'text'  => __( 'Basé entre le Maroc et Madagascar, je connais les réalités des hôteliers indépendants en Afrique francophone. Les contraintes de bande passante, les habitudes de réservation locales, les spécificités culturelles — tout est intégré dans mes solutions.', 'brio-guiseppe' ),
			],
			[
				'icon'  => 'fa-solid fa-circle-check',
				'title' => __( 'ROI Mesurable et Garanti', 'brio-guiseppe' ),
				'text'  => __( 'Chaque euro investi est traçable. Tableau de bord en temps réel, rapports mensuels avec métriques claires : trafic organique, taux de conversion, commissions économisées. Le ROI est atteint en 3 à 6 mois en moyenne.', 'brio-guiseppe' ),
			],
		],
	];

	return apply_filters( 'brio_philosophy_data', $data );
}

/**
 * Get homepage Showcase ("Image" / "Video") section content.
 *
 * Visual-only break: a 50px-rounded media container with a background
 * image, plus two decorative images that overflow above (top-left) and
 * below (bottom-right) the media container.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $bg      Main background image URL.
 *     @type array[] $images  Each item: { url, alt, position }.
 * }
 */
function brio_get_showcase_data() {
	$a = brio_get_assets()['showcase'];

	$data = [
		'bg'     => $a['bg'],
		'images' => [
			[
				'url'      => $a['top_left'],
				'alt'      => __( 'Site internet hôtel avec réservation directe', 'brio-guiseppe' ),
				'position' => 'top-left',
			],
			[
				'url'      => $a['bottom_right'],
				'alt'      => __( 'Design site hôtel moderne et responsive', 'brio-guiseppe' ),
				'position' => 'bottom-right',
			],
		],
	];

	return apply_filters( 'brio_showcase_data', $data );
}

/**
 * Get homepage Fun Facts ("Résultats") section content.
 *
 * Yellow section with 4 counter cards in an asymmetric 34/34/32 grid:
 *   - Card 1: 34% bg-image with overlay (full height)
 *   - Card 2: 34% cream (full height)
 *   - Card 3 + Card 4: 32% column, stacked (270px each)
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $overline  Small uppercase label.
 *     @type string  $heading   Section title.
 *     @type array[] $cards     Each: { variant, icon, prefix, number, suffix, title, ?bg }.
 * }
 */
function brio_get_fun_facts_data() {
	$a = brio_get_assets()['fun_facts'];

	$data = [
		'overline' => __( 'Des Résultats Concrets Pour les Hôtels', 'brio-guiseppe' ),
		'heading'  => __( 'Chaque commission récupérée est un dirham réinvesti dans votre hôtel', 'brio-guiseppe' ),
		'cards'    => [
			[
				'variant' => 'image',
				'bg'      => $a['bg'],
				'icon'    => $a['asset_1'],
				'prefix'  => '+',
				'number'  => 62000,
				'suffix'  => '€',
				'title'   => __( 'Commissions OTA économisées en moyenne par nos clients la première année', 'brio-guiseppe' ),
			],
			[
				'variant' => 'light',
				'icon'    => $a['asset_6'],
				'prefix'  => '−',
				'number'  => 30,
				'suffix'  => '%',
				'title'   => __( 'De dépendance Booking.com en 12 mois grâce au direct booking', 'brio-guiseppe' ),
			],
			[
				'variant' => 'dark',
				'icon'    => $a['asset_5'],
				'prefix'  => '',
				'number'  => 90,
				'suffix'  => ' Jours',
				'title'   => __( 'Délai moyen pour rentabiliser votre investissement. Le site se paye tout seul.', 'brio-guiseppe' ),
			],
			[
				'variant' => 'light',
				'icon'    => $a['asset_2'],
				'prefix'  => '+',
				'number'  => 45,
				'suffix'  => '%',
				'title'   => __( 'De trafic organique Google en 6 mois sur les sites que nous créons', 'brio-guiseppe' ),
			],
		],
	];

	return apply_filters( 'brio_fun_facts_data', $data );
}

/**
 * Get homepage FAQs section content.
 *
 * Two-column block: image left + content right (overline + h2 + 7-item
 * accordion). Items toggle between a cream "closed" state and a dark
 * "open" state. JSON-LD FAQPage schema is rendered by the template
 * for rich snippet eligibility in Google search.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $visual    Left-column background image URL.
 *     @type string  $overline  Small uppercase label.
 *     @type string  $heading   Section title.
 *     @type array[] $items     Each item: { question, answer }.
 * }
 */
function brio_get_faqs_data() {
	$data = [
		'visual'   => brio_asset( 'faqs', 'visual' ),
		'overline' => __( 'QUESTIONS FRÉQUENTES', 'brio-guiseppe' ),
		'heading'  => __( 'Tout ce que les hôteliers nous demandent avant de se lancer', 'brio-guiseppe' ),
		'items'    => [
			[
				'question' => __( 'Combien coûtent réellement les commissions OTA pour un riad ?', 'brio-guiseppe' ),
				'answer'   => __( 'Beaucoup plus que vous ne pensez. Un riad de 10 chambres à 900 MAD/nuit avec 65% d\'occupation et 70% de réservations via OTA verse entre 180 000 et 260 000 MAD par an en commissions — selon que Booking prend 15%, 18% ou 22%. Et ça, c\'est sans compter les commissions sur les extras, le programme Genius et les promotions imposées. En résumé : vous financez le marketing de Booking avec vos marges. Un site de réservation directe permet de récupérer 25 à 35% de ce montant dès la première année.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'En combien de temps mon investissement est-il rentabilisé ?', 'brio-guiseppe' ),
				'answer'   => __( 'En moyenne, 2 à 4 mois. Prenons un exemple concret : votre site coûte 5 000 MAD. Si vous récupérez ne serait-ce que 5 réservations directes par mois à 900 MAD (au lieu de Booking), vous économisez environ 810 MAD/mois en commissions (18%). En 6 mois, c\'est 4 860 MAD économisés. Le site est remboursé. Après, chaque réservation directe est du bénéfice net. Et plus votre référencement Google monte, plus les réservations directes augmentent — sans dépense supplémentaire.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'Faut-il quitter Booking complètement ?', 'brio-guiseppe' ),
				'answer'   => __( 'Non, surtout pas. Booking reste un excellent canal de visibilité. L\'objectif n\'est pas de couper Booking mais de rééquilibrer votre distribution. Aujourd\'hui, si 80% de vos réservations passent par les OTA, vous êtes dépendant. L\'idéal est d\'arriver à un mix 50/50 : Booking vous apporte la visibilité et les nouveaux clients, votre site capte les clients qui vous cherchent par votre nom et ceux qui reviennent. Vous gardez le meilleur des deux mondes, en payant beaucoup moins de commissions.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'Quelle est la différence avec SiteMinder ou Cloudbeds ?', 'brio-guiseppe' ),
				'answer'   => __( 'SiteMinder et Cloudbeds sont des plateformes SaaS standardisées à partir de 85$/mois. Elles fournissent des templates identiques pour tous les hôtels du monde. Moi, je construis un site sur-mesure adapté à VOTRE établissement : votre histoire, vos visuels, votre positionnement. Pas de template générique, pas d\'abonnement mensuel qui ne finit jamais. Vous payez une fois, le site est à vous. Et surtout, j\'intègre le SEO, l\'optimisation conversion et le conseil stratégique — des choses qu\'aucun SaaS ne fait à votre place.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'Travaillez-vous uniquement avec des hôtels au Maroc ?', 'brio-guiseppe' ),
				'answer'   => __( 'Le Maroc est mon marché principal parce que je connais le terrain, les contraintes et les spécificités locales. Mais j\'accompagne aussi des établissements en Afrique francophone (Madagascar, Sénégal, Côte d\'Ivoire) et dans le sud de l\'Europe. Si vous gérez un hôtel, un riad, une maison d\'hôtes ou une villa touristique, qu\'importe la géographie — tant que vous voulez réduire vos commissions OTA et développer le direct booking, on peut travailler ensemble.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'Comment se passe la collaboration concrètement ?', 'brio-guiseppe' ),
				'answer'   => __( 'C\'est simple et structuré. Étape 1 : un appel de 15 minutes pour comprendre votre situation (gratuit). Étape 2 : je vous envoie une proposition détaillée avec le périmètre, le planning et le prix. Étape 3 : on démarre avec un acompte de 40%. Je m\'occupe de tout — design, développement, contenu, SEO — et vous validez à chaque étape. Étape 4 : votre site est en ligne en 4 à 6 semaines. Étape 5 : je vous forme en 1h et je reste disponible pendant 3 mois pour le support. Tout se fait par WhatsApp, simple et rapide.', 'brio-guiseppe' ),
			],
			[
				'question' => __( 'Et si je n\'ai pas de site web du tout ?', 'brio-guiseppe' ),
				'answer'   => __( 'C\'est en fait le meilleur scénario. Pas de site à refondre, pas de contraintes techniques héritées. On part d\'une page blanche et on construit directement un site pensé pour la conversion. Vous me fournissez vos photos, vos tarifs et une description de vos chambres — le reste, c\'est mon travail. Et si vous n\'avez pas de photos professionnelles, je vous guide sur ce qu\'il faut photographier avec un smartphone. 80% de mes clients n\'avaient aucun site avant de travailler avec moi.', 'brio-guiseppe' ),
			],
		],
	];

	return apply_filters( 'brio_faqs_data', $data );
}

/**
 * Get homepage final CTA section content.
 *
 * Jumbo rounded card (cream → light-green bg) over an accent strip.
 * Two columns inside: icon + headline (bottom-aligned on the left) +
 * rotating SVG textPath badge + button (on the right).
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string $icon       Decorative icon URL.
 *     @type string $heading    Multiline headline (\n → <br>).
 *     @type string $badge_text Rotating text around the SVG badge.
 *     @type array  $cta        { label, href } final CTA button.
 * }
 */
function brio_get_cta_data() {
	$data = [
		'icon'     => brio_asset( 'fun_facts', 'asset_6' ),
		'heading'  => __( "Vous versez plus de 60 000 €/an à Booking.\nAvec un site optimisé, vous récupérez 25% de ce montant dès la première année.", 'brio-guiseppe' ),
		'taglines' => [
			__( 'MOINS DE COMMISSIONS', 'brio-guiseppe' ),
			__( 'PLUS DE REVENUS', 'brio-guiseppe' ),
			__( 'PLUS DE CONTRÔLE', 'brio-guiseppe' ),
		],
		'cta'      => [
			'label' => __( 'Calculer mes revenus perdus', 'brio-guiseppe' ),
			'href'  => 'https://www.brioguiseppe.fr/calculer-mes-commissions-ota/',
		],
	];

	return apply_filters( 'brio_cta_data', $data );
}

/**
 * Get homepage Blog section static content.
 *
 * Returns the section overline + heading. Posts themselves are queried
 * directly in the template via WP_Query so each render reflects current
 * publish state without going through a stale data cache.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string $overline       Small uppercase label.
 *     @type string $heading        Section title.
 *     @type int    $posts_per_page Number of cards to display.
 *     @type string $excerpt_words  Word count for trimmed excerpts.
 *     @type string $empty_message  Shown when no posts are published yet.
 * }
 */
function brio_get_blog_data() {
	$data = [
		'overline'       => __( 'Blog & News', 'brio-guiseppe' ),
		'heading'        => __( 'Insights & Stratégies pour Hôteliers Indépendants', 'brio-guiseppe' ),
		'posts_per_page' => 3,
		'excerpt_words'  => 14,
		'empty_message'  => __( 'De nouveaux articles arrivent bientôt.', 'brio-guiseppe' ),
	];

	return apply_filters( 'brio_blog_data', $data );
}

/**
 * Build the WP_Query args used by the homepage Blog section.
 *
 * Wrapped in its own function + filter so plugins / child themes can
 * change category, ordering, or count without touching the template.
 *
 * @since 1.0.0
 *
 * @param int $per_page Number of posts to fetch.
 * @return array WP_Query arguments.
 */
function brio_get_blog_query_args( $per_page = 3 ) {
	$args = [
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => max( 1, (int) $per_page ),
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,   // skip pagination calc — perf win.
		'update_post_meta_cache' => false,  // no meta read in the template.
		'update_post_term_cache' => false,  // no taxonomies read in the template.
		'orderby'                => 'date',
		'order'                  => 'DESC',
	];

	return apply_filters( 'brio_blog_query_args', $args );
}

/**
 * Get homepage Pricing section content.
 *
 * Header row (overline + heading on left, top CTA on right) + 3-card
 * pricing grid. Middle card uses the "dark" variant for emphasis.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $overline   Small uppercase label.
 *     @type string  $heading    Section title.
 *     @type array   $cta        { label, href } header CTA.
 *     @type array[] $plans      Each: { variant, rooms, title, price_prefix,
 *                                       price, tagline, cta, includes[], ideal }.
 * }
 */
function brio_get_pricing_data() {
	$wa = 'https://wa.me/212770740311?text=';

	$plan_msg = function ( $offer ) {
		return sprintf(
			/* translators: %s: offer name (e.g. "RIAD & MAISON D'HÔTES à partir de 5 000 MAD"). */
			__( 'Bonjour, je suis intéressé(e) par l\'offre %s. Pouvez-vous m\'envoyer plus d\'informations ?', 'brio-guiseppe' ),
			$offer
		);
	};

	$data = [
		'overline' => __( 'VOTRE SITE SE REMBOURSE EN 3 MOIS', 'brio-guiseppe' ),
		'heading'  => __( 'Investissez une fois, économisez chaque mois', 'brio-guiseppe' ),
		'cta'      => [
			'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),
			'href'  => $wa . rawurlencode( __( 'Bonjour, je souhaite réserver un audit gratuit pour analyser mes opportunités de réservations directes.', 'brio-guiseppe' ) ),
		],
		'plans'    => [
			[
				'variant'      => 'light',
				'rooms'        => __( '3 à 8 chambres', 'brio-guiseppe' ),
				'title'        => __( 'RIAD & MAISON D\'HÔTES', 'brio-guiseppe' ),
				'price_prefix' => __( 'À partir de', 'brio-guiseppe' ),
				'price'        => __( '5 000 MAD', 'brio-guiseppe' ),
				'tagline'      => __( 'Votre riad en ligne en 5 jours. Prêt à recevoir ses premières réservations directes.', 'brio-guiseppe' ),
				'cta'          => [
					'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),
					'href'  => $wa . rawurlencode( $plan_msg( 'RIAD & MAISON D\'HÔTES à partir de 5 000 MAD' ) ),
				],
				'includes'     => [
					__( 'Site optimisé réservation', 'brio-guiseppe' ),
					__( 'Moteur de booking intégré', 'brio-guiseppe' ),
					__( 'Design mobile-first', 'brio-guiseppe' ),
					__( 'Fiche Google Business optimisée', 'brio-guiseppe' ),
					__( 'Formation utilisation (1h)', 'brio-guiseppe' ),
				],
				'ideal'        => __( 'Les riads et maisons d\'hôtes qui n\'ont pas de site web ou qui ont une simple page vitrine sans réservation. Vous voulez exister sur Google et recevoir vos premières réservations directes sans passer par Booking.', 'brio-guiseppe' ),
			],
			[
				'variant'      => 'dark',
				'rooms'        => __( '8 à 20 chambres', 'brio-guiseppe' ),
				'title'        => __( 'BOUTIQUE HÔTEL', 'brio-guiseppe' ),
				'price_prefix' => __( 'À partir de', 'brio-guiseppe' ),
				'price'        => __( '12 000 MAD', 'brio-guiseppe' ),
				'tagline'      => __( 'Votre vitrine Booking vous coûte des milliers de dirhams. Ce site les récupère.', 'brio-guiseppe' ),
				'cta'          => [
					'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),
					'href'  => $wa . rawurlencode( $plan_msg( 'BOUTIQUE HÔTEL (8 à 20 chambres) à partir de 12 000 MAD' ) ),
				],
				'includes'     => [
					__( 'Site multi-pages', 'brio-guiseppe' ),
					__( 'Moteur de booking intégré', 'brio-guiseppe' ),
					__( 'Galerie immersive + storytelling', 'brio-guiseppe' ),
					__( 'SEO local + 4 pages optimisées', 'brio-guiseppe' ),
					__( 'Audit OTA offert', 'brio-guiseppe' ),
				],
				'ideal'        => __( 'Les boutique hôtels qui ont déjà du volume sur Booking et veulent reprendre le contrôle. Vous savez que vous perdez des milliers de dirhams en commissions chaque mois et vous êtes prêt à investir pour les récupérer.', 'brio-guiseppe' ),
			],
			[
				'variant'      => 'light',
				'rooms'        => __( '20+ chambres', 'brio-guiseppe' ),
				'title'        => __( 'HÔTEL INDÉPENDANT', 'brio-guiseppe' ),
				'price_prefix' => __( 'À partir de', 'brio-guiseppe' ),
				'price'        => __( '25 000 MAD', 'brio-guiseppe' ),
				'tagline'      => __( 'La solution complète pour ne plus jamais dépendre d\'une seule plateforme.', 'brio-guiseppe' ),
				'cta'          => [
					'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),
					'href'  => $wa . rawurlencode( $plan_msg( 'HÔTEL INDÉPENDANT (20+ chambres) à partir de 25 000 MAD' ) ),
				],
				'includes'     => [
					__( 'Site premium multilingue', 'brio-guiseppe' ),
					__( 'Moteur de booking + intégration PMS', 'brio-guiseppe' ),
					__( 'SEO tourisme complet', 'brio-guiseppe' ),
					__( 'Revenue Management basique', 'brio-guiseppe' ),
					__( 'Stratégie de contenu', 'brio-guiseppe' ),
				],
				'ideal'        => __( 'Les hôtels établis qui veulent une stratégie digitale complète : site premium, SEO sur toutes les requêtes de votre destination, pricing intelligent et indépendance totale vis-à-vis des OTA. Vous visez 50%+ de réservations en direct d\'ici 12 mois.', 'brio-guiseppe' ),
			],
		],
	];

	return apply_filters( 'brio_pricing_data', $data );
}

/**
 * Get homepage Partners section content.
 *
 * Returns the label and the list of partner/technology visuals for the
 * scrolling marquee. Items are intentionally not duplicated here — the
 * template handles duplication for the seamless CSS loop.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $label  Caption above the marquee.
 *     @type array[] $items  Each item: { url, alt }.
 * }
 */
function brio_get_partners_data() {
	$a = brio_get_assets()['partners'];

	$data = [
		'label' => __( 'Technologies & Partenaires qui propulsent mes solutions', 'brio-guiseppe' ),
		'items' => [
			[ 'url' => $a['ota-marge'],      'alt' => __( 'Ne laissez plus les OTA prendre votre marge', 'brio-guiseppe' ) ],
			[ 'url' => $a['controle'],        'alt' => __( 'Reprenez le contrôle dès maintenant', 'brio-guiseppe' ) ],
			[ 'url' => $a['direct-vs-ota'],   'alt' => __( 'Vos concurrents passent au direct — et vous ?', 'brio-guiseppe' ) ],
			[ 'url' => $a['strategie'],       'alt' => __( 'Passez à une stratégie rentable', 'brio-guiseppe' ) ],
			[ 'url' => $a['hotel-en-avant'],  'alt' => __( 'Mettez votre hôtel en avant, pas les OTA', 'brio-guiseppe' ) ],
			[ 'url' => $a['chaque-jour'],     'alt' => __( 'Chaque jour sans site optimisé vous coûte', 'brio-guiseppe' ) ],
			[ 'url' => $a['ota-marge-2'],     'alt' => __( 'Ne laissez plus les OTA prendre votre marge', 'brio-guiseppe' ) ],
		],
	];

	return apply_filters( 'brio_partners_data', $data );
}

/**
 * Get homepage Programs section content.
 *
 * Dark "Solutions" section with a 4-item accordion + CTA + sub-note.
 *
 * @since 1.0.0
 *
 * @return array {
 *     @type string  $overline  Small uppercase label above heading.
 *     @type string  $heading   Section title.
 *     @type array[] $items     Each item: { title, content }.
 *     @type array   $cta       { label, href } primary CTA.
 *     @type string  $note      Reassurance text under the CTA.
 * }
 */
function brio_get_programs_data() {
	$wa_text = __( 'Bonjour, je souhaite bénéficier d\'un audit OTA gratuit afin d\'identifier les opportunités d\'augmentation de mes réservations directes et de réduction des commissions.', 'brio-guiseppe' );

	$data = [
		'overline' => __( 'CE QUE VOUS GAGNEZ EN TRAVAILLANT AVEC MOI', 'brio-guiseppe' ),
		'heading'  => __( 'Des solutions concrètes pour chaque défi Hôtelier', 'brio-guiseppe' ),
		'items'    => [
			[
				'title'   => __( 'Une stratégie digitale complète (pas juste un site)', 'brio-guiseppe' ),
				'content' => __( 'Votre site web n\'est que le point de départ. On construit autour un écosystème complet : référencement Google pour attirer les voyageurs, fiche Google Business optimisée pour le local, contenu destination pour capter les recherches, et tracking analytics pour mesurer chaque réservation. Le tout forme une machine d\'acquisition qui tourne en continu — pas une brochure en ligne que personne ne visite.', 'brio-guiseppe' ),
			],
			[
				'title'   => __( 'Moins de commissions, plus de marge', 'brio-guiseppe' ),
				'content' => __( 'Un riad de 10 chambres à 900 MAD/nuit verse en moyenne 200 000 MAD/an à Booking. Avec un site optimisé pour la réservation directe, vous récupérez 25 à 35% de ces commissions dès la première année. On analyse votre distribution actuelle, on identifie les fuites, et on met en place les leviers concrets : offres exclusives "book direct", moteur de réservation intégré, parité tarifaire intelligente. Chaque réservation directe, c\'est 18 à 25% de marge en plus.', 'brio-guiseppe' ),
			],
			[
				'title'   => __( 'Acquisition de clients en direct', 'brio-guiseppe' ),
				'content' => __( '65% des voyageurs qui trouvent votre hôtel sur Booking vont ensuite chercher votre nom sur Google. Si votre site n\'apparaît pas — ou s\'il n\'inspire pas confiance — vous les perdez. On positionne votre établissement sur les requêtes qui comptent : "riad [votre ville]", "hôtel avec piscine [destination]", "maison d\'hôtes [région]". Le voyageur vous trouve, il réserve chez vous. Sans intermédiaire, sans commission.', 'brio-guiseppe' ),
			],
			[
				'title'   => __( 'Contrôle & fidélisation client', 'brio-guiseppe' ),
				'content' => __( 'Quand un client réserve via Booking, c\'est Booking qui possède ses données. Vous ne pouvez ni le recontacter, ni lui proposer une offre pour son anniversaire, ni l\'inviter à revenir en basse saison. Avec le direct booking, chaque client devient VOTRE client : email, téléphone, préférences, historique de séjour. Vous construisez une base de données qui prend de la valeur avec le temps. Un client fidèle coûte 5 fois moins cher qu\'un nouveau client Booking.', 'brio-guiseppe' ),
			],
		],
		'cta'      => [
			'label' => __( 'Réserver mon audit gratuit', 'brio-guiseppe' ),
			'href'  => 'https://wa.me/212770740311?text=' . rawurlencode( $wa_text ),
		],
		'note'     => __( 'En 48h, recevez un rapport personnalisé avec vos commissions estimées et vos opportunités de direct booking.', 'brio-guiseppe' ),
	];

	return apply_filters( 'brio_programs_data', $data );
}

/**
 * Get a single asset URL by section + key.
 *
 * Convenience wrapper around brio_get_assets() for template files.
 * Returns empty string if the section or key is unknown.
 *
 * @since 1.0.0
 *
 * @param string $section Section slug (e.g. 'hero', 'footer').
 * @param string $key     Asset slug within the section.
 * @return string Asset URL, or '' if not found.
 */
function brio_asset( $section, $key ) {
	$assets = brio_get_assets();
	return isset( $assets[ $section ][ $key ] ) ? $assets[ $section ][ $key ] : '';
}
