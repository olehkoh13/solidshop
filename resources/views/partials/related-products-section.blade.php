{{--
  Related products — same catalog cards as /shop/.
  Mobile: horizontal scroll-snap carousel with peek of next card.
  Супутні товари — картки каталогу; на mobile — swipe-кarousel з peek.
--}}
@php
  if (! isset($query) || ! $query instanceof \WP_Query) {
      return;
  }
@endphp

@if ($query->have_posts())
  <section class="related products solidshop-related-products" data-product-carousel>
    <h2>{{ apply_filters('woocommerce_product_related_products_heading', __('Супутні товари', 'solidshop')) }}</h2>

    <div
      class="solidshop-related-track solidshop-catalog-grid flex md:grid overflow-x-auto md:overflow-visible snap-x snap-mandatory md:snap-none gap-4 md:gap-6 -mx-4 sm:-mx-6 md:mx-0 md:px-0 md:grid-cols-2 lg:grid-cols-4 pb-2 md:pb-0"
      tabindex="0"
      role="region"
      aria-label="{{ __('Супутні товари', 'solidshop') }}"
    >
      @while ($query->have_posts())
        @php
          $query->the_post();
          global $product;
          $product = wc_get_product(get_the_ID());
        @endphp
        @if ($product instanceof \WC_Product)
          <div class="solidshop-related-slide snap-start shrink-0 w-[82%] max-w-[320px] md:contents">
            @include('partials.product-card-catalog', [
              'product' => $product,
              'layout'  => 'grid',
            ])
          </div>
        @endif
      @endwhile
    </div>

    <div class="solidshop-related-dots flex justify-center gap-2 mt-4 md:hidden" hidden></div>
  </section>
  @php wp_reset_postdata(); @endphp
@endif
