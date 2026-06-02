{{--
  Cross-sell strip — «Добре поєднується з»
  Смуга cross-sell у правій колонці
--}}
@php
  /** @var \WC_Product $product */
  $ids = $product->get_cross_sell_ids();
  if (empty($ids)) {
    $ids = $product->get_upsell_ids();
  }
  if (empty($ids)) {
    $related = wc_get_related_products($product->get_id(), 3);
    $ids = is_array($related) ? array_slice($related, 0, 2) : [];
  }

  $cross_products = [];
  foreach ($ids as $id) {
    $p = wc_get_product($id);
    if ($p && $p->is_purchasable() && $p->is_in_stock()) {
      $cross_products[] = $p;
    }
  }
@endphp

@if (! empty($cross_products))
  <div class="product-cross-sells" data-cross-sells>
    <div class="product-cross-sells__header">
      <h3 class="product-cross-sells__title">{{ __('Добре поєднується з', 'solidshop') }}</h3>
      @if (count($cross_products) > 1)
        <div class="product-cross-sells__nav">
          <button type="button" data-cross-prev aria-label="{{ __('Попередній', 'solidshop') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" data-cross-next aria-label="{{ __('Наступний', 'solidshop') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      @endif
    </div>

    <div class="product-cross-sells__slides">
      @foreach ($cross_products as $index => $cross)
        <div class="product-cross-sells__card" data-cross-slide="{{ $index }}" @if($index > 0) hidden @endif>
          <a href="{{ get_permalink($cross->get_id()) }}">
            {!! $cross->get_image('woocommerce_thumbnail', ['class' => 'product-cross-sells__thumb']) !!}
          </a>
          <div class="product-cross-sells__info">
            <p class="product-cross-sells__name">
              <a href="{{ get_permalink($cross->get_id()) }}">{{ $cross->get_name() }}</a>
            </p>
            <p class="product-cross-sells__price">{!! $cross->get_price_html() !!}</p>
            <div class="product-cross-sells__action product-loop-action-btn">
              @php
                $GLOBALS['product'] = $cross;
                woocommerce_template_loop_add_to_cart();
                $GLOBALS['product'] = $product;
              @endphp
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif
