<?php

/**
 * WooCommerce live search AJAX endpoint.
 * AJAX-ендпоінт live-пошуку WooCommerce.
 *
 * @package App
 */

namespace App;

use WC_Product;
use WP_Query;

/**
 * Minimum keyword length for live search requests.
 * Мінімальна довжина ключового слова для live-пошуку.
 */
const SOLIDSHOP_LIVE_SEARCH_MIN_LENGTH = 3;

/**
 * Maximum number of products returned per request.
 * Максимальна кількість товарів у відповіді.
 */
const SOLIDSHOP_LIVE_SEARCH_LIMIT = 8;

/**
 * AJAX handler: search products by title, content, or SKU.
 * AJAX-обробник: пошук товарів за назвою, описом або SKU.
 */
function solidshop_ajax_live_search(): void
{
    $keyword = isset($_POST['keyword'])
        ? sanitize_text_field(wp_unslash($_POST['keyword']))
        : '';

    if (mb_strlen($keyword) < SOLIDSHOP_LIVE_SEARCH_MIN_LENGTH) {
        wp_send_json([]);
    }

    $text_ids = solidshop_live_search_query_text($keyword);
    $sku_ids  = solidshop_live_search_query_sku($keyword);

    $product_ids = array_values(array_unique(array_merge($sku_ids, $text_ids)));
    $product_ids = array_slice($product_ids, 0, SOLIDSHOP_LIVE_SEARCH_LIMIT);

    if ($product_ids === []) {
        wp_send_json([]);
    }

    $results = [];

    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);

        if (! $product instanceof WC_Product || $product->get_status() !== 'publish') {
            continue;
        }

        $image_id  = $product->get_image_id();
        $image_url = $image_id
            ? wp_get_attachment_image_url((int) $image_id, 'woocommerce_thumbnail')
            : wc_placeholder_img_src('woocommerce_thumbnail');

        $results[] = [
            'title' => html_entity_decode(wp_strip_all_tags($product->get_name()), ENT_QUOTES, 'UTF-8'),
            'url'   => get_permalink($product_id),
            'image' => $image_url ?: wc_placeholder_img_src('woocommerce_thumbnail'),
            'price' => $product->get_price_html(),
            'sku'   => $product->get_sku(),
        ];
    }

    wp_send_json($results);
}

/**
 * Query product IDs matching title or post content.
 * Запит ID товарів за назвою або вмістом запису.
 *
 * @return int[]
 */
function solidshop_live_search_query_text(string $keyword): array
{
    $query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        's'                      => $keyword,
        'posts_per_page'         => SOLIDSHOP_LIVE_SEARCH_LIMIT,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    return array_map('intval', $query->posts);
}

/**
 * Query product IDs matching SKU meta (_sku).
 * Запит ID товарів за мета-полем SKU (_sku).
 *
 * @return int[]
 */
function solidshop_live_search_query_sku(string $keyword): array
{
    $query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => SOLIDSHOP_LIVE_SEARCH_LIMIT,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta_query'             => [
            [
                'key'     => '_sku',
                'value'   => $keyword,
                'compare' => 'LIKE',
            ],
        ],
    ]);

    return array_map('intval', $query->posts);
}

add_action('wp_ajax_solidshop_live_search', __NAMESPACE__ . '\\solidshop_ajax_live_search');
add_action('wp_ajax_nopriv_solidshop_live_search', __NAMESPACE__ . '\\solidshop_ajax_live_search');
add_action('wc_ajax_solidshop_live_search', __NAMESPACE__ . '\\solidshop_ajax_live_search');
add_action('wc_ajax_nopriv_solidshop_live_search', __NAMESPACE__ . '\\solidshop_ajax_live_search');

/**
 * Expose live search AJAX URL to the frontend.
 * Передає URL live-пошуку на фронтенд.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('WC') || ! class_exists(\WC_AJAX::class)) {
        return;
    }

    wp_register_script('solidshop-live-search-config', '', [], null, false);
    wp_enqueue_script('solidshop-live-search-config');

    $config = [
        'ajaxUrl' => \WC_AJAX::get_endpoint('solidshop_live_search'),
    ];

    wp_add_inline_script(
        'solidshop-live-search-config',
        'window.solidshopLiveSearch = Object.assign(' . wp_json_encode($config) . ', window.solidshopLiveSearch || {});',
        'before'
    );
}, 25);
