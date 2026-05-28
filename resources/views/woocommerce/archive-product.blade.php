{{--
  The Template for displaying product archives, including the main shop page which is a post type archive.
  @package App
--}}

@extends('layouts.app')

@section('content')
  <div class="container mx-auto px-4 py-8 font-sans antialiased">

    {{-- Головний заголовок каталогу / Main catalog heading --}}
    <header class="mb-6">
      <h1 class="text-3xl font-black text-gray-900 tracking-tight">
        @php woocommerce_page_title() @endphp
      </h1>
    </header>

    {{-- Кнопка відкриття фільтрів (тільки мобільні) / Open-filters button (mobile only) --}}
    <div class="lg:hidden mb-4">
      <button type="button" onclick="openMobileFilters()"
              class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 shadow-sm hover:border-gray-300 transition-colors">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        Фільтри
      </button>
    </div>

    {{-- ================================================================== --}}
    {{-- Основна сітка каталогу: десктоп 4 колонки / мобайл 1 стовп        --}}
    {{-- Main catalog grid: desktop 4 columns / mobile 1 column             --}}
    {{-- ================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">

      {{-- ================================================================== --}}
      {{-- 1. БІЧНА ПАНЕЛЬ ФІЛЬТРІВ / FILTER SIDEBAR                          --}}
      {{-- Mobile: фіксований повноекранний drawer / Mobile: fixed full-screen drawer --}}
      {{-- Desktop: статичний стовп сітки / Desktop: static grid column       --}}
      {{-- ================================================================== --}}
      <aside id="shop-filter-sidebar"
             class="fixed inset-0 z-50 bg-white overflow-x-hidden overflow-y-auto -translate-x-full transition-transform duration-300
                    lg:relative lg:inset-auto lg:z-auto lg:translate-x-0 lg:col-span-1 lg:overflow-visible lg:transition-none
                    lg:border lg:border-gray-100 lg:rounded-xl lg:shadow-sm lg:sticky lg:top-24">

        {{-- Мінімальний преміум-стиль скролбару для внутрішніх списків --}}
        {{-- Minimal premium scrollbar style for inner scrollable lists  --}}
        <style>
          .filter-scroll::-webkit-scrollbar { width: 4px; }
          .filter-scroll::-webkit-scrollbar-track { background: transparent; }
          .filter-scroll::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 9999px; }
          .filter-scroll::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }
        </style>

        {{-- Шапка мобільного drawer з кнопкою закриття (прихована на десктопі) --}}
        {{-- Mobile drawer header with close button (hidden on desktop)           --}}
        <div class="lg:hidden flex items-center justify-between px-5 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
          <span class="font-bold text-gray-900 text-base">Фільтри</span>
          <button type="button" onclick="closeMobileFilters()"
                  class="p-1.5 -mr-1.5 text-gray-400 hover:text-gray-700 transition-colors rounded-lg hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        {{-- Контейнер контенту панелі з відступами / Panel content container with padding --}}
        <div class="p-5">

          <form action="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp"
                method="get"
                id="shop-sidebar-filter-form"
                onsubmit="this.querySelectorAll('input[name=min_price],input[name=max_price]').forEach(function(el){if(el.value==='')el.disabled=true})">

            {{-- Зберігаємо вигляд та сортування з URL / Preserve view and sorting from URL --}}
            @if(request()->get('view'))
              <input type="hidden" name="view" value="{{ esc_attr(request()->get('view')) }}">
            @endif
            @if(request()->get('orderby'))
              <input type="hidden" name="orderby" value="{{ esc_attr(request()->get('orderby')) }}">
            @endif

            {{-- Заголовок панелі з кнопкою скидання / Panel header with reset link --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-1">
              {{-- Заголовок прихований на мобільних (є у шапці drawer) / Title hidden on mobile (already in drawer header) --}}
              <h2 class="hidden lg:block font-bold text-gray-900 text-base">Фільтри</h2>
              <a href="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp"
                 class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors ml-auto lg:ml-0">
                Скинути всі
              </a>
            </div>

            {{-- ============================================================= --}}
            {{-- Попереднє обчислення всіх даних фільтрів та станів акордеонів --}}
            {{-- Pre-compute all filter data and initial accordion open states  --}}
            {{-- ============================================================= --}}
            @php
              // Категорії / Categories
              $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0]);

              // Бренди / Brands
              $brands        = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => true]);
              $chosen_brands = request()->get('f_brand', []);
              if (!is_array($chosen_brands)) { $chosen_brands = explode(',', $chosen_brands); }

              // Кольори / Colors
              $color_terms   = get_terms(['taxonomy' => 'pa_color', 'hide_empty' => true]);
              $chosen_colors = request()->get('f_color', []);
              if (!is_array($chosen_colors)) { $chosen_colors = explode(',', $chosen_colors); }
              // Мапа slug → HEX для поширених кольорів / Slug → HEX map for common colors
              $color_map = [
                'black'     => '#111111', 'white'     => '#f5f5f5',
                'red'       => '#ef4444', 'blue'      => '#3b82f6',
                'grey'      => '#9ca3af', 'gray'      => '#9ca3af',
                'beige'     => '#e8d4b4', 'green'     => '#22c55e',
                'yellow'    => '#eab308', 'pink'      => '#f472b6',
                'orange'    => '#f97316', 'purple'    => '#a855f7',
                'navy'      => '#1e3a5f', 'brown'     => '#92400e',
                'gold'      => '#d97706', 'silver'    => '#cbd5e1',
                'turquoise' => '#2dd4bf', 'violet'    => '#7c3aed',
              ];
              // Темні свотчі: галочка біла / Dark swatches: white checkmark
              $dark_swatches = ['black', 'navy', 'blue', 'purple', 'brown', 'violet'];

              // Розміри / Sizes
              $size_terms   = get_terms(['taxonomy' => 'pa_size', 'hide_empty' => true]);
              $chosen_sizes = request()->get('f_size', []);
              if (!is_array($chosen_sizes)) { $chosen_sizes = explode(',', $chosen_sizes); }

              // Початковий стан акордеонів: відкрито якщо секція має активні фільтри
              // Initial accordion state: open if the section has active/checked filters
              $open_cats   = true; // Категорії завжди відкриті / Categories always open
              $open_brands = !empty($chosen_brands);
              $open_colors = !empty($chosen_colors);
              $open_sizes  = !empty($chosen_sizes);
              $open_price  = (bool)(request()->get('min_price') || request()->get('max_price'));
            @endphp

            {{-- ================================================================ --}}
            {{-- Компонент фільтра з акордеоном та внутрішнім скролом             --}}
            {{-- Filter component with accordion and internal scrollbar            --}}
            {{-- ================================================================ --}}

            {{-- === 1. КАТЕГОРІЇ / CATEGORIES === --}}
            @if(!empty($cats) && !is_wp_error($cats))
              <div class="filter-section border-b border-gray-100">
                <button type="button"
                        class="flex w-full items-center justify-between py-4 text-sm font-semibold uppercase tracking-wide text-gray-900 hover:text-gray-700 transition-colors"
                        onclick="toggleFilterSection(this)">
                  <span>Категорії</span>
                  <svg class="h-4 w-4 shrink-0 transform transition-transform duration-200 {{ $open_cats ? 'rotate-180' : '' }}"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                {{-- Контейнер зі скролом: макс висота 12rem (48), прихований горизонтальний скрол --}}
                {{-- Scrollable container: max height 12rem (48), hidden horizontal scroll           --}}
                <div class="filter-content overflow-hidden transition-all duration-200 {{ $open_cats ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' }}">
                  <ul class="filter-scroll max-h-48 overflow-y-auto space-y-2 text-sm text-gray-600 pb-4 pr-1">
                    @foreach($cats as $cat)
                      <li>
                        <a href="{{ get_term_link($cat) }}"
                           class="flex justify-between items-center hover:text-blue-600 transition-colors">
                          <span>{{ $cat->name }}</span>
                          <span class="text-xs text-gray-400">({{ $cat->count }})</span>
                        </a>
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            @endif

            {{-- === 2. БРЕНДИ / BRANDS === --}}
            {{-- Динамічні чекбокси з префіксом f_brand / Dynamic checkboxes with f_brand prefix --}}
            @if(!empty($brands) && !is_wp_error($brands))
              <div class="filter-section border-b border-gray-100">
                <button type="button"
                        class="flex w-full items-center justify-between py-4 text-sm font-semibold uppercase tracking-wide text-gray-900 hover:text-gray-700 transition-colors"
                        onclick="toggleFilterSection(this)">
                  <span>Бренд</span>
                  <svg class="h-4 w-4 shrink-0 transform transition-transform duration-200 {{ $open_brands ? 'rotate-180' : '' }}"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                {{-- Контейнер зі скролом / Scrollable container --}}
                <div class="filter-content overflow-hidden transition-all duration-200 {{ $open_brands ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' }}">
                  <div class="filter-scroll max-h-48 overflow-y-auto space-y-2.5 pb-4 pr-1">
                    @foreach($brands as $brand)
                      @php $is_checked = in_array($brand->slug, $chosen_brands); @endphp
                      <label class="flex items-center justify-between text-sm text-gray-600 cursor-pointer group">
                        <div class="flex items-center gap-2.5">
                          <input type="checkbox" name="f_brand[]" value="{{ $brand->slug }}"
                                 onchange="shopFilterSubmit()"
                                 class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                 {{ $is_checked ? 'checked' : '' }}>
                          <span class="{{ $is_checked ? 'text-blue-600 font-semibold' : 'group-hover:text-gray-900' }} transition-colors">
                            {{ $brand->name }}
                          </span>
                        </div>
                        <span class="text-xs text-gray-400">({{ $brand->count }})</span>
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif

            {{-- === 3. КОЛЬОРИ / COLORS (color swatches) === --}}
            @if(!empty($color_terms) && !is_wp_error($color_terms))
              <div class="filter-section border-b border-gray-100">
                <button type="button"
                        class="flex w-full items-center justify-between py-4 text-sm font-semibold uppercase tracking-wide text-gray-900 hover:text-gray-700 transition-colors"
                        onclick="toggleFilterSection(this)">
                  <span>Колір</span>
                  <svg class="h-4 w-4 shrink-0 transform transition-transform duration-200 {{ $open_colors ? 'rotate-180' : '' }}"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                {{-- Без скролу: кольорові кружечки у flex-гриді / No scroll: color swatches in flex grid --}}
                <div class="filter-content overflow-hidden transition-all duration-200 {{ $open_colors ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' }}">
                  <div class="flex flex-wrap gap-2.5 pb-4">
                    @foreach($color_terms as $ct)
                      @php
                        $cslug     = $ct->slug;
                        $chex      = $color_map[$cslug] ?? '#d1d5db';
                        $cactive   = in_array($cslug, $chosen_colors);
                        $check_cls = in_array($cslug, $dark_swatches) ? 'text-white' : 'text-gray-800';
                      @endphp
                      <label for="color-{{ $cslug }}" title="{{ esc_attr($ct->name) }}"
                             class="relative cursor-pointer group shrink-0">
                        <input type="checkbox" id="color-{{ $cslug }}" name="f_color[]"
                               value="{{ $cslug }}" class="sr-only"
                               {{ $cactive ? 'checked' : '' }} onchange="shopFilterSubmit()">
                        <span class="block w-8 h-8 rounded-full border border-gray-200 transition-all duration-150 group-hover:scale-110 {{ $cactive ? 'ring-2 ring-offset-2 ring-gray-700 scale-110' : '' }}"
                              style="background-color: {{ $chex }};"></span>
                        @if($cactive)
                          <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 {{ $check_cls }}" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                          </span>
                        @endif
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif

            {{-- === 4. РОЗМІРИ / SIZES (size badges) === --}}
            @if(!empty($size_terms) && !is_wp_error($size_terms))
              <div class="filter-section border-b border-gray-100">
                <button type="button"
                        class="flex w-full items-center justify-between py-4 text-sm font-semibold uppercase tracking-wide text-gray-900 hover:text-gray-700 transition-colors"
                        onclick="toggleFilterSection(this)">
                  <span>Розмір</span>
                  <svg class="h-4 w-4 shrink-0 transform transition-transform duration-200 {{ $open_sizes ? 'rotate-180' : '' }}"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                {{-- Без скролу: значки розмірів у flex-гриді / No scroll: size badges in flex grid --}}
                <div class="filter-content overflow-hidden transition-all duration-200 {{ $open_sizes ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' }}">
                  <div class="flex flex-wrap gap-2 pb-4">
                    @foreach($size_terms as $st)
                      @php
                        $sslug   = $st->slug;
                        $sactive = in_array($sslug, $chosen_sizes);
                      @endphp
                      <label for="size-{{ $sslug }}" title="{{ esc_attr($st->name) }}" class="cursor-pointer">
                        <input type="checkbox" id="size-{{ $sslug }}" name="f_size[]"
                               value="{{ $sslug }}" class="sr-only"
                               {{ $sactive ? 'checked' : '' }} onchange="shopFilterSubmit()">
                        <span class="flex items-center justify-center min-w-[2.5rem] h-10 px-2 rounded-lg text-xs font-bold uppercase border transition-all duration-150 select-none
                          {{ $sactive
                            ? 'bg-gray-900 text-white border-gray-900 shadow-sm'
                            : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400 hover:text-gray-900' }}">
                          {{ $st->name }}
                        </span>
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif

            {{-- === 5. ЦІНА / PRICE === --}}
            <div class="filter-section">
              <button type="button"
                      class="flex w-full items-center justify-between py-4 text-sm font-semibold uppercase tracking-wide text-gray-900 hover:text-gray-700 transition-colors"
                      onclick="toggleFilterSection(this)">
                <span>Ціна, ₴</span>
                <svg class="h-4 w-4 shrink-0 transform transition-transform duration-200 {{ $open_price ? 'rotate-180' : '' }}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
              <div class="filter-content overflow-hidden transition-all duration-200 {{ $open_price ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' }}">
                <div class="pb-4">
                  <div class="flex items-center gap-2 mb-3">
                    <input type="number" name="min_price" value="{{ request()->get('min_price') }}"
                           placeholder="Від"
                           class="w-1/2 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500">
                    <span class="text-gray-300">-</span>
                    <input type="number" name="max_price" value="{{ request()->get('max_price') }}"
                           placeholder="До"
                           class="w-1/2 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500">
                  </div>
                  <button type="submit"
                          class="w-full bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold py-2 rounded-lg transition-colors uppercase tracking-wider">
                    Застосувати ціну
                  </button>
                </div>
              </div>
            </div>

          </form>

        </div>{{-- /p-5 content wrapper --}}
      </aside>{{-- /shop-filter-sidebar --}}

      {{-- ================================================================== --}}
      {{-- 2. КОНТЕНТНА ЗОНА (3 колонки на десктопі) / CONTENT (3 cols desktop) --}}
      {{-- ================================================================== --}}
      <div class="col-span-1 lg:col-span-3">

        @if(have_posts())

          {{-- ВЕРХНІЙ ТУЛБАР / TOP TOOLBAR --}}
          <div class="bg-white border border-gray-100 p-4 rounded-xl shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 select-none">
            <div class="text-sm text-gray-500 font-medium">
              @php woocommerce_result_count() @endphp
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
              {{-- Сортування (незалежний нативний блок) / Sorting (standalone native block) --}}
              <div class="flex items-center gap-2 custom-woocommerce-ordering text-sm">
                @php woocommerce_catalog_ordering() @endphp
              </div>

              {{-- Перемикач виглядів / View mode switcher --}}
              @php $view_mode = request()->get('view', 'grid'); @endphp
              <div class="flex items-center border border-gray-200 rounded-lg p-0.5 bg-gray-50 shrink-0">
                <button type="button" onclick="setViewModeParam('grid')"
                        class="p-1.5 rounded-md transition-colors {{ $view_mode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                  </svg>
                </button>
                <button type="button" onclick="setViewModeParam('list')"
                        class="p-1.5 rounded-md transition-colors {{ $view_mode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          {{-- СІТКА / СПИСОК ТОВАРІВ / PRODUCT GRID / LIST --}}
          <div class="{{ $view_mode === 'list' ? 'space-y-4' : 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6' }}">
            @while(have_posts()) @php the_post(); global $product; @endphp

              @if($view_mode === 'list')
                {{-- ВИГЛЯД СПИСКОМ / LIST VIEW --}}
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
                {{-- ВИГЛЯД СІТКОЮ / GRID VIEW --}}
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

          {{-- Пагінація / Pagination --}}
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
          {{-- Пустий стан / Empty state --}}
          <div class="bg-white border border-gray-100 rounded-xl p-12 text-center shadow-sm max-w-md mx-auto mt-10">
            <h3 class="text-base font-bold text-gray-900 mb-1">Товарів не знайдено</h3>
            <p class="text-sm text-gray-500 mb-4">Жоден товар не відповідає заданим критеріям.</p>
            <a href="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp"
               class="inline-flex bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
              Скинути фільтри
            </a>
          </div>
        @endif

      </div>{{-- /col-span-1 lg:col-span-3 --}}
    </div>{{-- /grid grid-cols-1 lg:grid-cols-4 --}}
  </div>{{-- /container --}}

  {{-- ================================================================== --}}
  {{-- JavaScript: всі функції каталогу в одному блоці                    --}}
  {{-- JavaScript: all catalog functions in one consolidated block         --}}
  {{-- ================================================================== --}}
  <script>
    /**
     * Відправка форми фільтрів: вимикаємо порожні цінові поля перед сабмітом.
     * Filter form submit: disable empty price inputs before submitting.
     */
    function shopFilterSubmit() {
      var f = document.getElementById('shop-sidebar-filter-form');
      f.querySelectorAll('input[name=min_price],input[name=max_price]').forEach(function(el) {
        if (el.value === '') el.disabled = true;
      });
      f.submit();
    }

    /**
     * Перемикає акордеон секції фільтрів та повертає шеврон.
     * Toggles a filter section accordion and rotates its chevron.
     * @param {HTMLButtonElement} btn - кнопка-заголовок секції / section header button
     */
    function toggleFilterSection(btn) {
      var content = btn.nextElementSibling;
      var chevron = btn.querySelector('svg');
      var isOpen  = content.classList.contains('max-h-[500px]');

      if (isOpen) {
        // Закриваємо / Close
        content.classList.remove('max-h-[500px]', 'opacity-100');
        content.classList.add('max-h-0', 'opacity-0');
        chevron.classList.remove('rotate-180');
      } else {
        // Відкриваємо / Open
        content.classList.remove('max-h-0', 'opacity-0');
        content.classList.add('max-h-[500px]', 'opacity-100');
        chevron.classList.add('rotate-180');
      }
    }

    /**
     * Відкриває мобільний drawer фільтрів та блокує скрол сторінки.
     * Opens the mobile filter drawer and locks the page body scroll.
     */
    function openMobileFilters() {
      var sidebar = document.getElementById('shop-filter-sidebar');
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      // Блокуємо скрол фону / Lock background scroll
      document.body.classList.add('overflow-hidden');
    }

    /**
     * Закриває мобільний drawer фільтрів та розблоковує скрол сторінки.
     * Closes the mobile filter drawer and unlocks the page body scroll.
     */
    function closeMobileFilters() {
      var sidebar = document.getElementById('shop-filter-sidebar');
      sidebar.classList.remove('translate-x-0');
      sidebar.classList.add('-translate-x-full');
      // Розблоковуємо скрол / Unlock background scroll
      document.body.classList.remove('overflow-hidden');
    }

    /**
     * Змінює параметр view (grid/list) в URL та перезавантажує сторінку.
     * Changes the view param (grid/list) in URL and reloads the page.
     */
    function setViewModeParam(mode) {
      const url = new URL(window.location.href);
      url.searchParams.set('view', mode);
      window.location.href = url.toString();
    }
  </script>
@endsection
