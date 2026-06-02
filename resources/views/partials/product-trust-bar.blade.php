{{--
  Trust bar — 4 service highlights (included site-wide above footer)
  Смуга довіри — 4 переваги (на всіх сторінках над футером)
--}}
@php
  // Free shipping min_amount synced from WooCommerce zones / Поріг з налаштувань зон доставки WC
  $free_shipping_threshold = function_exists('\App\solidshop_free_shipping_threshold')
      ? \App\solidshop_free_shipping_threshold()
      : 0.0;
@endphp
<section class="product-trust-bar" aria-label="{{ __('Переваги магазину', 'solidshop') }}">
  <div class="product-trust-bar__item">
    <span class="product-trust-bar__icon" aria-hidden="true">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
    </span>
    <div>
      <p class="product-trust-bar__title">{{ __('Безкоштовна доставка', 'solidshop') }}</p>
      <p class="product-trust-bar__text">
        @if ($free_shipping_threshold > 0)
          {{-- Dynamic threshold from WC shipping zones / Динамічний поріг із зон доставки WC --}}
          {!! sprintf(
            __('На замовлення від %s', 'solidshop'),
            wp_strip_all_tags(wc_price($free_shipping_threshold, ['decimals' => 0]))
          ) !!}
        @else
          {{ __('На замовлення від мінімальної суми', 'solidshop') }}
        @endif
      </p>
    </div>
  </div>
  <div class="product-trust-bar__item">
    <span class="product-trust-bar__icon" aria-hidden="true">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
    </span>
    <div>
      <p class="product-trust-bar__title">{{ __('Легке повернення 14 днів', 'solidshop') }}</p>
      <p class="product-trust-bar__text">{{ __('Гарантія повернення коштів', 'solidshop') }}</p>
    </div>
  </div>
  <div class="product-trust-bar__item">
    <span class="product-trust-bar__icon" aria-hidden="true">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.467.732-3.562"/></svg>
    </span>
    <div>
      <p class="product-trust-bar__title">{{ __('Міжнародна гарантія', 'solidshop') }}</p>
      <p class="product-trust-bar__text">{{ __('Офіційний сервіс бренду', 'solidshop') }}</p>
    </div>
  </div>
  <div class="product-trust-bar__item">
    <span class="product-trust-bar__icon" aria-hidden="true">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
    </span>
    <div>
      <p class="product-trust-bar__title">{{ __('Безпечна оплата', 'solidshop') }}</p>
      <p class="product-trust-bar__text">{{ __('Visa · Mastercard · LiqPay', 'solidshop') }}</p>
    </div>
  </div>
</section>
