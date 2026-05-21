# Brio Guiseppe — WordPress Theme

Custom classical WordPress theme for [brioguiseppe.fr](https://www.brioguiseppe.fr/) — a hospitality-focused web agency. Minimal DOM, pixel-perfect to the Elementor design reference, no page builder dependency.

## Stack

- **WordPress** (classical theme, no block editor reliance)
- **PHP 7.4+**
- Pure HTML/CSS/JS — no framework on the front-end
- Manrope (Google Fonts) + Nebeco (self-hosted)
- Font Awesome 6 (CDN)

## Directory layout

```
brio-guiseppe-theme/
├── assets/
│   ├── css/
│   │   ├── components/      # Reusable UI components (button, phone, nav, typography)
│   │   ├── variables.css    # Design tokens (colors, typography, spacing)
│   │   ├── fonts.css        # @font-face declarations
│   │   ├── header.css       # Header layout
│   │   ├── header-responsive.css
│   │   └── footer.css       # Footer layout (incl. responsive)
│   ├── fonts/               # Self-hosted Nebeco font files
│   └── js/
├── includes/
│   ├── theme-data.php       # Centralized config (company data, columns, legal)
│   ├── setup.php            # add_theme_support, menu registration
│   ├── widgets.php
│   ├── custom-nav-walker.php
│   └── front/enqueue.php
├── partials/
├── header.php
├── footer.php
├── functions.php
└── style.css
```

## Architecture notes

### Single source of truth for content

Repeated content (phone numbers, address, footer columns, legal IDs) lives in [`includes/theme-data.php`](includes/theme-data.php) behind filterable accessor functions:

```php
$company = brio_get_company_data();   // name, phones, email, social, etc.
$columns = brio_get_footer_columns(); // Explorer + Services link lists
$legal   = brio_get_legal_data();     // ICE, fiscal ID, policy pages
$assets  = brio_get_assets();         // Image URLs
```

Each is filterable (`brio_company_data`, `brio_footer_columns`, etc.) so a child theme or plugin can override without forking templates.

### CSS organization

Each stylesheet starts with a table of contents and is split into numbered sections (Container → Components → Responsive). Property order is consistent: positioning → box model → typography → visual → transitions. References to the source Elementor JSON are kept inline as traceability comments.

## Local development

This theme runs inside a [Local](https://localwp.com/) site. The working directory is the WordPress webroot. Standard WP loading order applies — no build step required for CSS/JS (files are loaded directly by `wp_enqueue_*`).

Toggle `JU_DEV_MODE` in [`functions.php`](functions.php) to append cache-busting timestamps during development.

## Installation

1. Clone this repo into `wp-content/themes/`:
   ```
   git clone https://github.com/guiseppe130695/brio-guiseppe-theme.git
   ```
2. Activate the theme from **Appearance → Themes** in the WP admin.
3. Configure the primary menu under **Appearance → Menus** (location: *Primary*).

## Contributing

- Edit content shared between header and footer (phones, address, social links) in [`includes/theme-data.php`](includes/theme-data.php) — never hardcode it in templates.
- Design tokens (colors, fonts, spacing) live in [`assets/css/variables.css`](assets/css/variables.css). Reach for CSS variables instead of literal values when extending styles.
- Keep the CSS table-of-contents updated when adding new sections.

## License

Proprietary — © Brio Guiseppe.
