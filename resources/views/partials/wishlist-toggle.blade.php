{{--
  Wishlist toggle — icon button (catalog) or text link (single product).
  Перемикач wishlist — іконка (каталог) або текстове посилання (сторінка товару).

  @var int|null    $product_id
  @var string|null $variant  icon|link
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $product_id = isset($product_id) ? absint($product_id) : 0;

  if ($product_id <= 0) {
      return;
  }

  $variant = isset($variant) ? sanitize_key((string) $variant) : 'icon';
  $is_link = $variant === 'link';

  $in_wishlist = function_exists('\App\solidshop_is_in_wishlist')
      ? \App\solidshop_is_in_wishlist($product_id)
      : false;

  $label_add    = __('Додати до вподобаних', 'solidshop');
  $label_remove = __('У вподобаних', 'solidshop');

  if ($is_link) {
      $btn_classes = 'js-wishlist-toggle product-wishlist-link inline-flex items-center gap-2 text-sm font-semibold transition-colors';
      $btn_classes .= $in_wishlist ? ' is-in-wishlist text-gray-900' : ' text-gray-600 hover:text-gray-900';
  } else {
      $btn_classes = 'js-wishlist-toggle inline-flex items-center justify-center p-2 transition-colors';
      $btn_classes .= $in_wishlist ? ' is-in-wishlist text-black' : ' text-gray-400 hover:text-black';
  }
@endphp

<button
  type="button"
  class="{{ $btn_classes }}"
  data-product-id="{{ $product_id }}"
  @if ($is_link)
    data-label-add="{{ esc_attr($label_add) }}"
    data-label-remove="{{ esc_attr($label_remove) }}"
  @endif
  aria-pressed="{{ $in_wishlist ? 'true' : 'false' }}"
  aria-label="{{ $in_wishlist ? esc_attr($label_remove) : esc_attr($label_add) }}"
>
  <svg class="{{ $is_link ? 'w-4 h-4' : 'w-5 h-5' }} js-wishlist-icon shrink-0" viewBox="0 0 24 24" aria-hidden="true">
    <path
      class="js-wishlist-icon-path"
      fill="{{ $in_wishlist ? 'currentColor' : 'none' }}"
      stroke="currentColor"
      stroke-width="2"
      d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
    />
  </svg>
  @if ($is_link)
    <span class="js-wishlist-label">{{ $in_wishlist ? $label_remove : $label_add }}</span>
  @endif
</button>
