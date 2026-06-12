/**
 * Ванільна інтерактивна логіка для Мега-Меню в стилі Розетки.
 * Vanilla interactive logic for the Rozetka-style mega-menu.
 *
 * ПРИНЦИП РОЗДІЛЕННЯ ВІДПОВІДАЛЬНОСТІ:
 * SEPARATION OF CONCERNS PRINCIPLE:
 *   - <a> теги = чиста навігація браузера, JS їх не чіпає.
 *   - <a> tags = pure browser navigation, JS does NOT touch them.
 *   - <button> теги = перемикачі видимості (toggle), мають e.stopPropagation().
 *   - <button> tags = visibility toggles only, use e.stopPropagation().
 *   - e.preventDefault() НІДЕ не викликається на посиланнях.
 *   - e.preventDefault() is NEVER called on link elements.
 */
document.addEventListener('DOMContentLoaded', () => {
  initMegaMenu();
  initCheckoutShipping();
  initMiniCart();
  initWishlist();
  initQuickBuy();
  initCatalogCardQuickAdd();
  initCartPage();
  initLiveSearch();
  initLiveSearchModal();
  initProductCarousels();
  initContactForm();
  initContactFaq();
  initFloatingCartBar();
});

/**
 * Плаваючий нижній банер кошика.
 * Floating sticky cart bar at the bottom of the screen.
 *
 * Логіка / Logic:
 *   - Якщо кошик не порожній (data-cart-count > 0) і банер не закритий
 *     у цій сесії, він видимий одразу після завантаження сторінки.
 *   - If the cart is not empty (data-cart-count > 0) and the bar was not
 *     closed in this session, it is visible immediately on page load.
 *   - При скролі ВНИЗ банер видимий, при скролі ВГОРУ плавно з'їжджає
 *     донизу і зникає.
 *   - On scroll DOWN the bar stays visible; on scroll UP it smoothly
 *     slides down and disappears.
 *   - Кнопка X закриває банер до кінця сесії (sessionStorage).
 *   - The X button hides the bar for the session (sessionStorage).
 *   - Кількість і сума оновлюються через WooCommerce fragments.
 *   - Quantity and total refresh via WooCommerce fragments.
 */
function initFloatingCartBar() {
  const bar = document.getElementById('floating-cart-bar');
  if (!bar) {
    return;
  }

  const STORAGE_KEY = 'ss_floating_cart_closed';
  const HIDDEN_CLASSES = ['translate-y-[150%]', 'opacity-0', 'pointer-events-none'];

  // Примусово закритий у цій сесії - прибираємо вузол повністю.
  // Force-closed in this session - remove the node entirely.
  if (sessionStorage.getItem(STORAGE_KEY) === '1') {
    bar.remove();
    return;
  }

  let lastScrollTop = window.scrollY || 0;
  let visible = false;
  let ticking = false;

  const cartHasItems = () => parseInt(bar.dataset.cartCount || '0', 10) > 0;

  const showBar = () => {
    if (visible) { return; }
    visible = true;
    bar.classList.remove(...HIDDEN_CLASSES);
  };

  const hideBar = () => {
    if (!visible) { return; }
    visible = false;
    bar.classList.add(...HIDDEN_CLASSES);
  };

  // Початкова видимість: показуємо одразу, якщо в кошику є товари.
  // Initial visibility: show immediately when the cart has items.
  if (cartHasItems()) {
    showBar();
  }

  /**
   * Керує видимістю за напрямком скролу: вниз - показати, вгору - сховати.
   * Toggles visibility by scroll direction: down - show, up - hide.
   */
  const onScroll = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;

    if (scrollTop > lastScrollTop && cartHasItems()) {
      showBar();
    } else if (scrollTop < lastScrollTop) {
      hideBar();
    }

    lastScrollTop = Math.max(scrollTop, 0);
  };

  // requestAnimationFrame-тротлінг для плавних 60fps.
  // requestAnimationFrame throttling for smooth 60fps.
  window.addEventListener('scroll', () => {
    if (ticking) { return; }
    ticking = true;
    window.requestAnimationFrame(() => {
      onScroll();
      ticking = false;
    });
  }, { passive: true });

  // Примусове закриття банера на поточну сесію.
  // Force close the bar for the current browser session.
  const closeBtn = bar.querySelector('.js-floating-cart-close');
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      sessionStorage.setItem(STORAGE_KEY, '1');
      hideBar();
      // Видаляємо вузол після завершення анімації приховування.
      // Remove the node once the hide transition has finished.
      window.setTimeout(() => bar.remove(), 350);
    });
  }

  /**
   * Синхронізує data-cart-count після оновлення fragments.
   * Порожній кошик - ховаємо банер, з'явились товари - показуємо.
   * Syncs data-cart-count after a fragments refresh.
   * Empty cart - hide the bar, items appeared - show it.
   */
  const syncCartCount = () => {
    const countSpan = bar.querySelector('.js-floating-cart-count');
    if (countSpan && countSpan.dataset.count !== undefined) {
      bar.dataset.cartCount = countSpan.dataset.count;
    }
    if (cartHasItems()) {
      showBar();
    } else {
      hideBar();
    }
  };

  // Слухаємо події WooCommerce через jQuery (як у initMiniCart).
  // Listen to WooCommerce events via jQuery (same pattern as initMiniCart).
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart wc_fragments_refreshed removed_from_cart updated_wc_div', syncCartCount);
  }
}

