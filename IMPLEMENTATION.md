# 📋 Implementation Guide: Homepage Sections

**Document:** Implementation guidelines for the 9 remaining homepage sections  
**Theme:** Brio Guiseppe Theme Custom v1.0  
**Status:** 🟡 In Progress (1/10 sections complete: Hero)  
**Last Updated:** 2026-05-21

---

## 🎯 Overview

This document guides the implementation of the remaining 9 homepage sections:

| # | Section | Status | Data Function | Template | CSS |
|---|---------|--------|----------------|----------|-----|
| 1 | Hero | ✅ DONE | `brio_get_hero_data()` | [hero.php](template-parts/home/hero.php) | ✅ |
| 2 | About | ⏳ TODO | `brio_get_about_data()` | [about.php](template-parts/home/about.php) | ⏳ |
| 3 | Partners | ⏳ TODO | `brio_get_partners_data()` | [partners.php](template-parts/home/partners.php) | ⏳ |
| 4 | Programs | ⏳ TODO | `brio_get_programs_data()` | [programs.php](template-parts/home/programs.php) | ⏳ |
| 5 | Philosophy | ⏳ TODO | `brio_get_philosophy_data()` | [philosophy.php](template-parts/home/philosophy.php) | ⏳ |
| 6 | Fun Facts | ⏳ TODO | `brio_get_fun_facts_data()` | [fun-facts.php](template-parts/home/fun-facts.php) | ⏳ |
| 7 | Pricing | ⏳ TODO | `brio_get_pricing_data()` | [pricing.php](template-parts/home/pricing.php) | ⏳ |
| 8 | FAQs | ⏳ TODO | `brio_get_faqs_data()` | [faqs.php](template-parts/home/faqs.php) | ⏳ |
| 9 | Blog | ⏳ TODO | `brio_get_blog_data()` | [blog.php](template-parts/home/blog.php) | ⏳ |
| 10 | CTA Final | ⏳ TODO | `brio_get_cta_data()` | [cta.php](template-parts/home/cta.php) | ⏳ |

---

## 🏗️ Theme Architecture (Review)

### Single Source of Truth

All **repeated content** (company data, footer columns, hero content, etc.) lives in [`includes/theme-data.php`](includes/theme-data.php) behind filterable accessor functions.

**Pattern:**
```php
function brio_get_section_data() {
    $data = [
        'field_1' => 'Value 1',
        'field_2' => 'Value 2',
        // ...
    ];
    
    return apply_filters( 'brio_section_data', $data );
}
```

**Usage in templates:**
```php
<?php $data = brio_get_about_data(); ?>
<h2><?php echo esc_html( $data['title'] ); ?></h2>
<p><?php echo wp_kses_post( $data['description'] ); ?></p>
```

### CSS Organization

**File:** [`assets/css/home.css`](assets/css/home.css)

**Structure:**
```css
/* ========================================
   Table of Contents
   ======================================== 
   
   1. About Section
   2. Partners Section
   3. Programs Section
   ... (add as you implement)

======================================== */

/* ========================================
   1. About Section
   ======================================== */

.about-section {
    /* Positioning */
    position: relative;
    
    /* Box model */
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    
    /* Typography */
    font-size: var(--font-size-base);
    
    /* Visual */
    background-color: var(--color-white);
    
    /* Transitions */
    transition: all 0.3s ease;
}

/* Mobile-first: default (375px+) */
@media (min-width: 768px) {
    /* Tablet adjustments */
}

@media (min-width: 1024px) {
    /* Desktop adjustments */
}
```

### Design Tokens (CSS Variables)

**File:** [`assets/css/variables.css`](assets/css/variables.css)

**Use existing variables** instead of hardcoding values:

```css
/* Colors */
--color-primary
--color-secondary
--color-dark
--color-light
--color-white
--color-gray-*

/* Typography */
--font-family-primary
--font-size-sm, --font-size-base, --font-size-lg, --font-size-xl
--font-weight-regular, --font-weight-bold
--line-height-tight, --line-height-normal, --line-height-relaxed

/* Spacing */
--spacing-xs, --spacing-sm, --spacing-md, --spacing-lg, --spacing-xl, --spacing-2xl

/* Responsive Breakpoints (in your CSS) */
375px   /* Mobile */
768px   /* Tablet */
1024px  /* Desktop */
1440px  /* Wide desktop */
```

**Extend variables.css if needed** — add new colors/spacing for your sections, but **prefer reusing existing tokens**.

### Reusable Components

**File:** [`assets/css/components/`](assets/css/components/)

