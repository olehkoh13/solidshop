{{--
  Checkout Form — SolidShop premium two-column layout
  Форма оформлення замовлення — двоколонковий premium-макет SolidShop

  @see https://woocommerce.com/document/template-structure/
  @version 9.4.0 (WooCommerce core reference)
--}}

@php
  /**
   * $checkout is injected by wc_get_template( 'checkout/form-checkout.php' ).
   * $checkout передається через wc_get_template( 'checkout/form-checkout.php' ).
   */
  if (! defined('ABSPATH')) {
      exit;
  }
@endphp

<div class="solidshop-checkout-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-section">
  @include('partials.checkout-steps', ['current_step' => 2])

  @php do_action('woocommerce_before_checkout_form', $checkout); @endphp

  {{-- Якщо реєстрація вимкнена, але обов'язкова — гість не може оформити замовлення. --}}
  {{-- If registration is disabled but required, guests cannot proceed to checkout. --}}
  @if (
    ! $checkout->is_registration_enabled()
    && $checkout->is_registration_required()
    && ! is_user_logged_in()
  )
    @php
      echo esc_html(
        apply_filters(
          'woocommerce_checkout_must_be_logged_in_message',
          __('You must be logged in to checkout.', 'woocommerce')
        )
      );
    @endphp
  @else

  <form
    name="checkout"
    method="post"
    class="checkout woocommerce-checkout"
    action="{{ esc_url(wc_get_checkout_url()) }}"
    enctype="multipart/form-data"
    aria-label="{{ __('Checkout', 'woocommerce') }}"
  >

    {{-- ═══════════════════════════════════════════════════════════════
         Головна сітка: дані клієнта (7/12) + огляд замовлення (5/12)
         Main grid: customer details (7/12) + order review (5/12)
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start mt-10">

      {{-- ── ЛІВА КОЛОНКА: billing + shipping / LEFT: customer details ── --}}
      @if ($checkout->get_checkout_fields())
        @php do_action('woocommerce_checkout_before_customer_details'); @endphp

        <div id="customer_details" class="lg:col-span-7 space-y-6">
          @php do_action('woocommerce_checkout_billing'); @endphp
          @php do_action('woocommerce_checkout_shipping'); @endphp
          {{-- Примітки до замовлення — ліва колонка, як у reference / Order notes — left column --}}
          @php do_action('woocommerce_checkout_after_customer_details'); @endphp
        </div>
      @endif

      {{-- ── ПРАВА КОЛОНКА: order review (без upsells / reviews) ── --}}
      {{-- ── RIGHT: order review panel (no upsells / reviews hooks) ── --}}
      <aside
        class="lg:col-span-5 lg:sticky lg:top-24 bg-gray-50 border border-gray-200 rounded-none p-6 lg:p-8"
        aria-labelledby="order_review_heading"
      >
        @php do_action('woocommerce_checkout_before_order_review_heading'); @endphp

        <h3
          id="order_review_heading"
          class="text-base font-bold uppercase tracking-wider text-gray-900 mb-6"
        >
          Ваше замовлення
        </h3>

        @php do_action('woocommerce_checkout_before_order_review'); @endphp

        <div id="order_review" class="woocommerce-checkout-review-order bg-gray-50 border-0 rounded-none p-0">
          @php do_action('woocommerce_checkout_order_review'); @endphp
        </div>

        {{-- Жодних upsells / reviews під кнопкою — лише стандартний payment + place order --}}
        {{-- No upsells or reviews below the button — payment + place order only --}}
        @php do_action('woocommerce_checkout_after_order_review'); @endphp
      </aside>

    </div>

  </form>

  @endif
</div>

@php do_action('woocommerce_after_checkout_form', $checkout); @endphp
