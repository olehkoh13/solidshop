<?php

/**
 * Frontend performance: lean head, no bloat CSS/JS, conditional cart fragments.
 * Продуктивність фронтенду: чистий head, без зайвих CSS/JS, умовні cart fragments.
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Whether WooCommerce cart-fragments script should load on this request.
 * Чи потрібно завантажувати wc-cart-fragments на цьому запиті.
 */
function solidshop_should_load_cart_fragments(): bool
{
    if (! function_exists('is_woocommerce') || ! class_exists('WooCommerce')) {
        return false;
    }

    if (is_woocommerce() || is_cart() || is_checkout()) {
        return true;
    }

    // Wishlist page needs live cart badge / mini-cart updates.
    // Сторінка wishlist потребує оновлення лічильника кошика.
    if (function_exists('is_page_template') && is_page_template('template-wishlist.blade.php')) {
        return true;
    }

    return false;
}

/**
 * Clean WP head: emoji, RSD, WLW, generator, REST discovery link.
 * Очищення head: emoji, RSD, WLW, generator, REST discovery link.
 */
add_action('init', function (): void {
    // Emoji / Emoji
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    // Security leaks & bloat / Зайве в head
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rest_output_link_wp_head');
});

/**
 * Dequeue Gutenberg + WooCommerce block CSS (Tailwind-only frontend).
 * Відключити CSS блоків Gutenberg і WC (фронтенд лише на Tailwind).
 */
add_action('wp_enqueue_scripts', function (): void {
    $handles = [
        'wp-block-library',
        'wp-block-library-theme',
        'wc-blocks-style',
    ];

    foreach ($handles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}, 100);

/**
 * Disable jQuery Migrate on the public site.
 * Вимкнути jQuery Migrate на публічній частині сайту.
 */
add_action('wp_default_scripts', function (\WP_Scripts $scripts): void {
    if (is_admin()) {
        return;
    }

    if (isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_values(
            array_diff($scripts->registered['jquery']->deps, ['jquery-migrate'])
        );
    }

    $scripts->remove('jquery-migrate');
}, 20);

/**
 * Dequeue wc-cart-fragments on static pages (skip heavy AJAX).
 * Вимкнути wc-cart-fragments на статичних сторінках (без зайвого AJAX).
 */
add_action('wp_enqueue_scripts', function (): void {
    if (solidshop_should_load_cart_fragments()) {
        return;
    }

    wp_dequeue_script('wc-cart-fragments');
    wp_deregister_script('wc-cart-fragments');
}, 99);
