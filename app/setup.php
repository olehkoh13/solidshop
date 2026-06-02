<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Enable WooCommerce support & modern gallery features.
     * Вмикаємо підтримку WooCommerce та сучасних галерей (зум, слайдер).
     */
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    /**
     * Declare support for High-Performance Order Storage (HPOS).
     * Оголошуємо сумісність із високопродуктивним сховищем замовлень HPOS.
     */
    add_theme_support('woocommerce_single_product_image_zoom');
}, 20);

/**
 * Declare HPOS compatibility at the extension level.
 * Окреме оголошення для сумісності з таблицями custom_orders.
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', 'solidshop', true);
    }
});

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Повністю вимикаємо стандартні стилі та CSS WooCommerce, щоб вони не ламали Tailwind v4.
 * Completely disable default WooCommerce styles to prevent conflicts with Tailwind v4.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Примусово змушуємо WordPress використовувати наші Blade шаблони для всіх сторінок екомерсу.
 * Force WordPress to use our Blade templates for all e-commerce views.
 */
add_filter('template_include', function ($template) {
    if (is_shop() || is_product_category() || is_product_tag() || is_tax('product_brand')) {
        // Шукаємо кастомний Blade шаблон каталогу
        // Search for our custom Blade archive template
        $blade_shop = locate_template('resources/views/woocommerce/archive-product.blade.php');
        if ($blade_shop) {
            return $blade_shop;
        }
    }

    if (is_product()) {
        // Шукаємо кастомний Blade шаблон картки товару
        // Search for our custom Blade single product template
        $blade_single = locate_template('resources/views/woocommerce/single-product.blade.php');
        if ($blade_single) {
            return $blade_single;
        }
    }

    return $template;
}, 99);

/**
 * Вимикаємо WooCommerce ціновий фільтр (price_filter_post_clauses), коли
 * min_price та max_price передані як порожні рядки (незаповнена форма).
 * Без цього WooCommerce застосовує умову "ціна між 0 і 0" і ховає всі товари.
 */
add_filter('woocommerce_enable_post_clause_filtering', function (bool $enabled, \WP_Query $query): bool {
    if (!$enabled) {
        return false;
    }
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $min = isset($_GET['min_price']) ? trim((string) $_GET['min_price']) : null;
    $max = isset($_GET['max_price']) ? trim((string) $_GET['max_price']) : null;
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($min === '' && $max === '') {
        return false;
    }

    return $enabled;
}, 10, 2);

/**
 * Очищаємо та стилізуємо пагінацію WooCommerce под дизайн Tailwind.
 * Clean and style WooCommerce pagination using Tailwind classes.
 */
add_filter('woocommerce_pagination_args', function ($args) {
    $args['prev_text'] = '
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>';
    $args['next_text'] = '
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>';
    $args['type'] = 'plain'; // Повністю прибираємо застарілі теги ul/li. Remove deprecated ul/li tags completely.
    return $args;
});

/**
 * Додаємо глобальні класи до loop-кнопок «Додати в кошик» / «Оберіть опції».
 * Append global button classes to loop add-to-cart / choose-options links.
 */
add_filter('woocommerce_loop_add_to_cart_args', function (array $args): array {
    $args['class'] = trim(($args['class'] ?? 'button') . ' ss-btn ss-btn--loop');

    return $args;
}, 20);

/**
 * Фільтрація товарів за брендом, кольором, розміром та ціною.
 * Запускається з пріоритетом 15 - після того, як WooCommerce (пріоритет 10)
 * вже встановив tax_query з клозою видимості продуктів. Ми дописуємо
 * свої клози поверх, що гарантовано стабільно.
 *
 * Filter products by brand, color, size, and price.
 * Runs with priority 15 - after WooCommerce (priority 10) has populated
 * tax_query with product visibility constraints. We append our own clauses.
 */
