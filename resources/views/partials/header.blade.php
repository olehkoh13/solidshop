{{--
  Header WooCommerce actions: account, wishlist, cart.
  WooCommerce-дії в шапці: акаунт, wishlist, кошик.
--}}
@if (class_exists('WooCommerce'))
  @php
    $wishlist_count = function_exists('\App\solidshop_get_wishlist')
        ? count(\App\solidshop_get_wishlist())
        : 0;
    $wishlist_url = function_exists('\App\solidshop_get_wishlist_page_url')
        ? esc_url(\App\solidshop_get_wishlist_page_url())
        : esc_url(wc_get_page_permalink('myaccount'));
    $wishlist_badge_classes = 'solidshop-header-badge solidshop-wishlist-count absolute top-0 right-0 text-white text-[10px] font-bold min-w-4 h-4 px-1 rounded-full flex items-center justify-center transform translate-x-1 -translate-y-1';
    if ($wishlist_count === 0) {
        $wishlist_badge_classes .= ' hidden';
    }
  @endphp

  {{-- Особистий кабінет / My Account --}}
  <a href="{{ esc_url(wc_get_page_permalink('myaccount')) }}"
     class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors select-none"
     aria-label="{{ __('Особистий кабінет', 'solidshop') }}">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
    </svg>
  </a>

  {{-- Wishlist / Вподобані товари --}}
  <a href="{{ $wishlist_url }}"
     class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors select-none"
     aria-label="{{ __('Вподобані товари', 'solidshop') }}">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </svg>
    <span class="{{ $wishlist_badge_classes }}">{{ $wishlist_count }}</span>
  </a>

  {{-- Кошик: кнопка відкриває drawer міні-кошика / Cart mini-drawer trigger --}}
  <button type="button"
          id="cart-toggle-btn"
          onclick="toggleMiniCart(true)"
          aria-label="{{ __('Відкрити кошик', 'solidshop') }}"
          class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors select-none">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
    </svg>
    <span class="solidshop-header-badge solidshop-cart-count absolute top-0 right-0 text-white text-[10px] font-bold min-w-4 h-4 px-1 rounded-full flex items-center justify-center transform translate-x-1 -translate-y-1">
      {{ WC()->cart->get_cart_contents_count() }}
    </span>
  </button>
@endif