/**
 * Прибирає ціну з label карток доставки після AJAX WC Ukraine Shipping.
 * Strip shipping price from method card labels after WCUS AJAX updates.
 */
function initCheckoutShipping() {
  if (!document.body.classList.contains('woocommerce-checkout')) {
    return;
  }

  const normalizeShippingLabels = () => {
    document.querySelectorAll('#shipping_method.woocommerce-shipping-methods > li').forEach((li) => {
      const label = li.querySelector('label');
      if (!label) {
        return;
      }

      const hiddenName = li.querySelector('#wcus-shipping-name');
      const costSpan = label.querySelector('#wcus-shipping-cost');

      if (hiddenName && costSpan) {
        costSpan.textContent = hiddenName.value;
        return;
      }

      label.querySelectorAll('.woocommerce-Price-amount').forEach((el) => el.remove());

      const textOnly = label.textContent.trim();
      const nameOnly = textOnly.replace(/\s*:\s*.+$/, '').trim();

      if (nameOnly && nameOnly !== textOnly) {
        label.textContent = nameOnly;
      }
    });
  };

  normalizeShippingLabels();
  document.body.addEventListener('updated_checkout', normalizeShippingLabels);
}

/**
 * Автоматизоване мега-меню каталогу (Rozetka-style).
 * Automated catalog mega-menu (Rozetka-style).
 *
 * Логіка / Logic:
 *   - Відкривається при ховері на обгортку кнопки "Каталог",
 *     закривається з невеликою затримкою після mouseleave.
 *   - Opens on hover over the "Catalog" button wrapper,
 *     closes with a small delay after mouseleave.
 *   - Ховер на пункті лівої панелі миттєво перемикає праву панель
 *     за атрибутом data-target.
 *   - Hovering a left sidebar item instantly switches the right panel
 *     matched by the data-target attribute.
 *   - Клік по кнопці лишається toggle-фолбеком (тачскріни, клавіатура).
 *   - Button click stays as a toggle fallback (touch screens, keyboard).
 */
function initMegaMenu() {
  // ── Елементи мега-меню / Mega-menu elements ─────────────────────────────
  const wrapper = document.querySelector('.id-mega-menu-wrapper');
  const trigger = document.getElementById('mega-menu-trigger');
  const dropdown = document.getElementById('mega-menu-dropdown');

  // Пункти лівої панелі (посилання з data-target)
  // Left sidebar items (links carrying data-target)
  const catItems = document.querySelectorAll('.mega-menu-cat-item');

  // Контентні панелі правої зони / Right area content panels
  const panels = document.querySelectorAll('.mega-menu-panel');

  if (!trigger || !dropdown) return;

  // Класи активного пункту лівої панелі / Active sidebar item classes
  const ACTIVE_CLASSES = ['bg-blue-50', 'text-blue-600'];
  const INACTIVE_CLASSES = ['text-gray-700', 'hover:bg-blue-50', 'hover:text-blue-600'];

  // Таймер відкладеного закриття після mouseleave.
  // Delayed-close timer after mouseleave.
  let closeTimer = null;

  /**
   * Активує пункт лівої панелі та показує відповідну праву панель.
   * Activates a sidebar item and reveals the matching right panel.
   * Навігація по <a> не блокується / Link navigation is never blocked.
   *
   * @param {string} targetId - значення data-target (наприклад "menu-content-42") / data-target value
   */
  function activatePanel(targetId) {
    // Скидаємо активний стан усіх пунктів / Reset active state on all items
    catItems.forEach((item) => {
      item.classList.remove(...ACTIVE_CLASSES);
      item.classList.add(...INACTIVE_CLASSES);
    });

    // Ховаємо всі праві панелі / Hide all right panels
    panels.forEach((panel) => {
      panel.classList.add('hidden');
      panel.classList.remove('flex');
    });

    // Підсвічуємо поточний пункт / Highlight the current item
    const activeItem = document.querySelector(`.mega-menu-cat-item[data-target="${targetId}"]`);
    if (activeItem) {
      activeItem.classList.remove(...INACTIVE_CLASSES);
      activeItem.classList.add(...ACTIVE_CLASSES);
    }

    // Показуємо відповідну панель / Show the matching panel
    const targetPanel = document.getElementById(targetId);
    if (targetPanel) {
      targetPanel.classList.remove('hidden');
      targetPanel.classList.add('flex');
    }
  }

  /** Відкриває dropdown / Opens the dropdown. */
  function openMenu() {
    if (closeTimer) {
      window.clearTimeout(closeTimer);
      closeTimer = null;
    }
    dropdown.classList.remove('hidden');
  }

  /** Закриває dropdown із затримкою / Closes the dropdown with a delay. */
  function scheduleClose() {
    if (closeTimer) {
      window.clearTimeout(closeTimer);
    }
    closeTimer = window.setTimeout(() => {
      dropdown.classList.add('hidden');
      closeTimer = null;
    }, 150);
  }

  // ── 1. Відкриття по ховеру / Open on hover ──────────────────────────────
  //
  // mouseenter на обгортці (кнопка + dropdown) відкриває меню;
  // mouseleave планує закриття, повторний enter його скасовує.
  // mouseenter on the wrapper (button + dropdown) opens the menu;
  // mouseleave schedules the close, re-entering cancels it.
  if (wrapper) {
    wrapper.addEventListener('mouseenter', openMenu);
    wrapper.addEventListener('mouseleave', scheduleClose);
  }

  // ── 2. Клік по кнопці: toggle-фолбек / Button click: toggle fallback ────
  //
  // trigger - це <button>, НЕ посилання, тому e.stopPropagation() безпечний.
  // trigger is a <button>, NOT a link, so e.stopPropagation() is safe here.
  trigger.addEventListener('click', (e) => {
    e.stopPropagation(); // Не дає document.click закрити меню одразу / Prevents immediate close by document.click
    dropdown.classList.toggle('hidden');
  });

  // ── 3. Ховер на пункті лівої панелі перемикає праву панель ──────────────
  // ── 3. Hover on a sidebar item switches the right panel ─────────────────
  //
  // Пункт - це <a>: клік виконує нативну навігацію браузера,
  // mouseenter лише перемикає видимість панелей.
  // The item is an <a>: click performs native browser navigation,
  // mouseenter only toggles panel visibility.
  catItems.forEach((item) => {
    item.addEventListener('mouseenter', () => {
      const targetId = item.getAttribute('data-target');
      if (targetId) activatePanel(targetId);
    });
  });

  // ── 4. Закриття при кліку поза межами меню / Close on outside click ─────
  //
  // Посилання (<a>) всередині dropdown НЕ перехоплюються цим хендлером:
  // dropdown.contains(e.target) === true, тому меню не закривається,
  // і браузер виконує нативний перехід.
  // Links (<a>) inside the dropdown are NOT intercepted by this handler:
  // dropdown.contains(e.target) === true, so the menu stays open and
  // the browser performs native navigation.
  document.addEventListener('click', (e) => {
    if (
      !dropdown.contains(e.target) &&
      e.target !== trigger &&
      !trigger.contains(e.target)
    ) {
      dropdown.classList.add('hidden');
    }
  });
}

