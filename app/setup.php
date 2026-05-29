<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Enable WooCommerce support & modern gallery features.
     * Вмикаємо підтримку WooCommerce та сучасних галерей (зум, слайдер).
     */
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    /**
     * Declare support for High-Performance Order Storage (HPOS).
     * Оголошуємо сумісність із високопродуктивним сховищем замовлень HPOS.
     */
    add_theme_support('woocommerce_single_product_image_zoom');
}, 20);

/**
 * Declare HPOS compatibility at the extension level.
 * Окреме оголошення для сумісності з таблицями custom_orders.
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', 'solidshop', true);
    }
});

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Повністю вимикаємо стандартні стилі та CSS WooCommerce, щоб вони не ламали Tailwind v4.
 * Completely disable default WooCommerce styles to prevent conflicts with Tailwind v4.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Примусово змушуємо WordPress використовувати наші Blade шаблони для всіх сторінок екомерсу.
 * Force WordPress to use our Blade templates for all e-commerce views.
 */
add_filter('template_include', function ($template) {
    if (is_shop() || is_product_category() || is_product_tag() || is_tax('product_brand')) {
        // Шукаємо кастомний Blade шаблон каталогу
        // Search for our custom Blade archive template
        $blade_shop = locate_template('resources/views/woocommerce/archive-product.blade.php');
        if ($blade_shop) {
            return $blade_shop;
        }
    }

    if (is_product()) {
        // Шукаємо кастомний Blade шаблон картки товару
        // Search for our custom Blade single product template
        $blade_single = locate_template('resources/views/woocommerce/single-product.blade.php');
        if ($blade_single) {
            return $blade_single;
        }
    }

    return $template;
}, 99);

/**
 * Вимикаємо WooCommerce ціновий фільтр (price_filter_post_clauses), коли
 * min_price та max_price передані як порожні рядки (незаповнена форма).
 * Без цього WooCommerce застосовує умову "ціна між 0 і 0" і ховає всі товари.
 */
add_filter('woocommerce_enable_post_clause_filtering', function (bool $enabled, \WP_Query $query): bool {
    if (!$enabled) {
        return false;
    }
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $min = isset($_GET['min_price']) ? trim((string) $_GET['min_price']) : null;
    $max = isset($_GET['max_price']) ? trim((string) $_GET['max_price']) : null;
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($min === '' && $max === '') {
        return false;
    }

    return $enabled;
}, 10, 2);

/**
 * Очищаємо та стилізуємо пагінацію WooCommerce под дизайн Tailwind.
 * Clean and style WooCommerce pagination using Tailwind classes.
 */
add_filter('woocommerce_pagination_args', function ($args) {
    $args['prev_text'] = '
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>';
    $args['next_text'] = '
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>';
    $args['type'] = 'plain'; // Повністю прибираємо застарілі теги ul/li. Remove deprecated ul/li tags completely.
    return $args;
});

/**
 * Фільтрація товарів за брендом, кольором, розміром та ціною.
 * Запускається з пріоритетом 15 - після того, як WooCommerce (пріоритет 10)
 * вже встановив tax_query з клозою видимості продуктів. Ми дописуємо
 * свої клози поверх, що гарантовано стабільно.
 *
 * Filter products by brand, color, size, and price.
 * Runs with priority 15 - after WooCommerce (priority 10) has populated
 * tax_query with product visibility constraints. We append our own clauses.
 */
add_action('pre_get_posts', function (\WP_Query $query) {
    if (!$query->is_main_query() || is_admin()) {
        return;
    }

    $product_taxonomies = array_merge(['product_brand', 'product_cat', 'product_tag'], get_object_taxonomies('product'));
    $is_catalog         = (
        $query->is_post_type_archive('product') ||
        $query->is_tax($product_taxonomies) ||
        (function_exists('wc_get_page_id') && $query->is_page(wc_get_page_id('shop')))
    );

    if (!$is_catalog) {
        return;
    }

    // Зберігаємо поточний стан tax_query для нарощування умов
    // Store current tax_query state to append conditions
    $tax_query = (array) $query->get('tax_query');
    $tax_query_updated = false;

    // phpcs:disable WordPress.Security.NonceVerification.Recommended

    // --- Фільтр за брендами ---
    // --- Filter by brands ---
    $raw_brands = isset($_GET['f_brand']) ? (array) $_GET['f_brand'] : [];
    $brands     = array_values(array_filter(array_map('sanitize_title', $raw_brands)));

    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // --- Фільтр за кольором (атрибут pa_color) ---
    // --- Filter by color (pa_color attribute) ---
    $raw_colors = isset($_GET['f_color']) ? (array) $_GET['f_color'] : [];
    $colors     = array_values(array_filter(array_map('sanitize_title', $raw_colors)));

    if (!empty($colors)) {
        $tax_query[] = [
            'taxonomy' => 'pa_color',
            'field'    => 'slug',
            'terms'    => $colors,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // --- Фільтр за розміром (атрибут pa_size) ---
    // --- Filter by size (pa_size attribute) ---
    $raw_sizes = isset($_GET['f_size']) ? (array) $_GET['f_size'] : [];
    $sizes     = array_values(array_filter(array_map('sanitize_title', $raw_sizes)));

    if (!empty($sizes)) {
        $tax_query[] = [
            'taxonomy' => 'pa_size',
            'field'    => 'slug',
            'terms'    => $sizes,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // Якщо додали хоча б одну таксономію, оновлюємо запит
    // If at least one taxonomy filter was applied, update the query
    if ($tax_query_updated) {
        if (!isset($tax_query['relation'])) {
            $tax_query['relation'] = 'AND';
        }
        $query->set('tax_query', $tax_query);
    }

    // --- Фільтр за ціною ---
    // --- Filter by price ---
    if (isset($_GET['min_price']) && '' !== $_GET['min_price']) {
        $meta_query   = (array) $query->get('meta_query');
        $meta_query[] = [
            'key'     => '_price',
            'value'   => floatval($_GET['min_price']),
            'type'    => 'NUMERIC',
            'compare' => '>=',
        ];
        $query->set('meta_query', $meta_query);
    }

    if (isset($_GET['max_price']) && '' !== $_GET['max_price']) {
        $meta_query   = (array) $query->get('meta_query');
        $meta_query[] = [
            'key'     => '_price',
            'value'   => floatval($_GET['max_price']),
            'type'    => 'NUMERIC',
            'compare' => '<=',
        ];
        $query->set('meta_query', $meta_query);
    }

    // phpcs:enable WordPress.Security.NonceVerification.Recommended
}, 15);
