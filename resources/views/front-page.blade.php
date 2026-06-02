{{--
  Template Name: Homepage
  B2B premium front page — sharp corners, brand blue accents, minimalist borders.
  B2B головна — гострі кути, фірмовий синій акцент, мінімалістичні бордери.

  @package App
--}}

@extends('layouts.app')

@section('content')
  @php
    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');
  @endphp

  {{-- 1. Hero Section — split layout / Герой — розділений layout --}}
  <section class="front-page-hero max-w-7xl mx-auto px-4 sm:px-6 py-section">
    <div class="grid lg:grid-cols-12 items-center gap-10">
      <div class="lg:col-span-6">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
          {{ __('Преміальні рішення для вашого бізнесу', 'solidshop') }}
        </h1>
        <p class="text-lg text-gray-600 mb-8">
          {{ __('Надійні поставки, оптові ціни та бездоганний сервіс. Зареєструйтесь для отримання спеціальних умов.', 'solidshop') }}
        </p>
        <div class="flex flex-col sm:flex-row flex-wrap gap-4">
          <a
            href="{{ esc_url($shop_url) }}"
            class="ss-btn px-8 py-4 uppercase text-sm rounded-none no-underline"
          >
            {{ __('Каталог товарів', 'solidshop') }}
          </a>
          <a
            href="#"
            class="ss-btn ss-btn-outline px-8 py-4 uppercase text-sm rounded-none no-underline"
          >
            {{ __('Умови співпраці', 'solidshop') }}
          </a>
        </div>
      </div>

      <div class="lg:col-span-6">
        {{-- Featured image placeholder / Placeholder головного зображення --}}
        <div
          class="aspect-[4/3] w-full bg-gray-100 border border-gray-200 shadow-sm rounded-none"
          role="img"
          aria-label="{{ __('Зображення для бізнес-клієнтів', 'solidshop') }}"
        ></div>
      </div>
    </div>
  </section>

  {{-- 2. Trust Indicators — B2B features bar / Індикатори довіри --}}
  <section class="front-page-trust bg-gray-50 border-y border-gray-200 py-section" aria-label="{{ __('Переваги співпраці', 'solidshop') }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <div>
        <svg class="w-8 h-8 mx-auto mb-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
        </svg>
        <p class="font-bold text-sm uppercase tracking-wide text-gray-900">{{ __('Швидка доставка', 'solidshop') }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ __('По всій Україні для B2B-клієнтів', 'solidshop') }}</p>
      </div>

      <div>
        <svg class="w-8 h-8 mx-auto mb-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.375M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
        </svg>
        <p class="font-bold text-sm uppercase tracking-wide text-gray-900">{{ __('Оптові ціни', 'solidshop') }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ __('Спеціальні умови для партнерів', 'solidshop') }}</p>
      </div>

      <div>
        <svg class="w-8 h-8 mx-auto mb-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
        </svg>
        <p class="font-bold text-sm uppercase tracking-wide text-gray-900">{{ __('Гарантія якості', 'solidshop') }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ __('Сертифікована продукція', 'solidshop') }}</p>
      </div>

      <div>
        <svg class="w-8 h-8 mx-auto mb-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
        </svg>
        <p class="font-bold text-sm uppercase tracking-wide text-gray-900">{{ __('Підтримка 24/7', 'solidshop') }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ __('Персональний менеджер', 'solidshop') }}</p>
      </div>
    </div>
  </section>

  {{-- 3. Featured Categories — manual + sales fallback / Популярні категорії --}}
  @php
    $front_categories = \App\solidshop_get_front_page_categories(4);
  @endphp

  @if (! empty($front_categories))
    {{-- Mobile: horizontal swipe carousel / На mobile — горизонтальний swipe --}}
    <section class="front-page-categories py-section max-w-7xl mx-auto px-4 sm:px-6" data-product-carousel>
      <h2 class="text-2xl md:text-3xl font-bold mb-10 text-gray-900">
        {{ __('Популярні категорії', 'solidshop') }}
      </h2>

      <div
        class="solidshop-related-track flex md:grid md:grid-cols-4 overflow-x-auto md:overflow-visible snap-x snap-mandatory md:snap-none gap-4 md:gap-6 -mx-4 sm:-mx-6 md:mx-0 md:px-0 pb-2 md:pb-0"
        tabindex="0"
        role="region"
        aria-label="{{ __('Популярні категорії', 'solidshop') }}"
      >
        @foreach ($front_categories as $term)
          @php
            $category_link = get_term_link($term);
            $thumb_id      = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
            $category_img  = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'woocommerce_thumbnail') : '';
          @endphp

          @if (! is_wp_error($category_link))
            <div class="solidshop-related-slide snap-start shrink-0 w-[82%] max-w-[320px] md:contents">
              <a href="{{ esc_url($category_link) }}" class="group block no-underline">
                <div class="aspect-square bg-gray-50 border border-gray-200 group-hover:border-black transition rounded-none overflow-hidden">
                  @if ($category_img)
                    <img
                      src="{{ esc_url($category_img) }}"
                      alt="{{ esc_attr($term->name) }}"
                      class="w-full h-full object-cover"
                      loading="lazy"
                    />
                  @endif
                </div>
                <p class="font-bold mt-4 text-gray-900">{{ $term->name }}</p>
              </a>
            </div>
          @endif
        @endforeach
      </div>

      <div class="solidshop-related-dots flex justify-center gap-2 mt-4 md:hidden" hidden></div>
    </section>
  @endif

  {{-- 4. Featured Products — custom loop / Лідери продажів --}}
  <section class="front-page-products py-section bg-white max-w-7xl mx-auto px-4 sm:px-6 border-t border-gray-200">
    <div class="flex items-end justify-between mb-10 gap-4">
      <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
        {{ __('Лідери продажів', 'solidshop') }}
      </h2>
      <a
        href="{{ esc_url($shop_url) }}"
        class="text-sm font-bold uppercase tracking-wide text-blue-600 hover:text-blue-700 transition-colors shrink-0 no-underline"
      >
        {{ __('Усі товари', 'solidshop') }} &rarr;
      </a>
    </div>

    @php
      // Featured first, fallback to latest / Спочатку featured, інакше останні
      $featured_query = new \WP_Query([
          'post_type'      => 'product',
          'posts_per_page' => 4,
          'post_status'    => 'publish',
          'tax_query'      => [[
              'taxonomy' => 'product_visibility',
              'field'    => 'name',
              'terms'    => 'featured',
          ]],
      ]);

      if (! $featured_query->have_posts()) {
          wp_reset_postdata();
          $featured_query = new \WP_Query([
              'post_type'      => 'product',
              'posts_per_page' => 4,
              'post_status'    => 'publish',
              'orderby'        => 'date',
              'order'          => 'DESC',
          ]);
      }
    @endphp

    @if ($featured_query->have_posts())
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @while ($featured_query->have_posts())
          @php
            $featured_query->the_post();
            global $product;
            $product = wc_get_product(get_the_ID());
          @endphp
          @if ($product instanceof \WC_Product)
            @include('partials.product-card-catalog', [
              'product' => $product,
              'layout'  => 'grid',
            ])
          @endif
        @endwhile
      </div>
      @php wp_reset_postdata(); @endphp
    @else
      <p class="text-gray-500 text-center py-8">{{ __('Товарів не знайдено.', 'solidshop') }}</p>
    @endif
  </section>

  {{-- 5. New Arrivals — 8 latest products, mobile swipe carousel / Новинки — 8 останніх, swipe на mobile --}}
  {{-- After editing this template run: wp acorn view:clear / Після змін: wp acorn view:clear --}}
  @php
    // Latest catalog-visible products / Останні опубліковані товари, видимі в каталозі
    $new_arrivals_query = new \WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => [[
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'exclude-from-catalog',
            'operator' => 'NOT IN',
        ]],
    ]);
  @endphp

  @if ($new_arrivals_query->have_posts())
    <section class="front-page-new-arrivals py-section bg-gray-50 border-t border-gray-200" data-product-carousel>
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-10 gap-4">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
            {{ __('Наші новинки', 'solidshop') }}
          </h2>
          <a
            href="{{ esc_url($shop_url) }}"
            class="text-sm font-bold uppercase tracking-wide text-blue-600 hover:text-blue-700 transition-colors shrink-0 no-underline"
          >
            {{ __('В каталог', 'solidshop') }} &rarr;
          </a>
        </div>

        <ul
          class="solidshop-related-track solidshop-catalog-grid flex md:grid overflow-x-auto md:overflow-visible snap-x snap-mandatory md:snap-none gap-4 md:gap-6 -mx-4 sm:-mx-6 md:mx-0 md:px-0 md:grid-cols-4 pb-2 md:pb-0 list-none"
          tabindex="0"
          role="region"
          aria-label="{{ __('Наші новинки', 'solidshop') }}"
        >
          @while ($new_arrivals_query->have_posts())
            @php
              $new_arrivals_query->the_post();
              global $product;
              $product = wc_get_product(get_the_ID());
            @endphp
            @if ($product instanceof \WC_Product)
              <li class="solidshop-related-slide snap-start shrink-0 w-[82%] max-w-[320px] md:contents">
                @include('partials.product-card-catalog', [
                  'product' => $product,
                  'layout'  => 'grid',
                ])
              </li>
            @endif
          @endwhile
        </ul>

        <div class="solidshop-related-dots flex justify-center gap-2 mt-4 md:hidden" hidden></div>
      </div>
    </section>
    @php wp_reset_postdata(); @endphp
  @endif

  {{-- 6. Featured Brands — manual featured + sales fallback / Популярні бренди --}}
  {{--
    Hybrid logic / Гібридна логіка:
    1) Brands with "Show on Front Page" term meta appear first.
       Бренди з meta «Show on Front Page» — першими.
    2) If fewer than 6, remaining slots fill from top product_brand terms by total_sales.
       Якщо менше 6 — добираємо product_brand за сумою total_sales товарів.
    After editing this template run: wp acorn view:clear
    Після змін шаблону: wp acorn view:clear
  --}}
  @php
    $front_brands = \App\solidshop_get_front_page_brands(6);
  @endphp

  @if (! empty($front_brands))
    <section class="front-page-brands py-section max-w-7xl mx-auto px-4 sm:px-6 border-t border-gray-200">
      <h2 class="text-2xl font-bold mb-4 text-center text-gray-900">
        {{ __('Наші партнери та бренди', 'solidshop') }}
      </h2>
      <p class="text-center text-gray-600 max-w-2xl mx-auto mb-8">
        {{ __('Ми обираємо найкраще, щоб ви могли бути впевнені в якості. Немає жодних компромісів, коли йдеться про матеріали, зручність носіння та довговічність.', 'solidshop') }}
      </p>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-center">
        @foreach ($front_brands as $brand)
          @php
            $brand_link = get_term_link($brand);
            $brand_logo = \App\solidshop_get_brand_thumbnail_url($brand);
          @endphp

          @if (! is_wp_error($brand_link))
            <a
              href="{{ esc_url($brand_link) }}"
              class="flex items-center justify-center p-6 bg-white border border-gray-200 hover:border-black transition-colors rounded-none h-24 grayscale hover:grayscale-0 duration-300 no-underline"
            >
              @if ($brand_logo)
                <img
                  src="{{ esc_url($brand_logo) }}"
                  alt="{{ esc_attr($brand->name) }}"
                  class="max-h-12 w-full object-contain"
                  loading="lazy"
                />
              @else
                <span class="text-sm font-bold text-gray-900 text-center">{{ $brand->name }}</span>
              @endif
            </a>
          @endif
        @endforeach
      </div>
    </section>
  @endif

  {{-- 6. CTA / Newsletter — partner signup / Заклик до співпраці --}}
  <section class="front-page-cta bg-gray-900 text-white py-section text-center px-4">
    <h2 class="text-2xl md:text-3xl font-bold mb-4">
      {{ __('Готові до співпраці?', 'solidshop') }}
    </h2>
    <p class="text-gray-300 mb-8 max-w-xl mx-auto">
      {{ __('Приєднуйтесь до мережі партнерів SolidShop та отримуйте пріоритетні умови закупівлі.', 'solidshop') }}
    </p>
    <a
      href="#"
      class="ss-btn px-8 py-4 uppercase text-sm rounded-none no-underline"
    >
      {{ __('Стати партнером', 'solidshop') }}
    </a>
  </section>
@endsection