Available component styles to reuse:
- `button.css` — `.button`, `.button--primary`, `.button--secondary`
- `phone.css` — `.phone-number`
- `nav.css` — Navigation styles
- `typography.css` — Text styles

**Example:**
```html
<button class="button button--primary">Réserver mon audit</button>
<a href="tel:+33616975844" class="phone-number">+33 6 16 97 58 44</a>
```

### Responsive Design Approach

**Mobile-first:**
1. Write CSS for **375px (mobile) first**
2. Add tablet adjustments at **768px**
3. Add desktop enhancements at **1024px+**

**Example:**
```css
.card {
    /* Mobile: 100% width, single column */
    width: 100%;
    margin-bottom: var(--spacing-lg);
}

@media (min-width: 768px) {
    /* Tablet: 2 columns */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-md);
    }
}

@media (min-width: 1024px) {
    /* Desktop: 3 columns */
    .card-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-lg);
    }
}
```

---

## 📝 Implementation Steps (For Each Section)

### Step 1: Add Data Function to `includes/theme-data.php`

```php
/**
 * Get [Section Name] section content.
 *
 * @since 1.0.0
 *
 * @return array {
 *     [Section] content.
 *     @type ... field descriptions
 * }
 */
function brio_get_[section]_data() {
    $data = [
        // Define your data structure here
        // Keep it simple: strings, arrays, no HTML
        // Sanitization happens in templates
    ];
    
    return apply_filters( 'brio_[section]_data', $data );
}
```

**Rules:**
- ✅ Keep data **simple** (strings, arrays, booleans)
- ✅ **No HTML** in data functions — sanitize in templates
- ✅ Use `__()` for translatable strings: `__( 'Text', 'brio-guiseppe' )`
- ✅ Make it **filterable** via `apply_filters()`
- ✅ Add **docblock** with return type structure

### Step 2: Create Template Part at `template-parts/home/[section].php`

```php
<?php
/**
 * [Section Name] Section
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_[section]_data();
?>

<section class="[section]-section" id="[section]">
    <div class="container">
        <!-- Markup here -->
        <h2><?php echo esc_html( $data['title'] ); ?></h2>
        <!-- etc. -->
    </div>
</section>
```

**Rules:**
- ✅ Always check `defined( 'ABSPATH' )` for security
- ✅ Use `esc_html()`, `esc_attr()`, `wp_kses_post()` for output
- ✅ Use `<section>` for top-level container
- ✅ Wrap content in `.container` div (defines max-width)
- ✅ Use semantic HTML5: `<article>`, `<header>`, `<footer>`, etc.
- ✅ Add ID to section for anchor links

### Step 3: Add CSS to `assets/css/home.css`

```css
/* ========================================
   [#]. [Section Name]
   ======================================== */

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.[section]-section {
    padding: var(--spacing-xl) 0;
    background-color: var(--color-white);
}

.[section]-section h2 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    margin-bottom: var(--spacing-lg);
}

/* Responsive: Tablet */
@media (min-width: 768px) {
    .[section]-section {
        padding: var(--spacing-2xl) 0;
    }
}

/* Responsive: Desktop */
@media (min-width: 1024px) {
    .[section]-section {
        padding: var(--spacing-2xl) var(--spacing-lg);
    }
}
```

**Rules:**
- ✅ Add **table of contents entry** at the top of `home.css`
- ✅ Use `.` prefix classes for specificity
- ✅ **Mobile-first** — base styles for 375px, then add breakpoints
- ✅ Use **CSS variables** for colors, spacing, fonts
- ✅ Property order: positioning → box model → typography → visual → transitions
- ✅ Comment breakpoint purposes (Mobile, Tablet, Desktop)

### Step 4: Test Template Rendering

1. Open browser to homepage: `http://localhost/`
2. Open DevTools (F12) → Console
3. Check for:
   - ✅ No console errors
   - ✅ Section renders in correct position (check front-page.php order)
   - ✅ All data displays correctly
   - ✅ No broken images/links

### Step 5: Test Responsive Design

1. **375px (Mobile)** — DevTools phone emulator or real device
   - Text readable, buttons tappable (48px min)
   - Images fit, no overflow
   - Single column layout

2. **768px (Tablet)** — DevTools tablet mode
   - 2-column grids work
   - Spacing appropriate
   - Images scaled well

3. **1440px (Desktop)** — Full browser window
   - Full layout visible
   - 3-4 column grids (if applicable)
   - Proper spacing, not cramped

### Step 6: Commit

