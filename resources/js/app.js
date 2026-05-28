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

  // ── Елементи мега-меню / Mega-menu elements ─────────────────────────────
  const trigger = document.getElementById('mega-menu-trigger');
  const dropdown = document.getElementById('mega-menu-dropdown');

  // Рядки-обгортки лівої панелі (містять і <a>, і <button>)
  // Left panel row wrappers (contain both <a> link and <button> toggle)
  const tabItems = document.querySelectorAll('.mega-menu-tab-item');

  // Кнопки-стрілки в кожному рядку (тільки перемикають праву панель)
  // Arrow buttons in each row (only switch the right panel content)
  const tabTriggers = document.querySelectorAll('.mega-menu-tab-trigger');

  // Контентні блоки правої панелі / Right panel content blocks
  const tabContents = document.querySelectorAll('.mega-menu-tab-content');

  if (!trigger || !dropdown) return;

  // ── Допоміжна функція активації таба / Tab activation helper ───────────
  /**
   * Підсвічує рядок лівої панелі та показує відповідний контент праворуч.
   * Highlights left panel row and shows matching right panel content.
   * Посилання (<a>) в рядку не змінюються і навігація не блокується.
   * The <a> link inside the row is untouched; navigation is not blocked.
   *
   * @param {string} tabId - значення data-tab (наприклад "tab-42") / data-tab value
   */
  function activateTab(tabId) {
    // Скидаємо підсвічування всіх рядків / Reset highlight on all rows
    tabItems.forEach(item => item.classList.remove('bg-blue-50'));

    // Ховаємо весь контент правої панелі / Hide all right panel blocks
    tabContents.forEach(c => c.classList.add('hidden'));

    // Підсвічуємо поточний рядок / Highlight current row
    const activeItem = document.querySelector(`.mega-menu-tab-item[data-tab="${tabId}"]`);
    if (activeItem) activeItem.classList.add('bg-blue-50');

    // Відкриваємо відповідний блок підкатегорій / Show matching subcategory block
    const targetContent = document.getElementById(tabId);
    if (targetContent) targetContent.classList.remove('hidden');
  }

  // ── 1. Відкриття / Закриття всього мега-меню ────────────────────────────
  // ── 1. Open / close the whole mega-menu ─────────────────────────────────
  //
  // trigger — це <button>, НЕ посилання, тому e.stopPropagation() безпечний.
  // trigger is a <button>, NOT a link, so e.stopPropagation() is safe here.
  trigger.addEventListener('click', (e) => {
    e.stopPropagation(); // Не дає document.click закрити меню одразу / Prevents immediate close by document.click
    dropdown.classList.toggle('hidden');

    // Автоматично активуємо перший таб при першому відкритті
    // Auto-activate first tab on first open
    if (!dropdown.classList.contains('hidden') && tabItems.length > 0) {
      const firstTabId = tabItems[0].getAttribute('data-tab');
      if (firstTabId) activateTab(firstTabId);
    }
  });

  // ── 2. Ховер на рядку лівої панелі → активує таб ────────────────────────
  // ── 2. Hover on left panel row → activates tab ──────────────────────────
  //
  // Посилання (<a>) всередині рядка лишається повністю незачепленим:
  // The <a> link inside the row remains completely untouched:
  // клік по ньому виконує нативну навігацію браузера.
  // clicking it performs native browser navigation.
  tabItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
      const tabId = item.getAttribute('data-tab');
      if (tabId) activateTab(tabId);
    });
  });

  // ── 3. Клік на кнопку-стрілку → перемикає таб (без навігації) ───────────
  // ── 3. Click on arrow button → switches tab (no navigation) ─────────────
  //
  // Це <button type="button">, НЕ посилання.
  // This is <button type="button">, NOT a link.
  // e.stopPropagation() потрібен, щоб document.click не закрив меню.
  // e.stopPropagation() needed so document.click doesn't close the menu.
  // e.preventDefault() тут НЕ потрібен і НЕ викликається.
  // e.preventDefault() is NOT needed and NOT called here.
  tabTriggers.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // <button>, не <a> — безпечно / <button>, not <a> — safe
      const tabId = btn.getAttribute('data-tab');
      if (tabId) activateTab(tabId);
    });
  });

  // ── 4. Закриття мега-меню при кліку поза його межами ────────────────────
  // ── 4. Close mega-menu when clicking outside ─────────────────────────────
  //
  // Посилання (<a>) всередині dropdown НЕ перехоплюються цим хендлером.
  // Links (<a>) inside dropdown are NOT intercepted by this handler.
  // Коли користувач клікає посилання: dropdown.contains(e.target) === true,
  // When user clicks a link: dropdown.contains(e.target) === true,
  // тому меню НЕ закривається, і браузер виконує нативний перехід.
  // so the menu does NOT close, and the browser performs native navigation.
  document.addEventListener('click', (e) => {
    if (
      !dropdown.contains(e.target) &&
      e.target !== trigger &&
      !trigger.contains(e.target)
    ) {
      dropdown.classList.add('hidden');
    }
  });

});
