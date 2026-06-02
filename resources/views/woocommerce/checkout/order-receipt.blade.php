{{--
  Checkout Order Receipt Template — premium SolidShop layout
  Сторінка чеку/оплати замовлення — premium-макет SolidShop

  @see woocommerce/templates/checkout/order-receipt.php
  @version 3.2.0
  @var \WC_Order $order
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }
@endphp

<div class="solidshop-checkout-steps-wrapper max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-section pb-4">
  @include('partials.checkout-steps', ['current_step' => 3])
</div>

<div class="woocommerce-order-pay max-w-3xl mx-auto pb-section px-4 sm:px-6">
  
  <div class="bg-white border border-gray-200 rounded-none p-6 md:p-10 shadow-sm mb-8">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 text-blue-600 mb-4" aria-hidden="true">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h.007v.008H3.75V4.5Zm.008 11.25h.008v.008H3.75v-.008Zm6-10.5h.008v.008h-.008V5.25Zm0 10.5h.008v.008h-.008v-.008Zm6-10.5h.008v.008h-.008V5.25Zm0 10.5h.008v.008h-.008v-.008Zm3 3H12a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h9.75M9 9h.008v.008H9V9Zm0 4.5h.008v.008H9v-.008Z" />
        </svg>
      </div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
        {{ __('Оплата замовлення', 'solidshop') }}
      </h1>
      <p class="text-sm text-gray-500 mt-2">
        {{ __('Будь ласка, перевірте деталі замовлення перед здійсненням оплати.', 'solidshop') }}
      </p>
    </div>

    {{-- Сітка з деталями замовлення --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-b border-gray-100 py-6 mb-8">
      <div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
          {{ __('Номер замовлення', 'solidshop') }}
        </span>
        <strong class="text-lg font-bold text-gray-900">#{{ $order->get_order_number() }}</strong>
      </div>
      <div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
          {{ __('Дата', 'solidshop') }}
        </span>
        <strong class="text-lg font-bold text-gray-900">{!! wc_format_datetime($order->get_date_created()) !!}</strong>
      </div>
      <div class="mt-2 md:mt-0">
        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
          {{ __('Спосіб оплати', 'solidshop') }}
        </span>
        <strong class="text-lg font-bold text-gray-900">
          @if ($order->get_payment_method_title())
            {{ $order->get_payment_method_title() }}
          @else
            {{ __('Онлайн оплата', 'solidshop') }}
          @endif
        </strong>
      </div>
      <div class="mt-2 md:mt-0">
        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
          {{ __('Сума до сплати', 'solidshop') }}
        </span>
        <strong class="text-2xl font-extrabold text-blue-600">
          {!! $order->get_formatted_order_total() !!}
        </strong>
      </div>
    </div>

    {{-- Оболонка платіжних дій (сюди вбудовується віджет) --}}
    <div class="solidshop-payment-actions-wrapper">
      @php do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() ); @endphp
    </div>

  </div>

</div>