add_action('pre_get_posts', function (\WP_Query $query) {
    if (!$query->is_main_query() || is_admin()) {
        return;
    }

    $product_taxonomies = array_merge(['product_brand', 'product_cat', 'product_tag'], get_object_taxonomies('product'));
    $is_catalog         = (
        $query->is_post_type_archive('product') ||
        $query->is_tax($product_taxonomies) ||
        (function_exists('wc_get_page_id') && $query->is_page(wc_get_page_id('shop')))
    );

    if (!$is_catalog) {
        return;
    }

    // Зберігаємо поточний стан tax_query для нарощування умов
    // Store current tax_query state to append conditions
    $tax_query = (array) $query->get('tax_query');
    $tax_query_updated = false;

    // phpcs:disable WordPress.Security.NonceVerification.Recommended

    // --- Фільтр за брендами ---
    // --- Filter by brands ---
    $raw_brands = isset($_GET['f_brand']) ? (array) $_GET['f_brand'] : [];
    $brands     = array_values(array_filter(array_map('sanitize_title', $raw_brands)));

    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // --- Фільтр за кольором (атрибут pa_color) ---
    // --- Filter by color (pa_color attribute) ---
    $raw_colors = isset($_GET['f_color']) ? (array) $_GET['f_color'] : [];
    $colors     = array_values(array_filter(array_map('sanitize_title', $raw_colors)));

    if (!empty($colors)) {
        $tax_query[] = [
            'taxonomy' => 'pa_color',
            'field'    => 'slug',
            'terms'    => $colors,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // --- Фільтр за розміром (атрибут pa_size) ---
    // --- Filter by size (pa_size attribute) ---
    $raw_sizes = isset($_GET['f_size']) ? (array) $_GET['f_size'] : [];
    $sizes     = array_values(array_filter(array_map('sanitize_title', $raw_sizes)));

    if (!empty($sizes)) {
        $tax_query[] = [
            'taxonomy' => 'pa_size',
            'field'    => 'slug',
            'terms'    => $sizes,
            'operator' => 'IN',
        ];
        $tax_query_updated = true;
    }

    // Якщо додали хоча б одну таксономію, оновлюємо запит
    // If at least one taxonomy filter was applied, update the query
    if ($tax_query_updated) {
        if (!isset($tax_query['relation'])) {
            $tax_query['relation'] = 'AND';
        }
        $query->set('tax_query', $tax_query);
    }

    // --- Фільтр за ціною ---
    // --- Filter by price ---
    if (isset($_GET['min_price']) && '' !== $_GET['min_price']) {
        $meta_query   = (array) $query->get('meta_query');
        $meta_query[] = [
            'key'     => '_price',
            'value'   => floatval($_GET['min_price']),
            'type'    => 'NUMERIC',
            'compare' => '>=',
        ];
        $query->set('meta_query', $meta_query);
    }

    if (isset($_GET['max_price']) && '' !== $_GET['max_price']) {
        $meta_query   = (array) $query->get('meta_query');
        $meta_query[] = [
            'key'     => '_price',
            'value'   => floatval($_GET['max_price']),
            'type'    => 'NUMERIC',
            'compare' => '<=',
        ];
        $query->set('meta_query', $meta_query);
    }

    // phpcs:enable WordPress.Security.NonceVerification.Recommended
}, 15);

/**
 * Реєструємо WooCommerce fragment для значка лічильника кошика у шапці.
 *
 * Після кожного успішного AJAX "Додати в кошик" WooCommerce надсилає
 * масив fragments у JSON-відповіді. Наш обробник у single-product.blade.php
 * тригерить подію `added_to_cart`, яку wc-cart-fragments.js перехоплює
 * та замінює span.solidshop-cart-count свіжим HTML з правильним числом.
 *
 * Register a WooCommerce fragment for the header cart count badge.
 *
 * After every successful AJAX add-to-cart WooCommerce sends a `fragments`
 * map in the JSON response. Our handler in single-product.blade.php fires
 * the `added_to_cart` event; wc-cart-fragments.js intercepts it and replaces
 * span.solidshop-cart-count with the freshly rendered HTML below.
 */
add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
    if (!function_exists('WC') || !WC()->cart) {
        return $fragments;
    }

    $count = (int) WC()->cart->get_cart_contents_count();

    $fragments['span.solidshop-cart-count'] = sprintf(
        '<span class="solidshop-header-badge solidshop-cart-count absolute top-0 right-0 text-white text-[10px] font-bold min-w-4 h-4 px-1 rounded-full flex items-center justify-center transform translate-x-1 -translate-y-1">%d</span>',
        $count
    );

    return $fragments;
});