/**
 * Mini-cart drawer: delegated events for qty, upsells (desktop + touch).
 * Drawer міні-кошика: делегування подій для qty та upsell (desktop + touch).
 */
function initMiniCart() {
  const drawer = document.getElementById('mini-cart-drawer');
  if (!drawer || drawer.dataset.miniCartBound === '1') {
    return;
  }
  drawer.dataset.miniCartBound = '1';

  let qtyBusy = false;
  let upsellBusy = false;
  let upsellSlide = 0;

  const getConfig = () => window.solidshopMiniCart || {};

  const getAjaxAddUrl = () => {
    const config = getConfig();
    if (config.addToCartUrl) {
      return config.addToCartUrl;
    }
    if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url) {
      return window.wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
    }
    return null;
  };

  const applyFragments = (fragments) => {
    if (!fragments || typeof jQuery === 'undefined') {
      return false;
    }
    jQuery.each(fragments, (selector, html) => {
      jQuery(selector).replaceWith(html);
    });
    jQuery(document.body).trigger('wc_fragments_refreshed');
    return true;
  };

  const parseJsonResponse = async (response) => {
    const text = await response.text();
    if (!text) {
      return null;
    }
    try {
      return JSON.parse(text);
    } catch {
      return null;
    }
  };

  const updateQty = (cartItemKey, quantity) => {
    const config = getConfig();
    if (!config.ajaxUrl || !config.nonce || qtyBusy) {
      return;
    }

    qtyBusy = true;

    const body = new URLSearchParams({
      cart_item_key: cartItemKey,
      quantity: String(quantity),
      nonce: config.nonce,
    });

    fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    })
      .then(parseJsonResponse)
      .then((data) => {
        if (data && data.fragments) {
          applyFragments(data.fragments);
        }
      })
      .finally(() => {
        qtyBusy = false;
      });
  };

  const showUpsellSlide = (root, index) => {
    const upsells = root.querySelector('[data-mini-cart-upsells]');
    if (!upsells) {
      return;
    }
    const slides = upsells.querySelectorAll('[data-upsell-slide]');
    const dots = upsells.querySelectorAll('[data-upsell-dot]');
    if (!slides.length) {
      return;
    }
    upsellSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.hidden = i !== upsellSlide;
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle('is-active', i === upsellSlide);
    });
  };

  const handleQtyClick = (btn) => {
    const wrap = btn.closest('[data-mini-cart-qty-wrap]');
    const valueEl = wrap ? wrap.querySelector('[data-mini-cart-qty-value]') : null;
    if (!valueEl) {
      return;
    }

    const key = btn.getAttribute('data-cart-item-key') || '';
    const min = parseInt(btn.getAttribute('data-min') || '1', 10);
    const maxAttr = btn.getAttribute('data-max');
    const max = maxAttr ? parseInt(maxAttr, 10) : 0;
    let qty = parseInt(valueEl.textContent || '1', 10);

    if (btn.getAttribute('data-mini-cart-qty') === 'plus') {
      qty += 1;
      if (max > 0 && qty > max) {
        qty = max;
      }
    } else {
      qty -= 1;
      if (qty < min) {
        qty = 0;
      }
    }

    updateQty(key, qty);
  };

  const handleUpsellAdd = (btn) => {
    const productId = btn.getAttribute('data-upsell-add');
    const ajaxUrl = getAjaxAddUrl();
    if (!productId || !ajaxUrl || upsellBusy) {
      return;
    }

    upsellBusy = true;
    btn.classList.add('is-loading');
    btn.setAttribute('aria-disabled', 'true');

    const body = new URLSearchParams({
      product_id: productId,
      quantity: '1',
    });

    fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    })
      .then(parseJsonResponse)
      .then((data) => {
        if (data && data.fragments) {
          applyFragments(data.fragments);
          if (typeof window.toggleMiniCart === 'function') {
            window.toggleMiniCart(true);
          }
        }
      })
      .finally(() => {
        upsellBusy = false;
        btn.classList.remove('is-loading');
        btn.removeAttribute('aria-disabled');
      });
  };

  const onDrawerActivate = (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) {
      return;
    }

    const qtyBtn = target.closest('[data-mini-cart-qty]');
    if (qtyBtn) {
      event.preventDefault();
      event.stopPropagation();
      handleQtyClick(qtyBtn);
      return;
    }

    const upsellBtn = target.closest('[data-upsell-add]');
    if (upsellBtn) {
      event.preventDefault();
      event.stopPropagation();
      handleUpsellAdd(upsellBtn);
      return;
    }

    const prevBtn = target.closest('[data-upsell-prev]');
    if (prevBtn) {
      event.preventDefault();
      showUpsellSlide(drawer, upsellSlide - 1);
      return;
    }

    const nextBtn = target.closest('[data-upsell-next]');
    if (nextBtn) {
      event.preventDefault();
      showUpsellSlide(drawer, upsellSlide + 1);
      return;
    }

    const dot = target.closest('[data-upsell-dot]');
    if (dot) {
      event.preventDefault();
      const index = parseInt(dot.getAttribute('data-upsell-dot') || '0', 10);
      showUpsellSlide(drawer, index);
    }
  };

  drawer.addEventListener('click', onDrawerActivate);

  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('wc_fragments_refreshed added_to_cart', () => {
      upsellSlide = 0;
    });
  }
}

