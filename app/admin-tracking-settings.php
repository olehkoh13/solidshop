<?php

/**
 * Native Settings API page for SolidShop tracking credentials.
 * Нативна сторінка Settings API для credentials трекінгу SolidShop.
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/** Settings group / Група налаштувань */
const SOLIDSHOP_TRACKING_SETTINGS_GROUP = 'solidshop_tracking';

/**
 * Option keys stored in wp_options.
 * Ключі опцій у wp_options.
 *
 * @return list<string>
 */
function solidshop_tracking_option_keys(): array
{
    return [
        'ss_fb_pixel_id',
        'ss_fb_capi_token',
        'ss_ga4_measurement_id',
        'ss_ga4_api_secret',
    ];
}

/**
 * Register admin menu under Settings.
 * Реєстрація меню в Налаштуваннях.
 */
add_action('admin_menu', function (): void {
    add_options_page(
        __('SolidShop Tracking', 'solidshop'),
        __('SolidShop Tracking', 'solidshop'),
        'manage_options',
        'solidshop-tracking',
        __NAMESPACE__ . '\\solidshop_render_tracking_settings_page'
    );
});

/**
 * Register settings, sections, and fields.
 * Реєстрація settings, sections та fields.
 */
add_action('admin_init', function (): void {
    foreach (solidshop_tracking_option_keys() as $option) {
        register_setting(
            SOLIDSHOP_TRACKING_SETTINGS_GROUP,
            $option,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );
    }

    add_settings_section(
        'solidshop_tracking_facebook',
        __('Facebook', 'solidshop'),
        '__return_false',
        'solidshop-tracking'
    );

    add_settings_field(
        'ss_fb_pixel_id',
        __('FB Pixel ID', 'solidshop'),
        __NAMESPACE__ . '\\solidshop_tracking_field_callback',
        'solidshop-tracking',
        'solidshop_tracking_facebook',
        ['option' => 'ss_fb_pixel_id', 'type' => 'text']
    );

    add_settings_field(
        'ss_fb_capi_token',
        __('FB CAPI Token', 'solidshop'),
        __NAMESPACE__ . '\\solidshop_tracking_field_callback',
        'solidshop-tracking',
        'solidshop_tracking_facebook',
        ['option' => 'ss_fb_capi_token', 'type' => 'password']
    );

    add_settings_section(
        'solidshop_tracking_ga4',
        __('Google Analytics 4', 'solidshop'),
        '__return_false',
        'solidshop-tracking'
    );

    add_settings_field(
        'ss_ga4_measurement_id',
        __('GA4 Measurement ID', 'solidshop'),
        __NAMESPACE__ . '\\solidshop_tracking_field_callback',
        'solidshop-tracking',
        'solidshop_tracking_ga4',
        ['option' => 'ss_ga4_measurement_id', 'type' => 'text']
    );

    add_settings_field(
        'ss_ga4_api_secret',
        __('GA4 API Secret', 'solidshop'),
        __NAMESPACE__ . '\\solidshop_tracking_field_callback',
        'solidshop-tracking',
        'solidshop_tracking_ga4',
        ['option' => 'ss_ga4_api_secret', 'type' => 'password']
    );
});

/**
 * Render a settings field input.
 * Рендер поля налаштувань.
 *
 * @param array{option: string, type: string} $args
 */
function solidshop_tracking_field_callback(array $args): void
{
    $option = $args['option'] ?? '';
    $type = $args['type'] ?? 'text';
    $value = esc_attr((string) get_option($option, ''));

    printf(
        '<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text" autocomplete="off" />',
        esc_attr($type),
        esc_attr($option),
        $value
    );
}

/**
 * Settings page markup.
 * Розмітка сторінки налаштувань.
 */
function solidshop_render_tracking_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('SolidShop Tracking', 'solidshop'); ?></h1>
        <p><?php echo esc_html__('Server-side Facebook CAPI and GA4 Measurement Protocol. No third-party tracking plugins required.', 'solidshop'); ?></p>
        <p><em><?php echo esc_html__('Серверний Facebook CAPI та GA4 Measurement Protocol. Сторонні плагіни трекінгу не потрібні.', 'solidshop'); ?></em></p>
        <form action="options.php" method="post">
            <?php
            settings_fields(SOLIDSHOP_TRACKING_SETTINGS_GROUP);
            do_settings_sections('solidshop-tracking');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
