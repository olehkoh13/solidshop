<?php

/**
 * WooCommerce Blade Template Bridge for Sage 11 / Acorn v6
 *
 * @package App
 */

namespace App;

use WC_Product;

/**
 * Чи товар вважається «новинкою» (30 днів від дати створення).
 * Whether the product counts as "new" (within 30 days of creation).
 */
function is_product_new(WC_Product $product): bool
{
    $created = $product->get_date_created();

    if (! $created) {
        return false;
    }

    return $created->getTimestamp() >= strtotime('-30 days');
}

/**
 * HTML бейджів Розпродаж / Новинка для будь-якого контексту.
 * Sale / New badge HTML for any product context.
 */
function product_badges_html(?WC_Product $product = null): string
{
    $product = $product ?: ($GLOBALS['product'] ?? null);

    if (! $product instanceof WC_Product) {
        return '';
    }

    if (! $product->is_on_sale() && ! is_product_new($product)) {
        return '';
    }

    return (string) view('partials.product-badges', [
        'product'    => $product,
        'is_on_sale' => $product->is_on_sale(),
        'is_new'     => is_product_new($product),
    ])->render();
}

/**
 * Echo badge markup (loops, hooks).
 * Виводить розмітку бейджів (loops, hooks).
 */
function render_product_badges(?WC_Product $product = null): void
{
    echo product_badges_html($product);
}

/**
 * Slug → HEX map for pa_color swatches (shared with catalog filters).
 * Мапа slug → HEX для swatches pa_color (спільна з фільтрами каталогу).
 *
 * @return array<string, string>
 */
function solidshop_color_hex_map(): array
{
    return [
        'black'     => '#111111',
        'white'     => '#f5f5f5',
        'red'       => '#ef4444',
        'blue'      => '#3b82f6',
        'grey'      => '#9ca3af',
        'gray'      => '#9ca3af',
        'beige'     => '#e8d4b4',
        'green'     => '#22c55e',
        'yellow'    => '#eab308',
        'pink'      => '#f472b6',
        'orange'    => '#f97316',
        'purple'    => '#a855f7',
        'navy'      => '#1e3a5f',
        'brown'     => '#92400e',
        'gold'      => '#d97706',
        'silver'    => '#cbd5e1',
        'turquoise' => '#2dd4bf',
        'violet'    => '#7c3aed',
    ];
}

/**
 * Resolve HEX for a color attribute slug.
 * Повертає HEX для slug атрибута кольору.
 */
function solidshop_color_hex(string $slug): string
{
    $map = solidshop_color_hex_map();

    return $map[$slug] ?? '#d1d5db';
}

/**
 * First product_cat name for catalog cards.
 * Назва першої категорії для карток каталогу.
 */
function solidshop_loop_product_category(WC_Product $product): string
{
    $terms = get_the_terms($product->get_id(), 'product_cat');

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    return (string) $terms[0]->name;
}

/**
 * Unique in-stock color swatches for a loop product.
 * Унікальні in-stock swatches кольору для товару в loop.
 *
 * @return array<int, array{slug: string, label: string, hex: string}>
 */
function solidshop_loop_color_swatches(WC_Product $product): array
{
    $swatches = [];
    $seen     = [];

    if ($product->is_type('variable')) {
        foreach ($product->get_available_variations() as $variation) {
            if (empty($variation['is_in_stock'])) {
                continue;
            }

            $slug = (string) ($variation['attributes']['attribute_pa_color'] ?? '');

            if ($slug === '' || isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $term        = get_term_by('slug', $slug, 'pa_color');

            $swatches[] = [
                'slug'  => $slug,
                'label' => $term instanceof \WP_Term ? $term->name : $slug,
                'hex'   => solidshop_color_hex($slug),
            ];
        }

        return $swatches;
    }

    $attributes = $product->get_attributes();

    if (! isset($attributes['pa_color'])) {
        return [];
    }

    $color_attr = $attributes['pa_color'];

    if ($color_attr->is_taxonomy()) {
        $terms = wc_get_product_terms($product->get_id(), 'pa_color', ['fields' => 'all']);

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $swatches[] = [
                'slug'  => $term->slug,
                'label' => $term->name,
                'hex'   => solidshop_color_hex($term->slug),
            ];
        }
    }

    return $swatches;
}