/**
 * Wishlist toggle via WC AJAX / AJAX-перемикач wishlist.
 */
function initWishlist() {
  const getConfig = () => window.solidshopWishlist || {};

  const getToggleUrl = () => {
    const config = getConfig();

    if (config.ajaxUrl) {
      return config.ajaxUrl;
    }

    if (config.wcAjaxUrl) {
      return config.wcAjaxUrl;
    }

    if (window.woocommerce_params && window.woocommerce_params.wc_ajax_url) {
      return window.woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'toggle_wishlist');
    }

    return null;
  };

  const updateCountBadges = (count) => {
    document.querySelectorAll('.solidshop-wishlist-count').forEach((el) => {
      el.textContent = String(count);

      if (count > 0) {
        el.classList.remove('hidden');
      } else {
        el.classList.add('hidden');
      }
    });
  };

  const setButtonState = (button, inWishlist) => {
    button.classList.toggle('is-in-wishlist', inWishlist);
    button.setAttribute('aria-pressed', inWishlist ? 'true' : 'false');

    const path = button.querySelector('.js-wishlist-icon-path');
    if (path) {
      path.setAttribute('fill', inWishlist ? 'currentColor' : 'none');
    }

    const label = button.querySelector('.js-wishlist-label');
    if (label) {
      const addLabel = button.dataset.labelAdd || label.textContent;
      const removeLabel = button.dataset.labelRemove || addLabel;
      label.textContent = inWishlist ? removeLabel : addLabel;
      button.setAttribute('aria-label', inWishlist ? removeLabel : addLabel);
      button.classList.toggle('text-gray-900', inWishlist);
      button.classList.toggle('text-gray-600', !inWishlist);
      return;
    }

    button.classList.toggle('text-black', inWishlist);
    button.classList.toggle('text-gray-400', !inWishlist);
  };

  const playPopAnimation = (button) => {
    button.classList.remove('is-pop');
    // Force reflow so repeated clicks retrigger animation / Примусовий reflow для повторних кліків
    void button.offsetWidth;
    button.classList.add('is-pop');
    button.addEventListener(
      'animationend',
      () => button.classList.remove('is-pop'),
      { once: true },
    );
  };

  const parseJsonResponse = async (response) => {
    const text = await response.text();
    if (!text) {
      return null;
    }
    try {
      return JSON.parse(text);
    } catch {
      return null;
    }
  };

  let wishlistBusy = false;

  document.addEventListener('click', (event) => {
    const button = event.target instanceof Element
      ? event.target.closest('.js-wishlist-toggle')
      : null;

    if (!button || wishlistBusy) {
      return;
    }

    event.preventDefault();
    playPopAnimation(button);

    const productId = button.getAttribute('data-product-id');
    const ajaxUrl = getToggleUrl();
    const config = getConfig();

    if (!productId || !ajaxUrl || !config.nonce) {
      return;
    }

    wishlistBusy = true;
    button.setAttribute('aria-disabled', 'true');

    const body = new URLSearchParams({
      product_id: productId,
      nonce: config.nonce,
    });

    fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    })
      .then(parseJsonResponse)
      .then((data) => {
        if (!data || !data.success || !data.data) {
          return;
        }

        const { action, count, in_wishlist: inWishlist } = data.data;

        document
          .querySelectorAll(`.js-wishlist-toggle[data-product-id="${productId}"]`)
          .forEach((el) => setButtonState(el, inWishlist));

        if (typeof count === 'number') {
          updateCountBadges(count);
        }

        if (action === 'removed') {
          const card = button.closest('.wishlist-product-card')
            ?? button.closest('article[data-product-id]');
          if (card && document.getElementById('wishlist-grid')) {
            card.remove();

            if (!document.querySelector('#wishlist-grid .wishlist-product-card')) {
              window.location.reload();
            }
          }
        }
      })
      .finally(() => {
        wishlistBusy = false;
        button.removeAttribute('aria-disabled');
      });
  });

  const config = getConfig();
  if (typeof config.initialCount === 'number') {
    updateCountBadges(config.initialCount);
  }
}

