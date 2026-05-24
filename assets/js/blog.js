/**
 * Blog — interactive layer (filters, search, Load more).
 *
 * Toolbar unifiée : un seul "pill" arrondi avec input + clear + dropdown
 * catégorie + bouton submit (loupe accent). Le filtrage et la recherche
 * partent du même état → un seul applyFilters() côté serveur.
 *
 * Hybride server/client : 2 featured + 12 topics imprimés par PHP, puis
 * fetch sur l'endpoint REST /brio/v1/blog/posts pour filtres / search /
 * Load more. Renderer JS symétrique au partial topics.php.
 */
( function () {
	'use strict';

	const root = document.querySelector( '[data-blog-app]' );
	const dataNode = document.getElementById( 'brio-blog-data' );
	if ( ! root || ! dataNode ) return;

	let config = {};
	try {
		config = JSON.parse( dataNode.textContent || '{}' );
	} catch ( e ) {
		console.error( '[Brio Blog] Failed to parse hydration data:', e );
		return;
	}

	const restUrl = root.dataset.restUrl;
	const perPage = parseInt( root.dataset.perPage, 10 ) || 12;
	if ( ! restUrl ) {
		console.error( '[Brio Blog] Missing data-rest-url on root.' );
		return;
	}

	/* Contraintes d'archive (auteur, tag, date) — fixes pour toute la session.
	   Lues depuis config.archive injecté par PHP dans le JSON hydration. */
	const archiveConstraints = ( config.archive && typeof config.archive === 'object' )
		? config.archive
		: {};

	const state = {
		category: '',
		search: archiveConstraints.search || '',
		offset: 0,
		hasMore: true,
		loading: false,
	};

	const grid           = root.querySelector( '[data-blog-grid]' );
	const titleEl        = root.querySelector( '[data-blog-topics-title]' );
	const descriptionEl  = root.querySelector( '[data-blog-topics-description]' );
	const searchEl       = root.querySelector( '[data-blog-search-input]' );
	const clearEl        = root.querySelector( '[data-blog-clear]' );
	const submitEl       = root.querySelector( '[data-blog-submit]' );
	const dropdown       = root.querySelector( '[data-blog-dropdown]' );
	const dropdownTrig   = root.querySelector( '[data-blog-dropdown-trigger]' );
	const dropdownMenu   = root.querySelector( '[data-blog-dropdown-menu]' );
	const dropdownLabel  = root.querySelector( '[data-blog-dropdown-label]' );
	const dropdownItems  = Array.from( root.querySelectorAll( '[data-blog-dropdown-item], .blog-search__dropdown-item' ) );
	const loadMoreEl     = root.querySelector( '[data-blog-load-more]' );
	const emptyEl        = root.querySelector( '[data-blog-empty]' );
	const searchForm     = root.querySelector( '[data-blog-search]' );

	/* Masquer la pagination HTML dès que JS est actif */
	root.closest( '.blog-topics' )
		? root.closest( '.blog-topics' ).classList.add( 'js-blog-ready' )
		: document.querySelector( '[data-blog-topics]' ) && document.querySelector( '[data-blog-topics]' ).classList.add( 'js-blog-ready' );
	document.querySelector( '[data-blog-pagination]' ) && ( document.querySelector( '[data-blog-pagination]' ).hidden = true );

	if ( ! grid ) {
		console.error( '[Brio Blog] Missing [data-blog-grid] in DOM.' );
		return;
	}

	const defaultDropdownLabel = ( config.i18n && config.i18n.category ) || 'Catégorie';

	/* Pré-remplir l'input si on arrive depuis une recherche WP */
	if ( state.search && searchEl ) {
		searchEl.value = state.search;
		if ( clearEl ) clearEl.hidden = false;
	}

	/* ---------- Renderer ---------- */

	function buildCard( post ) {
		const article = document.createElement( 'article' );
		article.className = 'blog-card';

		const link = document.createElement( 'a' );
		link.className = 'blog-card__link';
		link.href = post.url;

		if ( post.thumbnail ) {
			const figure = document.createElement( 'figure' );
			figure.className = 'blog-card__media';
			const img = document.createElement( 'img' );
			img.src = post.thumbnail;
			img.alt = '';
			img.loading = 'lazy';
			img.decoding = 'async';
			figure.appendChild( img );
			link.appendChild( figure );
		}

		const body = document.createElement( 'div' );
		body.className = 'blog-card__body';

		const time = document.createElement( 'time' );
		time.className = 'blog-card__date';
		time.dateTime = post.date_iso;
		time.textContent = post.date_display;
		body.appendChild( time );

		const title = document.createElement( 'h3' );
		title.className = 'blog-card__title';
		title.textContent = post.title;
		body.appendChild( title );

		if ( post.excerpt ) {
			const p = document.createElement( 'p' );
			p.className = 'blog-card__excerpt';
			p.textContent = post.excerpt;
			body.appendChild( p );
		}

		link.appendChild( body );
		article.appendChild( link );
		return article;
	}

	function renderPosts( posts, { append } ) {
		if ( ! append ) {
			grid.innerHTML = '';
		}
		const frag = document.createDocumentFragment();
		posts.forEach( ( post ) => frag.appendChild( buildCard( post ) ) );
		grid.appendChild( frag );
	}

	function updateTitle() {
		if ( ! titleEl || ! config.title_template ) return;

		let label;
		if ( state.category === '' ) {
			label = ( config.i18n && config.i18n.all_topics ) || '';
		} else {
			const item = dropdownItems.find( ( i ) => i.dataset.catSlug === state.category );
			label = item ? ( item.dataset.catName || item.textContent.trim() ) : '';
		}

		titleEl.textContent = config.title_template.replace( '{category}', label );
	}

	function updateEmpty() {
		if ( ! emptyEl ) return;
		emptyEl.hidden = grid.children.length > 0;
	}

	function updateLoadMore() {
		if ( ! loadMoreEl ) return;
		loadMoreEl.hidden = ! state.hasMore || state.loading;
		loadMoreEl.disabled = state.loading;
		const i18n = config.i18n || {};
		loadMoreEl.textContent = state.loading ? ( i18n.loading || 'Loading…' ) : ( i18n.load_more || 'Load more' );
	}

	function updateClearButton() {
		if ( ! clearEl ) return;
		clearEl.hidden = ! state.search && state.category === '';
	}

	/**
	 * Description de catégorie sous le titre Topics. Affichée uniquement quand
	 * une catégorie spécifique est active ET que son term WP a une description
	 * non vide. État "Tous" → cachée. Source = config.categories[].description
	 * hydraté depuis brio_get_blog_categories() côté serveur.
	 */
	function updateCategoryDescription() {
		if ( ! descriptionEl ) return;

		if ( state.category === '' ) {
			descriptionEl.textContent = '';
			descriptionEl.hidden = true;
			return;
		}

		const cats = Array.isArray( config.categories ) ? config.categories : [];
		const cat = cats.find( ( c ) => c.slug === state.category );
		const desc = cat && cat.description ? String( cat.description ).trim() : '';

		descriptionEl.textContent = desc;
		descriptionEl.hidden = desc === '';
	}

	function updateDropdownLabel() {
		if ( ! dropdownLabel ) return;
		if ( state.category === '' ) {
			dropdownLabel.textContent = defaultDropdownLabel;
			return;
		}
		const item = dropdownItems.find( ( i ) => i.dataset.catSlug === state.category );
		dropdownLabel.textContent = item ? ( item.dataset.catName || item.textContent.trim() ) : defaultDropdownLabel;
	}

	function updateDropdownItems() {
		dropdownItems.forEach( ( i ) => {
			const active = ( i.dataset.catSlug || '' ) === state.category;
			i.classList.toggle( 'is-active', active );
			const li = i.closest( '[role="option"]' );
			if ( li ) li.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
	}

	/* ---------- Data fetching ---------- */

	function buildUrl() {
		const url = new URL( restUrl, window.location.origin );

		/* Contraintes d'archive fixes — on mappe les clés PHP vers les params REST */
		const keyMap = { author_id: 'author_id', tag: 'tag', year: 'year', monthnum: 'month', day: 'day' };
		Object.entries( archiveConstraints ).forEach( ( [ k, v ] ) => {
			if ( k === 'category' || k === 'search' ) return; /* gérés séparément */
			const param = keyMap[ k ] || k;
			if ( v ) url.searchParams.set( param, v );
		} );

		/* Filtre catégorie UI — priorité sur la contrainte d'archive category */
		if ( state.category ) url.searchParams.set( 'category', state.category );
		else if ( archiveConstraints.category ) url.searchParams.set( 'category', archiveConstraints.category );

		if ( state.search ) url.searchParams.set( 'search', state.search );
		url.searchParams.set( 'offset', String( state.offset ) );
		url.searchParams.set( 'per_page', String( perPage ) );
		return url.toString();
	}

	async function fetchPosts( { append } ) {
		state.loading = true;
		updateLoadMore();

		try {
			const res = await fetch( buildUrl(), {
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
			} );
			if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );
			const data = await res.json();
			const posts = Array.isArray( data.posts ) ? data.posts : [];

			renderPosts( posts, { append } );
			state.offset += posts.length;
			/* Double garde : le serveur dit "has_more" ET le batch retourné
			   atteint perPage. Si on reçoit moins que perPage, c'est la fin. */
			state.hasMore = !! data.has_more && posts.length >= perPage;
		} catch ( e ) {
			console.error( '[Brio Blog] Fetch failed:', e );
			state.hasMore = false;
			if ( ! append ) grid.innerHTML = '';
		} finally {
			state.loading = false;
			updateEmpty();
			updateLoadMore();
		}
	}

	/* ---------- Transitions ---------- */

	function applyFilters() {
		state.offset = 0;
		state.hasMore = true;
		grid.setAttribute( 'aria-busy', 'true' );
		updateTitle();
		updateClearButton();
		updateDropdownLabel();
		updateDropdownItems();
		updateCategoryDescription();
		fetchPosts( { append: false } ).finally( () => grid.removeAttribute( 'aria-busy' ) );
	}

	/* ---------- Dropdown open/close ---------- */

	function openDropdown() {
		if ( ! dropdown ) return;
		dropdownMenu.hidden = false;
		dropdownTrig.setAttribute( 'aria-expanded', 'true' );
		dropdown.classList.add( 'is-open' );
	}

	function closeDropdown() {
		if ( ! dropdown ) return;
		dropdownMenu.hidden = true;
		dropdownTrig.setAttribute( 'aria-expanded', 'false' );
		dropdown.classList.remove( 'is-open' );
	}

	function toggleDropdown() {
		if ( ! dropdown ) return;
		dropdown.classList.contains( 'is-open' ) ? closeDropdown() : openDropdown();
	}

	if ( dropdownTrig ) {
		dropdownTrig.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();
			toggleDropdown();
		} );
	}

	dropdownItems.forEach( ( item ) => {
		item.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const slug = item.dataset.catSlug || '';
			closeDropdown();
			if ( slug === state.category ) return;
			state.category = slug;
			applyFilters();
		} );
	} );

	document.addEventListener( 'click', ( e ) => {
		if ( ! dropdown ) return;
		if ( ! dropdown.contains( e.target ) ) closeDropdown();
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && dropdown && dropdown.classList.contains( 'is-open' ) ) {
			closeDropdown();
			if ( dropdownTrig ) dropdownTrig.focus();
		}
	} );

	/* ---------- Search input / clear / submit ---------- */

	if ( searchForm ) {
		searchForm.addEventListener( 'submit', ( e ) => {
			e.preventDefault();
			// Force un fetch immédiat (court-circuite le debounce).
			const v = searchEl ? searchEl.value.trim() : '';
			if ( v !== state.search ) state.search = v;
			applyFilters();
		} );
	}

	if ( submitEl ) {
		submitEl.addEventListener( 'click', ( e ) => {
			// Le submit form ci-dessus gère déjà l'event ; ce listener sert
			// si le bouton est en dehors d'un <form>. On évite le double-fetch.
			if ( ! searchForm ) {
				e.preventDefault();
				const v = searchEl ? searchEl.value.trim() : '';
				if ( v !== state.search ) state.search = v;
				applyFilters();
			}
		} );
	}

	if ( searchEl ) {
		let debounce;
		searchEl.addEventListener( 'input', () => {
			clearTimeout( debounce );
			debounce = setTimeout( () => {
				const v = searchEl.value.trim();
				if ( v === state.search ) return;
				state.search = v;
				applyFilters();
			}, 250 );
		} );
	}

	if ( clearEl ) {
		clearEl.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			if ( searchEl ) searchEl.value = '';
			state.search = '';
			state.category = '';
			applyFilters();
			if ( searchEl ) searchEl.focus();
		} );
	}

	if ( loadMoreEl ) {
		loadMoreEl.addEventListener( 'click', () => {
			if ( state.loading || ! state.hasMore ) return;
			fetchPosts( { append: true } );
		} );
	}

	/* ---------- Initial sync ---------- */

	state.offset = parseInt( root.dataset.initialOffset, 10 ) || 0;

	/* hasMore initial = vrai uniquement si le total des articles publiés
	   excède ce qu'on a déjà rendu côté serveur. Sans ça, le bouton "Charger
	   plus" reste visible même quand il n'y a aucune page suivante. */
	const initialTotal = Number.isFinite( Number( config.topics_total ) )
		? Number( config.topics_total )
		: 0;

	/* Deux signaux qui prouvent qu'on est à la fin :
	   1. le serveur a annoncé moins d'articles que perPage → forcément fini.
	   2. l'offset rendu côté serveur égale le total publié → fini aussi.
	   Le bouton reste visible uniquement si AU MOINS un article supplémentaire
	   peut être chargé. */
	const initialCount = Array.isArray( config.topics ) ? config.topics.length : 0;
	state.hasMore = initialCount >= perPage && state.offset < initialTotal;

	updateLoadMore();
	updateEmpty();
	updateClearButton();
	updateDropdownLabel();
	updateCategoryDescription();
} )();