/**
 * In-stock size options mapped to variation IDs (variable products).
 * In-stock опції розміру з variation_id (варіативні товари).
 *
 * @return array<int, array{slug: string, label: string, variation_id: int}>
 */
function solidshop_loop_size_options(WC_Product $product): array
{
    if (! $product->is_type('variable')) {
        return [];
    }

    $options = [];

    foreach ($product->get_available_variations() as $variation) {
        if (empty($variation['is_in_stock'])) {
            continue;
        }

        $slug = (string) ($variation['attributes']['attribute_pa_size'] ?? '');

        if ($slug === '' || isset($options[$slug])) {
            continue;
        }

        $term = get_term_by('slug', $slug, 'pa_size');

        $options[$slug] = [
            'slug'          => $slug,
            'label'         => $term instanceof \WP_Term ? $term->name : strtoupper($slug),
            'variation_id'  => (int) $variation['variation_id'],
        ];
    }

    return array_values($options);
}

/**
 * Mark custom catalog card render (skip duplicate WC loop badges).
 * Позначає кастомний рендер картки (без дублювання WC loop badges).
 */
function solidshop_mark_product_card_render(): void
{
    do_action('solidshop_before_product_card');
}

/**
 * Blade view name from WC template path (checkout/form-checkout.php → woocommerce.checkout.form-checkout).
 * Ім'я Blade-view з шляху WC-шаблону.
 */
function woocommerce_blade_view_name(string $template_name): string
{
    $name = str_replace('.php', '', $template_name);

    return 'woocommerce.' . str_replace('/', '.', $name);
}

/**
 * Path to the no-op PHP stub included after Blade is echoed.
 * Шлях до PHP-заглушки, яку include після виводу Blade.
 */
function woocommerce_blade_loader_path(): string
{
    return get_theme_file_path('resources/woocommerce-blade-loader.php');
}

/**
 * View data for cart/mini-cart.blade.php (progress bar + upsells).
 * Дані для Blade-шаблону міні-кошика.
 *
 * @return array{progress: array<string, mixed>, upsells: \WC_Product[]}
 */
function mini_cart_template_data(): array
{
    return [
        'progress' => solidshop_free_shipping_progress(),
        'upsells'  => solidshop_mini_cart_upsell_products(4),
    ];
}

/**
 * Free shipping threshold for mini-cart progress bar (UAH).
 * Поріг безкоштовної доставки для прогрес-бару міні-кошика.
 */
function solidshop_cart_subtotal_for_free_shipping(): float
{
    if (! function_exists('WC') || ! WC()->cart) {
        return 0.0;
    }

    $total = (float) WC()->cart->get_displayed_subtotal();

    if ('no' === get_option('woocommerce_exclude_tax_from_free_shipping', 'no')) {
        $total += (float) WC()->cart->get_subtotal_tax();
    }

    return (float) apply_filters('solidshop_cart_subtotal_for_free_shipping', $total);
}

/**
 * Free shipping threshold from WooCommerce zone methods (not checkout rate transients).
 * Поріг безкоштовної доставки з методів зон WC (не з transients розрахунку тарифів).
 *
 * Reads enabled `free_shipping` min_amount via WC_Shipping_Zone data store (live DB options).
 * Читає min_amount увімкнених методів free_shipping через data store зон (актуальні опції в БД).
 */
