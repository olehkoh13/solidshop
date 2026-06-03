<?php

/**
 * Dynamic typography: local font uploads, admin settings, CSS variables.
 * Динамічна типографіка: локальні шрифти, налаштування, CSS-змінні.
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/** Settings group / Група налаштувань */
const SOLIDSHOP_TYPOGRAPHY_GROUP = 'solidshop_typography';

/**
 * Typography module bootstrap.
 * Ініціалізація модуля типографії.
 */
final class Typography
{
    public static function boot(): void
    {
        add_filter('upload_mimes', [self::class, 'allowFontMimeTypes']);

        if (is_admin()) {
            add_action('admin_menu', [self::class, 'registerAdminPage']);
            add_action('admin_init', [self::class, 'registerSettings']);
            add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        }

        add_action('wp_head', [self::class, 'renderFrontendTypography'], 5);
    }

    /**
     * Allow .woff and .woff2 uploads.
     * Дозволити завантаження .woff та .woff2.
     *
     * @param array<string, string> $mimes
     * @return array<string, string>
     */
    public static function allowFontMimeTypes(array $mimes): array
    {
        $mimes['woff']  = 'font/woff';
        $mimes['woff2'] = 'font/woff2';

        return $mimes;
    }

    /**
     * Submenu under Appearance.
     * Підменю в розділі "Вигляд".
     */
    public static function registerAdminPage(): void
    {
        add_theme_page(
            __('SolidShop Typography', 'solidshop'),
            __('SolidShop Typography', 'solidshop'),
            'manage_options',
            'solidshop-typography',
            [self::class, 'renderSettingsPage']
        );
    }

    /**
     * Register options and fields.
     * Реєстрація опцій та полів.
     */
    public static function registerSettings(): void
    {
        $stringOptions = [
            'ss_font_heading_name',
            'ss_font_heading_url',
            'ss_font_body_name',
            'ss_font_body_url',
        ];

        foreach ($stringOptions as $option) {
            register_setting(
                SOLIDSHOP_TYPOGRAPHY_GROUP,
                $option,
                [
                    'type'              => 'string',
                    'sanitize_callback' => static function (mixed $value) use ($option): string {
                        $value = is_string($value) ? $value : '';

                        if (str_ends_with($option, '_url')) {
                            return esc_url_raw(trim($value));
                        }

                        return sanitize_text_field($value);
                    },
                    'default'           => '',
                ]
            );
        }

        register_setting(
            SOLIDSHOP_TYPOGRAPHY_GROUP,
            'ss_font_preload',
            [
                'type'              => 'string',
                'sanitize_callback' => static function (mixed $value): string {
                    return ! empty($value) ? '1' : '';
                },
                'default'           => '',
            ]
        );

        add_settings_section(
            'solidshop_typography_fonts',
            __('Custom fonts', 'solidshop'),
            '__return_false',
            'solidshop-typography'
        );

        add_settings_field(
            'ss_font_heading_name',
            __('Heading font name', 'solidshop'),
            [self::class, 'renderTextField'],
            'solidshop-typography',
            'solidshop_typography_fonts',
            ['option' => 'ss_font_heading_name', 'description' => __('CSS font-family name, e.g. Inter', 'solidshop')]
        );

        add_settings_field(
            'ss_font_heading_url',
            __('Heading font file (WOFF/WOFF2)', 'solidshop'),
            [self::class, 'renderFontUrlField'],
            'solidshop-typography',
            'solidshop_typography_fonts',
            ['option' => 'ss_font_heading_url']
        );

        add_settings_field(
            'ss_font_body_name',
            __('Body font name', 'solidshop'),
            [self::class, 'renderTextField'],
            'solidshop-typography',
            'solidshop_typography_fonts',
            ['option' => 'ss_font_body_name', 'description' => __('CSS font-family name, e.g. Inter', 'solidshop')]
        );

        add_settings_field(
            'ss_font_body_url',
            __('Body font file (WOFF/WOFF2)', 'solidshop'),
            [self::class, 'renderFontUrlField'],
            'solidshop-typography',
            'solidshop_typography_fonts',
            ['option' => 'ss_font_body_url']
        );

        add_settings_field(
            'ss_font_preload',
            __('Font preload', 'solidshop'),
            [self::class, 'renderPreloadField'],
            'solidshop-typography',
            'solidshop_typography_fonts'
        );
    }

    /**
     * Media Library on typography screen only.
     * Media Library лише на сторінці типографії.
     */
    public static function enqueueAdminAssets(string $hook): void
    {
        if ($hook !== 'appearance_page_solidshop-typography') {
            return;
        }

        wp_enqueue_media();

        wp_register_script('solidshop-typography-admin', '', [], false, true);
        wp_enqueue_script('solidshop-typography-admin');

        wp_add_inline_script(
            'solidshop-typography-admin',
            <<<'JS'
jQuery(function ($) {
  $(document).on('click', '.ss-typography-upload', function (e) {
    e.preventDefault();
    var targetId = $(this).data('target');
    var $input = $('#' + targetId);
    var frame = wp.media({
      title: 'Select font file',
      button: { text: 'Use this font' },
      library: { type: '' },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      if (attachment && attachment.url) {
        $input.val(attachment.url).trigger('change');
      }
    });
    frame.open();
  });
});
JS
        );
    }

    /**
     * @param array{option: string, description?: string} $args
     */
    public static function renderTextField(array $args): void
    {
        $option = $args['option'] ?? '';
        $value  = esc_attr((string) get_option($option, ''));

        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
            esc_attr($option),
            $value
        );

