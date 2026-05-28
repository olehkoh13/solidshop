{{--
  Template Name: Homepage
  @package App
--}}

@extends('layouts.app')

@section('content')
  {{-- 1. Секція Головного Банера (Hero Section) --}}
  <section class="relative bg-gray-900 text-white overflow-hidden rounded-b-2xl shadow-lg mb-12">
    <div class="absolute inset-0 opacity-40 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=2070');"></div>
    <div class="relative container mx-auto px-6 py-24 md:py-32 flex flex-col items-start z-10">
      <span class="bg-blue-600 text-xs uppercase font-bold tracking-widest px-3 py-1 rounded-full mb-4">New Collection</span>
      <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 max-w-2xl leading-tight">
        Сучасні рішення <br>для вашого інтер'єру
      </h1>
      <p class="text-lg md:text-xl text-gray-300 mb-8 max-w-md">
        Ексклюзивні товары високої якості з безкоштовною доставкою по всій Україні.
      </p>
      <a href="/shop/" class="bg-white text-gray-950 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-sm">
        Перейти до каталогу
      </a>
    </div>
  </section>

  <div class="container mx-auto px-4">
    {{-- 2. Секція Категорій Товарів --}}
    <section class="mb-16">
      <div class="flex justify-between items-end mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Популярні категорії</h2>
          <p class="text-sm text-gray-500 mt-1">Швидкий пошук товарів за категоріями</p>
        </div>
      </div>

      {{-- Динамічний вивід категорій через native WooCommerce API --}}
      @php
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'number'     => 4
        ]);
      @endphp

      @if(!empty($categories) && !is_wp_error($categories))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          @foreach($categories as $cat)
            @php
              $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
              $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=600';
            @endphp
            <a href="{{ get_term_link($cat) }}" class="group relative flex flex-col justify-end aspect-[4/3] bg-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
              <img src="{{ $image_url }}" alt="{{ $cat->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
              <div class="absolute inset-0 bg-gradient-to-t from-gray-950/70 via-transparent to-transparent"></div>
              <h3 class="relative text-white font-semibold p-4 text-lg z-10">{{ $cat->name }}</h3>
            </a>
          @endforeach
        </div>
      @endif
    </section>

{{-- НОВА СЕКЦІЯ: Бренди магазину (маленькі етикетки) --}}
    <section class="mb-16">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Популярні бренди</h2>
        <p class="text-sm text-gray-500 mt-1">Обирайте товари від найкращих виробників</p>
      </div>

      @php
        // Автоматично витягуємо всі заповнені бренди з нашої таксономії
        // Якщо ярлик таксономії відрізняється, ми його просто скоригуємо тут
        $all_brands = get_terms([
            'taxonomy'   => 'product_brand',
            'hide_empty' => true, // Показувати тільки ті бренди, де є товари
        ]);
      @endphp

      @if(!empty($all_brands) && !is_wp_error($all_brands))
        <div class="flex flex-wrap gap-3">
          @foreach($all_brands as $brand)
            <a href="{{ get_term_link($brand) }}" class="inline-flex items-center justify-center bg-white border border-gray-200 text-gray-800 text-sm font-semibold px-4 py-2.5 rounded-xl hover:border-blue-600 hover:text-blue-600 hover:shadow-sm transition-all duration-200">
              {{ $brand->name }}
              <span class="ml-1.5 text-xs text-gray-400 font-normal">({{ $brand->count }})</span>
            </a>
          @endforeach
        </div>
      @else
        <p class="text-gray-400 text-sm italic">Брендів із товарами поки не знайдено. Перевірте прив'язку в адмінці.</p>
      @endif
    </section>





    {{-- 3. Секція Останніх Товарів (Новинки) --}}
    <section class="mb-16">
      <div class="flex justify-between items-end mb-8">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Нові надходження</h2>
          <p class="text-sm text-gray-500 mt-1">Товари, які щойно з'явилися в нашому магазині</p>
        </div>
        <a href="/shop/" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
          Усі товари &rarr;
        </a>
      </div>

      {{-- Запит на 4 останніх товари --}}
      @php
        $products_query = new \WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'status'         => 'publish',
        ]);
      @endphp

      @if($products_query->have_posts())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          @while($products_query->have_posts()) @php $products_query->the_post(); global $product; @endphp
            <article class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col h-full group">

              {{-- Зображення товару --}}
              <div class="relative aspect-square bg-gray-50 overflow-hidden">
                {!! $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300']) !!}
              </div>

              {{-- Контентна частина картки --}}
              <div class="p-4 flex flex-col justify-between flex-grow">
                <div>
                  {{-- Динамічний вивід офіційного бренду WooCommerce --}}
                  @php
                    $brands = wp_get_post_terms($product->get_id(), 'product_brand');
                  @endphp
                  @if(!empty($brands) && !is_wp_error($brands))
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1">
                      {{ $brands[0]->name }}
                    </span>
                  @else
                    <span class="text-[11px] font-medium uppercase tracking-wider text-gray-400 block mb-1">
                      No Brand
                    </span>
                  @endif

                  {{-- Назва товару --}}
                  <h3 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                    <a href="{{ get_permalink() }}" class="hover:text-blue-600 transition-colors">
                      {{ $product->get_name() }}
                    </a>
                  </h3>
                </div>

                {{-- Ціна та Кнопка --}}
                <div class="mt-4 flex items-center justify-between">
                  <span class="text-base font-bold text-gray-900">
                    {!! $product->get_price_html() !!}
                  </span>
                  @php woocommerce_template_loop_add_to_cart() @endphp
                </div>
              </div>

            </article>
          @endwhile @php wp_reset_postdata(); @endphp
        </div>
      @else
        <p class="text-gray-500 text-center py-6">Товарів не знайдено.</p>
      @endif
    </section>
  </div>
@endsection