/**
 * Mini-cart drawer: ensure cart fragments script + AJAX config.
 * Drawer міні-кошика: wc-cart-fragments і конфіг AJAX.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('is_woocommerce') || ! class_exists('WooCommerce')) {
        return;
    }

    wp_enqueue_script('wc-cart-fragments');

    wp_localize_script('wc-cart-fragments', 'solidshopMiniCart', [
        'ajaxUrl'      => \WC_AJAX::get_endpoint('solidshop_update_mini_cart_qty'),
        'addToCartUrl' => \WC_AJAX::get_endpoint('add_to_cart'),
        'nonce'        => wp_create_nonce('solidshop_mini_cart'),
    ]);
}, 20);

/**
 * Preserve mini-cart root layout classes when WC refreshes cart fragments.
 * Зберігаємо layout-класи mini-cart-root після AJAX-оновлення фрагментів.
 */
add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
    if (! isset($fragments['div.widget_shopping_cart_content'])) {
        return $fragments;
    }

    $fragments['div.widget_shopping_cart_content'] = str_replace(
        '<div class="widget_shopping_cart_content">',
        '<div class="widget_shopping_cart_content mini-cart-root flex flex-col flex-1 min-h-0">',
        $fragments['div.widget_shopping_cart_content']
    );

    return $fragments;
}, 15);

/**
 * Classic checkout замість WooCommerce Blocks — інакше Blade/PHP-фільтри не працюють.
 * Classic checkout instead of WC Blocks — otherwise Blade/PHP filters never run.
 */
add_filter('the_content', function (string $content): string {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url()) {
        return $content;
    }

    return do_shortcode('[woocommerce_checkout]');
}, 5);

add_filter('woocommerce_has_block_template', function (bool $has_template, string $template_name): bool {
    if ($template_name === 'page-checkout') {
        return false;
    }

    return $has_template;
}, 10, 2);

/**
 * Обгортка 1440px для classic checkout (як header/archive).
 * 1440px fluid-boxed wrapper for classic checkout (matches header/archive).
 */
add_action('woocommerce_before_checkout_form', function () {
    echo '<div class="w-full font-sans antialiased"><div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">';
}, 1);

add_action('woocommerce_before_checkout_form', function () {
    echo '<h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8 text-center">Оформлення замовлення</h1>';
}, 5);

add_action('woocommerce_after_checkout_form', function () {
    echo '</div></div>';
}, 999);

/**
 * Checkout: спрощення полів + Tailwind-класи напряму в HTML розмітку WC.
 * Checkout: simplify fields + inject Tailwind classes directly into WC HTML.
 *
 * Після змін очистіть Acorn cache:
 * After changes, clear Acorn cache:
 *   rm -rf wp-content/cache/acorn/framework/views/
 */
add_filter('woocommerce_checkout_fields', function (array $fields): array {
    /* ── 1. Прибираємо зайві поля / Remove unnecessary fields ── */
    unset(
        $fields['billing']['billing_company'],
        $fields['billing']['billing_address_2'],
        $fields['shipping']['shipping_company'],
        $fields['shipping']['shipping_address_2']
    );

    if (isset($fields['billing']['billing_postcode'])) {
        $fields['billing']['billing_postcode']['required'] = false;
    }

    if (isset($fields['shipping']['shipping_postcode'])) {
        $fields['shipping']['shipping_postcode']['required'] = false;
    }

    /* ── 1b. Українські підписи полів / Ukrainian field labels ── */
    $ukLabelsBySection = [
        'billing' => [
            'billing_first_name' => 'Імʼя',
            'billing_last_name'  => 'Прізвище',
            'billing_country'    => 'Країна / Регіон',
            'billing_address_1'  => 'Адреса',
            'billing_city'       => 'Місто',
            'billing_state'      => 'Область',
            'billing_postcode'   => 'Поштовий індекс',
            'billing_phone'      => 'Телефон',
            'billing_email'      => 'Електронна адреса',
        ],
        'shipping' => [
            'shipping_first_name' => 'Імʼя',
            'shipping_last_name'  => 'Прізвище',
            'shipping_country'    => 'Країна / Регіон',
            'shipping_address_1'  => 'Адреса',
            'shipping_city'       => 'Місто',
            'shipping_state'      => 'Область',
            'shipping_postcode'   => 'Поштовий індекс',
        ],
        'order' => [
            'order_comments' => 'Примітки до замовлення (необовʼязково)',
        ],
    ];

    foreach ($ukLabelsBySection as $section => $labels) {
        if (! isset($fields[$section]) || ! is_array($fields[$section])) {
            continue;
        }

        foreach ($labels as $key => $label) {
            if (isset($fields[$section][$key])) {
                $fields[$section][$key]['label'] = $label;
            }
        }
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['placeholder'] = 'Примітки щодо замовлення, наприклад особливі побажання щодо доставки.';
    }

    /* ── 2. Tailwind-класи / Tailwind class sets ── */
    $wrapperDefault = ['w-full', 'mb-5', 'clear-both'];
    $wrapperNameLeft  = ['w-full', 'md:w-[48%]', 'mb-5', 'float-left'];
    $wrapperNameRight = ['w-full', 'md:w-[48%]', 'mb-5', 'float-right'];

    $inputClasses = [
        'w-full',
        'bg-white',
        'border',
        'border-gray-300',
        'rounded-none',
        'px-4',
        'py-3',
        'text-sm',
        'text-gray-900',
        'focus:outline-none',
        'focus:border-black',
        'focus:ring-1',
        'focus:ring-black',
    ];

    $labelClasses = ['block', 'text-sm', 'font-bold', 'text-gray-900', 'mb-2'];

    $nameLeftKeys  = ['billing_first_name', 'shipping_first_name'];
    $nameRightKeys = ['billing_last_name', 'shipping_last_name'];

    foreach (['billing', 'shipping'] as $section) {
        if (! isset($fields[$section]) || ! is_array($fields[$section])) {
            continue;
        }

        foreach ($fields[$section] as $key => &$field) {
            if (in_array($key, $nameLeftKeys, true)) {
                $field['class'] = $wrapperNameLeft;
            } elseif (in_array($key, $nameRightKeys, true)) {
                $field['class'] = $wrapperNameRight;
            } else {
                $field['class'] = $wrapperDefault;
            }

            $field['input_class'] = $inputClasses;
            $field['label_class'] = $labelClasses;
        }

        unset($field);
    }

    foreach (['order', 'account'] as $section) {
        if (! isset($fields[$section]) || ! is_array($fields[$section])) {
            continue;
        }

        foreach ($fields[$section] as $key => &$field) {
            $field['class'] = $wrapperDefault;
            $field['input_class'] = $inputClasses;
            $field['label_class'] = $labelClasses;
        }

        unset($field);
    }

    return $fields;
}, 20);

/**
 * Українські рядки WooCommerce checkout (fallback, якщо переклад ядра не підвантажився).
 * Ukrainian WooCommerce checkout strings (fallback when core translation is missing).
 */
add_filter('gettext', function (string $translated, string $text, string $domain): string {
    if ($domain !== 'woocommerce') {
        return $translated;
    }

    static $map = [
        'Billing details' => 'Платіжні дані',
        'Shipping details' => 'Дані доставки',
        'Additional information' => 'Додаткова інформація',
        'Your order' => 'Ваше замовлення',
        'Place order' => 'Підтвердити замовлення',
        'Subtotal' => 'Проміжний підсумок',
        'Total' => 'Разом',
        'Shipping' => 'Відправлення',
        'Free shipping' => 'Безкоштовна доставка',
        'Product' => 'Товар',
        'Have a coupon?' => 'Маєте купон?',
        'Click here to enter your code' => 'Натисніть, щоб ввести код',
        'Create an account?' => 'Створити обліковий запис?',
        'Ship to a different address?' => 'Доставити за іншою адресою?',
        'Order notes' => 'Примітки до замовлення',
        'Order notes (optional)' => 'Примітки до замовлення (необовʼязково)',
        'I have read and agree to the website %s' => 'Я прочитав(-ла) і погоджуюсь з %s сайту',
        'terms and conditions' => 'умовами та положеннями',
        'Direct bank transfer' => 'Прямий банківський переказ',
        'Check payments' => 'Оплата чеком',
        'Cash on delivery' => 'Оплата при отриманні',
        'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Наразі немає доступних способів оплати. Зверніться до нас, щоб отримати допомогу з оформленням замовлення.',
        'Login' => 'Увійти',
        'Register' => 'Реєстрація',
        'Log in' => 'Увійти',
        'Username or email address' => 'Імʼя користувача або email',
        'Password' => 'Пароль',
        'Remember me' => 'Запамʼятати мене',
        'Lost your password?' => 'Забули пароль?',
        'Username or email' => 'Імʼя користувача або email',
        'Reset password' => 'Скинути пароль',
        'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.' => 'Забули пароль? Введіть імʼя користувача або email. Ми надішлемо посилання для створення нового пароля.',
        'Password reset email has been sent.' => 'Лист для скидання пароля надіслано.',
        'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.' => 'Лист для скидання пароля надіслано на email вашого облікового запису. Зачекайте щонайменше 10 хвилин перед повторною спробою.',
        'Enter a new password below.' => 'Введіть новий пароль нижче.',
        'New password' => 'Новий пароль',
        'Re-enter new password' => 'Повторіть новий пароль',
        'Save' => 'Зберегти',
        'Password change' => 'Зміна пароля',
        'Current password (leave blank to leave unchanged)' => 'Поточний пароль (залиште порожнім, якщо не змінюєте)',
        'New password (leave blank to leave unchanged)' => 'Новий пароль (залиште порожнім, якщо не змінюєте)',
        'Confirm new password' => 'Підтвердіть новий пароль',
        'Save changes' => 'Зберегти зміни',
        'First name' => 'Імʼя',
        'Last name' => 'Прізвище',
        'Display name' => 'Відображуване імʼя',
        'Email address' => 'Email',
        'This will be how your name will be displayed in the account section and in reviews' => 'Так ваше імʼя відображатиметься в особистому кабінеті та відгуках',
        'Enter a username or email address.' => 'Введіть імʼя користувача або email.',
    ];

    return $map[$text] ?? $translated;
}, 20, 3);

add_filter('woocommerce_order_button_text', function (): string {
    return 'Підтвердити замовлення';
});

/**
 * Кнопка «Оформити замовлення» — чорна, гострі кути (reference design).
 * Place order button — black, sharp corners (reference design).
 */
add_filter('woocommerce_order_button_html', function (string $button): string {
    $label = esc_html(apply_filters('woocommerce_order_button_text', 'Підтвердити замовлення'));

    return sprintf(
        '<button type="submit" class="button alt ss-btn w-full py-4 mt-6 uppercase tracking-wider" name="woocommerce_checkout_place_order" id="place_order" value="%1$s" data-value="%1$s">%1$s</button>',
        $label
    );
});

/**
 * У картках доставки на checkout — лише назва перевізника.
 * На сторінці кошика — назва + ціна в окремих колонках (див. partial + CSS).
 * Shipping cards: checkout shows name only; cart shows name + price columns.
 */