        if (! empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html((string) $args['description']));
        }
    }

    /**
     * URL field with Media Library button.
     * Поле URL з кнопкою Media Library.
     *
     * @param array{option: string} $args
     */
    public static function renderFontUrlField(array $args): void
    {
        $option = $args['option'] ?? '';
        $value  = esc_attr((string) get_option($option, ''));

        printf(
            '<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text ss-typography-font-url" autocomplete="off" />',
            esc_attr($option),
            $value
        );
        printf(
            ' <button type="button" class="button ss-typography-upload" data-target="%1$s">%2$s</button>',
            esc_attr($option),
            esc_html__('Upload font', 'solidshop')
        );
        echo '<p class="description">' . esc_html__('Upload a .woff or .woff2 file from the Media Library.', 'solidshop') . '</p>';
    }

    public static function renderPreloadField(): void
    {
        $checked = (string) get_option('ss_font_preload', '') === '1';

        printf(
            '<label><input type="checkbox" name="ss_font_preload" value="1" %s /> %s</label>',
            checked($checked, true, false),
            esc_html__('Попереднє завантаження користувацьких шрифтів', 'solidshop')
        );
        echo '<p class="description">' . esc_html__(
            'Adds rel=preload for uploaded fonts to reduce Flash of Unstyled Text (FOUT). Recommended when using custom webfonts.',
            'solidshop'
        ) . '</p>';
        echo '<p class="description"><em>' . esc_html__(
            'Додає rel=preload для завантажених шрифтів, щоб зменшити FOUT. Рекомендовано для кастомних веб-шрифтів.',
            'solidshop'
        ) . '</em></p>';
    }

    public static function renderSettingsPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('SolidShop Typography', 'solidshop'); ?></h1>
            <p><?php echo esc_html__('Upload local fonts and expose CSS variables for Tailwind (--ss-font-heading, --ss-font-body).', 'solidshop'); ?></p>
            <p><em><?php echo esc_html__('Завантажте локальні шрифти та CSS-змінні для Tailwind (--ss-font-heading, --ss-font-body).', 'solidshop'); ?></em></p>
            <form action="options.php" method="post">
                <?php
                settings_fields(SOLIDSHOP_TYPOGRAPHY_GROUP);
                do_settings_sections('solidshop-typography');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Preload links and dynamic @font-face + :root vars.
     * Preload та динамічні @font-face і :root.
     */
    public static function renderFrontendTypography(): void
    {
        $headingName = sanitize_text_field((string) get_option('ss_font_heading_name', ''));
        $headingUrl  = esc_url((string) get_option('ss_font_heading_url', ''));
        $bodyName    = sanitize_text_field((string) get_option('ss_font_body_name', ''));
        $bodyUrl     = esc_url((string) get_option('ss_font_body_url', ''));
        $preload     = (string) get_option('ss_font_preload', '') === '1';

        $pairs = [];

        if ($headingName !== '' && $headingUrl !== '') {
            $pairs[] = ['name' => $headingName, 'url' => $headingUrl];
        }

        if ($bodyName !== '' && $bodyUrl !== '') {
            $pairs[] = ['name' => $bodyName, 'url' => $bodyUrl];
        }

        if ($pairs === []) {
            return;
        }

        if ($preload) {
            foreach ($pairs as $pair) {
                self::renderPreloadLink($pair['url']);
            }
        }

        echo '<style id="solidshop-dynamic-typography">' . "\n";

        foreach ($pairs as $pair) {
            echo self::buildFontFaceRule($pair['name'], $pair['url']);
        }

        $headingStack = $headingName !== '' && $headingUrl !== ''
            ? "'" . self::escapeCssFontFamily($headingName) . "', sans-serif"
            : 'sans-serif';
        $bodyStack = $bodyName !== '' && $bodyUrl !== ''
            ? "'" . self::escapeCssFontFamily($bodyName) . "', sans-serif"
            : 'sans-serif';

        echo ":root {\n";
        echo '  --ss-font-heading: ' . $headingStack . ";\n";
        echo '  --ss-font-body: ' . $bodyStack . ";\n";
        echo "}\n";

        // Apply vars for Tailwind font-sans and headings / Застосування змінних для Tailwind
        echo "body, .font-sans { font-family: var(--ss-font-body); }\n";
        echo "h1, h2, h3, h4, h5, h6 { font-family: var(--ss-font-heading); }\n";
        echo "</style>\n";
    }

    private static function renderPreloadLink(string $url): void
    {
        $mime = self::fontMimeType($url);

        printf(
            '<link rel="preload" href="%s" as="font" type="%s" crossorigin>' . "\n",
            esc_url($url),
            esc_attr($mime)
        );
    }

    private static function buildFontFaceRule(string $family, string $url): string
    {
        $format = self::fontFormat($url);
        $family = self::escapeCssFontFamily($family);

        return sprintf(
            "@font-face {\n  font-family: '%s';\n  src: url('%s') format('%s');\n  font-display: swap;\n  font-weight: 100 900;\n  font-style: normal;\n}\n",
            $family,
            esc_url($url),
            esc_attr($format)
        );
    }

    private static function escapeCssFontFamily(string $name): string
    {
        return str_replace(["\\", "'"], ['\\\\', "\\'"], $name);
    }

    private static function fontFormat(string $url): string
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?: $url);

        if (str_ends_with($path, '.woff')) {
            return 'woff';
        }

        return 'woff2';
    }

    private static function fontMimeType(string $url): string
    {
        return self::fontFormat($url) === 'woff' ? 'font/woff' : 'font/woff2';
    }
}

Typography::boot();
