@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @php(global $product)

    @if (empty($product))
        @php(return)
    @endif

    {{-- Головний контейнер картки товару з обмеженням 1440px --}}
    {{-- Main single product container restricted to 1440px --}}
    <div class="w-full bg-transparent">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

            {{-- Хлібні крихти для навігації --}}
            {{-- Breadcrumbs navigation --}}
            <nav class="text-sm text-gray-500 mb-8 breadcrumbs-wrapper">
                @php(woocommerce_breadcrumb(['delimiter' => ' <span class="text-gray-300 mx-2">/</span> ']))
            </nav>

            {{-- Стандартний хук для виведення сповіщень WooCommerce --}}
            {{-- Standard WooCommerce notice loop output --}}
            @php(do_action('woocommerce_before_single_product'))

            {{-- Головна двоколоночна сітка екрану покупки --}}
            {{-- Main two-column buying experience grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start mb-16">

                {{-- ЛІВА КОЛОНКА: Медіа та Галерея (Займає 7 колонок із 12) --}}
                {{-- LEFT COLUMN: Media & Gallery (Takes 7 out of 12 cols) --}}
                <div class="lg:col-span-7 space-y-4 md:sticky md:top-24">
                    @php(do_action('woocommerce_before_single_product_summary'))
                </div>

                {{-- ПРАВА КОЛОНКА: Інформація та Конверсія (Займає 5 колонок із 12) --}}
                {{-- RIGHT COLUMN: Info & Conversion (Takes 5 out of 12 cols) --}}
                <div class="lg:col-span-5 lg:sticky lg:top-24 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">

                    {{-- Бренд та категорія --}}
                    <div class="text-xs font-medium uppercase tracking-wider text-blue-600 mb-2">
                        @php(ucfirst(strip_tags(wc_get_product_category_list($product->get_id(), ', '))))
                    </div>

                    {{-- Назва товару --}}
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 mb-4">
                        {{ $product->get_name() }}
                    </h1>

                    {{-- Ціна товару --}}
                    <div class="text-3xl font-extrabold text-gray-900 mb-6 price-block">
                        {!! $product->get_price_html() !!}
                    </div>

                    {{-- Короткий опис товару --}}
                    @if ($product->get_short_description())
                        <div class="text-sm text-gray-600 leading-relaxed mb-6">
                            {!! $product->get_short_description() !!}
                        </div>
                    @endif

                    {{-- Форма додавання в кошик та селектори варіацій кольору/розміру --}}
                    {{-- Add to cart form and variations swatches --}}
                    <div class="product-actions-form mb-6">
                        @php(woocommerce_template_single_add_to_cart())
                    </div>

                    <hr class="border-gray-100 my-6">

                    {{-- Блок довіри: Доставка, Гарантія, Повернення --}}
                    {{-- Trust block: Delivery, Warranty, Returns --}}
                    <div class="space-y-4 text-xs text-gray-600">
                        <div class="flex items-center gap-3">
                            <span class="p-2 bg-gray-50 rounded-lg text-gray-700">🚚</span>
                            <div>
                                <p class="font-semibold text-gray-900">Доставка у відділення або кур'єром</p>
                                <p class="text-gray-500">Відправка у день замовлення</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="p-2 bg-gray-50 rounded-lg text-gray-700">🛡️</span>
                            <div>
                                <p class="font-semibold text-gray-900">Офіційна гарантія бренду</p>
                                <p class="text-gray-500">12 місяців повної підтримки</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- НИЖНІЙ БЛОК: Детальний опис, характеристики та відгуки --}}
            {{-- BOTTOM BLOCK: Specifications, description and reviews --}}
            <div class="w-full border-t border-gray-100 pt-12">
                @php(do_action('woocommerce_after_single_product_summary'))
            </div>

        </div>
    </div>

    @php(do_action('woocommerce_after_single_product'))
  @endwhile

  {{-- ================================================================== --}}
  {{-- JS: Tabs accordion + variation swatch buttons for single product    --}}
  {{-- JS: Акордеон табів + кнопки-свотчі варіацій для сторінки товару    --}}
  {{-- ================================================================== --}}
  <script>
  (function () {
    'use strict';

    /* ── Кольорова карта slug → CSS color / Color map slug → CSS color ──── */
    var COLOR_MAP = {
      'black':       '#111827', 'white':  '#ffffff', 'red':    '#ef4444',
      'blue':        '#3b82f6', 'green':  '#22c55e', 'yellow': '#facc15',
      'orange':      '#f97316', 'purple': '#a855f7', 'pink':   '#f472b6',
      'grey':        '#9ca3af', 'gray':   '#9ca3af', 'beige':  '#d4b896',
      'brown':       '#92400e', 'navy':   '#1e3a5f', 'silver': '#c0c0c0',
      'gold':        '#d4af37', 'cyan':   '#22d3ee', 'lime':   '#84cc16',
      'тemno-siniy': '#1e3a5f', 'chorniy':'#111827', 'biliy':  '#ffffff',
    };

    /* ═════════════════════════════════════════════════════════════════════
       1. PRODUCT TABS → ACCORDION
       Перетворюємо WC-таби на стековий акордеон:
       – ховаємо <ul> навігацію;
       – wrap-аємо вміст кожного panel у .wc-panel-body div;
       – перший panel відкритий за замовчуванням.
       ═════════════════════════════════════════════════════════════════════ */
    function initTabsAccordion() {
      var panels = document.querySelectorAll('.woocommerce-Tabs-panel');
      if (!panels.length) return;

      panels.forEach(function (panel, index) {
        /* Прибираємо inline display:none від WC / Remove WC's inline display:none */
        panel.style.removeProperty('display');

        var heading = panel.querySelector('h2');
        if (!heading) return;

        /* Wrap all content after h2 into .wc-panel-body ─────────────────── */
        /* Переносимо все після h2 в обгортку .wc-panel-body                 */
        var body = document.createElement('div');
        body.className = 'wc-panel-body';

        while (heading.nextSibling) {
          body.appendChild(heading.nextSibling);
        }
        panel.appendChild(body);

        /* Перший таб відкритий / First tab open by default */
        if (index === 0) {
          panel.classList.add('wc-panel--open');
        }

        /* Toggle on heading click ─────────────────────────────────────── */
        /* Перемикання при кліку на заголовок                              */
        heading.addEventListener('click', function () {
          var isOpen = panel.classList.contains('wc-panel--open');
          panel.classList.toggle('wc-panel--open', !isOpen);
        });
      });
    }

    /* ═════════════════════════════════════════════════════════════════════
       2. VARIATION SWATCHES — Color & Size
       Генеруємо кнопки-свотчі з <select> елементів форми варіацій.
       Generate swatch buttons from <select> elements in variations form.

       ПРИНЦИП: <select> залишається в DOM (прихований) щоб WC-JS варіацій
       продовжував працювати. Swatch-кнопки синхронізуються з select.
       PRINCIPLE: <select> stays in DOM (hidden) so WC variation JS keeps
       working. Swatch buttons stay in sync with the select value.
       ═════════════════════════════════════════════════════════════════════ */
    function buildSwatches() {
      var form = document.querySelector('.variations_form');
      if (!form) return;

      var selects = form.querySelectorAll('select[name^="attribute_"]');
      selects.forEach(function (select) {
        var attrName = (select.getAttribute('name') || '').toLowerCase();
        var isColor  = attrName.indexOf('color') !== -1 || attrName.indexOf('colour') !== -1 || attrName.indexOf('color') !== -1;
        var isSize   = attrName.indexOf('size') !== -1  || attrName.indexOf('rozmir') !== -1;

        if (!isColor && !isSize) return; /* Пропускаємо інші атрибути / Skip other attrs */

        /* Сховати оригінальний select / Hide original select */
        select.style.display = 'none';

        /* Контейнер для swatch-кнопок / Swatch button container */
        var group = document.createElement('div');
        group.className = 'wc-swatch-group';

        Array.from(select.options).forEach(function (option) {
          if (!option.value) return; /* Пропускаємо placeholder / Skip "Choose an option" */

          var btn = document.createElement('button');
          btn.type = 'button';
          btn.setAttribute('data-value', option.value);

          if (isColor) {
            /* ── Кругла кнопка кольору / Circular color swatch ── */
            btn.className = 'wc-color-swatch';
            var slugLower = option.value.toLowerCase();
            var color = COLOR_MAP[slugLower] || null;
            if (!color) {
              /* Спроба отримати колір з CSS-змінної або hex у slug              */
              /* Try extracting color from slug if it looks like a known token   */
              color = '#e5e7eb'; /* fallback gray */
            }
            btn.style.backgroundColor = color;
            btn.setAttribute('aria-label', option.text.trim());
            btn.setAttribute('title', option.text.trim());
          } else {
            /* ── Квадратна кнопка розміру / Square size badge ── */
            btn.className = 'wc-size-swatch';
            btn.textContent = option.text.trim().toUpperCase();
          }

          /* Клік: оновлюємо select і тригеримо WC change / Click: sync select + trigger WC */
          btn.addEventListener('click', function () {
            select.value = option.value;
            select.dispatchEvent(new Event('change'));
            /* Оновлюємо активний стан усіх кнопок групи / Update active state in group */
            group.querySelectorAll('button').forEach(function (b) {
              b.classList.toggle('is-active', b.getAttribute('data-value') === option.value);
            });
          });

          group.appendChild(btn);
        });

        /* Вставляємо групу перед select / Insert group before the select */
        select.parentNode.insertBefore(group, select);

        /* Синхронізуємо початковий стан / Sync initial active state */
        if (select.value) {
          var initBtn = group.querySelector('[data-value="' + select.value + '"]');
          if (initBtn) initBtn.classList.add('is-active');
        }

        /* Зовнішні зміни select (WC варіації reset) → оновлюємо swatch */
        /* External select changes (WC variations reset) → update swatch */
        select.addEventListener('change', function () {
          group.querySelectorAll('button').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-value') === select.value);
          });
        });
      });
    }

    /* ── Ініціалізація після завантаження DOM / Init after DOM ready ────── */
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        initTabsAccordion();
        buildSwatches();
      });
    } else {
      /* DOM вже готовий / DOM already ready */
      initTabsAccordion();
      buildSwatches();
    }
  }());
  </script>

@endsection
