{{--
  Cart sidebar — shipping methods (full-width card list)
  Бокова панель кошика — способи доставки (повноширинні картки)

  @see woocommerce/templates/cart/cart-shipping.php
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $packages = WC()->shipping()->get_packages();
  $shipping_progress = function_exists('\App\solidshop_free_shipping_progress')
      ? \App\solidshop_free_shipping_progress()
      : ['reached' => false];
@endphp

@foreach ($packages as $index => $package)
  @php
    $available_methods        = $package['rates'] ?? [];
    $chosen_method            = WC()->session->chosen_shipping_methods[$index] ?? '';
    $formatted_destination    = WC()->countries->get_formatted_address($package['destination'], ', ');
    $has_calculated_shipping  = WC()->customer->has_calculated_shipping();
    $show_shipping_calculator = false;
    $package_name             = $package['package_name'] ?? __('Доставка', 'solidshop');
  @endphp

  <section class="solidshop-cart-shipping woocommerce-shipping-totals shipping" aria-labelledby="solidshop-cart-shipping-heading-{{ $index }}">
    <div class="solidshop-cart-shipping__header">
      <h3 id="solidshop-cart-shipping-heading-{{ $index }}" class="solidshop-cart-shipping__title">
        {{ __('Доставка', 'solidshop') }}
      </h3>
      @if ($formatted_destination)
        <p class="solidshop-cart-shipping__destination">
          {{ __('Доставка до', 'solidshop') }}
          <strong>{{ $formatted_destination }}</strong>
        </p>
      @endif
    </div>

    <div class="solidshop-cart-shipping__body">
      @if (! empty($available_methods) && is_array($available_methods))
        <ul id="shipping_method" class="woocommerce-shipping-methods solidshop-cart-shipping__methods">
          @foreach ($available_methods as $method)
            @php
              $method_id = esc_attr(sanitize_title($method->id));
              $input_id  = "shipping_method_{$index}_{$method_id}";
              $rate_display = function_exists('\App\solidshop_cart_shipping_rate_display')
                  ? \App\solidshop_cart_shipping_rate_display($method)
                  : ['html' => wc_price((float) $method->cost), 'is_free' => (float) $method->cost <= 0];
            @endphp
            <li class="solidshop-cart-shipping__method{{ count($available_methods) === 1 ? ' is-single' : '' }}">
              @if (count($available_methods) > 1)
                <input
                  type="radio"
                  name="shipping_method[{{ $index }}]"
                  data-index="{{ $index }}"
                  id="{{ $input_id }}"
                  value="{{ esc_attr($method->id) }}"
                  class="shipping_method"
                  {!! checked($method->id, $chosen_method, false) !!}
                />
              @else
                <input
                  type="hidden"
                  name="shipping_method[{{ $index }}]"
                  data-index="{{ $index }}"
                  id="{{ $input_id }}"
                  value="{{ esc_attr($method->id) }}"
                  class="shipping_method"
                />
              @endif

              <label for="{{ $input_id }}" class="solidshop-cart-shipping__label">
                <span class="solidshop-cart-shipping__name">
                  {!! wp_kses_post(wc_cart_totals_shipping_method_label($method)) !!}
                </span>
                <span class="solidshop-cart-shipping__price {{ ! empty($rate_display['is_free']) ? 'is-free' : '' }}">
                  {!! wp_kses_post($rate_display['html']) !!}
                </span>
              </label>

              @php do_action('woocommerce_after_shipping_rate', $method, $index); @endphp
            </li>
          @endforeach
        </ul>

        <p class="solidshop-cart-shipping__note">
          {{ __('Точну адресу та відділення можна обрати під час оформлення замовлення.', 'solidshop') }}
        </p>
      @elseif (! $has_calculated_shipping || ! $formatted_destination)
        <p class="solidshop-cart-shipping__empty">
          {{ __('Вартість доставки буде розрахована під час оформлення замовлення.', 'solidshop') }}
        </p>
      @else
        <p class="solidshop-cart-shipping__empty">
          {{ __('Немає доступних варіантів доставки для обраної адреси.', 'solidshop') }}
        </p>
      @endif
    </div>
  </section>
@endforeach
