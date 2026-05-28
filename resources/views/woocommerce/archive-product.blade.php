{{--
  The Template for displaying product archives, including the main shop page which is a post type archive.
  @package App
--}}

@extends('layouts.app')

@section('content')
  <div class="container mx-auto px-4 py-8 font-sans antialiased">

    {{-- Головний заголовок каталогу --}}
    <header class="mb-6">
      <h1 class="text-3xl font-black text-gray-900 tracking-tight">
        @php woocommerce_page_title() @endphp
      </h1>
    </header>

    <div class="flex flex-col lg:flex-row gap-8 items-start">

      {{-- ========================================== --}}
      {{-- 1. БІЧНА ПАНЕЛЬ ФІЛЬТРІВ (Sidebar)          --}}
      {{-- ========================================== --}}
      <aside class="w-full lg:w-1/4 bg-white border border-gray-100 p-5 rounded-xl shadow-sm shrink-0 sticky top-24">

        {{-- ІЗОЛЬОВАНА ФОРМА ДЛЯ ФІЛЬТРІВ БРЕНДІВ ТА ЦІН --}}
        <script>
        function shopFilterSubmit() {
            var f = document.getElementById('shop-sidebar-filter-form');
            f.querySelectorAll('input[name=min_price],input[name=max_price]').forEach(function(el) {
                if (el.value === '') el.disabled = true;
            });
            f.submit();
        }
        </script>

        <form action="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp" method="get" id="shop-sidebar-filter-form"
              onsubmit="this.querySelectorAll('input[name=min_price],input[name=max_price]').forEach(function(el){if(el.value==='')el.disabled=true})">

          {{-- Зберігаємо поточний вигляд та сортування з URL, щоб не скидати їх при фільтрації цін/брендів --}}
          @if(request()->get('view'))
            <input type="hidden" name="view" value="{{ esc_attr(request()->get('view')) }}">
          @endif
          @if(request()->get('orderby'))
            <input type="hidden" name="orderby" value="{{ esc_attr(request()->get('orderby')) }}">
          @endif

          <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
            <h2 class="font-bold text-gray-900 text-base">Фільтри</h2>
            <a href="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">Скинути всі</a>
          </div>

          {{-- Фільтр категорій (статичні посилання, поза формою) --}}
          <div class="mb-6">
            <h3 class="font-semibold text-sm text-gray-900 uppercase tracking-wider mb-3">Категорії</h3>
            @php
              $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0]);
            @endphp
            @if(!empty($cats) && !is_wp_error($cats))
              <ul class="space-y-2 text-sm text-gray-600">
                @foreach($cats as $cat)
                  <li>
                    <a href="{{ get_term_link($cat) }}" class="hover:text-blue-600 flex justify-between items-center transition-colors">
                      <span>{{ $cat->name }}</span>
                      <span class="text-xs text-gray-400">({{ $cat->count }})</span>
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>

          {{-- Фільтр за брендами --}}
                    {{-- Фільтр за брендами (Динамічні чекбокси з префіксом f_brand) --}}
          <div class="mb-6 border-t border-gray-50 pt-5">
            <h3 class="font-semibold text-sm text-gray-900 uppercase tracking-wider mb-3">Бренд</h3>
            @php
              $brands = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => true]);
              $chosen_brands = request()->get('f_brand', []);
              if(!is_array($chosen_brands)) { $chosen_brands = explode(',', $chosen_brands); }
            @endphp
            @if(!empty($brands) && !is_wp_error($brands))
              <div class="space-y-2.5 max-h-48 overflow-y-auto pr-2">
                @foreach($brands as $brand)
                  @php $is_checked = in_array($brand->slug, $chosen_brands); @endphp
                  <label class="flex items-center justify-between text-sm text-gray-600 cursor-pointer group">
                    <div class="flex items-center gap-2.5">
                      <input type="checkbox" name="f_brand[]" value="{{ $brand->slug }}" onchange="shopFilterSubmit()" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ $is_checked ? 'checked' : '' }}>
                      <span class="{{ $is_checked ? 'text-blue-600 font-semibold' : 'group-hover:text-gray-900' }} transition-colors">{{ $brand->name }}</span>
                    </div>
                    <span class="text-xs text-gray-400">({{ $brand->count }})</span>
                  </label>
                @endforeach
              </div>
            @endif
          </div>

          {{-- Фільтр цін --}}
          <div class="border-t border-gray-50 pt-5">
            <h3 class="font-semibold text-sm text-gray-900 uppercase tracking-wider mb-3">Ціна, ₴</h3>
            <div class="flex items-center gap-2 mb-3">
              <input type="number" name="min_price" value="{{ request()->get('min_price') }}" placeholder="Від" class="w-1/2 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500">
              <span class="text-gray-300">-</span>
              <input type="number" name="max_price" value="{{ request()->get('max_price') }}" placeholder="До" class="w-1/2 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold py-2 rounded-lg transition-colors uppercase tracking-wider">
              Застосувати ціну
            </button>
          </div>

        </form>
      </aside>

      {{-- ========================================== --}}
      {{-- 2. КОНТЕНТНА ЗОНА                          --}}
      {{-- ========================================== --}}
      <div class="w-full lg:w-3/4">

        @if(have_posts())

          {{-- ВЕРХНІЙ ТУЛБАР --}}
          <div class="bg-white border border-gray-100 p-4 rounded-xl shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 select-none">
            <div class="text-sm text-gray-500 font-medium">
              @php woocommerce_result_count() @endphp
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
              {{-- Сортування (Абсолютно незалежний нативний блок) --}}
              <div class="flex items-center gap-2 custom-woocommerce-ordering text-sm">
                @php woocommerce_catalog_ordering() @endphp
              </div>

              {{-- Перемикач виглядів --}}
              @php $view_mode = request()->get('view', 'grid'); @endphp
              <div class="flex items-center border border-gray-200 rounded-lg p-0.5 bg-gray-50 shrink-0">
                <button type="button" onclick="setViewModeParam('grid')" class="p-1.5 rounded-md transition-colors {{ $view_mode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                  </svg>
                </button>
                <button type="button" onclick="setViewModeParam('list')" class="p-1.5 rounded-md transition-colors {{ $view_mode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          {{-- СІТКА ТОВАРІВ --}}
          <div class="{{ $view_mode === 'list' ? 'space-y-4' : 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6' }}">
            @while(have_posts()) @php the_post(); global $product; @endphp

              @if($view_mode === 'list')
                {{-- ВИГЛЯД СПИСКОМ --}}
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 flex flex-row p-4 gap-5 items-center group relative">
                  <div class="w-32 h-32 bg-gray-50 rounded-lg overflow-hidden shrink-0 relative">
                    <a href="{{ get_permalink() }}" class="block w-full h-full">
                      {!! $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300']) !!}
                    </a>
                  </div>
                  <div class="flex flex-col md:flex-row justify-between items-start md:items-center flex-grow gap-4">
                    <div>
                      @php $product_brands = wp_get_post_terms($product->get_id(), 'product_brand'); @endphp
                      @if(!empty($product_brands) && !is_wp_error($product_brands))
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block mb-0.5">{{ $product_brands[0]->name }}</span>
                      @endif
                      <h3 class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                        <a href="{{ get_permalink() }}">{{ $product->get_name() }}</a>
                      </h3>
                    </div>
                    <div class="flex items-center gap-4 shrink-0 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 border-gray-50 pt-2 md:pt-0">
                      <span class="text-lg font-black text-gray-950 whitespace-nowrap">{!! $product->get_price_html() !!}</span>
                      <div class="product-loop-action-btn select-none">@php woocommerce_template_loop_add_to_cart() @endphp</div>
                    </div>
                  </div>
                </article>
              @else
                {{-- ВИГЛЯД СІТКОЮ --}}
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col h-full group relative">
                  <div class="relative aspect-square bg-gray-50 overflow-hidden">
                    <a href="{{ get_permalink() }}" class="block w-full h-full">
                      {!! $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300']) !!}
                    </a>
                  </div>
                  <div class="p-4 flex flex-col justify-between flex-grow">
                    <div>
                      @php $product_brands = wp_get_post_terms($product->get_id(), 'product_brand'); @endphp
                      @if(!empty($product_brands) && !is_wp_error($product_brands))
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1">{{ $product_brands[0]->name }}</span>
                      @endif
                      <h3 class="text-sm font-semibold text-gray-900 mb-1.5 line-clamp-2">
                        <a href="{{ get_permalink() }}" class="hover:text-blue-600 transition-colors">{{ $product->get_name() }}</a>
                      </h3>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-gray-50 pt-3">
                      <span class="text-base font-black text-gray-950 leading-tight">{!! $product->get_price_html() !!}</span>
                      <div class="product-loop-action-btn">@php woocommerce_template_loop_add_to_cart() @endphp</div>
                    </div>
                  </div>
                </article>
              @endif

            @endwhile
          </div>

          {{-- Пагінація --}}
          <div class="mt-12 border-t border-gray-100 pt-8 flex justify-center id-custom-pagination">
            @php
              ob_start();
              woocommerce_pagination();
              $pagination_html = ob_get_clean();

              if (!empty($pagination_html)) {
                  $pagination_html = preg_replace('/<nav class="woocommerce-pagination"[^>]*>/i', '<nav class="flex items-center gap-2" aria-label="Пагінація товару">', $pagination_html);
                  $pagination_html = preg_replace('/<span[^>]*class="[^"]*current[^"]*"[^>]*>(.*?)<\/span>/i', '<span class="inline-flex items-center justify-center min-w-10 h-10 px-3 text-sm font-bold rounded-lg bg-blue-600 text-white border border-blue-600 shadow-sm select-none">$1</span>', $pagination_html);
                  $pagination_html = preg_replace('/<a[^>]*class="[^"]*page-numbers[^"]*"[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/i', '<a href="$1" class="inline-flex items-center justify-center min-w-10 h-10 px-3 text-sm font-semibold rounded-lg border border-gray-200 text-gray-700 bg-white hover:border-blue-600 hover:text-blue-600 hover:shadow-sm transition-all duration-200">$2</a>', $pagination_html);
                  $pagination_html = preg_replace('/<span[^>]*class="[^"]*dots[^"]*"[^>]*>(.*?)<\/span>/i', '<span class="inline-flex items-center justify-center w-10 h-10 text-gray-400 font-medium select-none">$1</span>', $pagination_html);
                  $pagination_html = str_replace(["<ul class='page-numbers'>", "</ul>", "<li>", "</li>"], "", $pagination_html);
                  echo $pagination_html;
              }
            @endphp
          </div>
        @else
          {{-- Пустий стан --}}
          <div class="bg-white border border-gray-100 rounded-xl p-12 text-center shadow-sm max-w-md mx-auto mt-10">
            <h3 class="text-base font-bold text-gray-900 mb-1">Товарів не знайдено</h3>
            <p class="text-sm text-gray-500 mb-4">Жоден товар не відповідає заданим критеріям.</p>
            <a href="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp" class="inline-flex bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
              Скинути фільтри
            </a>
          </div>
        @endif

      </div>
    </div>
  </div>

  {{-- Оновлений JS-скрипт без руйнування глобальних форм --}}
  <script>
    function setViewModeParam(mode) {
      const url = new URL(window.location.href);
      url.searchParams.set('view', mode);
      window.location.href = url.toString();
    }
  </script>
@endsection
