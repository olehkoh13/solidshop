{{--
  Catalog product card — grid or list layout.
  Картка товару каталогу — сітка або список.

  @var \WC_Product|null $product
  @var string           $layout  grid|list
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  if (! isset($product) || ! $product instanceof \WC_Product) {
      return;
  }

  $layout = isset($layout) ? sanitize_key((string) $layout) : 'grid';
  $is_list = $layout === 'list';

  \App\solidshop_mark_product_card_render();

  $category      = \App\solidshop_loop_product_category($product);
  $color_swatches = \App\solidshop_loop_color_swatches($product);
  $size_options  = \App\solidshop_loop_size_options($product);
  $rating_count  = (int) $product->get_rating_count();
  $is_variable   = $product->is_type('variable');
  $is_simple     = $product->is_type('simple');
  $permalink     = esc_url($product->get_permalink());

  $card_classes = $is_list
      ? 'ss-product-card ss-product-card--list bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-row p-4 gap-5 items-center group relative'
      : 'ss-product-card bg-white border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col h-full group relative';
@endphp

<article
  class="{{ $card_classes }}"
  data-product-id="{{ $product->get_id() }}"
>
  <div class="{{ $is_list ? 'w-32 h-32 shrink-0' : 'w-full' }} ss-product-card__media relative bg-gray-50 overflow-hidden {{ $is_list ? 'rounded-lg' : 'aspect-square' }}">
    @include('partials.product-badges', ['product' => $product])

    <div class="ss-product-card__wishlist absolute top-2 right-2 z-20">
      @include('partials.wishlist-toggle', ['product_id' => $product->get_id()])
    </div>

    <a href="{{ $permalink }}" class="block w-full h-full ss-product-card__image-link" aria-label="{{ esc_attr($product->get_name()) }}">
      {!! $product->get_image('woocommerce_thumbnail', [
        'class' => 'w-full h-full object-cover object-center transition-transform duration-300 group-hover:scale-103',
      ]) !!}
    </a>
  </div>

  <div class="ss-product-card__body {{ $is_list ? 'flex flex-col md:flex-row justify-between items-start md:items-center flex-grow gap-4 min-w-0' : 'p-4 flex flex-col flex-grow' }}">
    <div class="{{ $is_list ? 'min-w-0' : 'flex-grow' }}">
      @if ($category !== '')
        <span class="ss-product-card__category text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-1">
          {{ $category }}
        </span>
      @endif

      <h3 class="ss-product-card__title text-sm font-semibold text-gray-900 mb-1.5 {{ $is_list ? '' : 'line-clamp-2' }}">
        <a href="{{ $permalink }}" class="hover:text-gray-700 transition-colors">
          {{ $product->get_name() }}
        </a>
      </h3>

      @if ($rating_count > 0)
        <div class="ss-product-card__rating mb-1.5">
          @php woocommerce_template_loop_rating(); @endphp
        </div>
      @endif

      <div class="ss-product-card__price text-base font-black text-gray-950 leading-tight">
        {!! $product->get_price_html() !!}
      </div>

      @if (! empty($color_swatches))
        <div class="ss-product-card__colors flex flex-wrap gap-2 mt-3" aria-label="{{ __('Доступні кольори', 'solidshop') }}">
          @foreach ($color_swatches as $swatch)
            <span
              class="ss-product-card__color-swatch w-6 h-6 rounded-full border border-gray-200 shrink-0"
              style="background-color: {{ esc_attr($swatch['hex']) }};"
              title="{{ esc_attr($swatch['label']) }}"
            ></span>
          @endforeach
        </div>
      @endif
    </div>

    <div class="ss-product-card__hover-actions {{ $is_list ? 'shrink-0 w-full md:w-auto' : '' }}">
      @if ($is_simple && $product->is_purchasable())
        <div class="ss-product-card__add-to-cart">
          @php woocommerce_template_loop_add_to_cart(); @endphp
        </div>
      @elseif ($is_variable && ! empty($size_options))
        <div class="ss-product-card__quick-add">
          <p class="ss-product-card__quick-add-label text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">
            {{ __('Швидке додавання', 'solidshop') }}
          </p>
          <div class="ss-product-card__size-grid flex flex-wrap gap-2">
            @foreach ($size_options as $size)
              <button
                type="button"
                class="ss-product-card__size-btn"
                data-product-id="{{ $product->get_id() }}"
                data-variation-id="{{ $size['variation_id'] }}"
                aria-label="{{ esc_attr(sprintf(__('Додати розмір %s до кошика', 'solidshop'), $size['label'])) }}"
              >
                {{ $size['label'] }}
              </button>
            @endforeach
          </div>
        </div>
      @else
        <div class="ss-product-card__add-to-cart">
          @php woocommerce_template_loop_add_to_cart(); @endphp
        </div>
      @endif
    </div>
  </div>
</article>