add_filter('woocommerce_cart_shipping_method_full_label', function (string $label, $method): string {
    if (function_exists('is_cart') && is_cart()) {
        $name = wp_strip_all_tags($method->get_label());

        if (defined('WC_UKR_SHIPPING_NP_SHIPPING_NAME') && $method->get_method_id() === WC_UKR_SHIPPING_NP_SHIPPING_NAME) {
            return esc_html($name);
        }

        return esc_html($name);
    }

    $name = wp_strip_all_tags($method->get_label());

    if (defined('WC_UKR_SHIPPING_NP_SHIPPING_NAME') && $method->get_method_id() === WC_UKR_SHIPPING_NP_SHIPPING_NAME) {
        return '<span id="wcus-shipping-cost">' . esc_html($name) . '</span>';
    }

    return esc_html($name);
}, 25, 2);

/**
 * Custom product meta: delivery & care guide (admin textareas).
 * Кастомні meta-поля: доставка та догляд.
 */
add_action('woocommerce_product_options_general_product_data', function (): void {
    echo '<div class="options_group">';

    woocommerce_wp_textarea_input([
        'id'          => '_ss_delivery_returns',
        'label'       => __('Доставка і повернення', 'solidshop'),
        'desc_tip'    => true,
        'description' => __('Текст для акордеону на сторінці товару.', 'solidshop'),
        'rows'        => 4,
    ]);

    woocommerce_wp_textarea_input([
        'id'          => '_ss_care_guide',
        'label'       => __('Догляд', 'solidshop'),
        'desc_tip'    => true,
        'description' => __('Інструкції з догляду для акордеону.', 'solidshop'),
        'rows'        => 4,
    ]);

    echo '</div>';
});

add_action('woocommerce_process_product_meta', function (int $post_id): void {
    $fields = ['_ss_delivery_returns', '_ss_care_guide'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, wp_kses_post(wp_unslash($_POST[$field])));
        }
    }
});

/**
 * Product tabs: UA labels + review count badge in title.
 * Таби товару: українські назви.
 */
add_filter('woocommerce_product_tabs', function (array $tabs): array {
    unset($tabs['gallery']);

    if (isset($tabs['description'])) {
        $tabs['description']['title'] = __('Опис', 'solidshop');
        $tabs['description']['priority'] = 10;
    }

    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = __('Характеристики', 'solidshop');
        $tabs['additional_information']['priority'] = 20;
    }

    if (isset($tabs['reviews'])) {
        global $product;
        $count = ($product instanceof \WC_Product) ? $product->get_review_count() : 0;
        $tabs['reviews']['priority'] = 30;

        if ($count > 0) {
            $tabs['reviews']['title'] = sprintf(
                '%s <span class="wc-tab-badge">%d</span>',
                esc_html__('Відгуки', 'solidshop'),
                $count
            );
        } else {
            $tabs['reviews']['title'] = __('Відгуки', 'solidshop');
        }
    }

    return $tabs;
}, 20);

/**
 * Hide duplicate upsells block on single product.
 * Прибираємо дубль upsells на сторінці товару.
 */
add_action('init', function (): void {
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}, 25);

add_action('woocommerce_after_single_product_summary', function (): void {
    global $product;

    if ($product instanceof \WC_Product) {
        echo view('partials.product-meta-footer', compact('product'))->render();
    }
}, 11);

/**
 * Related products: 4 items (rendered via solidshop_render_related_products).
 * Супутні товари: 4 позиції (рендер через solidshop_render_related_products).
 */
add_filter('woocommerce_output_related_products_args', function (array $args): array {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;

    return $args;
});

/**
 * Server-side tracking: Facebook CAPI + GA4 (DataLayer + Measurement Protocol).
 * Серверний трекінг: Facebook CAPI + GA4 (DataLayer + Measurement Protocol).
 */
add_action('after_setup_theme', function (): void {
    if (! class_exists('WooCommerce')) {
        return;
    }

    (new \App\Tracking\GoogleTagManager())->register();
    (new \App\Tracking\FacebookCAPI())->register();
    (new \App\Tracking\GoogleAnalytics())->register();
}, 20);
