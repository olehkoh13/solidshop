{{--
  Floating Sticky Cart Bar
  Плаваючий нижній банер кошика

  Behavior / Поведінка:
  - Hidden by default; JS shows it only when the user scrolls DOWN past 70%
    of the document height and the cart is not empty.
  - Прихований за замовчуванням; JS показує його лише при скролі ВНИЗ
    після 70% висоти документа і коли кошик не порожній.
  - Hidden on Cart and Checkout pages (server-side guard below).
  - Не рендериться на сторінках Кошика та Оформлення замовлення.
  - Quantity and total are refreshed live via WooCommerce fragments
    (filter registered in app/setup.php).
  - Кількість і сума оновлюються наживо через WooCommerce fragments
    (фільтр зареєстровано в app/setup.php).
--}}
@if (class_exists('WooCommerce') && WC()->cart && ! is_cart() && ! is_checkout())
  @php
    $floating_cart_count = (int) WC()->cart->get_cart_contents_count();
    $floating_cart_label = \App\solidshop_cart_items_label($floating_cart_count);
    $floating_cart_total = WC()->cart->get_cart_total();
  @endphp

  <div id="floating-cart-bar"
       data-cart-count="{{ $floating_cart_count }}"
       role="region"
       aria-label="{{ __('Кошик', 'solidshop') }}"
       class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-[calc(100%-2rem)] max-w-xl
              bg-white rounded-xl shadow-lg border border-gray-200 px-3 sm:px-4 py-2.5
              flex items-center gap-2 sm:gap-3
              translate-y-[150%] opacity-0 pointer-events-none transition-all duration-300">

    {{-- Іконка кошика / Cart icon --}}
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
         class="w-5 h-5 shrink-0 text-blue-600 hidden sm:block" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
    </svg>

    {{-- Живий текст: кількість + сума / Live text: count + total --}}
    <p class="flex-1 min-w-0 text-xs sm:text-sm text-gray-900 leading-snug">
      {{ __('У кошику', 'solidshop') }}
      <span class="js-floating-cart-count font-medium" data-count="{{ $floating_cart_count }}">{{ $floating_cart_count }} {{ $floating_cart_label }}</span>
      {{ __('на суму', 'solidshop') }}
      <span class="js-floating-cart-total font-bold whitespace-nowrap">{!! $floating_cart_total !!}</span>
    </p>

    {{-- Відкрити міні-кошик / Open the mini-cart drawer --}}
    <button type="button"
            onclick="toggleMiniCart(true)"
            class="js-floating-cart-open shrink-0 text-blue-600 hover:text-blue-700 text-xs sm:text-sm font-medium underline-offset-2 hover:underline transition-colors">
      {{ __('Кошик', 'solidshop') }}
    </button>

    {{-- Головна CTA: сторінка оформлення / Main CTA: checkout page --}}
    <a href="{{ esc_url(wc_get_checkout_url()) }}"
       class="ss-btn shrink-0 px-3! sm:px-4! py-2! text-xs! sm:text-sm! whitespace-nowrap">
      <span class="hidden sm:inline">{{ __('Оформити замовлення', 'solidshop') }}</span>
      <span class="sm:hidden">{{ __('Оформити', 'solidshop') }}</span>
    </a>

    {{-- Примусове закриття на сесію / Force close for this session --}}
    <button type="button"
            class="js-floating-cart-close shrink-0 -mr-1 p-1 text-gray-400 hover:text-gray-600 transition-colors"
            aria-label="{{ __('Закрити банер кошика', 'solidshop') }}">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
      </svg>
    </button>

  </div>
@endif
