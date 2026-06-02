<?php

/**
 * ACF field group for Contact page templates.
 * ACF field group для шаблонів сторінки контактів.
 *
 * @package App\Fields
 */

declare(strict_types=1);

namespace App\Fields;

class ContactPage
{
    /**
     * Register local ACF field group for Contact templates.
     * Реєструє локальну ACF групу для contact-шаблонів.
     */
    public static function register(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key'                   => 'group_solidshop_contact_page',
            'title'                 => __('SolidShop — Contact Page', 'solidshop'),
            'fields'                => self::fields(),
            'location'              => [
                [
                    [
                        'param'    => 'page_template',
                        'operator' => '==',
                        'value'    => 'page-contact.blade.php',
                    ],
                ],
                [
                    [
                        'param'    => 'page_template',
                        'operator' => '==',
                        'value'    => 'page-contacts.blade.php',
                    ],
                ],
            ],
            'menu_order'            => 0,
            'position'              => 'normal',
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
                'key'   => 'field_solidshop_contact_tab_contacts',
                'label' => __('Контакти', 'solidshop'),
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_solidshop_contact_phone',
                'label'         => __('Телефон', 'solidshop'),
                'name'          => 'contact_phone',
                'type'          => 'text',
                'placeholder'   => '+38 (050) 123-45-67',
                'default_value' => '+38 (050) 123-45-67',
            ],
            [
                'key'           => 'field_solidshop_contact_email',
                'label'         => __('Email', 'solidshop'),
                'name'          => 'contact_email',
                'type'          => 'email',
                'placeholder'   => 'support@solidwebcraft.com',
                'default_value' => 'support@solidwebcraft.com',
            ],
            [
                'key'   => 'field_solidshop_contact_tab_location',
                'label' => __('Локація', 'solidshop'),
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_solidshop_location_street',
                'label'         => __('Вулиця', 'solidshop'),
                'name'          => 'location_street',
                'type'          => 'text',
                'placeholder'   => __('вул. Незалежності, 1', 'solidshop'),
            ],
            [
                'key'           => 'field_solidshop_location_city',
                'label'         => __('Місто', 'solidshop'),
                'name'          => 'location_city',
                'type'          => 'text',
                'placeholder'   => __('Івано-Франківськ', 'solidshop'),
                'default_value' => 'Івано-Франківськ',
            ],
            [
                'key'         => 'field_solidshop_location_zip',
                'label'       => __('Поштовий індекс', 'solidshop'),
                'name'        => 'location_zip',
                'type'        => 'text',
                'placeholder' => '76000',
            ],
            [
                'key'           => 'field_solidshop_location_country',
                'label'         => __('Країна (ISO)', 'solidshop'),
                'name'          => 'location_country',
                'type'          => 'text',
                'instructions'  => __('Двобуквений код, напр. UA', 'solidshop'),
                'placeholder'   => 'UA',
                'default_value' => 'UA',
            ],
            [
                'key'   => 'field_solidshop_contact_tab_hours',
                'label' => __('Години роботи', 'solidshop'),
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_solidshop_working_hours',
                'label'        => __('Графік роботи', 'solidshop'),
                'name'         => 'working_hours',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => __('Додати рядок', 'solidshop'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_solidshop_working_hours_days',
                        'label'         => __('Дні', 'solidshop'),
                        'name'          => 'days',
                        'type'          => 'text',
                        'placeholder'   => 'Пн-Пт',
                        'default_value' => 'Пн-Пт',
                    ],
                    [
                        'key'           => 'field_solidshop_working_hours_open',
                        'label'         => __('Відкриття', 'solidshop'),
                        'name'          => 'open_time',
                        'type'          => 'text',
                        'placeholder'   => '09:00',
                        'default_value' => '09:00',
                    ],
                    [
                        'key'           => 'field_solidshop_working_hours_close',
                        'label'         => __('Закриття', 'solidshop'),
                        'name'          => 'close_time',
                        'type'          => 'text',
                        'placeholder'   => '18:00',
                        'default_value' => '18:00',
                    ],
                ],
            ],
            [
                'key'   => 'field_solidshop_contact_tab_faq',
                'label' => __('FAQ', 'solidshop'),
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_solidshop_faq_items',
                'label'        => __('Часті запитання', 'solidshop'),
                'name'         => 'faq_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 0,
                'button_label' => __('Додати питання', 'solidshop'),
                'sub_fields'   => [
                    [
                        'key'   => 'field_solidshop_faq_question',
                        'label' => __('Питання', 'solidshop'),
                        'name'  => 'question',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_solidshop_faq_answer',
                        'label' => __('Відповідь', 'solidshop'),
                        'name'  => 'answer',
                        'type'  => 'textarea',
                        'rows'  => 4,
                    ],
                    [
                        'key'           => 'field_solidshop_faq_is_open',
                        'label'         => __('Відкрито за замовчуванням', 'solidshop'),
                        'name'          => 'is_open',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'default_value' => 0,
                    ],
                ],
            ],
            [
                'key'   => 'field_solidshop_contact_tab_map',
                'label' => __('Карта', 'solidshop'),
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_solidshop_map_iframe',
                'label'        => __('Google Maps embed', 'solidshop'),
                'name'         => 'map_iframe',
                'type'         => 'textarea',
                'instructions' => __('Вставте код embed (<iframe>) з Google Maps.', 'solidshop'),
                'rows'         => 4,
            ],
            [
                'key'         => 'field_solidshop_geo_latitude',
                'label'       => __('Широта (geo)', 'solidshop'),
                'name'        => 'geo_latitude',
                'type'        => 'number',
                'step'        => 'any',
                'placeholder' => '48.9226',
            ],
            [
                'key'         => 'field_solidshop_geo_longitude',
                'label'       => __('Довгота (geo)', 'solidshop'),
                'name'        => 'geo_longitude',
                'type'        => 'number',
                'step'        => 'any',
                'placeholder' => '24.7111',
            ],
        ];
    }

    /**
     * Default FAQ rows shown when field is empty.
     * Дефолтні FAQ-рядки, якщо поле порожнє.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultFaqItems(): array
    {
        return [
            [
                'question' => __('Скільки часу мені знадобиться, щоб отримати своє замовлення?', 'solidshop'),
                'answer'   => __('Стандартна доставка по Україні займає 1–3 робочі дні. Після відправлення ви отримаєте email з номером відстеження, щоб контролювати статус замовлення.', 'solidshop'),
                'is_open'  => 1,
            ],
            [
                'question' => __('Яка ваша політика повернення та обміну?', 'solidshop'),
                'answer'   => __('Ви можете повернути або обміняти товар протягом 14 днів з моменту отримання, якщо збережено товарний вигляд та упаковку. Для B2B-клієнтів діють окремі умови — звʼяжіться з менеджером.', 'solidshop'),
                'is_open'  => 0,
            ],
            [
                'question' => __('Як мені змінити адресу доставки?', 'solidshop'),
                'answer'   => __('Якщо замовлення ще не передано в службу доставки, напишіть нам через форму або на support@solidwebcraft.com — ми оновимо адресу вручну.', 'solidshop'),
                'is_open'  => 0,
            ],
        ];
    }

    /**
     * Pre-fill FAQ repeater in admin and on front when empty.
     * Заповнює FAQ repeater в адмінці та на фронті, якщо порожньо.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function loadDefaultFaqItems($value)
    {
        if ($value !== null && $value !== false && $value !== '' && $value !== []) {
            return $value;
        }

        return self::defaultFaqItems();
    }
}

add_action('acf/init', [ContactPage::class, 'register']);
add_filter('acf/load_value/name=faq_items', [ContactPage::class, 'loadDefaultFaqItems']);