/**
 * Catalog card — AJAX add variation to cart from size buttons.
 * Картка каталогу — AJAX додавання варіації з кнопок розміру.
 */
function initCatalogCardQuickAdd() {
  const getAddToCartUrl = () => {
    if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url) {
      return window.wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
    }

    return '/?wc-ajax=add_to_cart';
  };

  const parseJsonResponse = async (response) => {
    const text = await response.text();

    if (!text) {
      return null;
    }

    try {
      return JSON.parse(text);
    } catch {
      return null;
    }
  };

  document.addEventListener('click', async (event) => {
    const button = event.target instanceof Element
      ? event.target.closest('.ss-product-card__size-btn')
      : null;

    if (!button || button.disabled || button.classList.contains('is-loading')) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    const variationId = button.getAttribute('data-variation-id');
    const productId = button.getAttribute('data-product-id');

    if (!variationId || !productId) {
      return;
    }

    button.classList.add('is-loading');
    button.disabled = true;

    const body = new URLSearchParams({
      product_id: variationId,
      quantity: '1',
    });

    try {
      const response = await fetch(getAddToCartUrl(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString(),
      });

      const data = await parseJsonResponse(response);

      if (!data || data.error) {
        return;
      }

      if (typeof window.jQuery !== 'undefined') {
        window.jQuery(document.body).trigger('added_to_cart', [
          data.fragments,
          data.cart_hash,
          window.jQuery(button),
        ]);
      }
    } finally {
      button.classList.remove('is-loading');
      button.disabled = false;
    }
  });
}

/**
 * Quick Buy modal — AJAX load add-to-cart form / Модалка Quick Buy — AJAX форма купівлі.
 */