function solidshop_free_shipping_threshold(): float
{
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $filtered = (float) apply_filters('solidshop_free_shipping_threshold', 0);

    if ($filtered > 0) {
        $cached = $filtered;

        return $cached;
    }

    if (! class_exists(\WC_Shipping_Zones::class)) {
        $cached = 0.0;

        return $cached;
    }

    $candidates = [];
    $zone_ids   = array_keys(\WC_Shipping_Zones::get_zones());
    $zone_ids[] = 0;

    foreach ($zone_ids as $zone_id) {
        $zone = new \WC_Shipping_Zone((int) $zone_id);

        // Enabled methods only; settings come from zone-method data store, not shipping-rate cache.
        // Лише увімкнені методи; налаштування з data store, не з кешу тарифів checkout.
        foreach ($zone->get_shipping_methods(true) as $method) {
            if (! in_array($method->id, ['free_shipping', 'legacy_free_shipping'], true)) {
                continue;
            }

            $requires = (string) $method->get_option('requires', 'min_amount');

            // Coupon-only rules have no cart subtotal threshold / Лише купон — без порогу суми
            if (in_array($requires, ['coupon', 'both'], true)) {
                continue;
            }

            if (in_array($requires, ['', 'min_amount', 'either'], true)) {
                $min = $method->get_option('min_amount');

                if ($min !== '' && is_numeric($min) && (float) $min > 0) {
                    $candidates[] = (float) wc_format_decimal($min);
                }
            }
        }
    }

    $cached = $candidates === [] ? 0.0 : min($candidates);

    return $cached;
}

/**
 * Free shipping progress data for the mini-cart bar.
 * Дані прогресу безкоштовної доставки для міні-кошика.
 *
 * @return array{threshold: float, subtotal: float, percent: float, reached: bool}
 */
function solidshop_free_shipping_progress(): array
{
    $threshold = solidshop_free_shipping_threshold();
    $subtotal  = 0.0;

    if (function_exists('WC') && WC()->cart) {
        $subtotal = solidshop_cart_subtotal_for_free_shipping();
    }

    if ($threshold <= 0) {
        return [
            'threshold' => 0.0,
            'subtotal'  => $subtotal,
            'percent'   => 0.0,
            'reached'   => false,
        ];
    }

    $percent = min(100.0, ($subtotal / $threshold) * 100);

    return [
        'threshold' => $threshold,
        'subtotal'  => $subtotal,
        'percent'   => round($percent, 2),
        'reached'   => $subtotal >= $threshold,
    ];
}

/**
 * Cart shipping rate label — "Безкоштовно" only after free-shipping threshold is met.
 * Підпис тарифу доставки в кошику — «Безкоштовно» лише після досягнення порогу.
 *
 * @return array{html: string, is_free: bool}
 */
function solidshop_cart_shipping_rate_display(\WC_Shipping_Rate $rate): array
{
    $progress = solidshop_free_shipping_progress();

    if ($progress['reached']) {
        return [
            'html'    => esc_html__('Безкоштовно', 'solidshop'),
            'is_free' => true,
        ];
    }

    $cost = (float) $rate->cost;

    if (function_exists('WC') && WC()->cart && WC()->cart->display_prices_including_tax()) {
        $cost += (float) $rate->get_shipping_tax();
    }

    if ($cost > 0) {
        return [
            'html'    => wc_price($cost),
            'is_free' => false,
        ];
    }

    return [
        'html'    => esc_html__('Розраховується', 'solidshop'),
        'is_free' => false,
    ];
}

/**
 * Upsell products for mini-cart "Before you go" carousel.
 * Товари для блоку «Перед тим як піти» в міні-кошику.
 *
 * @return \WC_Product[]
 */
