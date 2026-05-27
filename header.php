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

	<?php /* Preload self-hosted fonts — both used above the fold.
	          Short-circuits the HTML → CSS → @font-face → woff2 chain. */ ?>
	<link rel="preload" as="font" type="font/woff2" href="<?php echo esc_url( get_theme_file_uri( '/assets/fonts/Nebeco.woff2' ) ); ?>" crossorigin />
	<link rel="preload" as="font" type="font/woff2" href="<?php echo esc_url( get_theme_file_uri( '/assets/fonts/Manrope-latin.woff2' ) ); ?>" crossorigin />

	<?php /* Critical above-the-fold CSS — eliminates render-blocking on first paint.
	          Tokens, header, hero base. Full bundle still loads via wp_head(). */ ?>
	<style id="brio-critical-css">
	:root{--color-primary:#173E04;--color-secondary:#B3D99A;--color-text:#215806;--color-accent:#FECE6E;--color-bg-primary:#FFFAEE;--color-bg-secondary:#F5F0E2;--color-border:rgba(33,88,6,.19);--font-primary:'Nebeco',serif;--font-secondary:'Manrope',sans-serif;--section-max-w:1400px;--container-pad-x:clamp(16px,4vw,40px);--h1-size:clamp(2.75rem, 1.5rem + 5vw, 5rem);--h2-size:clamp(2.125rem, 1.25rem + 3.6vw, 3.625rem);--h3-size:clamp(1.625rem, 1.125rem + 2.1vw, 2.375rem);--h4-size:clamp(1.375rem, 1rem + 1.4vw, 1.8125rem);--h5-size:clamp(1.125rem, .95rem + .6vw, 1.375rem);--h6-size:clamp(1.0625rem, .95rem + .4vw, 1.25rem);--body-size:clamp(.9375rem, .875rem + .2vw, 1rem)}
	*{box-sizing:border-box}body{margin:0;font-family:var(--font-secondary);font-size:var(--body-size);color:var(--color-text);line-height:1.6;letter-spacing:-.01em}
	h1,.h1{font-family:var(--font-primary);font-size:var(--h1-size);font-weight:300;line-height:1;letter-spacing:-.04em;color:var(--color-primary);margin:0}
	h2,.h2{font-family:var(--font-primary);font-size:var(--h2-size);font-weight:300;line-height:1.05;letter-spacing:-.04em;color:var(--color-primary);margin:0}
	h3,.h3{font-family:var(--font-primary);font-size:var(--h3-size);font-weight:300;line-height:1.1;letter-spacing:-.035em;color:var(--color-primary);margin:0}
	h4,.h4{font-family:var(--font-primary);font-size:var(--h4-size);font-weight:300;line-height:1.15;letter-spacing:-.03em;color:var(--color-primary);margin:0}
	h5,.h5{font-family:var(--font-primary);font-size:var(--h5-size);font-weight:300;line-height:1.25;letter-spacing:-.025em;color:var(--color-primary);margin:0}
	h6,.h6{font-family:var(--font-primary);font-size:var(--h6-size);font-weight:300;line-height:1.3;letter-spacing:-.025em;color:var(--color-primary);margin:0}
	.container{max-width:var(--section-max-w);margin:0 auto;padding-left:var(--container-pad-x);padding-right:var(--container-pad-x);width:100%}
	.site-header{width:100%;padding:20px 0;background:#FFF;position:relative;z-index:95}
	.site-header .container{display:flex;align-items:center;justify-content:space-between;gap:0;padding:0 40px}
	.site-header .logo{font-family:var(--font-primary);font-size:36px;font-weight:300;line-height:1;color:var(--color-primary);text-decoration:none;white-space:nowrap;order:2;width:20%;display:flex;justify-content:center}
	.site-header .nav-left{order:1;width:40%;display:flex;gap:40px;align-items:center}
	.site-header .nav-right{order:3;width:40%;display:flex;justify-content:flex-end;gap:20px;align-items:center}
	.site-header__mobile-actions,.site-header__burger{display:none}
	@media (max-width:768px){.site-header{padding:10px 14px}.site-header .container{padding:0;gap:10px}.site-header .logo{order:1;width:auto;font-size:18px;justify-content:flex-start}.site-header .nav-left{display:none}.site-header .nav-right{display:none}.site-header__mobile-actions{display:flex;gap:10px;order:2;margin-left:auto;align-items:center}.site-header__burger{display:inline-flex;width:38px;height:38px;border:1px solid var(--color-border);border-radius:8px;background:transparent;flex-direction:column;justify-content:center;gap:4px;padding:8px}}
	</style>

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'stretched no-transition' ); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
	<div class="container">

		<?php /* --- Site Branding (left on mobile, center on desktop) --- */ ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" rel="home" aria-label="<?php echo esc_attr( $company['name'] ); ?>">
			<?php echo esc_html( $company['name'] ); ?><span>.</span>
		</a>

		<?php /* --- Primary Navigation (drawer on mobile) --- */ ?>
		<nav class="nav-left" id="brio-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'brio-guiseppe' ); ?>">
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

		<?php /* --- Mobile CTA + burger (right cluster) --- */ ?>
		<div class="site-header__mobile-actions">
			<a href="#" class="btn btn-primary site-header__mobile-cta">
				<?php esc_html_e( 'Planifiez votre démo', 'brio-guiseppe' ); ?>
			</a>
			<button type="button"
			        class="site-header__burger"
			        aria-controls="brio-primary-nav"
			        aria-expanded="false"
			        aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'brio-guiseppe' ); ?>">
				<span class="site-header__burger-line"></span>
				<span class="site-header__burger-line"></span>
				<span class="site-header__burger-line"></span>
			</button>
		</div>

		<?php /* --- Contact Actions (desktop only) --- */ ?>
		<div class="nav-right">
			<?php foreach ( $company['phones'] as $phone ) : ?>
				<a href="tel:<?php echo esc_attr( $phone['tel'] ); ?>" class="phone">
					<?php echo brio_icon( 'phone' ); ?>
					<span><?php echo esc_html( $phone['label'] ); ?></span>
				</a>
			<?php endforeach; ?>

			<a href="#" class="btn btn-primary">
				<?php esc_html_e( 'Planifiez votre démo', 'brio-guiseppe' ); ?>
			</a>
		</div>

	</div>
</header>
