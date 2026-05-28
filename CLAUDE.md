# SolidShop — WooCommerce Theme

Custom WooCommerce store theme built on **Sage 11** (Roots ecosystem), **Acorn v6**, and **Tailwind CSS v4**.

## Stack

- **WordPress** + **WooCommerce** (Local by Flywheel)
- **Sage 11** — Blade templating via Acorn
- **Tailwind CSS v4.2** — WooCommerce default styles disabled (`__return_empty_array`)
- **PHP 8.2**

## Key Paths

```
app/
├── setup.php        # Theme hooks, WC support, filters, pre_get_posts
├── filters.php      # Additional filters
└── woocommerce.php  # Blade template bridge (woocommerce_locate_template)

resources/views/
└── woocommerce/
    └── archive-product.blade.php  # Custom shop/catalog page
```

## Local Dev

- Site: Local by Flywheel
- MySQL socket: `/Users/olehkohut/Library/Application Support/Local/run/MaUa0-72S/mysql/mysqld.sock`
- DB: `local`, user: `root`, pass: `root`
- PHP error log: `/Users/olehkohut/Local Sites/shoptemplate/logs/php/error.log`
- Blade cache: `/Users/olehkohut/Local Sites/shoptemplate/app/public/wp-content/cache/acorn/`

## Important Conventions

### Hooks
- Brand + price filter lives in `pre_get_posts` at **priority 15** (after WooCommerce at P10).
- Shop detection: use `$query->is_post_type_archive('product')` — NOT `is_shop()` (unreliable in `pre_get_posts`).
- Term slugs: sanitize with `sanitize_title()`, not `sanitize_text_field()`.

### WooCommerce Price Filter Bug (fixed)
WC's `price_filter_post_clauses` fires even for empty `min_price=&max_price=` strings.
Fixed via `woocommerce_enable_post_clause_filtering` hook in `app/setup.php` + JS `shopFilterSubmit()` in the Blade template.

### Blade Cache
After editing any `.blade.php` template, clear the cache:
```bash
rm -rf wp-content/cache/acorn/framework/views/
```

### Taxonomy
- Brand taxonomy: `product_brand` (registered by WooCommerce Brands plugin)
- Filter URL param: `?f_brand[]=slug`

## Brand Filter Form
- Form ID: `#shop-sidebar-filter-form`
- Brand checkboxes call `shopFilterSubmit()` on change (NOT `form.submit()` directly — that bypasses `onsubmit`)
- `shopFilterSubmit()` disables empty price inputs before submit to prevent WC price filter from firing with 0/0
