# Brio Guiseppe — WordPress Theme

Thème WordPress sur mesure pour [brioguiseppe.fr](https://www.brioguiseppe.fr/) — conseil en hôtellerie. Thème classique autonome : aucun constructeur de page, aucun plugin métier requis. Tout ce qui concerne le site (contenu, formulaires, SEO, sécurité, emails, leads) est codé directement dans le thème.

## Ce que le thème fait seul

| Fonctionnalité | Plugin évité |
|---|---|
| Landing pages avec 10 sections éditables | Elementor / Divi |
| Champs personnalisés (meta boxes) | ACF |
| Import / export de pages via CSV | WP All Import |
| Formulaire de contact sécurisé | Contact Form 7 |
| Leads sauvegardés en base de données | Gravity Forms / CRM |
| Emails via API Resend (clé révocable) | WP Mail SMTP |
| Meta SEO, Open Graph, Twitter Card | Yoast / Rank Math |
| JSON-LD Schema (Organization, Article…) | Yoast Premium |
| Canonical + sitemap XML | Yoast / Google XML Sitemaps |
| En-têtes de sécurité HTTP | Plugin sécurité |
| Icônes SVG inline | Font Awesome plugin |
| Navigation mega-menu | Max Mega Menu |

**Plugins requis en production (infrastructure serveur uniquement) :**
- **UpdraftPlus** — sauvegardes automatiques vers stockage externe
- **LiteSpeed Cache / WP Rocket** — cache serveur (inutile si l'hébergeur le gère nativement : Kinsta, WP Engine, Cloudways)

---

## Stack

- **WordPress** — thème classique, sans dépendance au block editor
- **PHP 7.4+**
- HTML / CSS / JS vanilla — aucun framework front-end
- **Nebeco** (self-hosted) + **Manrope** (Google Fonts, chargement non-bloquant)
- Icônes SVG inline via `brio_icon()` — aucune requête externe

---

## Structure des fichiers

```
brio-guiseppe-theme/
├── assets/
│   ├── css/
│   │   ├── variables.css          # Tokens design (couleurs, typo, espacements)
│   │   ├── fonts.css              # @font-face Nebeco
│   │   ├── components/            # Composants réutilisables (bouton, nav, typo…)
│   │   └── sections/              # Styles par section (hero, pricing, landing…)
│   ├── fonts/                     # Nebeco .woff2
│   ├── images/                    # Assets statiques du thème
│   └── js/                        # Scripts vanilla
│
├── includes/
│   ├── theme-data.php             # Source unique : données entreprise, footer, assets
│   ├── setup.php                  # add_theme_support, menus
│   ├── cleanup.php                # Suppression des émissions WP inutiles
│   ├── security-headers.php       # En-têtes HTTP (CSP, HSTS, X-Frame-Options…)
│   ├── icons.php                  # Registre SVG + helper brio_icon()
│   ├── custom-nav-walker.php      # Walker mega-menu
│   ├── widgets.php                # Sidebar widget area
│   │
│   ├── admin/
│   │   ├── meta-boxes-helpers.php # Primitives de rendu et de sauvegarde des champs
│   │   ├── meta-box-seo.php       # Champs SEO (meta title, description) par page
│   │   ├── meta-boxes-landing.php # 10 sections landing (tabs admin)
│   │   ├── meta-boxes-legal.php   # Pages légales
│   │   ├── meta-boxes-outils.php  # Pages outils
│   │   ├── meta-boxes-blog.php    # Articles de blog
│   │   ├── leads.php              # CPT brio_lead + colonnes admin
│   │   └── landing-csv-import.php # Import / export CSV des landing pages
│   │
│   └── front/
│       ├── enqueue.php            # Chargement CSS / JS
│       ├── seo.php                # Meta tags, OG, Twitter Card, JSON-LD, canonical
│       ├── sitemap.php            # Sitemap XML natif
│       ├── mailer.php             # Envoi email via API Resend
│       ├── landing-form.php       # Traitement formulaire (nonce, honeypot, regex…)
│       ├── data-landing.php       # Fournisseurs de données — Landing
│       ├── data-legal.php         # Fournisseurs de données — Légal
│       ├── data-outils.php        # Fournisseurs de données — Outils
│       ├── data-blog.php          # Fournisseurs de données — Blog
│       ├── rest-blog.php          # Endpoint REST articles
│       └── post-thumbnail.php     # Tailles d'images personnalisées
│
├── template-parts/
│   ├── home/                      # Sections page d'accueil
│   └── landing/                   # Sections landing pages
│
├── template-landing.php           # Template — Landing Page
├── header.php
├── footer.php
├── functions.php
└── style.css
```

---

## Architecture

### Source unique pour le contenu

Toutes les données partagées (nom, téléphones, adresse, réseaux sociaux, colonnes footer) sont centralisées dans [`includes/theme-data.php`](includes/theme-data.php) derrière des accesseurs filtrables :

```php
$company = brio_get_company_data();   // nom, téléphones, email, réseaux…
$columns = brio_get_footer_columns(); // liens footer
$legal   = brio_get_legal_data();     // ICE, SIRET, pages légales
$assets  = brio_get_assets();         // URLs des images du thème
```

Chaque fonction passe par `apply_filters()` — un thème enfant ou un plugin peut surcharger sans toucher aux templates.

### Landing pages — pattern de données

Chaque landing page lit ses propres meta en priorité, puis retombe sur les données de la homepage si un champ est vide. Cela permet de créer une nouvelle page immédiatement sans remplir tous les champs.

```php
// data-landing.php — exemple pour le hero
function brio_get_landing_hero_data( $post_id = 0 ) {
    $post_id = $post_id ?: get_queried_object_id();
    return [
        'title'    => brio_lmeta( $post_id, 'hero', 'title',    $home['title'] ),
        'subtitle' => brio_lmeta( $post_id, 'hero', 'subtitle', $home['subtitle'] ),
    ];
}
```

Les sections de landing qui ont leur propre template dans `template-parts/landing/` utilisent un filtre d'injection pour surcharger les données homepage sans dupliquer le HTML :

```php
add_filter( 'brio_hero_data', function() use ( $data ) { return $data; } );
```

### Sécurité du formulaire

Le formulaire de contact landing applique 6 couches de validation :
1. Nonce WordPress
2. Honeypot anti-bot
3. Rate limiting — 1 soumission / IP / 60 s (transient WP, ignoré pour les admins)
4. Regex sur chaque champ (Unicode, longueurs min/max)
5. Blocage des domaines email jetables
6. Détection de patterns spam dans le message

### Emails — API Resend

`mailer.php` intercepte `wp_mail()` via le filtre `pre_wp_mail` et délègue à l'API Resend. Si la clé n'est pas définie ou si l'API est indisponible, WordPress reprend son envoi natif (fallback transparent).

Configurer dans `wp-config.php` :
```php
define( 'BRIO_RESEND_API_KEY', 're_xxxxxxxxxxxx' );
define( 'BRIO_RESEND_FROM',    'Brio Guiseppe <noreply@tondomaine.com>' );
```

### Convention de clés meta

```
_brio_{template}_{section}_{field}
```

Exemples :
- `_brio_landing_hero_title`
- `_brio_landing_pricing_plan1_price`
- `_brio_seo_description`

---

## Développement local

Le thème tourne dans [Local by Flywheel](https://localwp.com/). Aucune étape de build requise — les CSS/JS sont chargés directement via `wp_enqueue_*`.

Activer le cache-busting en développement dans [`functions.php`](functions.php) :
```php
define( 'BRIO_DEV_MODE', true );
```

---

## Installation

1. Cloner dans `wp-content/themes/` :
   ```bash
   git clone https://github.com/guiseppe130695/brio-guiseppe-theme.git
   ```
2. Activer le thème dans **Apparence → Thèmes**.
3. Configurer le menu principal dans **Apparence → Menus** (emplacement : *Primary*).
4. Ajouter les constantes SMTP dans `wp-config.php` (voir section Emails ci-dessus).

---

## Conventions

- Ne jamais hardcoder téléphones, adresse ou liens dans les templates — utiliser `brio_get_company_data()`.
- Les tokens design (couleurs, typo, espacements) sont dans [`assets/css/variables.css`](assets/css/variables.css) — toujours utiliser les variables CSS plutôt que des valeurs littérales.
- Deux polices maximum : **Nebeco** (titres) et **Manrope** (texte courant). Ne pas en ajouter d'autres.
- Les clés meta suivent la convention `_brio_{template}_{section}_{field}`.

---

## Licence

Propriétaire — © Brio Guiseppe.
