<?php

/**
 * SolidShop custom wishlist (no third-party plugins).
 * Власний wishlist SolidShop (без сторонніх плагінів).
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

const SOLIDSHOP_WISHLIST_META_KEY = '_solidshop_wishlist';
const SOLIDSHOP_WISHLIST_COOKIE     = 'solidshop_wishlist';
const SOLIDSHOP_WISHLIST_NONCE      = 'solidshop_wishlist';

/**
 * Normalize and validate product ID list.
 * Нормалізує та валідує список ID товарів.
 *
 * @param  mixed  $ids
 * @return int[]
 */
function solidshop_normalize_wishlist_ids(mixed $ids): array
{
    if (! is_array($ids)) {
        return [];
    }

    $normalized = array_values(array_unique(array_filter(array_map('absint', $ids))));

    return array_values(array_filter($normalized, static function (int $product_id): bool {
        $product = wc_get_product($product_id);

        return $product instanceof \WC_Product && $product->is_visible();
    }));
}

/**
 * Get wishlist product IDs for the current visitor.
 * Отримує ID товарів wishlist для поточного відвідувача.
 *
 * @return int[]
 */
function solidshop_get_wishlist(): array
{
    $user_id = get_current_user_id();

    if ($user_id > 0) {
        $stored = get_user_meta($user_id, SOLIDSHOP_WISHLIST_META_KEY, true);

        return solidshop_normalize_wishlist_ids(is_array($stored) ? $stored : []);
    }

    if (empty($_COOKIE[SOLIDSHOP_WISHLIST_COOKIE])) {
        return [];
    }

    $raw = wp_unslash((string) $_COOKIE[SOLIDSHOP_WISHLIST_COOKIE]);
    $decoded = json_decode($raw, true);

    return solidshop_normalize_wishlist_ids($decoded);
}

/**
 * Backward-compatible alias / Зворотньо-сумісний alias.
 *
 * @return int[]
 */
function solidshop_get_wishlist_ids(): array
{
    return solidshop_get_wishlist();
}

/**
 * Persist wishlist IDs for logged-in user or guest cookie.
 * Зберігає ID wishlist для залогіненого користувача або cookie гостя.
 *
 * @param  int[]  $ids
 */
