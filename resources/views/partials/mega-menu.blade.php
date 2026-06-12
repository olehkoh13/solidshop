{{--
  Automated Rozetka-style mega-menu dropdown.
  Автоматизований дропдаун мега-меню в стилі Rozetka.

  Data source / Джерело даних:
  $megaMenu - cached product_cat hierarchy + brands, provided by
  App\View\Composers\MegaMenu (builder lives in app/mega-menu.php).
  $megaMenu - кешована ієрархія product_cat + бренди, передається
  через App\View\Composers\MegaMenu (білдер в app/mega-menu.php).

  Layout / Розкладка:
  - Left sidebar (25%): top-level categories, hover switches panels.
  - Ліва панель (25%): топ-категорії, hover перемикає панелі.
  - Right area (75%, light gray): grid of level 2/3 subcategories
    plus a bottom brand bar.
  - Права зона (75%, світло-сіра): грід підкатегорій рівнів 2/3
    і нижня смуга брендів.
--}}
<div id="mega-menu-dropdown"
     class="hidden absolute top-full left-0 mt-3 w-[960px] max-w-[calc(100vw-4rem)] bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">

  @if (!empty($megaMenu))
    <div class="flex min-h-[420px]">

      {{-- ЛІВА ПАНЕЛЬ: топ-категорії / LEFT SIDEBAR: top-level categories --}}
      <div class="w-1/4 bg-white border-r border-gray-100 p-2 flex flex-col gap-0.5">
        @foreach ($megaMenu as $cat)
          <a href="{{ esc_url($cat['url']) }}"
             class="mega-menu-cat-item flex items-center justify-between gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors
                    {{ $loop->first ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}"
             data-target="menu-content-{{ $cat['id'] }}">
            <span class="truncate">{{ $cat['name'] }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                 class="w-3.5 h-3.5 shrink-0 text-gray-300" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </a>
        @endforeach
      </div>

      {{-- ПРАВА ЗОНА: панелі підкатегорій + бренди / RIGHT AREA: subcategory panels + brands --}}
      <div class="w-3/4 bg-gray-50 relative">
        @foreach ($megaMenu as $cat)
          <div id="menu-content-{{ $cat['id'] }}"
               class="mega-menu-panel {{ $loop->first ? 'flex' : 'hidden' }} flex-col h-full p-6">

            {{-- Заголовок панелі: посилання на категорію / Panel heading: category link --}}
            <a href="{{ esc_url($cat['url']) }}"
               class="text-base font-bold text-gray-900 hover:text-blue-600 transition-colors mb-4 shrink-0">
              {{ $cat['name'] }}
            </a>

            {{-- Грід підкатегорій: рівень 2 = заголовок колонки, рівень 3 = список --}}
            {{-- Subcategory grid: level 2 = column heading, level 3 = item list   --}}
            @if (!empty($cat['children']))
              <div class="grid grid-cols-3 gap-x-6 gap-y-5 content-start flex-1">
                @foreach ($cat['children'] as $child)
                  <div>
                    <a href="{{ esc_url($child['url']) }}"
                       class="block font-bold text-sm text-gray-900 hover:text-blue-600 transition-colors mb-2">
                      {{ $child['name'] }}
                    </a>
                    @if (!empty($child['children']))
                      <ul class="space-y-1.5">
                        @foreach ($child['children'] as $grandchild)
                          <li>
                            <a href="{{ esc_url($grandchild['url']) }}"
                               class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                              {{ $grandchild['name'] }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </div>
                @endforeach
              </div>
            @else
              <p class="text-xs text-gray-400 italic flex-1">
                {{ __('Для цієї категорії немає підкатегорій.', 'solidshop') }}
              </p>
            @endif

            {{-- НИЖНЯ СМУГА БРЕНДІВ / BOTTOM BRAND BAR --}}
            @if (!empty($cat['brands']))
              <div class="mt-auto pt-4 border-t border-gray-200 shrink-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-2">
                  {{ __('Популярні бренди', 'solidshop') }}
                </p>
                <div class="flex flex-wrap gap-2">
                  @foreach ($cat['brands'] as $brand)
                    <a href="{{ esc_url($brand['url']) }}"
                       class="px-3 py-1.5 bg-white border border-gray-200 rounded-full text-xs font-medium text-gray-700 hover:border-blue-600 hover:text-blue-600 transition-colors">
                      {{ $brand['name'] }}
                    </a>
                  @endforeach
                </div>
              </div>
            @endif

          </div>{{-- /mega-menu-panel --}}
        @endforeach
      </div>{{-- /right area --}}

    </div>
  @else
    <p class="p-4 text-xs text-gray-400">
      {{ __('Категорії товарів ще не створені.', 'solidshop') }}
    </p>
  @endif

</div>{{-- /mega-menu-dropdown --}}
