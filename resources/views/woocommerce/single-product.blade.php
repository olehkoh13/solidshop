@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php
      the_post();
      global $product;
    @endphp

    @if (!empty($product))

      @php
        $rating_count = $product->get_rating_count();
        $avg_rating   = (float) $product->get_average_rating();
      @endphp

      <div class="w-full bg-transparent">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-section">

          <nav class="text-sm text-gray-500 mb-8 breadcrumbs-wrapper">
            @php woocommerce_breadcrumb(['delimiter' => ' <span class="text-gray-300 mx-2">/</span> ']); @endphp
          </nav>

          @php do_action('woocommerce_before_single_product'); @endphp

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start mb-16">

            {{-- Gallery / Галерея --}}
            <div class="lg:col-span-7 product-gallery-column">
              <div class="product-gallery-shell">
                @include('partials.product-badges', ['product' => $product])
                @php do_action('woocommerce_before_single_product_summary'); @endphp
              </div>
            </div>

            {{-- Conversion panel / Права колонка --}}
            <div class="lg:col-span-5 product-conversion-panel">

              @if ($sku = $product->get_sku())
                <p class="product-sku-above-title mb-2">
                  <span>{{ __('SKU', 'solidshop') }}: {{ $sku }}</span>
                </p>
              @endif

              <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 leading-snug mb-3">
                {{ $product->get_name() }}
              </h1>

              @if ($short = $product->get_short_description())
                <div class="product-short-description mb-4">
                  {!! wc_format_content($short) !!}
                </div>
              @endif

              @if ($rating_count > 0)
                <div class="flex items-center gap-2 mb-4">
                  <div class="flex items-center gap-0.5" aria-hidden="true">
                    @for ($s = 1; $s <= 5; $s++)
                      <svg class="w-4 h-4 {{ $s <= round($avg_rating) ? 'text-yellow-400' : 'text-gray-200' }}"
                           fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    @endfor
                  </div>
                  <a href="#tab-reviews" class="text-xs text-gray-500 font-medium hover:text-gray-900 transition-colors">
                    ({{ $rating_count }} {{ $rating_count === 1 ? 'відгук' : ($rating_count < 5 ? 'відгуки' : 'відгуків') }})
                  </a>
                </div>
              @endif

              <div class="product-actions-form">
                @php woocommerce_template_single_add_to_cart(); @endphp
              </div>

              <div class="product-secondary-actions">
                @include('partials.wishlist-toggle', [
                  'product_id' => $product->get_id(),
                  'variant'    => 'link',
                ])
              </div>

              @include('partials.product-sidebar-accordions', ['product' => $product])
              @include('partials.product-cross-sells', ['product' => $product])

            </div>
          </div>

          <div class="w-full border-t border-gray-100 pt-12">
            @php do_action('woocommerce_after_single_product_summary'); @endphp
          </div>

        </div>
      </div>

    @endif

    @php do_action('woocommerce_after_single_product'); @endphp
  @endwhile

  <script>