```bash
git add includes/theme-data.php template-parts/home/[section].php assets/css/home.css
git commit -m "feat(home): implement [section] section with mobile-first responsive design"
```

**Commit message format:** `feat(home): implement [section] section`

---

## 📚 Section-by-Section Details

### 2. About Section

**What:** Personal pitch from the agency founder  
**Content:** Title, description, avatar image, CTA button

**Data structure:**
```php
[
    'title'       => 'Je ne crée pas de sites web...',
    'description' => '...je crée des machines à vendre des nuits.',
    'avatar'      => $base . 'hero/avatar-1.jpg',  // or separate folder
    'cta_text'    => 'Découvrir mon approche',
    'cta_href'    => '#philosophy',
]
```

**Layout:**
- Desktop: Image left, text right
- Tablet: Stack vertical, image above text
- Mobile: Same as tablet, full-width

**CSS:** Flexbox for desktop/tablet, block for mobile

---

### 3. Partners Section

**What:** Logo grid of tools/platforms used  
**Content:** List of partner logos

**Data structure:**
```php
[
    'title' => 'Nos partenaires & technologies',
    'partners' => [
        [ 'name' => 'Partner Name', 'logo' => URL, 'href' => URL ],
        // ...
    ]
]
```

**Layout:**
- Desktop: 4 columns (grid)
- Tablet: 2 columns
- Mobile: 1 column

---

### 4. Programs Section

**What:** Accordion with 3 service offerings  
**Content:** Title, 3 programs with expand/collapse

**Data structure:**
```php
[
    'title' => 'Solutions concrètes',
    'programs' => [
        [ 'title' => 'Program 1', 'description' => 'Details...' ],
        [ 'title' => 'Program 2', 'description' => 'Details...' ],
        [ 'title' => 'Program 3', 'description' => 'Details...' ],
    ]
]
```

**Layout:** 
- Accordion (toggle details on click)
- Full-width responsive
- Use `<details>/<summary>` tags (native HTML, no JS needed)

---

### 5. Philosophy Section

**What:** 3-pillar approach (technical, human, results-focused)  
**Content:** 3 cards with icons, title, description

**Data structure:**
```php
[
    'title' => 'Notre approche',
    'pillars' => [
        [ 'title' => 'Technique', 'description' => '...', 'icon' => '⚙️' ],
        [ 'title' => 'Humaine', 'description' => '...', 'icon' => '❤️' ],
        [ 'title' => 'Résultats', 'description' => '...', 'icon' => '📈' ],
    ]
]
```

**Layout:**
- Desktop: 3 columns (grid)
- Tablet: 2 columns, 1 wrap
- Mobile: 1 column (stack)

---

### 6. Fun Facts Section

**What:** 4 key statistics/metrics  
**Content:** Number, label, optional color badge

**Data structure:**
```php
[
    'title' => 'Nos résultats',
    'facts' => [
        [ 'value' => '+62 000 €', 'label' => 'Commissions récupérées' ],
        [ 'value' => '−30 %', 'label' => 'Temps de chargement' ],
        [ 'value' => '90 jours', 'label' => 'Délai d\'implémentation' ],
        [ 'value' => '+45 %', 'label' => 'Augmentation de réservations' ],
    ]
]
```

**Layout:**
- Desktop: 4 columns (2×2 grid)
- Tablet: 2 columns
- Mobile: 1 column

---

### 7. Pricing Section

**What:** 3 pricing tiers (Riad, Boutique, Independent Hotel)  
**Content:** Price cards with features, highlighted tier

**Data structure:**
```php
[
    'title' => 'Offres',
    'tiers' => [
        [
            'name' => 'Riad',
            'price' => '2 999 €',
            'highlighted' => false,
            'features' => [ 'Feature 1', 'Feature 2', ... ],
            'cta_text' => 'Réserver consultation',
        ],
        // ... (boutique, hotel)
    ]
]
```

**Layout:**
- Desktop: 3 columns, highlighted tier scaled larger
- Tablet: Stack with 2 cards side-by-side if space
- Mobile: 1 column, full-width cards

---

### 8. FAQs Section

**What:** Q&A accordion  
**Content:** List of questions + answers

**Data structure:**
```php
[
    'title' => 'Questions fréquentes',
    'faqs' => [
        [
            'question' => 'How does it work?',
            'answer' => 'We build websites that...'
        ],
        // ...
    ]
]
```

**Layout:**
- Accordion (expand/collapse)
- Full-width
- Use `<details>/<summary>` or custom JS toggle

---

### 9. Blog Section

**What:** Recent blog posts grid  
**Content:** 3 latest articles with thumbnail, title, excerpt, date

