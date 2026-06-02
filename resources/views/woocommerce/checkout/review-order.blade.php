{{--
  Review order table — thumbnails + plugin-safe hooks preserved.
  Таблиця замовлення — мініатюри + збережені хуки для сумісності з плагінами.

  @see https://woocommerce.com/document/template-structure/
  @version 5.2.0 (WooCommerce core reference)
--}}

@php
  if (! defined('ABSPATH')) {
      exit;
  }
@endphp

<table class="shop_table woocommerce-checkout-review-order-table">
  <thead>
    <tr>
      <th class="product-name">Товар</th>
      <th class="product-total">Сума</th>
    </tr>
  </thead>
  <tbody>
    @php do_action('woocommerce_review_order_before_cart_contents'); @endphp

    @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
      @php
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      @endphp

      @if (
        $_product
        && $_product->exists()
        && $cart_item['quantity'] > 0
        && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)
      )
        <tr class="{{ esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)) }}">
          <td class="product-name">
            <div class="ss-checkout-line-item">
              <div class="ss-checkout-line-item__thumb" aria-hidden="true">
                {!! $_product->get_image([56, 56], [
                  'class' => 'ss-checkout-item-thumb',
                  'alt'   => esc_attr($_product->get_name()),
                ]) !!}
              </div>
              <div class="ss-checkout-line-item__meta">
                {!! wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) !!}
                {!! apply_filters(
                  'woocommerce_checkout_cart_item_quantity',
                  ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>',
                  $cart_item,
                  $cart_item_key
                ) !!}
                {!! wc_get_formatted_cart_item_data($cart_item) !!}
              </div>
            </div>
          </td>
          <td class="product-total">
            {!! apply_filters(
              'woocommerce_cart_item_subtotal',
              WC()->cart->get_product_subtotal($_product, $cart_item['quantity']),
              $cart_item,
              $cart_item_key
            ) !!}
          </td>
        </tr>
      @endif
    @endforeach

    @php do_action('woocommerce_review_order_after_cart_contents'); @endphp
  </tbody>
  <tfoot>
    <tr class="cart-subtotal">
      <th scope="row">{{ __('Subtotal', 'woocommerce') }}</th>
      <td>@php wc_cart_totals_subtotal_html(); @endphp</td>
    </tr>

    @foreach (WC()->cart->get_coupons() as $code => $coupon)
      <tr class="cart-discount coupon-{{ esc_attr(sanitize_title($code)) }}">
        <th scope="row">@php wc_cart_totals_coupon_label($coupon); @endphp</th>
        <td>@php wc_cart_totals_coupon_html($coupon); @endphp</td>
      </tr>
    @endforeach

    @if (WC()->cart->needs_shipping() && WC()->cart->show_shipping())
      @php do_action('woocommerce_review_order_before_shipping'); @endphp
      @php wc_cart_totals_shipping_html(); @endphp
      @php do_action('woocommerce_review_order_after_shipping'); @endphp
    @endif

    @foreach (WC()->cart->get_fees() as $fee)
      <tr class="fee">
        <th scope="row">{{ $fee->name }}</th>
        <td>@php wc_cart_totals_fee_html($fee); @endphp</td>
      </tr>
    @endforeach

    @if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax())
      @if ('itemized' === get_option('woocommerce_tax_total_display'))
        @foreach (WC()->cart->get_tax_totals() as $code => $tax)
          <tr class="tax-rate tax-rate-{{ esc_attr(sanitize_title($code)) }}">
            <th scope="row">{{ $tax->label }}</th>
            <td>{!! wp_kses_post($tax->formatted_amount) !!}</td>
          </tr>
        @endforeach
      @else
        <tr class="tax-total">
          <th scope="row">{{ WC()->countries->tax_or_vat() }}</th>
          <td>@php wc_cart_totals_taxes_total_html(); @endphp</td>
        </tr>
      @endif
    @endif

    @php do_action('woocommerce_review_order_before_order_total'); @endphp

    <tr class="order-total">
      <th scope="row">{{ __('Total', 'woocommerce') }}</th>
      <td>@php wc_cart_totals_order_total_html(); @endphp</td>
    </tr>

    @php do_action('woocommerce_review_order_after_order_total'); @endphp
  </tfoot>
</table>
