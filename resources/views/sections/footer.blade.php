<footer class="bg-gray-950 text-gray-400 mt-auto border-t border-gray-900">
  <div class="container mx-auto px-4 py-12 md:py-16">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-10">

      {{-- Блок бренду --}}
      <div class="flex flex-col gap-4">
        <span class="text-xl font-black tracking-tight text-white">SOLID<span class="text-blue-500">SHOP</span></span>
        <p class="text-sm text-gray-500 leading-relaxed">
          Сучасні, надійні рішення для вашого інтер'єру. Швидка доставка та гарантія якості на всі товари.
        </p>
      </div>

      {{-- Навігація --}}
      <div>
        <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Магазин</h4>
        <ul class="space-y-2.5 text-sm">
          <li><a href="/shop/" class="hover:text-white transition-colors">Усі товари</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Новинки</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Акції</a></li>
        </ul>
      </div>

      {{-- Клієнтська зона --}}
      <div>
        <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Підтримка</h4>
        <ul class="space-y-2.5 text-sm">
          <li><a href="#" class="hover:text-white transition-colors">Оплата та доставка</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Повернення товару</a></li>
          <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
        </ul>
      </div>

      {{-- Контакти --}}
      <div>
        <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Контакти</h4>
        <p class="text-sm text-gray-500 leading-relaxed mb-2">Україна</p>
        <p class="text-white font-medium text-sm">support@solidwebcraft.com</p>
      </div>

    </div>

    {{-- Нижня лінія --}}
    <div class="border-t border-gray-900 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-600">
      <p>&copy; {{ date('Y') }} SolidShop. Всі права захищено.</p>
      <p>Побудовано на базі clean architecture розробником Oleh Kohut.</p>
    </div>
  </div>
</footer>
