{{--
  My Account navigation — vertical menu
  Навігація особистого кабінету — вертикальне меню

  @see woocommerce/templates/myaccount/navigation.php
  @version 9.3.0 (WooCommerce core reference)
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  do_action('woocommerce_before_account_navigation');
@endphp

<nav class="woocommerce-MyAccount-navigation" aria-label="{{ __('Account pages', 'woocommerce') }}">
  <ul class="flex flex-col space-y-1 list-none p-0 m-0">
    @foreach (wc_get_account_menu_items() as $endpoint => $label)
      @php
        $item_classes = wc_get_account_menu_item_classes($endpoint);
        $is_active    = str_contains($item_classes, 'is-active');
        $link_classes = $is_active
            ? 'block px-4 py-3 text-sm font-semibold transition-colors bg-black text-white no-underline'
            : 'block px-4 py-3 text-sm font-semibold transition-colors text-gray-600 hover:bg-gray-50 hover:text-black no-underline';
      @endphp

      <li class="{{ esc_attr($item_classes) }}">
        <a
          href="{{ esc_url(wc_get_account_endpoint_url($endpoint)) }}"
          class="{{ $link_classes }}"
          @if (wc_is_current_account_menu_item($endpoint)) aria-current="page" @endif
        >
          {{ $label }}
        </a>
      </li>
    @endforeach
  </ul>
</nav>

@php do_action('woocommerce_after_account_navigation'); @endphp
