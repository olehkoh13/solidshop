{{--
  WooCommerce content-product template part — catalog card.
  WooCommerce content-product — картка каталогу.
--}}
@include('partials.product-card-catalog', [
  'product' => $GLOBALS['product'] ?? null,
  'layout'  => 'grid',
])
