@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php
      the_post();
      global $product;
    @endphp

    @if (!empty($product))

      {{-- ────────────────────────────────────────────────────────────────
           Збираємо дані товару ДО рендерингу (без side-effects)
           Collect product data BEFORE rendering (no side-effects)
           ──────────────────────────────────────────────────────────────── --}}
      @php
        $stock_status  = $product->get_stock_status();   // 'instock' | 'outofstock' | 'onbackorder'
        $stock_qty     = $product->get_stock_quantity();
        $is_low_stock  = ($stock_qty !== null && $stock_qty > 0 && $stock_qty <= 5);
        $rating_count  = $product->get_rating_count();
        $avg_rating    = (float) $product->get_average_rating();
      @endphp

      {{-- Зовнішній шар: повна ширина / Outer layer: full width --}}
      <div class="w-full bg-transparent">
        {{-- Внутрішній шар: 1440px + відступи / Inner layer: 1440px cap + padding --}}
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

          {{-- ── Хлібні крихти / Breadcrumbs ─────────────────────────── --}}
          <nav class="text-sm text-gray-500 mb-8 breadcrumbs-wrapper">
            @php woocommerce_breadcrumb(['delimiter' => ' <span class="text-gray-300 mx-2">/</span> ']); @endphp
          </nav>

          @php do_action('woocommerce_before_single_product'); @endphp

          {{-- ════════════════════════════════════════════════════════════
               ОСНОВНА СІТКА: ГАЛЕРЕЯ (7/12) + КОНВЕРСІЙНА ПАНЕЛЬ (5/12)
               MAIN GRID: GALLERY (7/12) + CONVERSION PANEL (5/12)
               ════════════════════════════════════════════════════════════ --}}
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start mb-16">

            {{-- ── ЛІВА КОЛОНКА: Галерея / LEFT: Gallery ──────────────── --}}
            {{-- gallery hook рендерить .woocommerce-product-gallery зі Flexslider --}}
            {{-- gallery hook renders .woocommerce-product-gallery with Flexslider  --}}
            <div class="lg:col-span-7 product-gallery-column">
              @php do_action('woocommerce_before_single_product_summary'); @endphp
            </div>

            {{-- ── ПРАВА КОЛОНКА: Конверсія / RIGHT: Conversion panel ─── --}}
            <div class="lg:col-span-5 lg:sticky lg:top-24 bg-white border border-gray-100 rounded-2xl p-6 lg:p-8 shadow-sm space-y-0">

              {{-- Категорія-лейбл / Category pill label --}}
              <p class="text-xs font-semibold uppercase tracking-widest text-blue-600 mb-3">
                @php echo ucfirst(strip_tags(wc_get_product_category_list($product->get_id(), ', '))); @endphp
              </p>

              {{-- Назва товару / Product title --}}
              <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 leading-snug mb-4">
                {{ $product->get_name() }}
              </h1>

              {{-- ── Рейтинг (якщо є відгуки) / Star rating (if reviews exist) -- --}}
              @if ($rating_count > 0)
                <div class="flex items-center gap-2 mb-4">
                  <div class="flex items-center gap-0.5">
                    @for ($s = 1; $s <= 5; $s++)
                      <svg class="w-4 h-4 {{ $s <= round($avg_rating) ? 'text-yellow-400' : 'text-gray-200' }}"
                           fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    @endfor
                  </div>
                  <span class="text-xs text-gray-500 font-medium">
                    {{ number_format($avg_rating, 1) }}
                    <span class="text-gray-300 mx-1">·</span>
                    {{ $rating_count }} {{ $rating_count === 1 ? 'відгук' : ($rating_count < 5 ? 'відгуки' : 'відгуків') }}
                  </span>
                </div>
              @endif

              {{-- ── Ціна / Price block ────────────────────────────────── --}}
              <div class="mb-4 price-block">
                {!! $product->get_price_html() !!}
              </div>

              {{-- ── Індикатор наявності / Stock availability badge ─────── --}}
              {{-- Правило: зелений — є; помаранчевий — мало; сірий — немає  --}}
              {{-- Rule: green = in stock; orange = low stock; grey = none   --}}
              @if ($stock_status === 'instock')
                @if ($is_low_stock)
                  <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 bg-orange-50 border border-orange-100 rounded-full px-3 py-1 mb-5">
                    <span aria-hidden="true">🔥</span>
                    Обмежена кількість@if ($stock_qty) · залишилось {{ $stock_qty }} шт.@endif
                  </div>
                @else
                  <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full px-3 py-1 mb-5">
                    <span class="w-2 h-2 bg-green-500 rounded-full shrink-0 inline-block" aria-hidden="true"></span>
                    В наявності · Готово до відправки
                  </div>
                @endif
              @elseif ($stock_status === 'outofstock')
                <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-200 rounded-full px-3 py-1 mb-5">
                  <span class="w-2 h-2 bg-gray-300 rounded-full shrink-0 inline-block" aria-hidden="true"></span>
                  Немає в наявності
                </div>
              @endif

              {{-- ── Короткий опис / Short description ───────────────────── --}}
              @if ($product->get_short_description())
                <div class="product-short-desc border-t border-gray-100 pt-4 mb-6">
                  {!! $product->get_short_description() !!}
                </div>
              @endif

              {{-- ── Форма варіацій + кнопка / Variations form + Add-to-cart --}}
              {{-- woocommerce_template_single_add_to_cart() виводить:          --}}
              {{-- – .variations_form (variable product) або .cart (simple)    --}}
              {{-- – input.qty, .single_add_to_cart_button                     --}}
              <div class="product-actions-form">
                @php woocommerce_template_single_add_to_cart(); @endphp
              </div>

              <hr class="border-gray-100 my-6">

              {{-- ── Блок довіри / Trust badges ──────────────────────────── --}}
              <div class="space-y-3">

                <div class="flex items-start gap-3">
                  <span class="mt-0.5 w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg shrink-0 text-base leading-none">🚚</span>
                  <div class="text-xs">
                    <p class="font-semibold text-gray-900">Доставка у відділення або кур'єром</p>
                    <p class="text-gray-400 mt-0.5">Відправка у день замовлення до 16:00</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <span class="mt-0.5 w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg shrink-0 text-base leading-none">🛡️</span>
                  <div class="text-xs">
                    <p class="font-semibold text-gray-900">Офіційна гарантія бренду</p>
                    <p class="text-gray-400 mt-0.5">12 місяців повного сервісного обслуговування</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <span class="mt-0.5 w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg shrink-0 text-base leading-none">↩️</span>
                  <div class="text-xs">
                    <p class="font-semibold text-gray-900">14 днів на повернення</p>
                    <p class="text-gray-400 mt-0.5">Безоплатне повернення у разі браку</p>
                  </div>
                </div>

              </div>{{-- /trust badges --}}

            </div>{{-- /right column --}}
          </div>{{-- /main grid --}}

          {{-- ════════════════════════════════════════════════════════════
               НИЖНІЙ БЛОК: Таби → Акордеон + Схожі товари
               BOTTOM BLOCK: Tabs → Accordion + Related products
               woocommerce_after_single_product_summary виводить:
               1. woocommerce_output_product_data_tabs()  — таби
               2. woocommerce_output_related_products()   — схожі
               3. woocommerce_upsell_display()            — апсейли
               ════════════════════════════════════════════════════════════ --}}
          <div class="w-full border-t border-gray-100 pt-12">
            @php do_action('woocommerce_after_single_product_summary'); @endphp
          </div>

        </div>{{-- /inner container --}}
      </div>{{-- /outer wrapper --}}

    @endif

    @php do_action('woocommerce_after_single_product'); @endphp
  @endwhile

  {{-- ══════════════════════════════════════════════════════════════════
       JS: акордеон табів · swatch-варіації · SVG зірки · qty ± кнопки
       JS: tabs accordion · swatch variations · SVG stars · qty ± buttons
       ══════════════════════════════════════════════════════════════════ --}}
  <script>
  (function () {
    'use strict';

    /* ── Карта кольорів slug → CSS / Color map slug → CSS ──────────────── */
    var COLOR_MAP = {
      'black':'#111827','white':'#ffffff','red':'#ef4444','blue':'#3b82f6',
      'green':'#22c55e','yellow':'#facc15','orange':'#f97316','purple':'#a855f7',
      'pink':'#f472b6','grey':'#9ca3af','gray':'#9ca3af','beige':'#d4b896',
      'brown':'#92400e','navy':'#1e3a5f','silver':'#c0c0c0','gold':'#d4af37',
      'cyan':'#22d3ee','lime':'#84cc16','temno-siniy':'#1e3a5f',
    };

    /* ══════════════════════════════════════════════════════════════════
       1. ТАБИ → АКОРДЕОН / TABS → ACCORDION
       Ховаємо <ul> навігацію, всі панелі завжди відкриті через
       display:block!important в CSS; JS тільки перемикає клас.
       We hide <ul> nav; CSS forces all panels visible; JS toggles class.
       ══════════════════════════════════════════════════════════════════ */
    function initTabsAccordion() {
      var panels = document.querySelectorAll('.woocommerce-Tabs-panel');
      if (!panels.length) return;

      panels.forEach(function (panel, i) {
        panel.style.removeProperty('display');

        var h2 = panel.querySelector('h2');
        if (!h2) return;

        /* Wrap вміст після h2 у .wc-panel-body / Wrap content after h2 */
        var body = document.createElement('div');
        body.className = 'wc-panel-body';
        while (h2.nextSibling) body.appendChild(h2.nextSibling);
        panel.appendChild(body);

        /* Перший таб відкритий / First panel open by default */
        if (i === 0) panel.classList.add('wc-panel--open');

        /* Клік по h2 → перемикання / h2 click → toggle */
        h2.addEventListener('click', function () {
          panel.classList.toggle('wc-panel--open');
        });
      });
    }

    /* ══════════════════════════════════════════════════════════════════
       2. SWATCH-КНОПКИ ВАРІАЦІЙ / VARIATION SWATCH BUTTONS
       <select> лишається в DOM (прихований) — WC-JS варіацій працює.
       <select> stays in DOM (hidden) — WC variation JS keeps working.
       ══════════════════════════════════════════════════════════════════ */
    function buildSwatches() {
      var form = document.querySelector('.variations_form');
      if (!form) return;

      form.querySelectorAll('select[name^="attribute_"]').forEach(function (sel) {
        var name     = (sel.getAttribute('name') || '').toLowerCase();
        var isColor  = /colou?r|kolir|colir/.test(name);
        var isSize   = /size|rozmir/.test(name);
        if (!isColor && !isSize) return;

        sel.style.display = 'none';

        var grp = document.createElement('div');
        grp.className = 'wc-swatch-group';

        Array.from(sel.options).forEach(function (opt) {
          if (!opt.value) return;

          var btn = document.createElement('button');
          btn.type = 'button';
          btn.setAttribute('data-value', opt.value);

          if (isColor) {
            btn.className = 'wc-color-swatch';
            btn.style.backgroundColor = COLOR_MAP[opt.value.toLowerCase()] || '#e5e7eb';
            btn.setAttribute('title', opt.text.trim());
            btn.setAttribute('aria-label', opt.text.trim());
          } else {
            btn.className = 'wc-size-swatch';
            btn.textContent = opt.text.trim().toUpperCase();
          }

          btn.addEventListener('click', function () {
            sel.value = opt.value;
            sel.dispatchEvent(new Event('change'));
            grp.querySelectorAll('button').forEach(function (b) {
              b.classList.toggle('is-active', b.getAttribute('data-value') === opt.value);
            });
          });

          grp.appendChild(btn);
        });

        sel.parentNode.insertBefore(grp, sel);

        if (sel.value) {
          var ab = grp.querySelector('[data-value="' + sel.value + '"]');
          if (ab) ab.classList.add('is-active');
        }

        sel.addEventListener('change', function () {
          grp.querySelectorAll('button').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-value') === sel.value);
          });
        });
      });
    }

    /* ══════════════════════════════════════════════════════════════════
       3. SVG ЗІРКИ У ФОРМІ ВІДГУКІВ / SVG STARS IN REVIEW FORM
       Замінюємо текстові посилання "1","2","3","4","5" на SVG-зірки.
       Replace WC text links "1".."5" with SVG star icons.
       WooCommerce's own star-rating JS продовжує керувати вибором.
       WooCommerce's own star-rating JS still manages selection.
       ══════════════════════════════════════════════════════════════════ */
    var STAR_SVG = '<svg fill="currentColor" viewBox="0 0 20 20" width="22" height="22" aria-hidden="true">'
      + '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>'
      + '</svg>';

    function initSvgStars() {
      document.querySelectorAll('#review_form .stars').forEach(function (container) {
        container.querySelectorAll('a').forEach(function (link) {
          link.innerHTML = STAR_SVG;
        });
        container.classList.add('wc-svg-stars');
      });
    }

    /* ══════════════════════════════════════════════════════════════════
       4. КНОПКИ ± ДО ЛІЧИЛЬНИКА КІЛЬКОСТІ / QTY ± STEPPER BUTTONS
       WC рендерить тільки <input type="number">; ми додаємо − і +.
       WC renders only <input type="number">; we inject − and + buttons.
       ══════════════════════════════════════════════════════════════════ */
    function initQtyButtons() {
      var wrap = document.querySelector('.product-actions-form .quantity');
      if (!wrap) return;
      var inp = wrap.querySelector('input.qty');
      if (!inp) return;

      var MINUS_ICON = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>';
      var PLUS_ICON  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>';

      function mkBtn(cls, icon, delta) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'qty-btn ' + cls;
        b.innerHTML = icon;
        b.addEventListener('click', function () {
          var v   = parseInt(inp.value, 10) || 1;
          var min = parseInt(inp.getAttribute('min'), 10) || 1;
          var max = parseInt(inp.getAttribute('max'), 10) || Infinity;
          var nv  = Math.max(min, Math.min(max, v + delta));
          if (nv !== v) { inp.value = nv; inp.dispatchEvent(new Event('change')); }
        });
        return b;
      }

      wrap.insertBefore(mkBtn('qty-btn--minus', MINUS_ICON, -1), inp);
      wrap.appendChild(mkBtn('qty-btn--plus', PLUS_ICON, +1));
    }

    /* ── Bootstrap / Ініціалізація ───────────────────────────────────── */
    function init() {
      initTabsAccordion();
      buildSwatches();
      initSvgStars();
      initQtyButtons();
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }

  }());
  </script>

@endsection