function initQuickBuy() {
  const modal = document.getElementById('js-quick-buy-modal');
  const responseEl = document.getElementById('js-quick-buy-response');

  if (!modal || !responseEl) {
    return;
  }

  const loadingMarkup = `
    <div class="quick-buy-modal__loading flex items-center justify-center py-12" aria-live="polite">
      <span class="quick-buy-modal__spinner" aria-hidden="true"></span>
      <span class="sr-only">Завантаження…</span>
    </div>
  `;

  let quickBuyBusy = false;

  const getQuickBuyUrl = () => {
    if (window.woocommerce_params && window.woocommerce_params.wc_ajax_url) {
      return window.woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'solidshop_load_quick_buy');
    }

    return '/?wc-ajax=solidshop_load_quick_buy';
  };

  const openModal = () => {
    modal.classList.add('is-open');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('quick-buy-modal-open');
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('quick-buy-modal-open');
    responseEl.innerHTML = loadingMarkup;
  };

  const initVariationForm = () => {
    if (typeof jQuery === 'undefined') {
      return;
    }

    const $modal = jQuery(modal);
    const $form = $modal.find('.variations_form');

    if ($form.length) {
      $form.each(function initForm() {
        const $el = jQuery(this);
        if (typeof $el.wc_variation_form === 'function') {
          $el.wc_variation_form();
        }
      });
    }
  };

  const parseJsonResponse = async (response) => {
    const text = await response.text();
    if (!text) {
      return null;
    }
    try {
      return JSON.parse(text);
    } catch {
      return null;
    }
  };

  const loadQuickBuyForm = async (productId) => {
    if (quickBuyBusy) {
      return;
    }

    quickBuyBusy = true;
    responseEl.innerHTML = loadingMarkup;
    openModal();

    try {
      const body = new URLSearchParams({ product_id: productId });
      const response = await fetch(getQuickBuyUrl(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString(),
      });

      const data = await parseJsonResponse(response);

      if (!data || !data.success || !data.data || !data.data.html) {
        responseEl.innerHTML = '<p class="text-sm text-red-600 py-6 text-center">Не вдалося завантажити форму.</p>';
        return;
      }

      responseEl.innerHTML = data.data.html;
      initVariationForm();
    } catch {
      responseEl.innerHTML = '<p class="text-sm text-red-600 py-6 text-center">Не вдалося завантажити форму.</p>';
    } finally {
      quickBuyBusy = false;
    }
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target instanceof Element
      ? event.target.closest('.js-quick-buy-trigger')
      : null;

    if (trigger) {
      event.preventDefault();
      const productId = trigger.getAttribute('data-product-id');
      if (productId) {
        loadQuickBuyForm(productId);
      }
      return;
    }

    const closeTarget = event.target instanceof Element
      ? event.target.closest('[data-quick-buy-close], .js-quick-buy-close')
      : null;

    if (closeTarget && modal.classList.contains('is-open')) {
      event.preventDefault();
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
}

/**
 * Submit cart quantity update via WooCommerce cart.js AJAX flow.
 * Надіслати оновлення кількості через AJAX-потік WooCommerce cart.js.
 */
function submitCartQuantityUpdate(form) {
  if (!form || form.classList.contains('processing')) {
    return;
  }

  const updateBtn = form.querySelector('[name="update_cart"]');
  if (!updateBtn) {
    return;
  }

  updateBtn.disabled = false;
  updateBtn.setAttribute('clicked', 'true');

  if (window.jQuery && typeof window.wc_cart_params !== 'undefined') {
    window.jQuery(form).trigger('submit');
  } else {
    form.requestSubmit(updateBtn);
  }
}

/**
 * Inject ± stepper buttons into cart quantity fields.
 * Додати кнопки ± у поля кількості на сторінці кошика.
 */
function enhanceCartQtySteppers() {
  const form = document.querySelector('.solidshop-cart-form');
  if (!form) {
    return;
  }

  const minusIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>';
  const plusIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>';

  form.querySelectorAll('.solidshop-cart-item__qty .quantity').forEach((wrap) => {
    if (!wrap || wrap.dataset.qtyEnhanced === '1') {
      return;
    }

    const input = wrap.querySelector('input.qty');
    if (!input) {
      return;
    }

    wrap.dataset.qtyEnhanced = '1';

    const makeButton = (className, icon, delta) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `qty-btn ${className}`;
      button.innerHTML = icon;

      button.addEventListener('click', () => {
        const current = parseInt(input.value, 10) || 1;
        const min = parseInt(input.getAttribute('min'), 10) || 1;
        const max = parseInt(input.getAttribute('max'), 10) || Infinity;
        const next = Math.max(min, Math.min(max, current + delta));

        if (next !== current) {
          input.value = String(next);
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });

      return button;
    };

    wrap.insertBefore(makeButton('qty-btn--minus', minusIcon, -1), input);
    wrap.appendChild(makeButton('qty-btn--plus', plusIcon, 1));
  });
}

let cartQtyDebounceTimer = null;

/**
 * Cart page — auto-update quantities without visible "Update cart" button.
 * Сторінка кошика — автооновлення кількості без кнопки «Оновити кошик».
 */
function initCartPage() {
  if (!document.body.classList.contains('solidshop-cart-page')) {
    return;
  }

  if (document.body.dataset.cartPageInit !== '1') {
    document.body.dataset.cartPageInit = '1';

    document.body.addEventListener('change', (event) => {
      if (!(event.target instanceof HTMLInputElement) || !event.target.matches('.woocommerce-cart-form input.qty')) {
        return;
      }

      clearTimeout(cartQtyDebounceTimer);
      submitCartQuantityUpdate(event.target.closest('.woocommerce-cart-form'));
    });

    document.body.addEventListener('input', (event) => {
      if (!(event.target instanceof HTMLInputElement) || !event.target.matches('.woocommerce-cart-form input.qty')) {
        return;
      }

      clearTimeout(cartQtyDebounceTimer);
      const form = event.target.closest('.woocommerce-cart-form');

      cartQtyDebounceTimer = setTimeout(() => {
        submitCartQuantityUpdate(form);
      }, 400);
    });

    if (window.jQuery) {
      window.jQuery(document.body).on('updated_wc_div', () => {
        enhanceCartQtySteppers();
      });
    }
  }

  enhanceCartQtySteppers();
}

/**
 * Debounce helper — delays execution until typing pauses.
 * Debounce — відкладає виконання, поки користувач не припинить введення.
 *
 * @param {Function} fn
 * @param {number} delayMs
 * @returns {Function}
 */
function debounce(fn, delayMs) {
  let timerId = null;

  return (...args) => {
    clearTimeout(timerId);
    timerId = setTimeout(() => fn(...args), delayMs);
  };
}

/**
 * Resolve WooCommerce AJAX URL for live search.
 * URL AJAX live-пошуку WooCommerce.
 */
function getLiveSearchUrl() {
  if (window.woocommerce_params && window.woocommerce_params.wc_ajax_url) {
    return window.woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'solidshop_live_search');
  }

  if (window.solidshopLiveSearch && window.solidshopLiveSearch.ajaxUrl) {
    return window.solidshopLiveSearch.ajaxUrl;
  }

  return '/?wc-ajax=solidshop_live_search';
}

/**
 * Bind AJAX live search behaviour to a single root element.
 * Прив'язка live-пошуку до одного root-елемента.
 *
 * @param {HTMLElement} root
 */
