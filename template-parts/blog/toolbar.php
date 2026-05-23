<?php
/**
 * Blog — Toolbar (search input + clear + category dropdown + search button)
 *
 * Tout-en-un, un seul "pill" arrondi qui contient :
 *   • <input> de recherche libre
 *   • bouton × pour effacer (visible si recherche OU catégorie active)
 *   • bouton "Catégorie ▾" qui déploie une liste verticale
 *   • bouton loupe rond accent qui force un fetch immédiat
 *
 * L'ouverture du dropdown se fait au clic ; le JS gère l'état et le focus
 * trap minimal (Escape ferme, clic outside ferme).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$cats = brio_get_blog_categories();
?>
<section class="blog-toolbar" aria-label="<?php esc_attr_e( 'Filtrer les articles', 'brio-guiseppe' ); ?>">

	<form class="blog-search"
	      role="search"
	      data-blog-search>

		<input id="blog-search-input"
		       type="search"
		       class="blog-search__input"
		       aria-label="<?php esc_attr_e( 'Rechercher un article', 'brio-guiseppe' ); ?>"
		       placeholder="<?php esc_attr_e( 'Rechercher un article…', 'brio-guiseppe' ); ?>"
		       autocomplete="off"
		       data-blog-search-input />

		<button type="button"
		        class="blog-search__clear"
		        data-blog-clear
		        aria-label="<?php esc_attr_e( 'Effacer la recherche', 'brio-guiseppe' ); ?>"
		        hidden>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>

		<?php if ( ! empty( $cats ) ) : ?>
			<div class="blog-search__dropdown" data-blog-dropdown>
				<button type="button"
				        class="blog-search__dropdown-trigger"
				        data-blog-dropdown-trigger
				        aria-haspopup="listbox"
				        aria-expanded="false">
					<span class="blog-search__dropdown-label" data-blog-dropdown-label>
						<?php esc_html_e( 'Catégorie', 'brio-guiseppe' ); ?>
					</span>
					<svg class="blog-search__dropdown-caret" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>

				<ul class="blog-search__dropdown-menu"
				    role="listbox"
				    data-blog-dropdown-menu
				    hidden>
					<li role="option" aria-selected="true">
						<button type="button"
						        class="blog-search__dropdown-item is-active"
						        data-cat-slug=""
						        data-cat-name="<?php esc_attr_e( 'Tous', 'brio-guiseppe' ); ?>">
							<?php esc_html_e( 'Tous', 'brio-guiseppe' ); ?>
						</button>
					</li>
					<?php foreach ( $cats as $cat ) : ?>
						<li role="option" aria-selected="false">
							<button type="button"
							        class="blog-search__dropdown-item"
							        data-cat-slug="<?php echo esc_attr( $cat['slug'] ); ?>"
							        data-cat-name="<?php echo esc_attr( $cat['name'] ); ?>">
								<?php echo esc_html( $cat['name'] ); ?>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<button type="submit"
		        class="blog-search__submit"
		        data-blog-submit
		        aria-label="<?php esc_attr_e( 'Lancer la recherche', 'brio-guiseppe' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
				<line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
	</form>

</section>
