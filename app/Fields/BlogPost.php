<?php

/**
 * ACF field group for blog posts — mentioned products.
 * ACF field group для записів блогу — згадані товари.
 *
 * @package App\Fields
 */

declare(strict_types=1);

namespace App\Fields;

class BlogPost
{
    /**
     * Register local ACF field group for posts.
     * Реєструє локальну ACF групу для post.
     */
    public static function register(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        if (! function_exists('wc_get_product')) {
            return;
        }

        acf_add_local_field_group([
            'key'                   => 'group_solidshop_blog_post',
            'title'                 => __('SolidShop — Blog Post', 'solidshop'),
            'fields'                => self::fields(),
            'location'              => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'post',
                    ],
                ],
            ],
            'menu_order'            => 5,
            'position'              => 'side',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function fields(): array
    {
        return [
            [
                'key'           => 'field_solidshop_post_mentioned_products',
                'label'         => __('Згадані товари', 'solidshop'),
                'name'          => 'mentioned_products',
                'type'          => 'relationship',
                'instructions'  => __('До 3 товарів для блоку «Згадано в цій статті». Якщо порожньо — показуються featured товари.', 'solidshop'),
                'post_type'     => ['product'],
                'filters'       => ['search'],
                'elements'      => ['featured_image'],
                'min'           => 0,
                'max'           => 3,
                'return_format' => 'id',
            ],
        ];
    }
}

add_action('acf/init', [BlogPost::class, 'register']);