function solidshop_mini_cart_upsell_products(int $limit = 4): array
{
    if (! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty()) {
        return [];
    }

    $exclude_ids   = [];
    $candidate_ids = [];

    foreach (WC()->cart->get_cart() as $cart_item) {
        $exclude_ids[] = (int) $cart_item['product_id'];

        if (! empty($cart_item['variation_id'])) {
            $exclude_ids[] = (int) $cart_item['variation_id'];
        }

        $product = $cart_item['data'] ?? null;

        if ($product instanceof \WC_Product) {
            $candidate_ids = array_merge(
                $candidate_ids,
                $product->get_cross_sell_ids(),
                $product->get_upsell_ids()
            );
        }
    }

    $exclude_ids   = array_unique(array_filter($exclude_ids));
    $candidate_ids = array_values(array_unique(array_diff(array_map('intval', $candidate_ids), $exclude_ids)));

    if (count($candidate_ids) < $limit) {
        $fallback = wc_get_products([
            'status'  => 'publish',
            'limit'   => $limit * 2,
            'orderby' => 'date',
            'order'   => 'DESC',
            'exclude' => $exclude_ids,
        ]);

        foreach ($fallback as $fallback_product) {
            if ($fallback_product instanceof \WC_Product) {
                $candidate_ids[] = $fallback_product->get_id();
            }
        }

        $candidate_ids = array_values(array_unique($candidate_ids));
    }

    $products = [];

    foreach ($candidate_ids as $product_id) {
        if (count($products) >= $limit) {
            break;
        }

        $product = wc_get_product($product_id);

        if (
            $product instanceof \WC_Product
            && $product->is_purchasable()
            && $product->is_in_stock()
        ) {
            $products[] = $product;
        }
    }

    return $products;
}

/**
 * Перенаправляємо завантаження шаблонів WooCommerce на Blade views.
 * Шукає файли у папці resources/views/woocommerce/
 */
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    $theme_template = locate_template("resources/views/woocommerce/{$template_name}");
    if ($theme_template) {
        return $theme_template;
    }

    $blade_name     = str_replace('.php', '.blade.php', $template_name);
    $blade_template = locate_template("resources/views/woocommerce/{$blade_name}");
    if ($blade_template) {
        return $blade_template;
    }

    return $template;
}, 10, 3);

/**
 * Рендеримо .blade.php через Acorn замість сирого include().
 * Render .blade.php via Acorn instead of a raw PHP include().
 */
add_filter('wc_get_template', function ($template, $template_name, $args, $template_path, $default_path) {
    if (! is_string($template) || ! str_ends_with($template, '.blade.php')) {
        return $template;
    }

    $view = woocommerce_blade_view_name($template_name);

    if (! view()->exists($view)) {
        return $template;
    }

    $loader = woocommerce_blade_loader_path();

    if (! is_readable($loader)) {
        return $template;
    }

    add_action(
        'woocommerce_before_template_part',
        $renderBlade = function ($name, $path, $located, $part_args) use ($view, $args, $template_name, &$renderBlade) {
            if ($name !== $template_name) {
                return;
            }

            remove_action('woocommerce_before_template_part', $renderBlade, 10);

            $viewData = is_array($args) ? $args : [];

            // Inject mini-cart data here — Blade cannot reliably call App\ namespaced helpers.
            // Дані міні-кошика передаємо з PHP-bridge, а не викликаємо App\ helpers у Blade.
            if ($template_name === 'cart/mini-cart.php') {
                $viewData['progress'] = solidshop_free_shipping_progress();
                $viewData['upsells']  = solidshop_mini_cart_upsell_products(4);
            }

            echo view($view, $viewData)->render();
        },
        10,
        4
    );

    return $loader;
}, 10, 5);

/**
 * Глобальні бейджі у стандартних WC product loops (related, upsells, shortcodes).
 * Global badges in default WC product loops (related, upsells, shortcodes).
 */
add_action('woocommerce_before_shop_loop_item', function () {
    if (did_action('solidshop_before_product_card')) {
        return;
    }

    render_product_badges();
}, 5);

/**
 * Вимикаємо стандартні WC sale flash — використовуємо ss-product-badge.
 * Disable default WC sale flash — we use ss-product-badge instead.
 */
add_action('init', function () {
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
}, 20);

/**
 * Очищаємо застарілі WC notices на сторінці товару.
 * Clear stale WC session notices on single product page.
 */
add_action('woocommerce_before_single_product', function () {
    wc_clear_notices();
});

/**
 * Purchase-zone price above add-to-cart (simple, external, grouped).
 * Ціна над кнопкою купити для простих товарів.
 */
