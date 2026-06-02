{{--
  Template Name: Wishlist
  Шаблон: Wishlist (Обране)
--}}
@extends('layouts.app')

@section('content')
  @php
    $wishlist_ids = \App\solidshop_get_wishlist();
    $shop_url     = function_exists('wc_get_page_id')
        ? get_permalink(wc_get_page_id('shop'))
        : home_url('/shop/');
  @endphp

  <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-section">
    <header class="text-center mb-10">
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
        {{ __('Обране', 'solidshop') }}
      </h1>
      @if (! empty($wishlist_ids))
        <p class="text-sm text-gray-500 mt-2">
          {{ sprintf(
            _n('%d товар', '%d товари', count($wishlist_ids), 'solidshop'),
            count($wishlist_ids)
          ) }}
        </p>
      @endif
    </header>

    @if (empty($wishlist_ids))
      {{-- Empty state / Порожній wishlist --}}
      <div class="max-w-md mx-auto text-center bg-white border border-gray-200 p-10">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        <h2 class="text-lg font-bold text-gray-900 mb-2">{{ __('Ваш список обраного порожній', 'solidshop') }}</h2>
        <p class="text-sm text-gray-500 mb-6">{{ __('Додайте товари, натиснувши на серце в каталозі.', 'solidshop') }}</p>
        <a
          href="{{ esc_url($shop_url) }}"
          class="inline-flex items-center justify-center bg-black hover:bg-gray-800 text-white font-bold py-3 px-6 rounded-none transition-colors no-underline"
        >
          {{ __('Перейти до каталогу', 'solidshop') }}
        </a>
      </div>
    @else
      @php
        $wishlist_query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'post__in'       => $wishlist_ids,
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
        ]);
      @endphp

      @if ($wishlist_query->have_posts())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="wishlist-grid">
          @while ($wishlist_query->have_posts())
            @php
              $wishlist_query->the_post();
              $product = wc_get_product(get_the_ID());
            @endphp

            @if ($product instanceof \WC_Product)
              @include('partials.product-card-grid', [
                'product'              => $product,
                'show_wishlist_toggle' => false,
                'show_remove_button'   => true,
              ])
            @endif
          @endwhile
        </div>
        @php wp_reset_postdata(); @endphp
      @else
        <div class="max-w-md mx-auto text-center bg-white border border-gray-200 p-10">
          <p class="text-sm text-gray-500 mb-6">{{ __('Товари з вашого списку більше недоступні.', 'solidshop') }}</p>
          <a
            href="{{ esc_url($shop_url) }}"
            class="inline-flex items-center justify-center bg-black hover:bg-gray-800 text-white font-bold py-3 px-6 rounded-none transition-colors no-underline"
          >
            {{ __('Перейти до каталогу', 'solidshop') }}
          </a>
        </div>
      @endif
    @endif
  </div>
@endsection
