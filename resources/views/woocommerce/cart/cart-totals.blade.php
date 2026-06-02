{{--
  Cart totals sidebar — stacked summary panel (no cramped table cells)
  Підсумки кошика — вертикальна панель без стиснутих комірок таблиці

  @see woocommerce/templates/cart/cart-totals.php
  @version 2.3.6
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }
@endphp

<div class="cart_totals solidshop-cart-totals {{ WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : '' }}">

  @php do_action('woocommerce_before_cart_totals'); @endphp

  <h2 class="solidshop-cart-totals__title">{{ __('Підсумки кошика', 'solidshop') }}</h2>

  <div class="solidshop-cart-totals__summary">

    <div class="solidshop-cart-totals__row solidshop-cart-totals__row--subtotal">
      <span class="solidshop-cart-totals__label">{{ __('Підсумок', 'solidshop') }}</span>
      <span class="solidshop-cart-totals__value">@php wc_cart_totals_subtotal_html(); @endphp</span>
    </div>

    @foreach (WC()->cart->get_coupons() as $code => $coupon)
      <div class="solidshop-cart-totals__row solidshop-cart-totals__row--discount coupon-{{ esc_attr(sanitize_title($code)) }}">
        <span class="solidshop-cart-totals__label">@php wc_cart_totals_coupon_label($coupon); @endphp</span>
        <span class="solidshop-cart-totals__value">@php wc_cart_totals_coupon_html($coupon); @endphp</span>
      </div>
    @endforeach

    @foreach (WC()->cart->get_fees() as $fee)
      <div class="solidshop-cart-totals__row solidshop-cart-totals__row--fee">
        <span class="solidshop-cart-totals__label">{{ $fee->name }}</span>
        <span class="solidshop-cart-totals__value">@php wc_cart_totals_fee_html($fee); @endphp</span>
      </div>
    @endforeach

    @if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax())
      @php
        $taxable_address = WC()->customer->get_taxable_address();
        $estimated_text  = '';

        if (WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()) {
            $estimated_text = sprintf(
              ' <small>' . __('(estimated for %s)', 'woocommerce') . '</small>',
              WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]
            );
        }
      @endphp

      @if ('itemized' === get_option('woocommerce_tax_total_display'))
        @foreach (WC()->cart->get_tax_totals() as $code => $tax)
          <div class="solidshop-cart-totals__row solidshop-cart-totals__row--tax tax-rate-{{ esc_attr(sanitize_title($code)) }}">
            <span class="solidshop-cart-totals__label">{!! esc_html($tax->label) . wp_kses_post($estimated_text) !!}</span>
            <span class="solidshop-cart-totals__value">{!! wp_kses_post($tax->formatted_amount) !!}</span>
          </div>
        @endforeach
      @else
        <div class="solidshop-cart-totals__row solidshop-cart-totals__row--tax">
          <span class="solidshop-cart-totals__label">{!! esc_html(WC()->countries->tax_or_vat()) . wp_kses_post($estimated_text) !!}</span>
          <span class="solidshop-cart-totals__value">@php wc_cart_totals_taxes_total_html(); @endphp</span>
        </div>
      @endif
    @endif

  </div>

  @if (WC()->cart->needs_shipping() && WC()->cart->show_shipping())
    @php do_action('woocommerce_cart_totals_before_shipping'); @endphp
    @include('woocommerce.cart.partials.shipping-methods')
    @php do_action('woocommerce_cart_totals_after_shipping'); @endphp
  @elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc'))
    <section class="solidshop-cart-shipping solidshop-cart-shipping--calculator">
      <h3 class="solidshop-cart-shipping__title">{{ __('Доставка', 'solidshop') }}</h3>
      <p class="solidshop-cart-shipping__empty">
        {{ __('Вартість доставки буде розрахована під час оформлення замовлення.', 'solidshop') }}
      </p>
    </section>
  @endif

  @php do_action('woocommerce_cart_totals_before_order_total'); @endphp

  <div class="solidshop-cart-totals__row solidshop-cart-totals__row--total order-total">
    <span class="solidshop-cart-totals__label">{{ __('Орієнтовна загальна сума', 'solidshop') }}</span>
    <span class="solidshop-cart-totals__value">@php wc_cart_totals_order_total_html(); @endphp</span>
  </div>

  @php do_action('woocommerce_cart_totals_after_order_total'); @endphp

  <div class="wc-proceed-to-checkout solidshop-cart-totals__checkout">
    @php do_action('woocommerce_proceed_to_checkout'); @endphp
  </div>

  @php do_action('woocommerce_after_cart_totals'); @endphp
</div>
