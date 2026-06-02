{{--
  Proceed to checkout — full-width black CTA
  Перехід до оформлення — повноширинна чорна кнопка

  @see woocommerce/templates/cart/proceed-to-checkout-button.php
  @version 7.0.1
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }
@endphp

<a
  href="{{ esc_url(wc_get_checkout_url()) }}"
  class="checkout-button button alt wc-forward solidshop-cart-checkout-btn{{ esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') }}"
>
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="solidshop-cart-checkout-btn__icon" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
  </svg>
  {{ __('Перейти до оформлення замовлення', 'solidshop') }}
</a>
