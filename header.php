<?php
/**
 * Site Header Template
 *
 * Outputs the opening <html>, <head>, <body> markup plus the site header.
 * Header layout: [primary nav] [logo] [phones + demo CTA].
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$company = brio_get_company_data();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />

	<?php /* Preconnect to font CDNs for faster first paint. */ ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'stretched no-transition' ); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
	<div class="container">

		<?php /* --- Primary Navigation (left) --- */ ?>
		<nav class="nav-left" aria-label="<?php esc_attr_e( 'Primary navigation', 'brio-guiseppe' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( [
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 4,
					'walker'         => new JU_Custom_Nav_Walker(),
				] );
			}
			?>
		</nav>

		<?php /* --- Site Branding (center) --- */ ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" rel="home" aria-label="<?php echo esc_attr( $company['name'] ); ?>">
			<?php echo esc_html( $company['name'] ); ?><span>.</span>
		</a>

		<?php /* --- Contact Actions (right) --- */ ?>
		<div class="nav-right">
			<?php foreach ( $company['phones'] as $phone ) : ?>
				<a href="tel:<?php echo esc_attr( $phone['tel'] ); ?>" class="phone">
					<i class="fas fa-phone" aria-hidden="true"></i>
					<span><?php echo esc_html( $phone['label'] ); ?></span>
				</a>
			<?php endforeach; ?>

			<a href="#" class="btn btn-primary">
				<?php esc_html_e( 'Planifiez votre démo', 'brio-guiseppe' ); ?>
			</a>
		</div>

	</div>
</header>
