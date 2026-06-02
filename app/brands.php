<?php

/**
 * Product brands taxonomy + front-page featured brands.
 * Таксономія брендів + featured-бренди на головній.
 *
 * @package App
 */

namespace App;

use WC_Product;

/** Brand taxonomy slug / Slug таксономії брендів */
const BRAND_TAXONOMY = 'product_brand';

/** Term meta: show brand on homepage / Meta: показувати бренд на головній */
const BRAND_FEATURED_ON_FRONT_META = '_solidshop_featured_on_front';

/** Transient: sales-based brand ranking / Кеш рейтингу брендів за продажами */
const BRAND_SALES_RANK_TRANSIENT = 'solidshop_brand_sales_rank';

/** Cache TTL (6 hours) / TTL кешу (6 годин) */
const BRAND_SALES_RANK_TTL = 6 * HOUR_IN_SECONDS;

/**
 * Register product_brand when no plugin provides it.
 * Реєстрація product_brand, якщо плагін ще не створив таксономію.
 */
add_action('init', function (): void {
    if (taxonomy_exists(BRAND_TAXONOMY)) {
        return;
    }

    register_taxonomy(BRAND_TAXONOMY, 'product', [
        'labels'            => [
            'name'          => __('Brands', 'solidshop'),
            'singular_name' => __('Brand', 'solidshop'),
            'menu_name'     => __('Brands', 'solidshop'),
        ],
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => [
            'slug'       => 'brand',
            'with_front' => false,
        ],
    ]);
}, 0);

/**
 * Register brand term meta (featured flag + thumbnail support).
 * Реєстрація term meta для брендів (featured + thumbnail).
 */
add_action('init', function (): void {
    if (! taxonomy_exists(BRAND_TAXONOMY)) {
        return;
    }

    register_term_meta(BRAND_TAXONOMY, BRAND_FEATURED_ON_FRONT_META, [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => static function ($value): string {
            return ($value === '1' || $value === true || $value === 1) ? '1' : '';
        },
    ]);

    register_term_meta(BRAND_TAXONOMY, 'thumbnail_id', [
        'type'              => 'integer',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => static function ($value): int {
            return max(0, (int) $value);
        },
    ]);
}, 11);

/**
 * Add-brand form: featured checkbox / Форма додавання: чекбокс featured.
 */
add_action('product_brand_add_form_fields', function (): void {
    ?>
    <div class="form-field">
        <label for="solidshop_brand_featured_on_front">
            <input
                type="checkbox"
                name="solidshop_brand_featured_on_front"
                id="solidshop_brand_featured_on_front"
                value="1"
            />
            <?php esc_html_e('Show on Front Page (Featured)', 'solidshop'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Display this brand in the Featured Brands block on the homepage.', 'solidshop'); ?>
        </p>
    </div>
    <?php
});

/**
 * Edit-brand form: featured checkbox / Форма редагування: чекбокс featured.
 *
 * @param \WP_Term $term Current brand term.
 */
add_action('product_brand_edit_form_fields', function (\WP_Term $term): void {
    $is_featured = get_term_meta($term->term_id, BRAND_FEATURED_ON_FRONT_META, true) === '1';
    $thumb_id    = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
    $thumb_url   = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';
    $placeholder = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '';
    ?>
    <tr class="form-field term-thumbnail-wrap">
        <th scope="row"><?php esc_html_e('Brand logo', 'solidshop'); ?></th>
        <td>
            <div id="solidshop_brand_thumbnail" style="float:left;margin-right:10px;">
                <img src="<?php echo esc_url($thumb_url ?: $placeholder); ?>" width="60" height="60" alt="" />
            </div>
            <input type="hidden" id="solidshop_brand_thumbnail_id" name="solidshop_brand_thumbnail_id" value="<?php echo esc_attr((string) $thumb_id); ?>" />
            <p>
                <button type="button" class="button solidshop-brand-upload"><?php esc_html_e('Upload / Add image', 'solidshop'); ?></button>
                <button type="button" class="button solidshop-brand-remove"><?php esc_html_e('Remove image', 'solidshop'); ?></button>
            </p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><?php esc_html_e('Front Page', 'solidshop'); ?></th>
        <td>
            <label for="solidshop_brand_featured_on_front">
                <input
                    type="checkbox"
                    name="solidshop_brand_featured_on_front"
                    id="solidshop_brand_featured_on_front"
                    value="1"
                    <?php checked($is_featured); ?>
                />
                <?php esc_html_e('Show on Front Page (Featured)', 'solidshop'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Display this brand in the Featured Brands block on the homepage.', 'solidshop'); ?>
            </p>
        </td>
    </tr>
    <?php
}, 10, 1);

/**
 * Media uploader for brand logo on edit screen.
 * Media uploader для логотипу бренду на екрані редагування.
 */
add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (! $screen || ($screen->taxonomy ?? '') !== BRAND_TAXONOMY) {
        return;
    }

    wp_enqueue_media();

    wp_add_inline_script('jquery', <<<'JS'
        jQuery(function ($) {
            var frame;
            var $input = $('#solidshop_brand_thumbnail_id');
            var $preview = $('#solidshop_brand_thumbnail img');

            $('.solidshop-brand-upload').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Brand logo',
                    button: { text: 'Use image' },
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $input.val(attachment.id);
                    $preview.attr('src', attachment.url);
                });
                frame.open();
            });

            $('.solidshop-brand-remove').on('click', function (e) {
                e.preventDefault();
                $input.val('');
                $preview.attr('src', $preview.data('placeholder') || '');
            });
        });
    JS);
});

