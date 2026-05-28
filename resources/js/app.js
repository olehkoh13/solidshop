/**
 * Ванільна інтерактивна логіка для Мега-Меню в стилі Розетки
 */
document.addEventListener('DOMContentLoaded', () => {
  const trigger = document.getElementById('mega-menu-trigger');
  const dropdown = document.getElementById('mega-menu-dropdown');
  const tabTriggers = document.querySelectorAll('.mega-menu-tab-trigger');
  const tabContents = document.querySelectorAll('.mega-menu-tab-content');

  if (!trigger || !dropdown) return;

  // 1. Відкриття / Закриття всього меню по кліку на кнопку
  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('hidden');

    // Автоматично активуємо першу вкладку при відкритті, якщо нічого не вибрано
    if (!dropdown.classList.contains('hidden') && tabTriggers.length > 0) {
      const activeTab = document.querySelector(
        '.mega-menu-tab-trigger.bg-blue-50',
      );
      if (!activeTab) {
        tabTriggers[0].dispatchEvent(new Event('mouseenter'));
      }
    }
  });

  // 2. Логіка перемикання табів (ліва бокова панель) при наведенні (mouseenter)
  tabTriggers.forEach((btn) => {
    btn.addEventListener('mouseenter', () => {
      // Очищаємо всі активні класи у кнопок
      tabTriggers.forEach((t) =>
        t.classList.remove('bg-blue-50', 'text-blue-600'),
      );
      // Ховаємо весь контент підкатегорій праворуч
      tabContents.forEach((c) => c.classList.add('hidden'));

      // Активуємо поточну кнопку
      btn.classList.add('bg-blue-50', 'text-blue-600');
      // Показуємо контент відповідного табу
      const tabId = btn.getAttribute('data-tab');
      const targetContent = document.getElementById(tabId);
      if (targetContent) {
        targetContent.classList.remove('hidden');
      }
    });
  });

  // 3. Закриття меню при кліку в будь-яку іншу точку екрана
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
