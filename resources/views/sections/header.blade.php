<header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm font-sans antialiased">
  <div class="container mx-auto px-4 h-20 flex items-center justify-between">

    {{-- Блок Логотипу та кнопки Каталогу --}}
    <div class="flex items-center gap-6">
      <a href="{{ home_url('/') }}" class="text-2xl font-black tracking-tight text-gray-900 hover:opacity-90 transition-opacity select-none">
        SOLID<span class="text-blue-600">SHOP</span>
      </a>

      {{-- Кнопка Каталогу в стилі Розетки --}}
      <div class="relative id-mega-menu-wrapper">
        <button id="mega-menu-trigger" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow-sm focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
          Каталог
        </button>

        {{-- Контейнер Мега-Меню --}}
        <div id="mega-menu-dropdown" class="hidden absolute top-full left-0 mt-3 w-[800px] bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">

          @if (has_nav_menu('primary_navigation'))
            @php
              // Отримуємо чисті об'єкти елементів меню
              $locations = get_nav_menu_locations();
              $menu = wp_get_nav_menu_object($locations['primary_navigation']);
              $menu_items = wp_get_nav_menu_items($menu->term_id, ['update_post_term_cache' => false]);

              // Будуємо дерево для зручного перебору (SOLID approach)
              $menu_tree = [];
              foreach ($menu_items as $item) {
                  if (!$item->menu_item_parent) {
                      $menu_tree[$item->ID] = ['item' => $item, 'children' => []];
                  } else {
                      if (isset($menu_tree[$item->menu_item_parent])) {
                          $menu_tree[$item->menu_item_parent]['children'][$item->ID] = ['item' => $item, 'children' => []];
                      } else {
                          // Шукаємо глибшу вкладеність (рівень 3)
                          foreach ($menu_tree as $top_id => $top_node) {
                              if (isset($top_node['children'][$item->menu_item_parent])) {
                                  $menu_tree[$top_id]['children'][$item->menu_item_parent]['children'][$item->ID] = ['item' => $item];
                              }
                          }
                      }
                  }
              }
            @endphp

            <div class="flex min-h-[400px]">
              {{-- Ліва панель: Головні батьківські категорії (Бокове меню Розетки) --}}
              <div class="w-1/3 bg-gray-50 border-r border-gray-100 p-2 flex flex-col gap-0.5">
                @foreach ($menu_tree as $top_id => $node)
                  {{-- Якщо це кнопка Каталог, перебираємо її прямих дітей як головні розділи --}}
                  @if(str_contains(mb_strtolower($node['item']->title), 'каталог'))
                    @foreach($node['children'] as $cat_id => $cat_node)
                      <button class="mega-menu-tab-trigger w-full flex items-center justify-between text-left px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors focus:outline-none" data-tab="tab-{{ $cat_id }}">
                        <span>{{ $cat_node['item']->title }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 opacity-50">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                      </button>
                    @endforeach
                  @endif
                @endforeach
              </div>

              {{-- Права панель: Підкатегорії та посилання (Контентна сітка Розетки) --}}
              <div class="w-2/3 p-6 bg-white relative">
                @foreach ($menu_tree as $top_id => $node)
                  @if(str_contains(mb_strtolower($node['item']->title), 'каталог'))
                    @foreach($node['children'] as $cat_id => $cat_node)
                      <div id="tab-{{ $cat_id }}" class="mega-menu-tab-content hidden grid grid-cols-2 gap-6 animate-fade-in">
                        @if(!empty($cat_node['children']))
                          @foreach($cat_node['children'] as $sub_id => $sub_node)
                            <div>
                              <a href="{{ $sub_node['item']->url }}" class="font-bold text-sm text-gray-900 hover:text-blue-600 transition-colors block mb-2">
                                {{ $sub_node['item']->title }}
                              </a>
                            </div>
                          @endforeach
                        @else
                          <p class="text-xs text-gray-400 col-span-2 italic">Для цієї категорії немає підкатегорій.</p>
                        @endif
                      </div>
                    @endforeach
                  @endif
                @endforeach
              </div>
            </div>
          @else
            <p class="p-4 text-xs text-gray-400">Будь ласка, створіть меню в адмінці та призначте його як Primary Navigation.</p>
          @endif

        </div>
      </div>
    </div>

    {{-- Звичайні статичні сторінки (Блог, Контакти тощо) праворуч від каталогу --}}
    <div class="flex items-center gap-6">
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
        <a href="/blog/" class="hover:text-blue-600 transition-colors">Блог</a>
        <a href="/about-us/" class="hover:text-blue-600 transition-colors">Про нас</a>
        <a href="/contacts/" class="hover:text-blue-600 transition-colors">Контакты</a>
      </nav>

      <span class="h-6 w-px bg-gray-200"></span>

      {{-- Права частина: Кошик WooCommerce --}}
      @if (class_exists('WooCommerce'))
        <a href="{{ wc_get_cart_url() }}" class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors select-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
          <span class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center transform translate-x-1 -translate-y-1">
            {{ WC()->cart->get_cart_contents_count() }}
          </span>
        </a>
      @endif
    </div>

  </div>
</header>