add_action('woocommerce_before_add_to_cart_button', function (): void {
    global $product;

    if (! $product instanceof \WC_Product || $product->is_type('variable')) {
        return;
    }

    echo '<div class="price-block price-block--purchase">' . $product->get_price_html() . '</div>';
}, 5);

/**
 * Variable product price range above purchase zone (before variation selected).
 * Діапазон цін для варіативного товару до вибору варіації.
 */
add_action('woocommerce_after_variations_table', function (): void {
    global $product;

    if (! $product instanceof \WC_Product || ! $product->is_type('variable')) {
        return;
    }

    echo '<div class="price-block price-block--purchase">' . $product->get_price_html() . '</div>';
}, 5);

/**
 * AJAX: update mini-cart item quantity and return refreshed fragments.
 * AJAX: оновлення кількості в міні-кошику з поверненням фрагментів.
 */
add_action('wc_ajax_solidshop_update_mini_cart_qty', function (): void {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (! wp_verify_nonce($nonce, 'solidshop_mini_cart')) {
        wp_send_json_error(['message' => 'Invalid security token.'], 403);
    }

    $cart_item_key = isset($_POST['cart_item_key'])
        ? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
        : '';
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;

    if ($cart_item_key === '' || ! WC()->cart) {
        wp_send_json_error(['message' => 'Invalid cart item.'], 400);
    }

    $cart_item = WC()->cart->get_cart_item($cart_item_key);

    if (! $cart_item) {
        wp_send_json_error(['message' => 'Cart item not found.'], 404);
    }

    /** @var \WC_Product $product */
    $product = $cart_item['data'];
    $min_qty = max(1, $product->get_min_purchase_quantity());
    $max_qty = $product->get_max_purchase_quantity();

    if ($quantity < $min_qty) {
        $quantity = $min_qty;
    }

    if ($max_qty > 0 && $quantity > $max_qty) {
        $quantity = $max_qty;
    }

    if ($quantity === 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
    }

    WC()->cart->calculate_totals();

    \WC_AJAX::get_refreshed_fragments();
});

/**
 * AJAX: load single-product add-to-cart form for Quick Buy modal.
 * AJAX: завантаження форми add-to-cart для модалки Quick Buy.
 */
