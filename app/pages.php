<?php

/**
 * Auto-create required theme pages (contacts, about-us, blog).
 * Автоматично створює обов'язкові сторінки теми.
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

/**
 * Required static pages mapped to header/footer URLs.
 * Обов'язкові статичні сторінки для URL у шапці/футері.
 *
 * @return array<string, array{title: string, template?: string}>
 */
function solidshop_required_pages(): array
{
    return [
        'contacts' => [
            'title'    => 'Контакти',
            'template' => 'page-contacts.blade.php',
        ],
        'about-us' => [
            'title'    => 'Про нас',
            'template' => 'page-about-us.blade.php',
        ],
        'blog' => [
            'title' => 'Блог',
        ],
    ];
}

/**
 * Create or update a page; return page ID.
 * Створює або оновлює сторінку; повертає ID.
 */
function solidshop_ensure_page(string $slug, string $title, string $template = ''): int
{
    $existing = get_page_by_path($slug, OBJECT, 'page');

    if ($existing instanceof \WP_Post) {
        $page_id = (int) $existing->ID;

        if ($template !== '') {
            $current_template = (string) get_post_meta($page_id, '_wp_page_template', true);
            if ($current_template !== $template) {
                update_post_meta($page_id, '_wp_page_template', $template);
            }
        }

        if ($existing->post_status !== 'publish') {
            wp_update_post([
                'ID'          => $page_id,
                'post_status' => 'publish',
            ]);
        }

        return $page_id;
    }

    $page_id = wp_insert_post([
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ], true);

    if (is_wp_error($page_id)) {
        return 0;
    }

    $page_id = (int) $page_id;

    if ($template !== '') {
        update_post_meta($page_id, '_wp_page_template', $template);
    }

    return $page_id;
}

/**
 * Ensure all theme pages exist and blog is wired in Reading settings.
 * Перевіряє наявність сторінок і налаштовує блог у Reading.
 */
function solidshop_bootstrap_theme_pages(): void
{
    if (wp_installing() || ! function_exists('get_page_by_path')) {
        return;
    }

    $created = false;
    $blog_page_id = 0;

    foreach (solidshop_required_pages() as $slug => $config) {
        $before = get_page_by_path($slug, OBJECT, 'page');

        $page_id = solidshop_ensure_page(
            $slug,
            $config['title'],
            $config['template'] ?? ''
        );

        if (! $before instanceof \WP_Post && $page_id > 0) {
            $created = true;
        }

        if ($slug === 'blog' && $page_id > 0) {
            $blog_page_id = $page_id;
        }
    }

    if ($blog_page_id > 0 && ! get_option('page_for_posts')) {
        update_option('page_for_posts', $blog_page_id);
    }

    if ($created) {
        flush_rewrite_rules(false);
    }
}

add_action('init', __NAMESPACE__ . '\\solidshop_bootstrap_theme_pages', 20);

add_action('after_switch_theme', __NAMESPACE__ . '\\solidshop_bootstrap_theme_pages');
