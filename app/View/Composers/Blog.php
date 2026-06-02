<?php

/**
 * Blog archive view composer — breadcrumbs, sidebar, pagination data.
 * View composer архіву блогу — breadcrumbs, sidebar, пагінація.
 *
 * @package App\View\Composers
 */

declare(strict_types=1);

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use WP_Term;

class Blog extends Composer
{
    /**
     * @var array<int, string>
     */
    protected static $views = [
        'index',
        'category',
        'tag',
        'single',
        'partials.blog-archive',
        'partials.content-blog-card',
        'partials.single-post',
        'partials.blog-mentioned-products',
    ];

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'breadcrumbItems'         => $this->breadcrumbItems(),
            'archiveSubtitle'         => $this->archiveSubtitle(),
            'sidebarCategories'       => $this->sidebarCategories(),
            'sidebarTags'             => $this->sidebarTags(),
            'sidebarArchives'         => $this->sidebarArchives(),
            'blogAuthorName'          => $this->blogAuthorName(),
            'blogAuthorBio'           => $this->blogAuthorBio(),
            'blogAuthorAvatar'        => $this->blogAuthorAvatar(),
            'sidebarProductCats'      => $this->sidebarProductCategories(),
            'sidebarFeaturedProducts' => $this->sidebarFeaturedProducts(),
            'blogPagination'          => $this->blogPagination(),
            'postFormattedDate'       => self::formattedPostDate(),
            'postAuthorName'          => $this->postAuthorName(),
            'postAuthorBio'           => $this->postAuthorBio(),
            'postAuthorAvatar'        => $this->postAuthorAvatar(),
            'postAuthorUrl'           => $this->postAuthorUrl(),
            'postCategories'          => $this->postCategories(),
            'postTags'                => $this->postTags(),
            'mentionedProducts'       => $this->mentionedProducts(),
        ];
    }

    /**
     * Ukrainian uppercase archive date, e.g. 14 ТРАВНЯ 2024 РОКУ.
     * Українська дата архіву uppercase.
     */
    public static function formattedPostDate(?int $postId = null): string
    {
        $postId = $postId ?? get_the_ID();
        $timestamp = get_post_time('U', true, $postId);

        if (! $timestamp) {
            return '';
        }

        $formatted = wp_date('j F Y', $timestamp);

        return mb_strtoupper($formatted . ' ' . __('року', 'solidshop'), 'UTF-8');
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    private function breadcrumbItems(): array
    {
        $items = [
            [
                'label' => __('Головна', 'solidshop'),
                'url'   => home_url('/'),
            ],
        ];

        $blogUrl = $this->postsPageUrl();

        if (is_singular('post')) {
            if ($blogUrl !== '') {
                $items[] = [
                    'label' => $this->postsPageTitle(),
                    'url'   => $blogUrl,
                ];
            }

            $items[] = [
                'label' => get_the_title(),
                'url'   => null,
            ];

            return $items;
        }

        if (is_home()) {
            $items[] = [
                'label' => $this->archiveLabel(),
                'url'   => null,
            ];

            return $items;
        }

        if ($blogUrl !== '') {
            $items[] = [
                'label' => $this->postsPageTitle(),
                'url'   => $blogUrl,
            ];
        }

        $items[] = [
            'label' => $this->currentArchiveLabel(),
            'url'   => null,
        ];

        return $items;
    }

    private function archiveSubtitle(): string
    {
        if (! is_home()) {
            return '';
        }

        $postsPageId = (int) get_option('page_for_posts', 0);

        if ($postsPageId <= 0) {
            return '';
        }

        $excerpt = get_the_excerpt($postsPageId);

        return is_string($excerpt) ? trim($excerpt) : '';
    }

    /**
     * @return array<int, array{name: string, url: string, count: int}>
     */
    private function sidebarCategories(): array
    {
        $terms = get_categories([
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (! is_array($terms)) {
            return [];
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $items[] = [
                'name'  => $term->name,
                'url'   => get_category_link($term->term_id),
                'count' => (int) $term->count,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, WP_Term>
     */
    private function sidebarTags(): array
    {
        $tags = get_tags([
            'hide_empty' => true,
            'number'     => 20,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        return is_array($tags) ? $tags : [];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function sidebarArchives(): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT YEAR(post_date) AS year, MONTH(post_date) AS month
             FROM {$wpdb->posts}
             WHERE post_type = 'post' AND post_status = 'publish'
             GROUP BY YEAR(post_date), MONTH(post_date)
             ORDER BY year DESC, month DESC
             LIMIT 6"
        );

        if (! is_array($rows)) {
            return [];
        }

        $items = [];

        foreach ($rows as $row) {
            $year  = (int) ($row->year ?? 0);
            $month = (int) ($row->month ?? 0);

            if ($year <= 0 || $month <= 0) {
                continue;
            }

            $timestamp = mktime(0, 0, 0, $month, 1, $year);
            $label     = wp_date('F Y', $timestamp);

            $items[] = [
                'label' => mb_strtoupper($label, 'UTF-8'),
                'url'   => get_month_link($year, $month),
            ];
        }

        return $items;
    }

    private function blogAuthorName(): string
    {
        $userId = $this->blogAuthorUserId();
        $name   = get_the_author_meta('display_name', $userId);

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return get_bloginfo('name') ?: __('SolidShop', 'solidshop');
    }

    private function blogAuthorBio(): string
    {
        $description = get_bloginfo('description');

        if (is_string($description) && trim($description) !== '') {
            return trim($description);
        }

        return __('Новини, поради та оновлення для B2B-клієнтів.', 'solidshop');
    }

    private function blogAuthorAvatar(): string
    {
        $userId = $this->blogAuthorUserId();
        $avatar = get_avatar_url($userId, ['size' => 120]);

        return is_string($avatar) ? $avatar : '';
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function sidebarProductCategories(): array
    {
        if (! taxonomy_exists('product_cat')) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'number'     => 6,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        if (is_wp_error($terms) || ! is_array($terms)) {
            return [];
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $items[] = [
                'name' => $term->name,
                'url'  => get_term_link($term),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, price_html: string, image: string}>
     */
    private function sidebarFeaturedProducts(): array
    {
        if (! function_exists('wc_get_products')) {
            return [];
        }

        $products = wc_get_products([
            'status'   => 'publish',
            'featured' => true,
            'limit'    => 3,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ]);

        if ($products === []) {
            $products = wc_get_products([
                'status'  => 'publish',
                'limit'   => 3,
                'orderby' => 'date',
                'order'   => 'DESC',
            ]);
        }

        $items = [];

        foreach ($products as $product) {
            if (! is_object($product) || ! method_exists($product, 'get_id')) {
                continue;
            }

            $imageId = $product->get_image_id();
            $image   = $imageId ? (string) wp_get_attachment_image_url($imageId, 'woocommerce_thumbnail') : '';

            $items[] = [
                'id'         => (int) $product->get_id(),
                'name'       => $product->get_name(),
                'url'        => $product->get_permalink(),
                'price_html' => $product->get_price_html(),
                'image'      => $image,
            ];
        }

        return $items;
    }

    private function blogPagination(): string
    {
        global $wp_query;

        $total = (int) $wp_query->max_num_pages;

        if ($total <= 1) {
            return '';
        }

        $links = paginate_links([
            'total'     => $total,
            'current'   => max(1, (int) get_query_var('paged')),
            'type'      => 'list',
            'prev_next' => true,
            'prev_text' => '',
            'next_text' => '&rsaquo;',
            'mid_size'  => 2,
            'end_size'  => 1,
        ]);

        return is_string($links) ? $links : '';
    }

    private function blogAuthorUserId(): int
    {
        $users = get_users([
            'role'    => 'administrator',
            'number'  => 1,
            'orderby' => 'ID',
            'order'   => 'ASC',
            'fields'  => 'ID',
        ]);

        if (is_array($users) && isset($users[0])) {
            return (int) $users[0];
        }

        return 1;
    }

    private function postsPageUrl(): string
    {
        $postsPageId = (int) get_option('page_for_posts', 0);

        if ($postsPageId <= 0) {
            return home_url('/blog/');
        }

        $url = get_permalink($postsPageId);

        return is_string($url) ? $url : home_url('/blog/');
    }

    private function postsPageTitle(): string
    {
        $postsPageId = (int) get_option('page_for_posts', 0);

        if ($postsPageId <= 0) {
            return __('Блог', 'solidshop');
        }

        $title = get_the_title($postsPageId);

        return is_string($title) && $title !== '' ? $title : __('Блог', 'solidshop');
    }

    private function archiveLabel(): string
    {
        return $this->postsPageTitle();
    }

    private function currentArchiveLabel(): string
    {
        if (is_category()) {
            $title = single_cat_title('', false);

            return is_string($title) ? $title : __('Категорія', 'solidshop');
        }

        if (is_tag()) {
            $title = single_tag_title('', false);

            return is_string($title) ? $title : __('Тег', 'solidshop');
        }

        if (is_archive()) {
            return wp_strip_all_tags(get_the_archive_title());
        }

        return $this->postsPageTitle();
    }

    private function postAuthorName(): string
    {
        $name = get_the_author();

        return is_string($name) && $name !== '' ? $name : '';
    }

    private function postAuthorBio(): string
    {
        $authorId = (int) get_the_author_meta('ID');
        $bio      = get_the_author_meta('description', $authorId);

        return is_string($bio) ? trim($bio) : '';
    }

    private function postAuthorAvatar(): string
    {
        $authorId = (int) get_the_author_meta('ID');
        $avatar   = get_avatar_url($authorId, ['size' => 96]);

        return is_string($avatar) ? $avatar : '';
    }

    private function postAuthorUrl(): string
    {
        $authorId = (int) get_the_author_meta('ID');
        $url      = get_author_posts_url($authorId);

        return is_string($url) ? $url : '';
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function postCategories(): array
    {
        $terms = get_the_category();

        if (! is_array($terms)) {
            return [];
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $items[] = [
                'name' => $term->name,
                'url'  => get_category_link($term->term_id),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function postTags(): array
    {
        $terms = get_the_tags();

        if (! is_array($terms)) {
            return [];
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $items[] = [
                'name' => $term->name,
                'url'  => get_tag_link($term->term_id),
            ];
        }

        return $items;
    }

    /**
     * Products mentioned in the post — ACF relationship or featured fallback.
     * Товари з ACF або featured fallback.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     url: string,
     *     price_html: string,
     *     image: string,
     *     category: string,
     *     rating_html: string,
     *     on_sale: bool
     * }>
     */
    private function mentionedProducts(): array
    {
        if (! function_exists('wc_get_product')) {
            return [];
        }

        $productIds = $this->mentionedProductIds();

        if ($productIds === []) {
            return [];
        }

        $items = [];

        foreach ($productIds as $productId) {
            $product = wc_get_product($productId);

            if (! is_object($product) || ! method_exists($product, 'get_id')) {
                continue;
            }

            $mapped = $this->mapProductCard($product);

            if ($mapped !== null) {
                $items[] = $mapped;
            }
        }

        return $items;
    }

    /**
     * @return array<int, int>
     */
    private function mentionedProductIds(): array
    {
        if (! function_exists('get_field')) {
            return [];
        }

        $related = get_field('mentioned_products', get_the_ID());

        if (! is_array($related) || $related === []) {
            return [];
        }

        $ids = [];

        foreach ($related as $item) {
            if ($item instanceof \WP_Post) {
                $ids[] = (int) $item->ID;
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     url: string,
     *     price_html: string,
     *     image: string,
     *     category: string,
     *     rating_html: string,
     *     on_sale: bool
     * }|null
     */
    private function mapProductCard(object $product): ?array
    {
        if (! method_exists($product, 'get_id')) {
            return null;
        }

        $imageId = $product->get_image_id();
        $image   = $imageId ? (string) wp_get_attachment_image_url($imageId, 'woocommerce_thumbnail') : '';

        $category = function_exists('App\solidshop_loop_product_category')
            ? \App\solidshop_loop_product_category($product)
            : '';

        $ratingHtml = '';

        if (function_exists('wc_get_rating_html') && (int) $product->get_rating_count() > 0) {
            $ratingHtml = wc_get_rating_html(
                (float) $product->get_average_rating(),
                (int) $product->get_rating_count()
            );
            $ratingHtml = is_string($ratingHtml) ? $ratingHtml : '';
        }

        return [
            'id'          => (int) $product->get_id(),
            'name'        => $product->get_name(),
            'url'         => $product->get_permalink(),
            'price_html'  => $product->get_price_html(),
            'image'       => $image,
            'category'    => is_string($category) ? $category : '',
            'rating_html' => $ratingHtml,
            'on_sale'     => (bool) $product->is_on_sale(),
        ];
    }
}