function solidshop_ajax_load_quick_buy(): void
{
    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;

    if ($product_id <= 0) {
        wp_send_json_error(['message' => __('Invalid product.', 'solidshop')], 400);
    }

    $product = wc_get_product($product_id);

    if (! $product instanceof \WC_Product || ! $product->is_purchasable()) {
        wp_send_json_error(['message' => __('Product unavailable.', 'solidshop')], 404);
    }

    // Setup global post + product context for WC templates / Глобальний контекст для шаблонів WC
    $post_object = get_post($product_id);

    if (! $post_object) {
        wp_send_json_error(['message' => __('Product not found.', 'solidshop')], 404);
    }

    global $post, $product;
    $previous_post    = $post;
    $previous_product = $product ?? null;

    $post    = $post_object;
    $product = wc_get_product($product_id);
    setup_postdata($post);

    ob_start();
    echo '<div class="product-actions-form quick-buy-modal__form">';
    woocommerce_template_single_add_to_cart();
    echo '</div>';
    $html = ob_get_clean();

    wp_reset_postdata();

    $post    = $previous_post;
    $product = $previous_product;

    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_solidshop_load_quick_buy', __NAMESPACE__ . '\\solidshop_ajax_load_quick_buy');
add_action('wp_ajax_nopriv_solidshop_load_quick_buy', __NAMESPACE__ . '\\solidshop_ajax_load_quick_buy');
add_action('wc_ajax_solidshop_load_quick_buy', __NAMESPACE__ . '\\solidshop_ajax_load_quick_buy');
add_action('wc_ajax_nopriv_solidshop_load_quick_buy', __NAMESPACE__ . '\\solidshop_ajax_load_quick_buy');

/**
 * Render related products with catalog card partial (4 columns).
 * Супутні товари — картки каталогу, 4 колонки.
 */
function solidshop_render_related_products(): void
{
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $args = apply_filters('woocommerce_output_related_products_args', [
        'posts_per_page' => 4,
        'columns'        => 4,
        'orderby'        => 'rand',
    ]);

    $related_ids = wc_get_related_products(
        $product->get_id(),
        (int) $args['posts_per_page']
    );

    if ($related_ids === []) {
        return;
    }

    $query = new \WP_Query([
        'post_type'      => 'product',
        'post__in'       => $related_ids,
        'posts_per_page' => (int) $args['posts_per_page'],
        'orderby'        => 'post__in',
        'post_status'    => 'publish',
    ]);

    if (! $query->have_posts()) {
        return;
    }

    echo view('partials.related-products-section', ['query' => $query])->render();
}

add_action('woocommerce_after_single_product_summary', __NAMESPACE__ . '\\solidshop_render_related_products', 20);

/**
 * Enqueue variation scripts on catalog + single product (related cards, Quick Buy).
 * Скрипти варіацій на каталозі та single product (related, Quick Buy).
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('is_shop')) {
        return;
    }

    if (! is_shop() && ! is_product_taxonomy() && ! is_product() && ! is_front_page()) {
        return;
    }

    wp_enqueue_script('wc-add-to-cart');
    wp_enqueue_script('wc-add-to-cart-variation');
}, 30);

/**
 * Cart page — classic shortcode instead of WooCommerce Blocks.
 * Сторінка кошика — класичний шорткод замість WooCommerce Blocks.
 */
add_filter('render_block', function (string $content, array $block): string {
    if (is_admin() || (($block['blockName'] ?? '') !== 'woocommerce/cart')) {
        return $content;
    }

    return do_shortcode('[woocommerce_cart]');
}, 10, 2);

/**
 * Restore classic wc-cart.js after Cart block dequeues it (wp_enqueue_scripts:20).
 * Блок кошика знімає wc-cart — повертаємо для AJAX-оновлення qty у класичному шаблоні.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('is_cart') || ! is_cart()) {
        return;
    }

    wp_enqueue_script('wc-cart');
}, 25);

add_filter('body_class', function (array $classes): array {
    if (function_exists('is_cart') && is_cart()) {
        $classes[] = 'solidshop-cart-page';

        if (function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
            $classes[] = 'solidshop-cart-empty-page';
        }
    }

    if (function_exists('is_checkout') && is_checkout()) {
        if (function_exists('is_order_received_page') && is_order_received_page()) {
            $classes[] = 'solidshop-order-received-page';
        } else {
            $classes[] = 'solidshop-checkout-page';
        }
    }

    return $classes;
});

add_action('wp', function (): void {
    if (! function_exists('is_cart') || ! is_cart()) {
        return;
    }

    remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
}, 20);

/**
 * Cart page — hide address calculator (chosen at checkout).
 * Сторінка кошика — без калькулятора адреси (адреса на checkout).
 */
add_filter('woocommerce_shipping_show_shipping_calculator', function (bool $show, int $index, array $package): bool {
    if (function_exists('is_cart') && is_cart()) {
        return false;
    }

    return $show;
}, 10, 3);

/*
|--------------------------------------------------------------------------
| Front page featured categories / Популярні категорії на головній
|--------------------------------------------------------------------------
*/

/** Term meta key: show category on homepage / Meta: показувати на головній */
const FEATURED_ON_FRONT_META = '_solidshop_featured_on_front';

/** Transient key for sales-based category ranking / Кеш рейтингу за продажами */
const CAT_SALES_RANK_TRANSIENT = 'solidshop_cat_sales_rank';

/** Cache TTL (6 hours) / TTL кешу (6 годин) */
const CAT_SALES_RANK_TTL = 6 * HOUR_IN_SECONDS;

/**
 * Register term meta for product_cat featured flag.
 * Реєстрація term meta для прапорця featured.
 */
add_action('init', function (): void {
    register_term_meta('product_cat', FEATURED_ON_FRONT_META, [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => static function ($value): string {
            return ($value === '1' || $value === true || $value === 1) ? '1' : '';
        },
    ]);
});

/**
 * Add-category form checkbox / Чекбокс на формі додавання категорії.
 */
add_action('product_cat_add_form_fields', function (): void {
    ?>
    <div class="form-field">
        <label for="solidshop_featured_on_front">
            <input
                type="checkbox"
                name="solidshop_featured_on_front"
                id="solidshop_featured_on_front"
                value="1"
            />
            <?php esc_html_e('Show on Front Page (Featured)', 'solidshop'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Display this category in the Popular Categories block on the homepage.', 'solidshop'); ?>
        </p>
    </div>
    <?php
});

/**
 * Edit-category form checkbox / Чекбокс на формі редагування категорії.
 *
 * @param \WP_Term $term Current term.
 */
add_action('product_cat_edit_form_fields', function (\WP_Term $term): void {
    $is_featured = get_term_meta($term->term_id, FEATURED_ON_FRONT_META, true) === '1';
    ?>
    <tr class="form-field">
        <th scope="row"><?php esc_html_e('Front Page', 'solidshop'); ?></th>
        <td>
            <label for="solidshop_featured_on_front">
                <input
                    type="checkbox"
                    name="solidshop_featured_on_front"
                    id="solidshop_featured_on_front"
                    value="1"
                    <?php checked($is_featured); ?>
                />
                <?php esc_html_e('Show on Front Page (Featured)', 'solidshop'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Display this category in the Popular Categories block on the homepage.', 'solidshop'); ?>
            </p>
        </td>
    </tr>
    <?php
}, 10, 1);

/**
 * Persist featured flag on create/edit / Збереження прапорця featured.
 *
 * @param int $term_id Term ID.
 */
function solidshop_save_product_cat_featured_meta(int $term_id): void
{
    if (! current_user_can('manage_product_terms')) {
        return;
    }

    if (isset($_POST['solidshop_featured_on_front']) && $_POST['solidshop_featured_on_front'] === '1') {
        update_term_meta($term_id, FEATURED_ON_FRONT_META, '1');
    } else {
        delete_term_meta($term_id, FEATURED_ON_FRONT_META);
    }
}

add_action('created_product_cat', __NAMESPACE__ . '\\solidshop_save_product_cat_featured_meta');
add_action('edited_product_cat', __NAMESPACE__ . '\\solidshop_save_product_cat_featured_meta');

/**
 * Invalidate sales rank cache / Скинути кеш рейтингу за продажами.
 */
function solidshop_invalidate_category_sales_cache(): void
{
    delete_transient(CAT_SALES_RANK_TRANSIENT);
}

add_action('woocommerce_update_product', __NAMESPACE__ . '\\solidshop_invalidate_category_sales_cache');
add_action('woocommerce_new_order', __NAMESPACE__ . '\\solidshop_invalidate_category_sales_cache');
add_action('woocommerce_order_status_completed', __NAMESPACE__ . '\\solidshop_invalidate_category_sales_cache');

/**
 * Resolve top-level product_cat ancestor ID.
 * ID top-level предка для product_cat.
 */
function solidshop_get_top_level_product_cat(int $term_id): ?int
{
    $term = get_term($term_id, 'product_cat');

    if (! $term instanceof \WP_Term || is_wp_error($term)) {
        return null;
    }

    while ((int) $term->parent !== 0) {
        $term = get_term((int) $term->parent, 'product_cat');

        if (! $term instanceof \WP_Term || is_wp_error($term)) {
            return null;
        }
    }

    return (int) $term->term_id;
}

/**
 * Top-level categories ranked by aggregated product total_sales.
 * Top-level категорії, відсортовані за сумою total_sales товарів.
 *
 * @return \WP_Term[]
 */
function solidshop_get_categories_by_sales(int $limit = 10): array
{
    $cached = get_transient(CAT_SALES_RANK_TRANSIENT);

    if (is_array($cached)) {
        return array_slice($cached, 0, $limit);
    }

    if (! function_exists('wc_get_products')) {
        return [];
    }

    $scores = [];

    $products = wc_get_products([
        'status'   => 'publish',
        'limit'    => 200,
        'orderby'  => 'meta_value_num',
        'meta_key' => 'total_sales',
        'order'    => 'DESC',
        'return'   => 'objects',
    ]);

    foreach ($products as $product) {
        if (! $product instanceof WC_Product) {
            continue;
        }

        $sales = (int) $product->get_total_sales();

        if ($sales <= 0) {
            continue;
        }

        $terms = get_the_terms($product->get_id(), 'product_cat');

        if (! $terms || is_wp_error($terms)) {
            continue;
        }

        $attributed = [];

        foreach ($terms as $term) {
            $top_id = solidshop_get_top_level_product_cat((int) $term->term_id);

            if ($top_id) {
                $attributed[$top_id] = true;
            }
        }

        foreach (array_keys($attributed) as $top_id) {
            $scores[$top_id] = ($scores[$top_id] ?? 0) + $sales;
        }
    }

    if ($scores === []) {
        set_transient(CAT_SALES_RANK_TRANSIENT, [], CAT_SALES_RANK_TTL);

        return [];
    }

    uksort($scores, static function (int|string $a, int|string $b) use ($scores): int {
        $sales_cmp = $scores[$b] <=> $scores[$a];

        return $sales_cmp !== 0 ? $sales_cmp : ((int) $a <=> (int) $b);
    });

    $ranked = [];

    foreach (array_keys($scores) as $term_id) {
        $term = get_term((int) $term_id, 'product_cat');

        if (
            $term instanceof \WP_Term
            && ! is_wp_error($term)
            && (int) $term->parent === 0
            && (int) $term->count > 0
        ) {
            $ranked[] = $term;
        }
    }

    set_transient(CAT_SALES_RANK_TRANSIENT, $ranked, CAT_SALES_RANK_TTL);

    return array_slice($ranked, 0, $limit);
}

/**
 * Hybrid front-page categories: manual featured first, sales fallback.
 * Гібрид: спочатку обрані вручну, потім fallback за продажами.
 *
 * @return \WP_Term[]
 */
function solidshop_get_front_page_categories(int $limit = 4): array
{
    $featured = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'meta_query' => [[
            'key'     => FEATURED_ON_FRONT_META,
            'value'   => '1',
            'compare' => '=',
        ]],
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($featured)) {
        $featured = [];
    }

    $result = $featured;
    $seen   = array_map('intval', wp_list_pluck($featured, 'term_id'));

    if (count($result) < $limit) {
        $by_sales = solidshop_get_categories_by_sales(max($limit * 2, 8));

        foreach ($by_sales as $term) {
            if (count($result) >= $limit) {
                break;
            }

            if (! in_array((int) $term->term_id, $seen, true)) {
                $result[] = $term;
                $seen[]   = (int) $term->term_id;
            }
        }
    }

    return array_slice($result, 0, $limit);
}

/*
|--------------------------------------------------------------------------
| Transactional email branding / Брендинг транзакційних email
|--------------------------------------------------------------------------
*/

/**
 * Remove default WooCommerce footer credit from transactional emails.
 * Прибираємо стандартний credit WooCommerce з підвалу email.
 */
function solidshop_sanitize_email_footer_text(string $footer_text, $email = null): string
{
    unset($email);

    if (! is_string($footer_text)) {
        $footer_text = is_scalar($footer_text) ? (string) $footer_text : '';
    }

    // Remove WooCommerce credit lines and links / Прибираємо credit та посилання WooCommerce
    $footer_text = preg_replace('/\s*Built with.*?WooCommerce.*?/i', '', $footer_text);
    $footer_text = preg_replace('/<a[^>]*woocommerce\.com[^>]*>.*?<\/a>/i', '', $footer_text);
    $footer_text = preg_replace('/\{WooCommerce\}|\{woocommerce\}/', '', $footer_text);

    return trim($footer_text);
}

add_filter('woocommerce_email_footer_text', __NAMESPACE__ . '\\solidshop_sanitize_email_footer_text', 20, 2);