(function () {
  'use strict';

  var COLOR_MAP = {
    'black': '#111827', 'white': '#ffffff', 'red': '#ef4444',
    'blue': '#3b82f6', 'green': '#22c55e', 'yellow': '#eab308',
    'orange': '#f97316', 'purple': '#a855f7', 'pink': '#f472b6',
    'grey': '#9ca3af', 'gray': '#9ca3af', 'beige': '#d4b896',
    'brown': '#92400e', 'navy': '#1e3a5f', 'silver': '#c0c0c0',
    'gold': '#d4af37', 'cyan': '#22d3ee', 'lime': '#84cc16',
  };

  function updateSwatchValue(statusSpan, valueText) {
    if (!statusSpan) return;
    var empty = !valueText || !valueText.length;
    statusSpan.textContent = empty ? 'Не обрано' : valueText.trim().toUpperCase();
    statusSpan.classList.toggle('is-selected', !empty);
  }

  function attributeLabelKey(attrName, fallback) {
    if (/colou?r|kolir|colir/.test(attrName)) return 'Колір';
    if (/size|rozmir|size|logo/.test(attrName)) return 'Розмір';
    return fallback || 'Параметр';
  }

  function initProductTabs() {
    var wrapper = document.querySelector('.woocommerce-tabs');
    if (!wrapper) return;

    var tabLinks = wrapper.querySelectorAll('.wc-tabs li a');
    var panels = wrapper.querySelectorAll('.woocommerce-Tabs-panel');
    if (!tabLinks.length || !panels.length) return;

    panels.forEach(function (panel) {
      panel.style.removeProperty('display');
      panel.classList.remove('wc-panel--open');
    });

    function activateTab(hash) {
      var target = hash || tabLinks[0].getAttribute('href');
      tabLinks.forEach(function (link) {
        var li = link.parentElement;
        if (li) li.classList.toggle('active', link.getAttribute('href') === target);
      });
      panels.forEach(function (panel) {
        panel.classList.toggle('wc-tab-panel--active', '#' + panel.id === target);
      });
    }

    tabLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        activateTab(link.getAttribute('href'));
      });
    });

    activateTab(window.location.hash && wrapper.querySelector(window.location.hash) ? window.location.hash : null);
  }

  function buildSwatches() {
    var form = document.querySelector('.variations_form');
    if (!form) return;

    form.querySelectorAll('select[name^="attribute_"]').forEach(function (select) {
      if (select.dataset.swatchBuilt === '1') return;
      select.dataset.swatchBuilt = '1';

      var attrName = (select.getAttribute('name') || '').toLowerCase();
      var isColor = /colou?r|kolir|colir/.test(attrName);

      select.style.display = 'none';

      var group = document.createElement('div');
      group.className = 'wc-swatch-group mb-6';

      var labelEl = null;
      if (select.id) {
        labelEl = form.querySelector('label[for="' + select.id + '"]');
      }
      if (!labelEl) {
        var row = select.closest('tr') || select.closest('tbody');
        if (row) labelEl = row.querySelector('.label label') || row.querySelector('label');
      }

      var statusSpan = document.createElement('span');
      statusSpan.className = 'wc-swatch-value';

      if (labelEl) {
        var baseText = labelEl.textContent.replace(/[\s:*]+$/, '').trim();
        var key = attributeLabelKey(attrName, baseText).toUpperCase();
        labelEl.className = 'text-xs font-bold uppercase tracking-wider text-gray-900 block mb-3';
        labelEl.textContent = key + ': ';
        labelEl.appendChild(statusSpan);
      }

      updateSwatchValue(statusSpan, select.value ? select.options[select.selectedIndex].text : '');

      Array.from(select.options).forEach(function (option) {
        if (!option.value) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('data-value', option.value);

        if (isColor) {
          btn.className = 'wc-color-swatch';
          btn.style.backgroundColor = COLOR_MAP[option.value.toLowerCase()] || '#e5e7eb';
          btn.setAttribute('title', option.text.trim());
          btn.setAttribute('aria-label', option.text.trim());
        } else {
          btn.className = 'wc-size-swatch';
          btn.textContent = option.text.trim();
        }

        btn.addEventListener('click', function () {
          if (window.jQuery) {
            window.jQuery(select).val(option.value).trigger('change');
          } else {
            select.value = option.value;
            select.dispatchEvent(new Event('change'));
          }
          group.querySelectorAll('button').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-value') === option.value);
          });
          updateSwatchValue(statusSpan, option.text.trim());
        });

        group.appendChild(btn);
      });

      select.parentNode.insertBefore(group, select);

      if (select.value) {
        var activeBtn = group.querySelector('[data-value="' + CSS.escape(select.value) + '"]');
        if (activeBtn) activeBtn.classList.add('is-active');
      }

      select.addEventListener('change', function () {
        group.querySelectorAll('button').forEach(function (b) {
          b.classList.toggle('is-active', b.getAttribute('data-value') === select.value);
        });
        updateSwatchValue(statusSpan, select.value ? select.options[select.selectedIndex].text : '');
      });
    });
  }

  function initVariationPriceSwap() {
    var form = document.querySelector('.variations_form');
    var priceBlock = document.querySelector('.price-block--purchase');
    if (!form || !priceBlock || !window.jQuery) return;

    window.jQuery(form).on('show_variation', function () {
      priceBlock.classList.add('variation-active');
    }).on('hide_variation reset_data', function () {
      priceBlock.classList.remove('variation-active');
    });
  }

  function initGalleryMagnifier() {
    var gallery = document.querySelector('.woocommerce-product-gallery');
    if (!gallery) return;

    var MAGNIFIER_SVG = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="product-gallery-magnifier-icon" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>';

    function enhance() {
      var trigger = gallery.querySelector('.woocommerce-product-gallery__trigger');
      if (!trigger || trigger.dataset.enhanced === '1') return;
      trigger.dataset.enhanced = '1';
      trigger.setAttribute('aria-label', 'Збільшити зображення');
      trigger.innerHTML = MAGNIFIER_SVG;
    }

    enhance();
    if (window.jQuery) {
      window.jQuery(gallery).on('wc-product-gallery-after-init', enhance);
    }
    setTimeout(enhance, 400);
  }

  function initQtyButtons() {
    var wrap = document.querySelector('.product-actions-form .quantity');
    if (!wrap || wrap.dataset.qtyEnhanced === '1') return;
    var inp = wrap.querySelector('input.qty');
    if (!inp) return;
    wrap.dataset.qtyEnhanced = '1';

    var MINUS = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>';
    var PLUS  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>';

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
        if (nv !== v) {
          inp.value = nv;
          inp.dispatchEvent(new Event('change'));
        }
      });
      return b;
    }

    wrap.insertBefore(mkBtn('qty-btn--minus', MINUS, -1), inp);
    wrap.appendChild(mkBtn('qty-btn--plus', PLUS, +1));
  }

  function initSidebarAccordions() {
    var root = document.querySelector('[data-sidebar-accordions]');
    if (!root) return;

    root.querySelectorAll('[data-accordion-trigger]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('[data-accordion-item]');
        var panel = item && item.querySelector('[data-accordion-panel]');
        if (!item || !panel) return;

        var isOpen = item.classList.contains('is-open');
        root.querySelectorAll('[data-accordion-item]').forEach(function (el) {
          el.classList.remove('is-open');
          var p = el.querySelector('[data-accordion-panel]');
          if (p) p.hidden = true;
          var t = el.querySelector('[data-accordion-trigger]');
          if (t) t.setAttribute('aria-expanded', 'false');
        });

        if (!isOpen) {
          item.classList.add('is-open');
          panel.hidden = false;
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  function initCrossSellNav() {
    var root = document.querySelector('[data-cross-sells]');
    if (!root) return;

    var slides = root.querySelectorAll('[data-cross-slide]');
    if (slides.length < 2) return;

    var index = 0;

    function show(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (slide, n) {
        slide.hidden = n !== index;
      });
    }

    var prev = root.querySelector('[data-cross-prev]');
    var next = root.querySelector('[data-cross-next]');
    if (prev) prev.addEventListener('click', function () { show(index - 1); });
    if (next) next.addEventListener('click', function () { show(index + 1); });
  }

  function initAddToCart($) {
    var ajaxUrl = (typeof woocommerce_params !== 'undefined' && woocommerce_params.wc_ajax_url)
      ? woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart')
      : '/?wc-ajax=add_to_cart';

    $('form.cart .single_add_to_cart_button').attr('type', 'button');

    function sendAddToCart($form, $btn) {
      if ($btn.data('processing')) return;
      if ($btn.hasClass('disabled') || $btn.is(':disabled')) return;

      var isVariable = $form.hasClass('variations_form');
      var varId = '';

      if (isVariable) {
        var hasEmpty = false;
        $form.find('select[name^="attribute_"]').each(function () {
          if (!$(this).val()) {
            hasEmpty = true;
            var $group = $(this).prev('.wc-swatch-group');
            if (!$group.length) $group = $(this).closest('td, .value, tr').find('.wc-swatch-group');
            $group.addClass('wc-swatch-group--error');
            setTimeout(function () { $group.removeClass('wc-swatch-group--error'); }, 2000);
          }
        });
        if (hasEmpty) return;

        varId = $form.find('input.variation_id').val();
        if (!varId || varId === '0') return;
      }

      var parentId = $form.data('product_id')
        || $form.find('input[name="product_id"]').val()
        || $form.find('input[name="add-to-cart"]').val()
        || $btn.val();

      if (!parentId && !varId) return;

      var ajaxProductId = isVariable ? varId : parentId;
      var params = {
        product_id: ajaxProductId,
        quantity: $form.find('input.qty').val() || 1
      };

      if (isVariable) {
        $form.find('select[name^="attribute_"]').each(function () {
          params[$(this).attr('name')] = $(this).val() || '';
        });
      }

      $btn.data('processing', true);

      $.ajax({
        type: 'POST',
        url: ajaxUrl,
        data: params,
        dataType: 'json',
        beforeSend: function () {
          if (!$btn.data('original-text')) {
            $btn.data('original-text', $btn.text());
          }
          $btn.addClass('loading').prop('disabled', true);
        },
        success: function (response) {
          $btn.removeClass('loading').prop('disabled', false).removeData('processing');

          if (response && response.error) return;

          if (response && response.fragments) {
            var originalText = $btn.data('original-text');
            $btn.addClass('added').text('✓ Додано');
            setTimeout(function () {
              $btn.removeClass('added').text(originalText);
            }, 2500);

            $.each(response.fragments, function (key, value) {
              $(key).replaceWith(value);
            });

            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
            $(document.body).trigger('wc_fragments_refreshed');

            if (typeof window.toggleMiniCart === 'function') {
              window.toggleMiniCart(true);
            }
          }
        },
        error: function () {
          $btn.removeClass('loading').prop('disabled', false).removeData('processing');
        }
      });
    }

    document.addEventListener('submit', function (e) {
      if (e.target && e.target.matches && e.target.matches('form.cart')) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }, true);

    document.addEventListener('click', function (e) {
      var btnEl = e.target.closest('.single_add_to_cart_button');
      if (!btnEl) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      var $btn = $(btnEl);
      var $form = $btn.closest('form.cart');
      if ($form.length) sendAddToCart($form, $btn);
    }, true);
  }

  jQuery(document).ready(function ($) {
    initProductTabs();
    buildSwatches();
    initVariationPriceSwap();
    initQtyButtons();
    initGalleryMagnifier();
    initSidebarAccordions();
    initCrossSellNav();
    initAddToCart($);

    $(document.body).on('init_variation_form', function () {
      buildSwatches();
      initQtyButtons();
    });
  });
}());
  </script>
@endsection
