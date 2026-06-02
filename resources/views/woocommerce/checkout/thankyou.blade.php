{{--
  Thank You / Order Received — premium SolidShop layout
  Сторінка «Замовлення отримано» — premium-макет SolidShop

  @see woocommerce/templates/checkout/thankyou.php
  @version 8.1.0 (WooCommerce core reference)
  @var \WC_Order|false|null $order
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  /** @var \WC_Order|false|null $order */
  $order = $order ?? false;
@endphp

<div class="solidshop-thankyou-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-section pb-4">
  @include('partials.checkout-steps', ['current_step' => 3])
</div>

<div class="woocommerce-order max-w-4xl mx-auto pb-section px-4 sm:px-6">

  @if ($order instanceof \WC_Order)

    @php do_action('woocommerce_before_thankyou', $order->get_id()); @endphp

    @if ($order->has_status('failed'))
      {{-- Failed payment / Невдала оплата --}}
      <div class="woocommerce-thankyou-order-failed text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 text-red-600 mb-6" aria-hidden="true">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
          </svg>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-4">
          {{ __('На жаль, оплату не вдалося завершити', 'solidshop') }}
        </h1>

        <p class="woocommerce-notice woocommerce-notice--error text-red-700 bg-red-50 border border-red-100 rounded-xl px-5 py-4 text-sm md:text-base max-w-2xl mx-auto mb-8">
          {{ __('На жаль, ваше замовлення не може бути оброблене, оскільки банк або платіжна система відхилили транзакцію. Спробуйте оплатити ще раз.', 'woocommerce') }}
        </p>

        <div class="woocommerce-thankyou-order-failed-actions flex flex-wrap items-center justify-center gap-3">
          <a
            href="{{ esc_url($order->get_checkout_payment_url()) }}"
            class="button pay inline-flex items-center justify-center bg-black text-white px-6 py-3 font-bold no-underline hover:bg-gray-800 transition-colors"
          >
            {{ __('Оплатити', 'woocommerce') }}
          </a>

          @if (is_user_logged_in())
            <a
              href="{{ esc_url(wc_get_page_permalink('myaccount')) }}"
              class="button inline-flex items-center justify-center border border-gray-300 text-gray-900 px-6 py-3 font-bold no-underline hover:bg-gray-50 transition-colors"
            >
              {{ __('Мій обліковий запис', 'woocommerce') }}
            </a>
          @endif
        </div>
      </div>

    @else
      {{-- Success state / Успішне замовлення --}}
      <div class="woocommerce-thankyou-order-success text-center mb-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 text-gray-900 mb-6" aria-hidden="true">
          <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
          </svg>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight mb-3">
          {{ __('Дякуємо! Ваше замовлення підтверджено.', 'solidshop') }}
        </h1>

        <p class="text-sm md:text-base text-gray-500 max-w-xl mx-auto">
          @php
            echo wp_kses_post(
              apply_filters(
                'woocommerce_thankyou_order_received_text',
                __('Ми надіслали підтвердження на вашу електронну пошту.', 'solidshop'),
                $order
              )
            );
          @endphp
        </p>
      </div>

      {{-- Order meta grid / Сітка метаданих замовлення --}}
      <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 list-none p-0 m-0">
        <li class="woocommerce-order-overview__order order bg-gray-50 border border-gray-100 p-4 rounded-xl">
          <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
            {{ __('Номер замовлення', 'solidshop') }}
          </span>
          <strong class="block text-base font-bold text-gray-900">{{ $order->get_order_number() }}</strong>
        </li>

        <li class="woocommerce-order-overview__date date bg-gray-50 border border-gray-100 p-4 rounded-xl">
          <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
            {{ __('Дата', 'woocommerce') }}
          </span>
          <strong class="block text-base font-bold text-gray-900">{!! wc_format_datetime($order->get_date_created()) !!}</strong>
        </li>

        @if ($order->get_billing_email())
          <li class="woocommerce-order-overview__email email bg-gray-50 border border-gray-100 p-4 rounded-xl">
            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
              {{ __('Email', 'woocommerce') }}
            </span>
            <strong class="block text-sm md:text-base font-bold text-gray-900 break-all">{{ $order->get_billing_email() }}</strong>
          </li>
        @endif

        <li class="woocommerce-order-overview__total total bg-gray-50 border border-gray-100 p-4 rounded-xl">
          <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
            {{ __('Разом', 'woocommerce') }}
          </span>
          <strong class="block text-base font-bold text-gray-900">{!! $order->get_formatted_order_total() !!}</strong>
        </li>
      </ul>
    @endif

    @php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); @endphp
    @php do_action('woocommerce_thankyou', $order->get_id()); @endphp

  @else
    {{-- No order object (edge case) / Немає об'єкта замовлення --}}
    <div class="text-center py-8">
      <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received text-lg text-gray-700">
        @php
          echo wp_kses_post(
            apply_filters(
              'woocommerce_thankyou_order_received_text',
              __('Дякуємо. Ваше замовлення отримано.', 'woocommerce'),
              false
            )
          );
        @endphp
      </p>
    </div>
  @endif

</div>
