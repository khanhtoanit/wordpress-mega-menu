# Kato Salient Mega Menu

A WordPress plugin that adds a custom **3-column mega menu** to sites built with the [Salient theme](https://themenectar.com/salient/) (Nectar). The panel replaces the default Salient dropdown for any top-level nav item you opt in, turning it into an interactive, image-rich flyout with three distinct columns.

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0 |
| PHP | 7.4 |
| Salient / NectarThemes | any version that ships `Nectar_Arrow_Walker_Nav_Menu` |

> **Note:** The plugin degrades gracefully if the Salient walker class is not present — it falls back to the core `Walker_Nav_Menu`.

---

## Installation

1. Copy the plugin folder into `wp-content/plugins/`.
2. Activate **Kato Salient Mega Menu** from **Plugins > Installed Plugins**.
3. No settings page is required. All configuration happens inside **Appearance > Menus**.

---

## How the Mega Menu Works

When activated on a top-level menu item, the mega panel renders as a **3-column grid**:

```
┌──────────────────────┬──────────────────┬──────────────────┐
│  Col 1 – Preview     │  Col 2 – Level 2 │  Col 3 – Level 3 │
│  (image + text CTA)  │  (nav list)      │  (sub-nav list)  │
└──────────────────────┴──────────────────┴──────────────────┘
```

| Column | Content |
|---|---|
| **Preview** | Featured image, title, description, and CTA link — updates dynamically as the user hovers over a level-2 item. |
| **Level 2** | The direct children of the top-level item listed as clickable links. The first item is active by default on panel open. |
| **Level 3** | The children of whichever level-2 item is currently active. Updated client-side from pre-rendered `<template>` tags (no extra HTTP requests). |

The panel is only shown on **desktop** (`> 999 px`). On mobile it is hidden via CSS and the standard Salient/Nectar mobile menu takes over.

---

## Enabling the Mega Menu on a Menu Item

Two conditions must both be true for a top-level item to render as the Kato mega panel:

1. **Salient mega menu** must be enabled on the item via the Nectar meta box (`nectar_menu_options → enable_mega_menu = on`).
2. **Kato SRS mega menu** checkbox must be ticked in the Kato meta box (see below).

Both flags must be set, giving you full control over which items use the plugin versus the default Salient behaviour.

### Supported menu locations

The plugin activates only for items registered in these theme locations:

- `top_nav`
- `top_nav_pull_left`
- `top_nav_pull_right`

Items in any other menu location are unaffected.

---

## Admin Fields (Appearance > Menus)

Each menu item exposes a **"Kato 3-column mega menu"** field group in the menu editor.

### Top-level items (depth 0)

| Field | Description |
|---|---|
| **Enable Kato SRS mega menu** | Checkbox. Opt this item into the Kato mega panel. Must also have Salient mega menu enabled. |

### Level-2 items (depth 1)

These fields are all **optional overrides**. If left empty, the plugin falls back to the menu item's title, the WordPress nav menu description field, the linked post's excerpt/content, and the linked post's featured image — in that order.

| Field | Meta key | Fallback |
|---|---|---|
| **Preview title override** | `_kato_mega_preview_title` | Menu item title |
| **Preview description override** | `_kato_mega_preview_desc` | Nav menu description → post excerpt → post content (first 24 words) → taxonomy term description |
| **CTA text override** | `_kato_mega_preview_cta` | `"Read more"` |
| **Preview image override** | `_kato_mega_preview_image_id` (attachment ID) | Post featured image |

Use the **Select image** / **Remove image** buttons to pick images from the WordPress Media Library.

---

## Customization

### CSS Custom Properties & Class Reference

All plugin styles are scoped to `.kato-mega-menu`. Override them in your child theme or custom CSS file.

| Selector | Purpose |
|---|---|
| `.kato-mega-menu__panel` | The dropdown panel itself. Controls width (`min(1180px, calc(100vw - 48px))`), border-radius, shadow. |
| `.kato-mega-menu__col--preview` | Left preview column background (`#fafafa`). |
| `.kato-mega-menu__col--level2` | Middle column padding and left border. |
| `.kato-mega-menu__col--level3` | Right column padding and left border. |
| `.kato-mega-menu__heading--preview` | Preview title. Default `font-size: 26px`. |
| `.kato-mega-menu__heading--level1` | Section heading above level-2 list. |
| `.kato-mega-menu__heading--level2` | Section heading above level-3 list. |
| `.kato-mega-menu__preview-desc` | Preview description text. Default `font-size: 14px; color: #5c5c5c`. |
| `.kato-mega-menu__preview-cta` | CTA link label and arrow. |
| `.kato-mega-menu__level2-item.is-active > a` | Active level-2 item highlight. |

**Example — change panel width and border-radius:**

```css
.kato-mega-menu > .kato-mega-menu__panel {
  width: min(1400px, calc(100vw - 32px));
  border-radius: 8px;
}
```

**Example — change the preview column background colour:**

```css
.kato-mega-menu__col--preview {
  background: #f0f4ff;
}
```

### Panel Alignment

Controlled via Nectar's built-in **Mega Menu Alignment** option on the menu item. The plugin reads the `mega_menu_alignment` value from `nectar_menu_options` and appends one of these CSS classes to the `<li>`:

| Nectar value | CSS class | Behaviour |
|---|---|---|
| *(default)* | — | Panel aligns to the left edge of the `<li>`. |
| `center` | `align-center` | Panel is horizontally centred under the `<li>`. |
| `right` | `align-right` | Panel aligns to the right edge of the `<li>`. |

### Panel Width

Set via Nectar's **Mega Menu Width** option. The plugin appends `width-{value}` to the `<li>`. You can use this to target specific items:

```css
/* Make a specific item's panel full-width */
.kato-mega-menu.width-full > .kato-mega-menu__panel {
  width: 100vw;
  left: 0;
  border-radius: 0;
}
```

### Grid Columns

The 3-column grid is defined in CSS. Adjust the ratio of each column:

```css
/* Equal-width columns */
.kato-mega-menu:hover > .kato-mega-menu__panel,
.kato-mega-menu.sfHover > .kato-mega-menu__panel,
.kato-mega-menu:focus-within > .kato-mega-menu__panel {
  grid-template-columns: 1fr 1fr 1fr;
}
```

### Changing the Default CTA Text

The default "Read more" string is translatable. Add it to your theme's `functions.php` or a translation file:

```php
// In a custom plugin or functions.php
add_filter( 'gettext', function( $translation, $text, $domain ) {
  if ( 'kato-salient-mega-menu' === $domain && 'Read more' === $text ) {
    return 'Learn more';
  }
  return $translation;
}, 10, 3 );
```

### Extending Preview Data via PHP

The `get_menu_preview_data()` method on `Kato_Salient_Mega_Menu_Walker_Trait` returns an array:

```php
[
  'title'     => string,
  'desc'      => string,  // trimmed to 24 words
  'cta'       => string,
  'image_url' => string,
  'link'      => string,
]
```

Because the walker is instantiated via a dynamic `eval`-created class that extends Nectar's walker, the cleanest extension point is to create a child class of your own that uses the trait:

```php
// In your child theme or a custom plugin

trait My_Extended_Preview_Trait {
  protected function get_menu_preview_data( WP_Post $menu_item ) {
    $data = parent::get_menu_preview_data( $menu_item );
    // Add or override any key here
    $data['cta'] = 'Explore →';
    return $data;
  }
}

// Register the extended walker
add_filter( 'wp_nav_menu_args', function( $args ) {
  $supported = [ 'top_nav', 'top_nav_pull_left', 'top_nav_pull_right' ];
  if ( in_array( $args['theme_location'] ?? '', $supported, true ) && ! wp_is_mobile() ) {
    if ( ! class_exists( 'My_Mega_Menu_Walker' ) ) {
      // Resolve the same base class the plugin uses
      $base = class_exists( 'Nectar_Arrow_Walker_Nav_Menu' )
        ? 'Nectar_Arrow_Walker_Nav_Menu'
        : 'Walker_Nav_Menu';
      eval( 'class My_Mega_Menu_Walker extends ' . $base . ' { use Kato_Salient_Mega_Menu_Walker_Trait, My_Extended_Preview_Trait; }' );
    }
    $args['walker'] = new My_Mega_Menu_Walker();
  }
  return $args;
}, 30 ); // priority 30 runs after the plugin's 20
```

---

## JavaScript Behaviour

`assets/kato-salient-mega-menu.js` is a small, dependency-free IIFE that runs on `DOMContentLoaded`.

For each `.kato-mega-menu` element it:

1. Reads `data-preview-*` attributes from each level-2 `<li>` to update the preview column on `mouseenter` / `focusin`.
2. Swaps the level-3 column content by cloning the matching `<template data-kato-key>` element — all level-3 HTML is pre-rendered server-side so there are no AJAX calls.
3. Marks the newly activated item with the `is-active` class and removes it from all others.

The first level-2 item is activated immediately on init, so the panel always shows meaningful content when it first opens.

---

## File Structure

```
kato-salient-mega-menu/
├── kato-salient-mega-menu.php          # Plugin bootstrap, constants, loader
├── includes/
│   └── class-kato-salient-mega-menu.php  # Main class + Walker trait
└── assets/
    ├── kato-salient-mega-menu.css      # All plugin styles
    └── kato-salient-mega-menu.js       # Interactive panel behaviour
```

---

## Meta Keys Reference

| Constant | Meta key | Used on |
|---|---|---|
| `META_ENABLE` | `_kato_enable_srs_mega_menu` | Level-1 item |
| `META_PREVIEW_ID` | `_kato_mega_preview_image_id` | Level-2 item |
| `META_PREVIEW_T` | `_kato_mega_preview_title` | Level-2 item |
| `META_PREVIEW_D` | `_kato_mega_preview_desc` | Level-2 item |
| `META_PREVIEW_C` | `_kato_mega_preview_cta_text` | Level-2 item |

---

## Changelog

### 1.0.0
- Initial release: 3-column mega menu panel with dynamic preview, level-2/level-3 navigation, admin fields, and media library integration.
