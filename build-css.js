/**
 * Build CSS bundles for production.
 *
 *   node build-css.js
 *
 * Bundles:
 *   dist/global.min.css   — fonts + variables + header + footer + typography + button + section + animations + phone + navigation
 *   dist/home.min.css     — home sections + landing.css (used on home & landings)
 *   dist/blog.min.css     — blog-page + blog section
 *   dist/single.min.css   — single
 *   dist/legal.min.css    — legal
 *   dist/author.min.css   — author
 *   dist/404.min.css      — 404
 *
 * Minification: strip CSS comments + collapse whitespace. Lightweight, no
 * postcss/cssnano dependency.
 */
const fs   = require( 'fs' );
const path = require( 'path' );

const ROOT = path.join( __dirname, 'assets/css' );
const DIST = path.join( ROOT, 'dist' );

if ( ! fs.existsSync( DIST ) ) fs.mkdirSync( DIST, { recursive: true } );

function read( rel ) {
	const full = path.join( ROOT, rel );
	if ( ! fs.existsSync( full ) ) {
		console.warn( '  (skipped, missing) ' + rel );
		return '';
	}
	let css = fs.readFileSync( full, 'utf8' );

	// Rewrite url(...) paths so they remain valid when the bundle lives
	// in assets/css/dist/ rather than assets/css/{section,components}/.
	// Source file's directory:        assets/css/<dirOf(rel)>/
	// Bundle output directory:        assets/css/dist/
	// We compute the path FROM the bundle dir TO the asset.
	const sourceDir = path.posix.join( 'assets/css', path.dirname( rel ).replace( /\\/g, '/' ) );
	const bundleDir = 'assets/css/dist';
	css = css.replace( /url\(\s*(['"]?)([^'")]+)\1\s*\)/g, function ( m, q, target ) {
		// Leave absolute URLs (http, //, data:) untouched.
		if ( /^(https?:|\/\/|data:)/i.test( target ) ) return m;
		// Resolve target relative to the source file's directory, then
		// recompute relative to the bundle directory.
		const absFromTheme = path.posix.normalize( path.posix.join( sourceDir, target ) );
		const fromBundle   = path.posix.relative( bundleDir, absFromTheme );
		return 'url(' + q + fromBundle + q + ')';
	} );

	// Strip @import statements — the bundler already inlines everything we
	// need, and @import inside a concatenated bundle blocks rendering.
	css = css.replace( /@import\s+url\([^)]+\)\s*;?/g, '' );
	css = css.replace( /@import\s+["'][^"']+["']\s*;?/g, '' );

	return css;
}

function minify( css ) {
	return css
		.replace( /\/\*[\s\S]*?\*\//g, '' )
		.replace( /\s+/g, ' ' )
		// Strip spaces around structural chars only. `+` and `~` are NOT
		// included here because they double as arithmetic operators inside
		// calc()/clamp()/min()/max() where surrounding spaces are mandatory
		// per the CSS spec ("1rem+5vw" is a parse error, "1rem + 5vw" is valid).
		.replace( /\s*([{}:;,>])\s*/g, '$1' )
		.replace( /;}/g, '}' )
		.trim();
}

const bundles = {
	'global.min.css': [
		'fonts.css',
		'variables.css',
		'components/typography.css',
		'components/button.css',
		'components/section.css',
		'components/phone.css',
		'components/navigation.css',
		'components/animations.css',
		'header.css',
		'header-responsive.css',
		'footer.css',
	],
	'home.min.css': [
		'home.css',
		'sections/hero.css',
		'sections/about.css',
		'sections/philosophy.css',
		'sections/showcase.css',
		'sections/partners.css',
		'sections/programs.css',
		'sections/fun-facts.css',
		'sections/pricing.css',
		'sections/faqs.css',
		'sections/blog.css',
		'sections/cta.css',
		'sections/landing.css',
	],
	'blog.min.css':   [ 'sections/blog-page.css', 'sections/blog.css' ],
	'single.min.css': [ 'sections/single.css' ],
	'legal.min.css':  [ 'sections/legal.css' ],
	'author.min.css': [ 'sections/author.css' ],
	'404.min.css':    [ 'sections/404.css' ],
};

for ( const out of Object.keys( bundles ) ) {
	console.log( '→ ' + out );
	let css = '';
	for ( const file of bundles[ out ] ) {
		const part = read( file );
		if ( part ) {
			css += '\n/* === ' + file + ' === */\n' + part;
			console.log( '  + ' + file );
		}
	}
	const min = minify( css );
	fs.writeFileSync( path.join( DIST, out ), min );
	console.log( '  → ' + ( min.length / 1024 ).toFixed( 1 ) + ' KB' );
}
console.log( '\nDone. Set BRIO_DEV_MODE to false to use these bundles.' );