**Data options:**

**Option A: Dynamic (from WordPress posts)**
```php
function brio_get_blog_data() {
    $args = [ 'posts_per_page' => 3, 'post_type' => 'post' ];
    $posts = get_posts( $args );
    
    $data = [];
    foreach ( $posts as $post ) {
        $data[] = [
            'title'       => $post->post_title,
            'excerpt'     => get_the_excerpt( $post->ID ),
            'date'        => get_the_date( 'j M Y', $post->ID ),
            'thumbnail'   => get_the_post_thumbnail_url( $post->ID ),
            'link'        => get_permalink( $post->ID ),
        ];
    }
    
    return [ 'posts' => $data ];
}
```

**Option B: Static (hardcoded)**
```php
[
    'title' => 'Derniers articles',
    'posts' => [
        [ 'title' => '...', 'excerpt' => '...', 'date' => '21 mai 2026', 'link' => '#' ],
        // ...
    ]
]
```

**Layout:**
- Desktop: 3 columns (card grid)
- Tablet: 2 columns
- Mobile: 1 column

---

### 10. CTA Final Section

**What:** Final call-to-action banner  
**Content:** Headline, subtext, button

**Data structure:**
```php
[
    'headline'    => 'Vous versez plus de 60 000 €/an aux OTA ?',
    'subtext'     => 'Je peux vous aider à récupérer cet argent.',
    'button_text' => 'Réserver mon audit gratuit',
    'button_href' => '#audit',
    'background'  => $base . 'newsletter/background.webp',
]
```

**Layout:**
- Full-width banner
- Centered text on background image
- Button below on mobile, beside on desktop

---

## 🧪 Testing Checklist (Per Section)

- [ ] Data function returns correct data
- [ ] Template renders without errors (no 404 console warnings)
- [ ] All data displays correctly (no missing fields)
- [ ] Images load (browser Network tab, no 404s)
- [ ] Links work (click and navigate)
- [ ] Mobile responsive: 375px readable, single column
- [ ] Tablet responsive: 768px, 2-column where applicable
- [ ] Desktop responsive: 1024px+, full layout
- [ ] No CSS conflicts with other sections
- [ ] Accessibility: text readable, buttons tappable (48px+)

---

## 🚀 Git Workflow

**For each section:**

```bash
# 1. Create feature branch (optional)
git checkout -b feat/about-section

# 2. Implement section (data + template + CSS)
# ... edit includes/theme-data.php, template-parts/home/[section].php, assets/css/home.css

# 3. Test in browser

# 4. Stage changes
git add includes/theme-data.php template-parts/home/[section].php assets/css/home.css

# 5. Commit
git commit -m "feat(home): implement [section] section with mobile-first responsive design"

# 6. Merge (if using branches)
git checkout main
git merge feat/about-section
```

**Commit message format:**
```
feat(home): implement [section] section with mobile-first responsive design

- Added brio_get_[section]_data() function to theme-data.php
- Created [section].php template part with semantic HTML
- Added responsive CSS (375px mobile, 768px tablet, 1024px desktop)
- Tested on multiple breakpoints, no accessibility issues
```

---

## 📞 When Stuck

### Common Issues

**Problem:** Data not displaying  
**Solution:** Check `esc_html()`, `wp_kses_post()` functions; verify array key names

**Problem:** CSS not applying  
**Solution:** Check `.container` max-width; verify CSS cascading (no conflicts)

**Problem:** Responsive not working  
**Solution:** Check media query breakpoints (375px, 768px, 1024px); use DevTools device emulation

**Problem:** Images not loading  
**Solution:** Check image path in theme-data.php; verify `$base = get_theme_file_uri()` is correct

### Reference Files

- [hero.php](template-parts/home/hero.php) — Example implementation ✅
- [theme-data.php](includes/theme-data.php) — Data function patterns
- [assets/css/home.css](assets/css/home.css) — CSS patterns
- [README.md](README.md) — Theme overview

---

## ✅ Completion Criteria

A section is **DONE** when:

- [x] Data function added to `includes/theme-data.php`
- [x] Template part created at `template-parts/home/[section].php`
- [x] CSS added to `assets/css/home.css` (with TOC entry)
- [x] Mobile-responsive (375px, 768px, 1024px tested)
- [x] No console errors
- [x] All images load, links work
- [x] Accessibility baseline (readable, tappable)
- [x] Git commit with semantic message
- [x] Table of contents in home.css updated

---

**Happy implementing! 🚀**

Questions? Check the [README.md](README.md) or the Hero section implementation for reference patterns.
