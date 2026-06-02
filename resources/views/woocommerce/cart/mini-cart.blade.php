{{--
  Mini-cart drawer content — premium side-cart layout
  Вміст drawer міні-кошика — преміальний боковий кошик

  @see woocommerce/templates/cart/mini-cart.php
  @version 10.0.0
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  /** @var string $list_class */
  $list_class = $list_class ?? '';
@endphp

@php do_action('woocommerce_before_mini_cart'); @endphp

@if (WC()->cart && ! WC()->cart->is_empty())
  @php
    $progress = $progress ?? ['threshold' => 0, 'subtotal' => 0, 'percent' => 0, 'reached' => false];
    $upsells  = $upsells ?? [];
  @endphp

  @if ($progress['threshold'] > 0)
    <div class="mini-cart__progress" aria-live="polite">
      <div class="mini-cart__progress-track" role="progressbar"
           aria-valuemin="0" aria-valuemax="100"
           aria-valuenow="{{ (int) $progress['percent'] }}">
        <span class="mini-cart__progress-fill" style="width: {{ $progress['percent'] }}%;"></span>
      </div>
      <p class="mini-cart__progress-text">
        @if ($progress['reached'])
          {!! wp_kses(
            __('Ви отримали <strong>безкоштовну доставку</strong>!', 'solidshop'),
            ['strong' => []]
          ) !!}
        @else
          {!! wp_kses(
            sprintf(
              /* translators: %s: minimum order amount for free shipping */
              __('Отримайте <strong>безкоштовну доставку</strong> для замовлень від %s', 'solidshop'),
              wc_price($progress['threshold'])
            ),
            ['strong' => []]
          ) !!}
        @endif
      </p>
    </div>
  @endif

  <div class="mini-cart__scroll">
    <ul class="mini-cart__items woocommerce-mini-cart cart_list product_list_widget {{ esc_attr($list_class) }}">
      @php do_action('woocommerce_before_mini_cart_contents'); @endphp

      @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
        @php
          $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
          $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

          if (
            ! $_product
            || ! $_product->exists()
            || $cart_item['quantity'] <= 0
            || ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)
          ) {
              continue;
          }

          $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
          $product_price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
          $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
          $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail', ['class' => 'mini-cart__thumb-img']), $cart_item, $cart_item_key);
          $item_class        = apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key);
          $min_qty           = max(1, $_product->get_min_purchase_quantity());
          $max_qty           = $_product->get_max_purchase_quantity();
          $current_qty       = (int) $cart_item['quantity'];
        @endphp

        <li class="mini-cart__item woocommerce-mini-cart-item {{ esc_attr($item_class) }}">
          <div class="mini-cart__item-row">
            <div class="mini-cart__item-main">
              {!! apply_filters(
                'woocommerce_cart_item_remove_link',
                sprintf(
                  '<a role="button" href="%s" class="mini-cart__remove remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></a>',
                  esc_url(wc_get_cart_remove_url($cart_item_key)),
                  esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                  esc_attr($product_id),
                  esc_attr($cart_item_key),
                  esc_attr($_product->get_sku()),
                  esc_attr(sprintf(__('&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce'), wp_strip_all_tags($product_name)))
                ),
                $cart_item_key
              ) !!}

              <div class="mini-cart__item-text">
                @if ($product_permalink)
                  <a href="{{ esc_url($product_permalink) }}" class="mini-cart__item-name">
                    {!! wp_kses_post($product_name) !!}
                  </a>
                @else
                  <span class="mini-cart__item-name">{!! wp_kses_post($product_name) !!}</span>
                @endif

                <span class="mini-cart__item-price">{!! $product_price !!}</span>

                {!! wc_get_formatted_cart_item_data($cart_item) !!}

                <div class="mini-cart__qty" data-mini-cart-qty-wrap>
                  <button type="button" class="mini-cart__qty-btn" data-mini-cart-qty="minus"
                          data-cart-item-key="{{ esc_attr($cart_item_key) }}"
                          data-min="{{ $min_qty }}"
                          @if($max_qty > 0) data-max="{{ $max_qty }}" @endif
                          aria-label="{{ __('Зменшити кількість', 'solidshop') }}">−</button>
                  <span class="mini-cart__qty-value" data-mini-cart-qty-value>{{ $current_qty }}</span>
                  <button type="button" class="mini-cart__qty-btn" data-mini-cart-qty="plus"
                          data-cart-item-key="{{ esc_attr($cart_item_key) }}"
                          data-min="{{ $min_qty }}"
                          @if($max_qty > 0) data-max="{{ $max_qty }}" @endif
                          aria-label="{{ __('Збільшити кількість', 'solidshop') }}">+</button>
                </div>
              </div>
            </div>

            @if ($product_permalink)
              <a href="{{ esc_url($product_permalink) }}" class="mini-cart__thumb">
                {!! $thumbnail !!}
              </a>
            @else
              <div class="mini-cart__thumb">{!! $thumbnail !!}</div>
            @endif
          </div>
        </li>
      @endforeach

      @php do_action('woocommerce_mini_cart_contents'); @endphp
    </ul>

    @if (! empty($upsells))
      <div class="mini-cart__upsells" data-mini-cart-upsells>
        <h3 class="mini-cart__upsells-title">{{ __('Перед тим як піти', 'solidshop') }}</h3>

        <div class="mini-cart__upsells-slides">
          @foreach ($upsells as $index => $upsell)
            @php
              $upsell_id = $upsell->get_id();
            @endphp
            <div class="mini-cart__upsell-card" data-upsell-slide="{{ $index }}" @if($index > 0) hidden @endif>
              <a href="{{ get_permalink($upsell_id) }}" class="mini-cart__upsell-thumb">
                {!! $upsell->get_image('woocommerce_thumbnail', ['class' => 'mini-cart__upsell-img']) !!}
              </a>
              <div class="mini-cart__upsell-info">
                <a href="{{ get_permalink($upsell_id) }}" class="mini-cart__upsell-name">{{ $upsell->get_name() }}</a>
                <span class="mini-cart__upsell-price">{!! $upsell->get_price_html() !!}</span>
              </div>
              <button type="button"
                      class="mini-cart__upsell-add"
                      data-upsell-add="{{ $upsell_id }}"
                      aria-label="{{ esc_attr(sprintf(__('Додати %s до кошика', 'solidshop'), $upsell->get_name())) }}">
                {{ __('Натисніть, щоб додати', 'solidshop') }}
              </button>
            </div>
          @endforeach
        </div>

        @if (count($upsells) > 1)
          <div class="mini-cart__upsells-footer">
            <div class="mini-cart__upsells-dots" data-upsell-dots>
              @foreach ($upsells as $index => $upsell)
                <span class="mini-cart__upsells-dot {{ $index === 0 ? 'is-active' : '' }}" data-upsell-dot="{{ $index }}"></span>
              @endforeach
            </div>
            <div class="mini-cart__upsells-nav">
              <button type="button" data-upsell-prev aria-label="{{ __('Попередній', 'solidshop') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              </button>
              <button type="button" data-upsell-next aria-label="{{ __('Наступний', 'solidshop') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        @endif
      </div>
    @endif
  </div>

  <div class="mini-cart__footer">
    <div class="mini-cart__subtotal">
      <span class="mini-cart__subtotal-label">{{ __('Підсумок:', 'solidshop') }}</span>
      <span class="mini-cart__subtotal-value">{!! WC()->cart->get_cart_subtotal() !!}</span>
    </div>
    <a href="{{ esc_url(wc_get_checkout_url()) }}" class="mini-cart__checkout ss-btn">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
      </svg>
      {{ __('Оформити замовлення', 'solidshop') }}
    </a>
  </div>
@else
  <div class="mini-cart__empty-wrap">
    <p class="mini-cart__empty woocommerce-mini-cart__empty-message">
      {{ __('У кошику поки немає товарів.', 'solidshop') }}
    </p>
    <a href="{{ esc_url(wc_get_page_permalink('shop')) }}" class="mini-cart__shop-link ss-btn">
      {{ __('Перейти до магазину', 'solidshop') }}
    </a>
  </div>
@endif

@php do_action('woocommerce_after_mini_cart'); @endphp