function solidshop_save_wishlist_ids(array $ids): void
{
    $ids = solidshop_normalize_wishlist_ids($ids);

    $user_id = get_current_user_id();

    if ($user_id > 0) {
        update_user_meta($user_id, SOLIDSHOP_WISHLIST_META_KEY, $ids);

        return;
    }

    $payload = wp_json_encode(array_values($ids));

    if ($payload === false) {
        return;
    }

    $expires = time() + (30 * DAY_IN_SECONDS);

    setcookie(
        SOLIDSHOP_WISHLIST_COOKIE,
        $payload,
        [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );

    $_COOKIE[SOLIDSHOP_WISHLIST_COOKIE] = $payload;
}

/**
 * Check if a product is in the current wishlist.
 * Перевіряє, чи товар у wishlist поточного відвідувача.
 */
function solidshop_is_in_wishlist(int $product_id): bool
{
    return in_array($product_id, solidshop_get_wishlist(), true);
}

/**
 * Wishlist item count.
 * Кількість товарів у wishlist.
 */
function solidshop_get_wishlist_count(): int
{
    return count(solidshop_get_wishlist());
}

/**
 * Toggle product in wishlist; returns action + count.
 * Перемикає товар у wishlist; повертає action + count.
 *
 * @return array{action: string, count: int, in_wishlist: bool}
 */
function solidshop_toggle_wishlist_product(int $product_id): array
{
    $product = wc_get_product($product_id);

    if (! $product instanceof \WC_Product || ! $product->is_visible()) {
        wp_send_json_error(['message' => __('Invalid product.', 'solidshop')], 400);
    }

    $ids = solidshop_get_wishlist();
    $key = array_search($product_id, $ids, true);

    if ($key !== false) {
        unset($ids[$key]);
        $ids = array_values($ids);
        $action = 'removed';
        $in_wishlist = false;
    } else {
        $ids[] = $product_id;
        $ids = array_values(array_unique($ids));
        $action = 'added';
        $in_wishlist = true;
    }

    solidshop_save_wishlist_ids($ids);

    return [
        'action'      => $action,
        'count'       => count($ids),
        'in_wishlist' => $in_wishlist,
    ];
}

/**
 * Resolve wishlist URL (My Account endpoint).
 * URL wishlist (endpoint особистого кабінету).
 */
function solidshop_get_wishlist_page_url(): string
{
    if (function_exists('wc_get_account_endpoint_url')) {
        return wc_get_account_endpoint_url('wishlist');
    }

    return home_url('/my-account/wishlist/');
}

/**
 * Register My Account rewrite endpoint.
 * Реєструє rewrite-endpoint для особистого кабінету.
 */
function solidshop_register_wishlist_endpoint(): void
{
    add_rewrite_endpoint('wishlist', EP_ROOT | EP_PAGES);
}

add_action('init', __NAMESPACE__ . '\\solidshop_register_wishlist_endpoint');

/**
 * Register wishlist as a WooCommerce account query var.
 * Реєструє wishlist як query var WooCommerce для My Account.
 *
 * @param  array<string, string>  $vars
 * @return array<string, string>
 */
function solidshop_add_wishlist_query_var(array $vars): array
{
    $vars['wishlist'] = 'wishlist';

    return $vars;
}

add_filter('woocommerce_get_query_vars', __NAMESPACE__ . '\\solidshop_add_wishlist_query_var');

/**
 * Flush rewrite rules once after endpoint registration.
 * Одноразово скидає rewrite rules після реєстрації endpoint.
 */
/**
 * Flush rewrite rules once after endpoint registration.
 * Одноразово скидає rewrite rules після реєстрації endpoint.
 */
add_action('init', static function (): void {
    if (get_option('solidshop_wishlist_endpoint_flushed') === '1') {
        return;
    }

    solidshop_register_wishlist_endpoint();
    flush_rewrite_rules(false);
    update_option('solidshop_wishlist_endpoint_flushed', '1', true);
}, 99);

add_action('after_switch_theme', static function (): void {
    delete_option('solidshop_wishlist_endpoint_flushed');
    solidshop_register_wishlist_endpoint();
    flush_rewrite_rules(false);
    update_option('solidshop_wishlist_endpoint_flushed', '1', true);
});

/**
 * Add wishlist tab to My Account navigation.
 * Додає вкладку wishlist у меню особистого кабінету.
 *
 * @param  array<string, string>  $items
 * @return array<string, string>
 */
function solidshop_add_wishlist_account_menu_item(array $items): array
{
    $wishlist_label = __('Вподобані товари', 'solidshop');
    $updated        = [];
    $inserted       = false;

    foreach ($items as $endpoint => $label) {
        $updated[$endpoint] = $label;

        if ($endpoint === 'orders') {
            $updated['wishlist'] = $wishlist_label;
            $inserted            = true;
        }
    }

    if (! $inserted) {
        $updated = [];
        foreach ($items as $endpoint => $label) {
            $updated[$endpoint] = $label;

            if ($endpoint === 'dashboard') {
                $updated['wishlist'] = $wishlist_label;
            }
        }
    }

    return $updated;
}

add_filter('woocommerce_account_menu_items', __NAMESPACE__ . '\\solidshop_add_wishlist_account_menu_item', 20);

/**
 * Render wishlist grid on My Account endpoint.
 * Виводить сітку wishlist у endpoint особистого кабінету.
 */
function solidshop_render_account_wishlist_endpoint(): void
{
    echo view('woocommerce.myaccount.wishlist')->render();
}

add_action('woocommerce_account_wishlist_endpoint', __NAMESPACE__ . '\\solidshop_render_account_wishlist_endpoint');

/**
 * AJAX handler: toggle wishlist product.
 * AJAX-обробник: додати/прибрати товар з wishlist.
 */
function solidshop_ajax_toggle_wishlist(): void
{
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (! wp_verify_nonce($nonce, SOLIDSHOP_WISHLIST_NONCE)) {
        wp_send_json_error(['message' => __('Invalid security token.', 'solidshop')], 403);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

    if ($product_id <= 0) {
        wp_send_json_error(['message' => __('Missing product ID.', 'solidshop')], 400);
    }

    $result = solidshop_toggle_wishlist_product($product_id);

    wp_send_json_success([
        'status'      => 'success',
        'action'      => $result['action'],
        'count'       => $result['count'],
        'in_wishlist' => $result['in_wishlist'],
        'product_id'  => $product_id,
    ]);
}

add_action('wp_ajax_toggle_wishlist', __NAMESPACE__ . '\\solidshop_ajax_toggle_wishlist');
add_action('wp_ajax_nopriv_toggle_wishlist', __NAMESPACE__ . '\\solidshop_ajax_toggle_wishlist');
add_action('wc_ajax_toggle_wishlist', __NAMESPACE__ . '\\solidshop_ajax_toggle_wishlist');
add_action('wc_ajax_nopriv_toggle_wishlist', __NAMESPACE__ . '\\solidshop_ajax_toggle_wishlist');

/**
 * Expose wishlist AJAX config to the frontend.
 * Передає AJAX-конфіг wishlist на фронтенд.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('WC')) {
        return;
    }

    wp_register_script('solidshop-wishlist-config', '', [], null, false);
    wp_enqueue_script('solidshop-wishlist-config');

    $config = [
        'ajaxUrl'      => class_exists(\WC_AJAX::class)
            ? \WC_AJAX::get_endpoint('toggle_wishlist')
            : admin_url('admin-ajax.php?action=toggle_wishlist'),
        'wcAjaxUrl'    => class_exists(\WC_AJAX::class)
            ? \WC_AJAX::get_endpoint('toggle_wishlist')
            : '',
        'nonce'        => wp_create_nonce(SOLIDSHOP_WISHLIST_NONCE),
        'wishlistUrl'  => solidshop_get_wishlist_page_url(),
        'initialCount' => solidshop_get_wishlist_count(),
    ];

    wp_add_inline_script(
        'solidshop-wishlist-config',
        'window.solidshopWishlist = Object.assign(' . wp_json_encode($config) . ', window.solidshopWishlist || {});',
        'before'
    );
}, 25);