/**
 * Save brand featured flag and thumbnail / Збереження featured та thumbnail бренду.
 *
 * @param int $term_id Term ID.
 */
function solidshop_save_product_brand_meta(int $term_id): void
{
    if (! current_user_can('manage_product_terms')) {
        return;
    }

    if (isset($_POST['solidshop_brand_featured_on_front']) && $_POST['solidshop_brand_featured_on_front'] === '1') {
        update_term_meta($term_id, BRAND_FEATURED_ON_FRONT_META, '1');
    } else {
        delete_term_meta($term_id, BRAND_FEATURED_ON_FRONT_META);
    }

    if (isset($_POST['solidshop_brand_thumbnail_id'])) {
        $thumb_id = (int) $_POST['solidshop_brand_thumbnail_id'];

        if ($thumb_id > 0) {
            update_term_meta($term_id, 'thumbnail_id', $thumb_id);
        } else {
            delete_term_meta($term_id, 'thumbnail_id');
        }
    }
}

add_action('created_product_brand', __NAMESPACE__ . '\\solidshop_save_product_brand_meta');
add_action('edited_product_brand', __NAMESPACE__ . '\\solidshop_save_product_brand_meta');

/**
 * Invalidate brand sales rank cache / Скинути кеш рейтингу брендів.
 */
function solidshop_invalidate_brand_sales_cache(): void
{
    delete_transient(BRAND_SALES_RANK_TRANSIENT);
}

add_action('woocommerce_update_product', __NAMESPACE__ . '\\solidshop_invalidate_brand_sales_cache');
add_action('woocommerce_new_order', __NAMESPACE__ . '\\solidshop_invalidate_brand_sales_cache');
add_action('woocommerce_order_status_completed', __NAMESPACE__ . '\\solidshop_invalidate_brand_sales_cache');

/**
 * Brand logo URL from known term meta keys.
 * URL логотипу бренду з відомих ключів term meta.
 */
function solidshop_get_brand_thumbnail_url(\WP_Term $term): string
{
    $meta_keys = ['thumbnail_id', 'brand_thumbnail_id', 'image'];

    foreach ($meta_keys as $key) {
        $attachment_id = (int) get_term_meta($term->term_id, $key, true);

        if ($attachment_id <= 0) {
            continue;
        }

        $url = wp_get_attachment_image_url($attachment_id, 'medium');

        if ($url) {
            return $url;
        }
    }

    return '';
}

/**
 * Brands ranked by aggregated product total_sales.
 * Бренди, відсортовані за сумою total_sales товарів.
 *
 * Step 2 fallback: top-selling products → extract product_brand terms → rank by sales.
 * Fallback крок 2: топ товари за продажами → product_brand → рейтинг за sales.
 *
 * @return \WP_Term[]
 */
function solidshop_get_brands_by_sales(int $limit = 12): array
{
    $cached = get_transient(BRAND_SALES_RANK_TRANSIENT);

    if (is_array($cached)) {
        return array_slice($cached, 0, $limit);
    }

    if (! function_exists('wc_get_products') || ! taxonomy_exists(BRAND_TAXONOMY)) {
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

        $brands = get_the_terms($product->get_id(), BRAND_TAXONOMY);

        if (! $brands || is_wp_error($brands)) {
            continue;
        }

        $attributed = [];

        foreach ($brands as $brand) {
            $attributed[(int) $brand->term_id] = true;
        }

        foreach (array_keys($attributed) as $brand_id) {
            $scores[$brand_id] = ($scores[$brand_id] ?? 0) + $sales;
        }
    }

    if ($scores === []) {
        set_transient(BRAND_SALES_RANK_TRANSIENT, [], BRAND_SALES_RANK_TTL);

        return [];
    }

    uksort($scores, static function (int|string $a, int|string $b) use ($scores): int {
        $sales_cmp = $scores[$b] <=> $scores[$a];

        return $sales_cmp !== 0 ? $sales_cmp : ((int) $a <=> (int) $b);
    });

    $ranked = [];

    foreach (array_keys($scores) as $term_id) {
        $term = get_term((int) $term_id, BRAND_TAXONOMY);

        if ($term instanceof \WP_Term && ! is_wp_error($term) && (int) $term->count > 0) {
            $ranked[] = $term;
        }
    }

    set_transient(BRAND_SALES_RANK_TRANSIENT, $ranked, BRAND_SALES_RANK_TTL);

    return array_slice($ranked, 0, $limit);
}

/**
 * Hybrid front-page brands: manual featured first, sales fallback to 6.
 * Гібрид: спочатку обрані вручну, потім fallback за продажами (до 6).
 *
 * Step 1: terms with "Show on Front Page" meta checked.
 * Крок 1: терміни з meta «Show on Front Page».
 *
 * Step 2: if count < 6, fill from solidshop_get_brands_by_sales() without duplicates.
 * Крок 2: якщо < 6, добираємо з продажів без дублікатів.
 *
 * @return \WP_Term[]
 */
function solidshop_get_front_page_brands(int $limit = 6): array
{
    if (! taxonomy_exists(BRAND_TAXONOMY)) {
        return [];
    }

    $featured = get_terms([
        'taxonomy'   => BRAND_TAXONOMY,
        'hide_empty' => true,
        'meta_query' => [[
            'key'     => BRAND_FEATURED_ON_FRONT_META,
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
        $by_sales = solidshop_get_brands_by_sales(max($limit * 2, 12));

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
