{{--
  Empty cart page — centered premium layout
  Порожній кошик — центрований premium-макет

  @see woocommerce/templates/cart/cart-empty.php
  @version 7.0.1
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $shop_url = wc_get_page_id('shop') > 0
      ? apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))
      : '';
@endphp

<div class="solidshop-cart-page solidshop-cart-page--empty max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-section">
  @include('partials.checkout-steps', ['current_step' => 1])

  @php woocommerce_output_all_notices(); @endphp

  <div class="solidshop-cart-empty">
    <div class="solidshop-cart-empty__content">
      <div class="solidshop-cart-empty__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 96 96" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" d="M28 18l-6 8M38 14l-2 8M48 18l2 8"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M24 32h48l-4 36H28L24 32Z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M36 32V26a12 12 0 0 1 24 0v6"/>
        </svg>
      </div>

      <h1 class="solidshop-cart-empty__title">
        {{ __('Ваш кошик наразі порожній.', 'solidshop') }}
      </h1>

      @if ($shop_url)
        <a
          href="{{ esc_url($shop_url) }}"
          class="solidshop-cart-empty__btn button wc-backward{{ esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') }}"
        >
          {{ apply_filters('woocommerce_return_to_shop_text', __('Повернутися до магазину', 'solidshop')) }}
        </a>
      @endif
    </div>
  </div>
</div>

@php do_action('woocommerce_after_cart'); @endphp
