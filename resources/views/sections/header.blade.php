{{--
  Header: logo, catalog mega-menu (desktop only), nav links, cart, mobile drawer.
  Зовнішній шар: w-full (повна ширина екрану) / Outer layer: w-full (full screen width).
  Внутрішній шар: max-w-[1440px] вирівнює контент з каталогом / Inner layer: max-w-[1440px] aligns with catalog.
--}}
{{-- Зовнішній шар шапки: повна ширина фону / Header outer layer: full-width background --}}
{{-- overflow-x-hidden тут НЕ використовується — він обрізав би absolute dropdown мега-меню --}}
{{-- overflow-x-hidden is NOT used here — it would clip the absolute mega-menu dropdown     --}}
<header class="w-full bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm font-sans antialiased">
  {{-- Внутрішній шар: обмеження 1440px, адаптивні відступи / Inner layer: 1440px cap, responsive padding --}}
  <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

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
                class="ss-btn flex items-center gap-2 px-5 py-2.5 font-semibold text-sm shadow-sm focus:outline-none">
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

                      {{-- Правильна структура: кнопка окремо керує видимістю таба, посилання відкриває сторінку --}}
                      {{-- Correct structure: button manages tab visibility, link performs page navigation        --}}
                      <div class="mega-menu-tab-item flex items-center justify-between rounded-lg hover:bg-blue-50 transition-colors"
                           data-tab="tab-{{ $cat_id }}">

                        {{-- Навігаційне посилання на сторінку категорії / Category page navigation link --}}
                        <a href="{{ $cat_node['item']->url }}"
                           class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                          {{ $cat_node['item']->title }}
                        </a>

                        {{-- Кнопка-перемикач: тільки показує підкатегорії праворуч, навігації немає --}}
                        {{-- Tab-switch button: only reveals subcategories on the right, no navigation    --}}
                        <button type="button"
                                class="mega-menu-tab-trigger shrink-0 p-3 text-gray-400 hover:text-blue-600 transition-colors focus:outline-none"
                                data-tab="tab-{{ $cat_id }}"
                                aria-label="Показати підкатегорії">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                          </svg>
                        </button>

                      </div>{{-- /mega-menu-tab-item --}}
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

    {{-- Центральна колонка: live-пошук (десктоп) / Center column: live search (desktop) --}}
    @if (class_exists('WooCommerce'))
      <div class="hidden lg:flex flex-1 max-w-md xl:max-w-xl mx-4 min-w-0">
        @include('partials.live-search', ['uid' => 'desktop', 'variant' => 'inline'])
      </div>
    @endif

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

      {{-- Акаунт + wishlist + кошик / Account + wishlist + cart --}}
      @include('partials.header')

      @if (class_exists('WooCommerce'))
        {{-- Мобільний пошук: іконка відкриває fullscreen modal / Mobile search icon --}}
        <button type="button"
                class="js-live-search-open lg:hidden flex items-center justify-center p-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition-colors rounded-none"
                aria-label="{{ __('Відкрити пошук', 'solidshop') }}"
                aria-controls="mobile-live-search-modal"
                aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
        </button>
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
         class="ss-btn flex items-center gap-3 px-4 py-3 font-semibold text-sm">
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

      {{-- Акаунт + кошик (мобільне меню) / Account + cart (mobile nav) --}}
      @if (class_exists('WooCommerce'))
        <div class="pt-2 border-t border-gray-100 mt-2 space-y-1">
          <a href="{{ esc_url(wc_get_page_permalink('myaccount')) }}"
             class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            {{ __('Особистий кабінет', 'solidshop') }}
          </a>
          <a href="{{ esc_url(\App\solidshop_get_wishlist_page_url()) }}"
             class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            {{ __('Вподобані товари', 'solidshop') }}
            @php $mobile_wishlist_count = count(\App\solidshop_get_wishlist()); @endphp
            @if ($mobile_wishlist_count > 0)
              <span class="ml-auto solidshop-header-badge solidshop-wishlist-count text-white text-[10px] font-bold min-w-5 h-5 px-1 rounded-full flex items-center justify-center">{{ $mobile_wishlist_count }}</span>
            @else
              <span class="ml-auto solidshop-wishlist-count hidden">0</span>
            @endif
          </a>
          <a href="{{ wc_get_cart_url() }}"
             class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            Кошик
            <span class="ml-auto solidshop-header-badge text-white text-[10px] font-bold min-w-5 h-5 px-1 rounded-full flex items-center justify-center">
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

    /**
     * Відкриває або закриває drawer міні-кошика та керує оверлеєм і скролом.
     * Opens or closes the mini-cart drawer; manages overlay and body scroll lock.
     *
     * Функція оголошена глобально, щоб її міг викликати AJAX-обробник
     * у single-product.blade.php та будь-яка інша сторінка.
     * Declared globally so the AJAX handler in single-product.blade.php
     * and any other page can call it via window.toggleMiniCart(true/false).
     *
     * @param {boolean} open - true = відкрити / open, false = закрити / close
     */
    function toggleMiniCart(open) {
      var drawer  = document.getElementById('mini-cart-drawer');
      var overlay = document.getElementById('mini-cart-overlay');
      if (!drawer || !overlay) { return; }

      if (open) {
        /* Показуємо оверлей, потім висуваємо drawer у наступному кадрі,
           щоб CSS transition встиг спрацювати (не можна transition з display:none).
           Show overlay first, then slide in the drawer on next animation frame
           so the CSS transition has a painted base to transition from. */
        overlay.classList.remove('hidden');
        requestAnimationFrame(function () {
          drawer.classList.remove('translate-x-full');
          drawer.classList.add('translate-x-0');
        });
        document.body.classList.add('overflow-hidden');
      } else {
        /* Ховаємо drawer та оверлей / Slide out drawer and hide overlay. */
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }
    }
    window.toggleMiniCart = toggleMiniCart;
  </script>

  {{-- ════════════════════════════════════════════════════════════════════
       DRAWER МІНІ-КОШИКА (фіксований, права сторона, 420px)
       MINI-CART DRAWER (fixed, right side, 420px)

       Вміст: div.widget_shopping_cart_content оновлюється автоматично
       через WooCommerce fragments після кожного AJAX-запиту add_to_cart.
       Content: div.widget_shopping_cart_content auto-refreshes via
       WooCommerce fragments after every AJAX add_to_cart request.

       Значок лічильника span.solidshop-cart-count у кнопці вище
       також оновлюється через fragments (фільтр у app/setup.php).
       The span.solidshop-cart-count badge on the button above is also
       refreshed via fragments (filter registered in app/setup.php).
       ════════════════════════════════════════════════════════════════════ --}}
  @if (class_exists('WooCommerce'))

    @include('partials.live-search-mobile-modal')

    {{-- Mini-cart AJAX config (available before footer scripts) / Конфіг AJAX міні-кошика --}}
    @php
      $mini_cart_js = [
          'ajaxUrl'      => \WC_AJAX::get_endpoint('solidshop_update_mini_cart_qty'),
          'addToCartUrl' => \WC_AJAX::get_endpoint('add_to_cart'),
          'nonce'        => wp_create_nonce('solidshop_mini_cart'),
      ];
    @endphp
    <script>
      window.solidshopMiniCart = Object.assign(@json($mini_cart_js), window.solidshopMiniCart || {});
    </script>

    {{-- Напівпрозорий оверлей (клік → закриває drawer) / Semi-transparent backdrop --}}
    <div id="mini-cart-overlay"
         class="hidden fixed inset-0 z-40 bg-gray-900/50"
         onclick="toggleMiniCart(false)"
         aria-hidden="true"></div>

    {{-- Основна панель drawer / Main drawer panel --}}
    <div id="mini-cart-drawer"
         role="dialog"
         aria-modal="true"
         aria-label="Кошик"
         aria-labelledby="mini-cart-heading"
         class="fixed top-0 right-0 h-full w-full max-w-[420px] bg-white z-50 shadow-2xl
                translate-x-full transition-transform duration-300 flex flex-col">

      {{-- ── Шапка drawer / Drawer header ─────────────────────────────── --}}
      <div class="mini-cart__header shrink-0">
        <h2 id="mini-cart-heading" class="mini-cart__title">{{ __('Ваш кошик', 'solidshop') }}</h2>
        <button type="button"
                onclick="toggleMiniCart(false)"
                class="mini-cart__close"
                aria-label="{{ __('Закрити кошик', 'solidshop') }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- ── Вміст кошика / Cart contents (WC fragments) ───────────────── --}}
      <div class="flex-1 min-h-0 flex flex-col">
        <div class="widget_shopping_cart_content mini-cart-root flex flex-col flex-1 min-h-0">
          @php woocommerce_mini_cart(); @endphp
        </div>
      </div>

    </div>{{-- /mini-cart-drawer --}}

  @endif{{-- /class_exists('WooCommerce') --}}

</header>
