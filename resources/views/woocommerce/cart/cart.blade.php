{{--
  Cart page — premium B2B two-column layout
  Сторінка кошика — двоколонковий premium B2B макет

  @see woocommerce/templates/cart/cart.php
  @version 10.8.0
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $progress = function_exists('\App\solidshop_free_shipping_progress')
      ? \App\solidshop_free_shipping_progress()
      : ['threshold' => 0, 'subtotal' => 0, 'percent' => 0, 'reached' => false];
@endphp

@php do_action('woocommerce_before_cart'); @endphp

<div class="solidshop-cart-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-section">
  @include('partials.checkout-steps', ['current_step' => 1])

  <div class="solidshop-cart-page__grid grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start mt-10">

    {{-- Left: line items + perks / Ліва колонка: товари + переваги --}}
    <div class="lg:col-span-7 xl:col-span-8">
      <form class="woocommerce-cart-form solidshop-cart-form" action="{{ esc_url(wc_get_cart_url()) }}" method="post">

        @php do_action('woocommerce_before_cart_table'); @endphp

        <div class="solidshop-cart-table">
          <div class="solidshop-cart-table__head" aria-hidden="true">
            <span class="solidshop-cart-table__head-product">{{ __('Товар', 'solidshop') }}</span>
            <span class="solidshop-cart-table__head-total">{{ __('Загалом', 'solidshop') }}</span>
          </div>

          <div class="solidshop-cart-table__body woocommerce-cart-form__contents">
            @php do_action('woocommerce_before_cart_contents'); @endphp

            @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
              @php
                $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                $visible    = apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key);
              @endphp

              @if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && $visible)
                @php
                  $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                  $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                  $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail', ['class' => 'solidshop-cart-item__img']), $cart_item, $cart_item_key);

                  if ($_product->is_sold_individually()) {
                      $min_quantity = 1;
                      $max_quantity = 1;
                  } else {
                      $min_quantity = 0;
                      $max_quantity = $_product->get_max_purchase_quantity();
                  }

                  $product_quantity = woocommerce_quantity_input([
                    'input_name'   => "cart[{$cart_item_key}][qty]",
                    'input_value'  => $cart_item['quantity'],
                    'max_value'    => $max_quantity,
                    'min_value'    => $min_quantity,
                    'product_name' => $product_name,
                  ], $_product, false);

                  $line_subtotal = apply_filters(
                    'woocommerce_cart_item_subtotal',
                    WC()->cart->get_product_subtotal($_product, $cart_item['quantity']),
                    $cart_item,
                    $cart_item_key
                  );

                  $regular_price = (float) $_product->get_regular_price();
                  $sale_price    = (float) $_product->get_sale_price();
                  $saved_amount  = 0.0;

                  if ($_product->is_on_sale() && $regular_price > 0 && $sale_price > 0) {
                      $saved_amount = ($regular_price - $sale_price) * (int) $cart_item['quantity'];
                  }
                @endphp

                <article class="solidshop-cart-item woocommerce-cart-form__cart-item {{ esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)) }}">
                  <div class="solidshop-cart-item__remove product-remove">
                    {!! apply_filters(
                      'woocommerce_cart_item_remove_link',
                      sprintf(
                        '<a role="button" href="%s" class="remove solidshop-cart-item__remove-link" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></a>',
                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                        esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                        esc_attr($product_id),
                        esc_attr($_product->get_sku())
                      ),
                      $cart_item_key
                    ) !!}
                  </div>

                  <div class="solidshop-cart-item__media product-thumbnail">
                    @if ($product_permalink)
                      <a href="{{ esc_url($product_permalink) }}">{!! $thumbnail !!}</a>
                    @else
                      {!! $thumbnail !!}
                    @endif
                  </div>

                  <div class="solidshop-cart-item__details product-name">
                    @if ($product_permalink)
                      <a href="{{ esc_url($product_permalink) }}" class="solidshop-cart-item__title">
                        {!! wp_kses_post($product_name) !!}
                      </a>
                    @else
                      <span class="solidshop-cart-item__title">{!! wp_kses_post($product_name) !!}</span>
                    @endif

                    <div class="solidshop-cart-item__price product-price">
                      {!! apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key) !!}
                    </div>

                    <div class="solidshop-cart-item__meta">
                      {!! wc_get_formatted_cart_item_data($cart_item) !!}
                    </div>

                    @if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity']))
                      <p class="solidshop-cart-item__backorder">
                        {{ __('Available on backorder', 'woocommerce') }}
                      </p>
                    @endif

                    <div class="solidshop-cart-item__qty product-quantity">
                      {!! apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item) !!}
                    </div>
                  </div>

                  <div class="solidshop-cart-item__total product-subtotal">
                    <div class="solidshop-cart-item__total-value">{!! $line_subtotal !!}</div>
                    @if ($saved_amount > 0)
                      <div class="solidshop-cart-item__save-badge">
                        {{ __('Зберегти', 'solidshop') }}
                        {!! wc_price($saved_amount) !!}
                      </div>
                    @endif
                  </div>
                </article>
              @endif
            @endforeach

            @php do_action('woocommerce_cart_contents'); @endphp
          </div>
        </div>

        @php do_action('woocommerce_after_cart_table'); @endphp

        {{-- Free shipping progress / Прогрес безкоштовної доставки --}}
        @if ($progress['threshold'] > 0)
          <div class="solidshop-cart-progress" aria-live="polite">
            <div class="solidshop-cart-progress__track" role="progressbar"
                 aria-valuemin="0" aria-valuemax="100"
                 aria-valuenow="{{ (int) $progress['percent'] }}">
              <span class="solidshop-cart-progress__fill" style="width: {{ $progress['percent'] }}%;"></span>
            </div>
            <p class="solidshop-cart-progress__text">
              @if ($progress['reached'])
                {!! wp_kses(
                  __('Ви отримали <strong>безкоштовну доставку</strong>!', 'solidshop'),
                  ['strong' => []]
                ) !!}
              @else
                @php
                  $threshold_formatted = wp_strip_all_tags(wc_price($progress['threshold'], ['decimals' => 0]));
                @endphp
                {!! wp_kses(
                  sprintf(
                    __('Отримайте <strong>безкоштовну доставку</strong> для замовлень від %s.', 'solidshop'),
                    $threshold_formatted
                  ),
                  ['strong' => []]
                ) !!}
                <a href="{{ esc_url(wc_get_page_permalink('shop')) }}" class="solidshop-cart-progress__link">
                  {{ __('Продовжити покупки', 'solidshop') }}
                </a>
              @endif
            </p>
          </div>
        @endif

        {{-- Coupon row / Ряд купона --}}
        @if (wc_coupons_enabled())
          <div class="solidshop-cart-coupon coupon">
            <label for="coupon_code" class="screen-reader-text">{{ esc_html_e('Coupon:', 'woocommerce') }}</label>
            <input
              type="text"
              name="coupon_code"
              class="input-text solidshop-cart-coupon__input"
              id="coupon_code"
              value=""
              placeholder="{{ __('Код купона', 'solidshop') }}"
            />
            <button
              type="submit"
              class="button solidshop-cart-coupon__btn"
              name="apply_coupon"
              value="{{ __('Apply coupon', 'woocommerce') }}"
            >
              {{ __('Застосувати купон', 'solidshop') }}
            </button>
            @php do_action('woocommerce_cart_coupon'); @endphp
          </div>
        @endif

        {{-- Info blocks / Інформаційні блоки --}}
        <div class="solidshop-cart-info">
          <div class="solidshop-cart-info__block">
            <h3 class="solidshop-cart-info__title">{{ __('Доставка', 'solidshop') }}</h3>
            <p class="solidshop-cart-info__text">
              {{ __('Замовлення до 22:00 — безкоштовна доставка наступного дня. Стандартна доставка 3–5 робочих днів.', 'solidshop') }}
            </p>
          </div>
          <div class="solidshop-cart-info__block">
            <h3 class="solidshop-cart-info__title">{{ __('Легке повернення', 'solidshop') }}</h3>
            <p class="solidshop-cart-info__text">
              {{ __('Повернення протягом 14 днів без зайвих питань. Гарантія повернення коштів.', 'solidshop') }}
            </p>
          </div>
        </div>

        <button type="submit" class="button solidshop-cart-update hidden" name="update_cart" value="{{ __('Update cart', 'woocommerce') }}">
          {{ esc_html_e('Update cart', 'woocommerce') }}
        </button>

        @php do_action('woocommerce_cart_actions'); @endphp
        @php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); @endphp
      </form>
    </div>

    {{-- Right: totals sidebar / Права колонка: підсумки --}}
    <aside class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-24">
      @php do_action('woocommerce_before_cart_collaterals'); @endphp

      <div class="cart-collaterals solidshop-cart-collaterals">
        @php do_action('woocommerce_cart_collaterals'); @endphp
      </div>

      @php do_action('woocommerce_after_cart_collaterals'); @endphp
    </aside>
  </div>
</div>

@php do_action('woocommerce_after_cart'); @endphp
