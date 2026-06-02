{{--
  Product card — grid layout for catalog / wishlist.
  Картка товару — сітка для каталогу / wishlist.

  @var \WC_Product $product
  @var bool $show_wishlist_toggle
  @var bool $show_remove_button
--}}
@php
  if (! defined('ABSPATH') || ! isset($product) || ! $product instanceof \WC_Product) {
      return;
  }

  $show_wishlist_toggle = $show_wishlist_toggle ?? true;
  $show_remove_button   = $show_remove_button ?? false;
  $product_brands       = wp_get_post_terms($product->get_id(), 'product_brand');
@endphp

<article
  class="wishlist-product-card bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full group relative"
  data-product-id="{{ $product->get_id() }}"
>
  <div class="relative aspect-square bg-gray-50 overflow-hidden">
    @include('partials.product-badges', ['product' => $product])

    @if ($show_wishlist_toggle)
      <div class="absolute top-2 right-2 z-10 bg-white/90 border border-gray-100">
        @include('partials.wishlist-toggle', ['product_id' => $product->get_id()])
      </div>
    @endif

    <a href="{{ esc_url($product->get_permalink()) }}" class="block w-full h-full">
      {!! $product->get_image('woocommerce_thumbnail', [
        'class' => 'w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300',
      ]) !!}
    </a>
  </div>

  <div class="p-4 flex flex-col justify-between flex-grow">
    <div>
      @if (! empty($product_brands) && ! is_wp_error($product_brands))
        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1">
          {{ $product_brands[0]->name }}
        </span>
      @endif

      <h3 class="text-sm font-semibold text-gray-900 mb-1.5 line-clamp-2">
        <a href="{{ esc_url($product->get_permalink()) }}" class="hover:text-blue-600 transition-colors">
          {{ $product->get_name() }}
        </a>
      </h3>
    </div>

    <div class="mt-4 flex items-center justify-between gap-2 border-t border-gray-100 pt-3">
      <span class="text-base font-black text-gray-950 leading-tight">{!! $product->get_price_html() !!}</span>

      @if ($show_remove_button)
        <button
          type="button"
          class="js-wishlist-toggle text-xs font-bold uppercase tracking-wide text-gray-600 hover:text-black transition-colors is-in-wishlist"
          data-product-id="{{ $product->get_id() }}"
          aria-pressed="true"
        >
          {{ __('Видалити', 'solidshop') }}
        </button>
      @else
        <div class="product-loop-action-btn">
          @php woocommerce_template_loop_add_to_cart() @endphp
        </div>
      @endif
    </div>
  </div>
</article>
