<?php

/**
 * WooCommerce loop product — delegates to Blade catalog card.
 * Loop product — делегує до Blade-картки каталогу.
 */
defined('ABSPATH') || exit;

\App\solidshop_mark_product_card_render();

echo view('partials.product-card-catalog', [
    'product' => $GLOBALS['product'] ?? null,
    'layout'  => 'grid',
])->render();
