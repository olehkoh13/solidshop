{{--
  Mobile fullscreen live search modal.
  Повноекранне модальне вікно live-пошуку для мобільних.
--}}
<div
  id="mobile-live-search-modal"
  class="lg:hidden fixed inset-0 z-[60] bg-white flex flex-col hidden"
  role="dialog"
  aria-modal="true"
  aria-labelledby="mobile-live-search-title"
  aria-hidden="true"
  hidden
>
  <h2 id="mobile-live-search-title" class="sr-only">
    {{ __('Пошук товарів', 'solidshop') }}
  </h2>

  <div
    class="js-live-search flex flex-col flex-1 min-h-0 w-full"
    data-live-search-mode="modal"
    data-live-search-uid="mobile"
  >
    {{-- Шапка модалки: закрити + поле вводу / Modal header: close + input --}}
    <div class="shrink-0 border-b border-gray-200 px-4 py-3 flex items-center gap-3">
      <button
        type="button"
        class="js-live-search-close shrink-0 p-2 text-gray-500 hover:text-gray-900 transition-colors rounded-none"
        aria-label="{{ __('Закрити пошук', 'solidshop') }}"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

      <div class="flex-1 min-w-0">
        @include('partials.live-search', ['uid' => 'mobile', 'variant' => 'modal', 'part' => 'input'])
      </div>
    </div>

    @include('partials.live-search', ['uid' => 'mobile', 'variant' => 'modal', 'part' => 'results'])
  </div>
</div>
