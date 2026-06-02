{{--
  Quick Buy modal shell — AJAX-loaded add-to-cart form
  Оболонка модального Quick Buy — форма купівлі через AJAX
--}}
<div
  id="js-quick-buy-modal"
  class="quick-buy-modal fixed inset-0 z-50 flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300"
  aria-hidden="true"
  role="presentation"
>
  {{-- Backdrop click closes modal / Клік по фону закриває модалку --}}
  <div class="quick-buy-modal__backdrop absolute inset-0" data-quick-buy-close aria-hidden="true"></div>

  <div
    class="quick-buy-modal__panel bg-white w-full max-w-xl p-6 md:p-8 rounded-none relative shadow-2xl border border-gray-200 mx-4 z-10"
    role="dialog"
    aria-modal="true"
    aria-labelledby="js-quick-buy-title"
  >
    <button
      type="button"
      class="js-quick-buy-close absolute top-4 right-4 text-gray-500 hover:text-black font-bold uppercase text-xs tracking-wide transition-colors"
      aria-label="{{ __('Закрити', 'solidshop') }}"
    >
      {{ __('Закрити', 'solidshop') }}
    </button>

    <h2 id="js-quick-buy-title" class="sr-only">{{ __('Швидка покупка', 'solidshop') }}</h2>

    <div id="js-quick-buy-response" class="quick-buy-modal__response min-h-[8rem]">
      {{-- Default loading state / Стан завантаження за замовчуванням --}}
      <div class="quick-buy-modal__loading flex items-center justify-center py-12" aria-live="polite">
        <span class="quick-buy-modal__spinner" aria-hidden="true"></span>
        <span class="sr-only">{{ __('Завантаження…', 'solidshop') }}</span>
      </div>
    </div>
  </div>
</div>
