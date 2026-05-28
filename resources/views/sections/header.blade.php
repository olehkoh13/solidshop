{{--
  Header: logo, catalog mega-menu (desktop only), nav links, cart, mobile drawer.
  overflow-x-hidden на header запобігає горизонтальному скролу / prevents horizontal scroll.
--}}
<header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm font-sans antialiased overflow-x-hidden w-full">
  <div class="container mx-auto px-4 h-16 sm:h-20 flex items-center justify-between max-w-full">

    {{-- ================================================= --}}
    {{-- ЛІВА ЧАСТИНА: Логотип + Кнопка Каталогу           --}}
    {{-- LEFT: Logo + Catalog button                        --}}
    {{-- ================================================= --}}
    <div class="flex items-center gap-4 lg:gap-6 min-w-0">

      {{-- Логотип / Logo --}}
      <a href="{{ home_url('/') }}"
         class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 hover:opacity-90 transition-opacity select-none shrink-0">
        SOLID<span class="text-blue-600">SHOP</span>
      </a>

      {{-- Кнопка Каталогу з Мега-Меню: тільки десктоп / Catalog button + mega-menu: desktop only --}}
      {{-- Прихована на мобільних/планшетах, щоб w-[800px] dropdown не створював горизонтальний скрол --}}
      {{-- Hidden on mobile/tablet so the w-[800px] dropdown cannot cause horizontal overflow         --}}
      <div class="relative id-mega-menu-wrapper hidden lg:block">
        <button id="mega-menu-trigger"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow-sm focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
          Каталог
        </button>

        {{-- Контейнер Мега-Меню (десктоп, 800px) / Mega-menu dropdown (desktop, 800px) --}}
        {{-- Ніколи не рендериться на мобільних, бо батьківський div прихований / Never rendered on mobile (parent is hidden) --}}
        <div id="mega-menu-dropdown"
             class="hidden absolute top-full left-0 mt-3 w-[800px] bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">

          @if (has_nav_menu('primary_navigation'))
            @php
              // Отримуємо об'єкти елементів меню / Get menu item objects
              $locations  = get_nav_menu_locations();
              $menu       = wp_get_nav_menu_object($locations['primary_navigation']);
              $menu_items = wp_get_nav_menu_items($menu->term_id, ['update_post_term_cache' => false]);

              // Будуємо дерево меню / Build menu tree
              $menu_tree = [];
              foreach ($menu_items as $item) {
                  if (!$item->menu_item_parent) {
                      $menu_tree[$item->ID] = ['item' => $item, 'children' => []];
                  } else {
                      if (isset($menu_tree[$item->menu_item_parent])) {
                          $menu_tree[$item->menu_item_parent]['children'][$item->ID] = ['item' => $item, 'children' => []];
                      } else {
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
              {{-- Ліва панель: Головні категорії / Left panel: Top-level categories --}}
              <div class="w-1/3 bg-gray-50 border-r border-gray-100 p-2 flex flex-col gap-0.5">
                @foreach ($menu_tree as $top_id => $node)
                  @if(str_contains(mb_strtolower($node['item']->title), 'каталог'))
                    @foreach($node['children'] as $cat_id => $cat_node)
                      <button class="mega-menu-tab-trigger w-full flex items-center justify-between text-left px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors focus:outline-none"
                              data-tab="tab-{{ $cat_id }}">
                        <span>{{ $cat_node['item']->title }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 opacity-50">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                      </button>
                    @endforeach
                  @endif
                @endforeach
              </div>

              {{-- Права панель: Підкатегорії / Right panel: Subcategories --}}
              <div class="w-2/3 p-6 bg-white relative">
                @foreach ($menu_tree as $top_id => $node)
                  @if(str_contains(mb_strtolower($node['item']->title), 'каталог'))
                    @foreach($node['children'] as $cat_id => $cat_node)
                      <div id="tab-{{ $cat_id }}" class="mega-menu-tab-content hidden grid grid-cols-2 gap-6 animate-fade-in">
                        @if(!empty($cat_node['children']))
                          @foreach($cat_node['children'] as $sub_id => $sub_node)
                            <div>
                              <a href="{{ $sub_node['item']->url }}"
                                 class="font-bold text-sm text-gray-900 hover:text-blue-600 transition-colors block mb-2">
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

        </div>{{-- /mega-menu-dropdown --}}
      </div>{{-- /id-mega-menu-wrapper --}}
    </div>{{-- /LEFT --}}

    {{-- ================================================= --}}
    {{-- ПРАВА ЧАСТИНА: Навігація + Кошик + Мобільне меню  --}}
    {{-- RIGHT: Nav links + Cart + Mobile hamburger         --}}
    {{-- ================================================= --}}
    <div class="flex items-center gap-2 lg:gap-6 shrink-0">

      {{-- Десктопна навігація: прихована на мобільних/планшетах / Desktop nav: hidden on mobile/tablet --}}
      <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-gray-600">
        <a href="/blog/"      class="hover:text-blue-600 transition-colors">Блог</a>
        <a href="/about-us/"  class="hover:text-blue-600 transition-colors">Про нас</a>
        <a href="/contacts/"  class="hover:text-blue-600 transition-colors">Контакти</a>
      </nav>

      {{-- Роздільник: тільки десктоп / Separator: desktop only --}}
      <span class="hidden lg:block h-6 w-px bg-gray-200"></span>

      {{-- Кошик WooCommerce (завжди видимий) / WooCommerce cart (always visible) --}}
      @if (class_exists('WooCommerce'))
        <a href="{{ wc_get_cart_url() }}"
           class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors select-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
          <span class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center transform translate-x-1 -translate-y-1">
            {{ WC()->cart->get_cart_contents_count() }}
          </span>
        </a>
      @endif

      {{-- Кнопка гамбургер: тільки мобільні/планшети / Hamburger button: mobile/tablet only --}}
      {{-- Прихована на десктопі / Hidden on desktop --}}
      <button type="button"
              id="mobile-nav-open-btn"
              onclick="openMobileNav()"
              class="lg:hidden flex items-center justify-center p-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition-colors"
              aria-label="Відкрити меню">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

    </div>{{-- /RIGHT --}}
  </div>{{-- /container --}}

  {{-- ======================================================== --}}
  {{-- МОБІЛЬНА НАВІГАЦІЯ: повноекранний drawer                  --}}
  {{-- MOBILE NAV DRAWER: full-screen slide-in panel             --}}
  {{-- Видимий тільки на мобільних/планшетах / Mobile/tablet only --}}
  {{-- ======================================================== --}}
  <nav id="mobile-nav-drawer"
       class="lg:hidden fixed inset-0 z-50 bg-white overflow-x-hidden overflow-y-auto -translate-x-full transition-transform duration-300"
       aria-label="Мобільна навігація">

    {{-- Шапка drawer з кнопкою закрити / Drawer header with close button --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
      <a href="{{ home_url('/') }}" class="text-xl font-black tracking-tight text-gray-900 select-none">
        SOLID<span class="text-blue-600">SHOP</span>
      </a>
      <button type="button"
              onclick="closeMobileNav()"
              class="p-1.5 -mr-1.5 text-gray-400 hover:text-gray-700 transition-colors rounded-lg hover:bg-gray-50"
              aria-label="Закрити меню">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Вміст мобільної навігації / Mobile nav content --}}
    <div class="px-5 py-4 space-y-1">

      {{-- Посилання на Каталог / Link to the shop/catalog --}}
      <a href="@php echo esc_url(get_permalink(wc_get_page_id('shop'))); @endphp"
         class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
        Каталог товарів
      </a>

      {{-- Статичні сторінки / Static pages --}}
      <div class="pt-2 border-t border-gray-100 mt-2 space-y-1">
        <a href="/blog/"
           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
          Блог
        </a>
        <a href="/about-us/"
           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
          Про нас
        </a>
        <a href="/contacts/"
           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
          Контакти
        </a>
      </div>

      {{-- Посилання на кошик / Link to cart --}}
      @if (class_exists('WooCommerce'))
        <div class="pt-2 border-t border-gray-100 mt-2">
          <a href="{{ wc_get_cart_url() }}"
             class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            Кошик
            <span class="ml-auto bg-blue-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
              {{ class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : '0' }}
            </span>
          </a>
        </div>
      @endif

    </div>{{-- /mobile nav content --}}
  </nav>{{-- /mobile-nav-drawer --}}

  {{-- ======================================================== --}}
  {{-- JS: мобільний drawer хедера / Header mobile drawer JS    --}}
  {{-- ======================================================== --}}
  <script>
    /**
     * Відкриває мобільне навігаційне меню + блокує скрол сторінки.
     * Opens the mobile nav drawer + locks page body scroll.
     */
    function openMobileNav() {
      var drawer = document.getElementById('mobile-nav-drawer');
      drawer.classList.remove('-translate-x-full');
      drawer.classList.add('translate-x-0');
      document.body.classList.add('overflow-hidden');
    }

    /**
     * Закриває мобільне навігаційне меню + розблоковує скрол сторінки.
     * Closes the mobile nav drawer + unlocks page body scroll.
     */
    function closeMobileNav() {
      var drawer = document.getElementById('mobile-nav-drawer');
      drawer.classList.remove('translate-x-0');
      drawer.classList.add('-translate-x-full');
      document.body.classList.remove('overflow-hidden');
    }
  </script>

</header>
