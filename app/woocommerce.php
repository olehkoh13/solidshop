<?php

/**
 * WooCommerce Blade Template Bridge for Sage 11 / Acorn v6
 * * @package App
 */

namespace App;

/**
 * Перенаправляємо завантаження шаблонів WooCommerce на Blade views.
 * Шукає файли у папці resources/views/woocommerce/
 */
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    // Спочатку шукаємо звичайний .php override
    $theme_template = locate_template("resources/views/woocommerce/{$template_name}");
    if ($theme_template) {
        return $theme_template;
    }

    // Потім шукаємо Blade-варіант (.php → .blade.php)
    $blade_name     = str_replace('.php', '.blade.php', $template_name);
    $blade_template = locate_template("resources/views/woocommerce/{$blade_name}");
    if ($blade_template) {
        return $blade_template;
    }

    return $template;
}, 10, 3);