function bindLiveSearchRoot(root) {
  const input = root.querySelector('.js-live-search-input');
  const resultsEl = root.querySelector('.js-live-search-results');
  const mode = root.dataset.liveSearchMode || 'inline';

  if (!input || !resultsEl) {
    return;
  }

  let abortController = null;

  const showResults = () => {
    resultsEl.classList.remove('hidden');
    resultsEl.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  };

  const hideResults = () => {
    resultsEl.classList.add('hidden');
    resultsEl.hidden = true;
    input.setAttribute('aria-expanded', 'false');
  };

  const resetResults = () => {
    if (abortController) {
      abortController.abort();
      abortController = null;
    }

    hideResults();
    resultsEl.innerHTML = '';
  };

  const setLoading = () => {
    resultsEl.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">Пошук…</div>';
    showResults();
  };

  const renderEmpty = () => {
    resultsEl.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">Нічого не знайдено</div>';
    showResults();
  };

  const renderResults = (items) => {
    if (!Array.isArray(items) || items.length === 0) {
      renderEmpty();
      return;
    }

    const fragment = document.createDocumentFragment();

    items.forEach((item) => {
      const link = document.createElement('a');
      link.href = item.url || '#';
      link.className = 'flex items-center p-3 hover:bg-gray-50 border-b border-gray-100 transition-colors';
      link.setAttribute('role', 'option');

      const img = document.createElement('img');
      img.src = item.image || '';
      img.alt = item.title || '';
      img.width = 48;
      img.height = 48;
      img.className = 'w-12 h-12 object-cover mr-4 shrink-0 rounded-none';
      img.loading = 'lazy';

      const body = document.createElement('div');
      body.className = 'min-w-0 flex-1';

      if (item.sku) {
        const skuEl = document.createElement('p');
        skuEl.className = 'text-xs text-gray-500 mb-1';
        skuEl.textContent = `SKU: ${item.sku}`;
        body.appendChild(skuEl);
      }

      const titleEl = document.createElement('p');
      titleEl.className = 'text-sm font-bold text-gray-900 truncate';
      titleEl.textContent = item.title || '';
      body.appendChild(titleEl);

      const priceEl = document.createElement('div');
      priceEl.className = 'text-sm text-gray-900 mt-0.5';
      priceEl.innerHTML = item.price || '';
      body.appendChild(priceEl);

      link.appendChild(img);
      link.appendChild(body);
      fragment.appendChild(link);
    });

    resultsEl.innerHTML = '';
    resultsEl.appendChild(fragment);
    showResults();
  };

  const fetchResults = async (keyword) => {
    if (abortController) {
      abortController.abort();
    }

    abortController = new AbortController();

    const formData = new FormData();
    formData.append('keyword', keyword);

    try {
      const response = await fetch(getLiveSearchUrl(), {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        signal: abortController.signal,
      });

      if (!response.ok) {
        renderEmpty();
        return;
      }

      const data = await response.json();
      renderResults(data);
    } catch (error) {
      if (error.name === 'AbortError') {
        return;
      }

      renderEmpty();
    }
  };

  const debouncedSearch = debounce((keyword) => {
    fetchResults(keyword);
  }, 400);

  input.addEventListener('input', () => {
    const keyword = input.value.trim();

    if (keyword.length <= 2) {
      resetResults();
      return;
    }

    setLoading();
    debouncedSearch(keyword);
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && mode === 'inline') {
      hideResults();
    }
  });

  if (mode === 'inline') {
    document.addEventListener('click', (event) => {
      if (!(event.target instanceof Node) || root.contains(event.target)) {
        return;
      }

      hideResults();
    });
  }

  root._solidshopLiveSearchReset = resetResults;
}

/**
 * Header live search — bind all inline/modal roots.
 * Live-пошук у шапці — ініціалізація всіх root-елементів.
 */
function initLiveSearch() {
  document.querySelectorAll('.js-live-search').forEach((root) => {
    if (root instanceof HTMLElement) {
      bindLiveSearchRoot(root);
    }
  });
}

/**
 * Mobile fullscreen search modal open/close lifecycle.
 * Життєвий цикл повноекранного модального пошуку на мобільних.
 */
function initLiveSearchModal() {
  const modal = document.getElementById('mobile-live-search-modal');
  const openButtons = document.querySelectorAll('.js-live-search-open');
  const closeButtons = document.querySelectorAll('.js-live-search-close');
  const mobileRoot = modal ? modal.querySelector('.js-live-search[data-live-search-mode="modal"]') : null;
  const mobileInput = mobileRoot ? mobileRoot.querySelector('.js-live-search-input') : null;

  if (!modal || !mobileRoot || !mobileInput || openButtons.length === 0) {
    return;
  }

  const isOpen = () => !modal.classList.contains('hidden') && !modal.hidden;

  const closeModal = () => {
    modal.classList.add('hidden');
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');

    openButtons.forEach((button) => {
      button.setAttribute('aria-expanded', 'false');
    });

    mobileInput.value = '';

    if (typeof mobileRoot._solidshopLiveSearchReset === 'function') {
      mobileRoot._solidshopLiveSearchReset();
    }
  };

  const openModal = () => {
    modal.classList.remove('hidden');
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');

    openButtons.forEach((button) => {
      button.setAttribute('aria-expanded', 'true');
    });

    window.requestAnimationFrame(() => {
      mobileInput.focus();
    });
  };

  openButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (isOpen()) {
        closeModal();
        return;
      }

      openModal();
    });
  });

  closeButtons.forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  mobileRoot.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const link = event.target.closest('a[href]');
    const resultsEl = mobileRoot.querySelector('.js-live-search-results');

    if (!link || !(resultsEl instanceof HTMLElement) || !resultsEl.contains(link)) {
      return;
    }

    closeModal();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen()) {
      closeModal();
    }
  });
}

