<?php

/**
 * Automated mega-menu data builder for the catalog dropdown.
 * Автоматизований білдер даних мега-меню для дропдауна каталогу.
 *
 * Builds a 3-level product_cat hierarchy with related brands per
 * top-level category and caches the whole array via Transients API.
 * Будує 3-рівневу ієрархію product_cat з брендами для кожної
 * топ-категорії та кешує весь масив через Transients API.
 */

declare(strict_types=1);

namespace App;

/**
 * Transient key for the cached mega-menu array.
 * Ключ транзієнта для кешованого масиву мега-меню.
 */
const SOLIDSHOP_MEGA_MENU_TRANSIENT = 'solidshop_mega_menu';

/**
 * Returns the processed mega-menu array (cached for 12 hours).
 * Повертає готовий масив мега-меню (кеш на 12 годин).
 *
 * Element structure / Структура елемента:
 * [
 *   'id'       => int,
 *   'name'     => string,
 *   'url'      => string,
 *   'children' => [ ['name', 'url', 'children' => [['name', 'url'], ...]], ... ],
 *   'brands'   => [ ['name', 'url'], ... ],
 * ]
 */
function solidshop_get_mega_menu(): array
{
    $cached = get_transient(SOLIDSHOP_MEGA_MENU_TRANSIENT);

    if (is_array($cached)) {
        return $cached;
    }

    $menu = solidshop_build_mega_menu();

    set_transient(SOLIDSHOP_MEGA_MENU_TRANSIENT, $menu, 12 * HOUR_IN_SECONDS);

    return $menu;
}

/**
 * Builds the mega-menu array from scratch (heavy queries live here).
 * Будує масив мега-меню з нуля (усі важкі запити зосереджено тут).
 */
function solidshop_build_mega_menu(): array
{
    if (! taxonomy_exists('product_cat')) {
        return [];
    }

    // Top-level (parent) categories for the left sidebar.
    // Топ-рівневі (батьківські) категорії для лівої панелі.
    $parents = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($parents) || empty($parents)) {
        return [];
    }

    $menu = [];

    foreach ($parents as $parent) {
        // Level 2 subcategories (column headings in the right panel).
        // Підкатегорії рівня 2 (заголовки колонок правої панелі).
        $children = get_terms([
            'taxonomy'   => 'product_cat',
            'parent'     => $parent->term_id,
            'hide_empty' => true,
            'orderby'    => 'menu_order',
            'order'      => 'ASC',
        ]);

        $children_data = [];

        if (! is_wp_error($children)) {
            foreach ($children as $child) {
                // Level 3 items under each column heading.
                // Пункти рівня 3 під кожним заголовком колонки.
                $grandchildren = get_terms([
                    'taxonomy'   => 'product_cat',
                    'parent'     => $child->term_id,
                    'hide_empty' => true,
                    'orderby'    => 'menu_order',
                    'order'      => 'ASC',
                ]);

                $grandchildren_data = [];

                if (! is_wp_error($grandchildren)) {
                    foreach ($grandchildren as $grandchild) {
                        $grandchildren_data[] = [
                            'name' => $grandchild->name,
                            'url'  => (string) get_term_link($grandchild),
                        ];
                    }
                }

                $children_data[] = [
                    'name'     => $child->name,
                    'url'      => (string) get_term_link($child),
                    'children' => $grandchildren_data,
                ];
            }
        }

        $menu[] = [
            'id'       => (int) $parent->term_id,
            'name'     => $parent->name,
            'url'      => (string) get_term_link($parent),
            'children' => $children_data,
            'brands'   => solidshop_get_category_brands((int) $parent->term_id),
        ];
    }

    return $menu;
}

/**
 * Returns unique brands assigned to products within a category tree.
 * Повертає унікальні бренди товарів у дереві категорії.
 *
 * @param int $category_id Parent product_cat term ID / ID батьківської категорії.
 * @param int $max_brands  Brand cap for the bottom bar / Ліміт брендів для нижньої смуги.
 */
function solidshop_get_category_brands(int $category_id, int $max_brands = 12): array
{
    if (! taxonomy_exists('product_brand')) {
        return [];
    }

    // Lightweight ID-only query: no pagination count, no meta/term caches.
    // Полегшений запит лише за ID: без підрахунку пагінації та кешів.
    $product_ids = (new \WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => 500,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [
            [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $category_id,
                'include_children' => true,
            ],
        ],
    ]))->posts;

    if (empty($product_ids)) {
        return [];
    }

    $terms = wp_get_object_terms($product_ids, 'product_brand', [
        'orderby' => 'name',
        'order'   => 'ASC',
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    $brands = [];

    foreach ($terms as $term) {
        // wp_get_object_terms already returns unique terms, keep the cap only.
        // wp_get_object_terms вже повертає унікальні терміни, лишаємо тільки ліміт.
        if (count($brands) >= $max_brands) {
            break;
        }

        $brands[] = [
            // Decode stored entities so Blade escapes the name exactly once.
            // Декодуємо збережені ентіті, щоб Blade екранував ім'я рівно один раз.
            'name' => wp_specialchars_decode($term->name, ENT_QUOTES),
            'url'  => (string) get_term_link($term),
        ];
    }

    return $brands;
}

/**
 * Clears the cached menu whenever product categories change.
 * Скидає кеш меню при будь-яких змінах категорій товарів.
 */
function solidshop_flush_mega_menu_cache(): void
{
    delete_transient(SOLIDSHOP_MEGA_MENU_TRANSIENT);
}

add_action('edited_product_cat', __NAMESPACE__ . '\\solidshop_flush_mega_menu_cache');
add_action('create_product_cat', __NAMESPACE__ . '\\solidshop_flush_mega_menu_cache');
add_action('delete_product_cat', __NAMESPACE__ . '\\solidshop_flush_mega_menu_cache');