/**
 * Product carousels (related + front-page new arrivals) — scroll-snap + dots on mobile.
 * Каруселі товарів — scroll-snap + крапки на mobile.
 */
function initProductCarousels() {
  document.querySelectorAll('[data-product-carousel]').forEach((section) => {
    if (!(section instanceof HTMLElement)) {
      return;
    }

    const track = section.querySelector('.solidshop-related-track');
    const dotsContainer = section.querySelector('.solidshop-related-dots');
    if (!(track instanceof HTMLElement) || !(dotsContainer instanceof HTMLElement)) {
      return;
    }

    const slides = track.querySelectorAll('.solidshop-related-slide');
    if (slides.length <= 1) {
      return;
    }

    const mdBreakpoint = window.matchMedia('(min-width: 768px)');

    dotsContainer.replaceChildren();
    slides.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = index === 0 ? 'active' : '';
      dot.setAttribute('aria-label', `Slide ${index + 1}`);
      dot.addEventListener('click', () => {
        const slide = slides[index];
        if (slide instanceof HTMLElement) {
          slide.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
        }
      });
      dotsContainer.appendChild(dot);
    });

    dotsContainer.hidden = false;

    const dots = dotsContainer.querySelectorAll('button');
    let scrollTicking = false;

    const updateActiveDot = () => {
      if (mdBreakpoint.matches) {
        return;
      }

      const trackRect = track.getBoundingClientRect();
      const trackCenter = trackRect.left + trackRect.width / 2;
      let closestIndex = 0;
      let closestDistance = Infinity;

      slides.forEach((slide, index) => {
        if (!(slide instanceof HTMLElement)) {
          return;
        }

        const slideRect = slide.getBoundingClientRect();
        const slideCenter = slideRect.left + slideRect.width / 2;
        const distance = Math.abs(slideCenter - trackCenter);

        if (distance < closestDistance) {
          closestDistance = distance;
          closestIndex = index;
        }
      });

      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === closestIndex);
      });
    };

    track.addEventListener('scroll', () => {
      if (scrollTicking) {
        return;
      }

      scrollTicking = true;
      requestAnimationFrame(() => {
        updateActiveDot();
        scrollTicking = false;
      });
    }, { passive: true });

    mdBreakpoint.addEventListener('change', updateActiveDot);
    updateActiveDot();
  });
}

/**
 * Contact page AJAX form — vanilla fetch, no plugins.
 * AJAX форма контактів — vanilla fetch, без плагінів.
 */
function initContactForm() {
  const form = document.getElementById('js-contact-form');
  const responseEl = document.getElementById('js-contact-response');
  const submitBtn = document.getElementById('js-contact-submit');

  if (!form || !responseEl || !submitBtn) {
    return;
  }

  const ajaxUrl = form.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
  const defaultLabel = submitBtn.textContent.trim();
  const loadingLabel = 'Відправка...';

  const resetResponse = () => {
    responseEl.classList.add('hidden');
    responseEl.textContent = '';
    responseEl.classList.remove(
      'bg-green-50',
      'text-green-800',
      'border-green-200',
      'bg-red-50',
      'text-red-800',
      'border-red-200'
    );
  };

  const showResponse = (message, type) => {
    resetResponse();
    responseEl.textContent = message;
    responseEl.classList.remove('hidden');

    if (type === 'success') {
      responseEl.classList.add('bg-green-50', 'text-green-800', 'border-green-200');
    } else {
      responseEl.classList.add('bg-red-50', 'text-red-800', 'border-red-200');
    }
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    resetResponse();

    submitBtn.disabled = true;
    submitBtn.textContent = loadingLabel;

    const formData = new FormData(form);
    formData.set('action', 'solidshop_submit_contact');

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });

      const payload = await response.json();
      const message = typeof payload.data === 'string'
        ? payload.data
        : (payload.data?.message || 'Помилка відправки. Спробуйте пізніше.');

      if (payload.success) {
        form.reset();
        showResponse(message, 'success');
      } else {
        showResponse(message, 'error');
      }
    } catch {
      showResponse('Помилка відправки. Спробуйте пізніше.', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = defaultLabel;
    }
  });
}

/**
 * Contact FAQ — single open item at a time (optional enhancement).
 * FAQ контактів — лише один відкритий пункт одночасно.
 */
function initContactFaq() {
  const root = document.querySelector('[data-contact-faq]');

  if (!root) {
    return;
  }

  const items = root.querySelectorAll('.contact-faq__item');

  items.forEach((item) => {
    item.addEventListener('toggle', () => {
      if (!item.open) {
        return;
      }

      items.forEach((other) => {
        if (other !== item) {
          other.open = false;
        }
      });
    });
  });
}
