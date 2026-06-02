{{--
  Header live search — AJAX dropdown with thumbnails, SKU, and price.
  Live-пошук у шапці — AJAX випадаючий список з мініатюрами, SKU та ціною.

  @param string $uid     Unique suffix for IDs (desktop|mobile)
  @param string $variant inline|modal
  @param string|null $part  modal only: input|results
--}}
@php
  $uid = $uid ?? 'desktop';
  $variant = $variant ?? 'inline';
  $part = $part ?? null;
  $inputId = "solidshop-live-search-input-{$uid}";
  $resultsId = "solidshop-live-search-results-{$uid}";
  $inputClasses = 'js-live-search-input w-full bg-white border border-gray-300 rounded-none px-4 py-2 text-sm text-gray-900 focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition-colors';
  $resultsInlineClasses = 'js-live-search-results absolute left-0 right-0 top-full mt-0 bg-white border border-gray-200 shadow-2xl z-50 hidden max-h-96 overflow-y-auto rounded-none';
  $resultsModalClasses = 'js-live-search-results flex-1 min-h-0 overflow-y-auto bg-white border-t border-gray-100 hidden rounded-none';
@endphp

@if ($variant === 'inline')
  <div
    class="js-live-search relative w-full"
    data-live-search-mode="inline"
    data-live-search-uid="{{ $uid }}"
  >
    <label for="{{ $inputId }}" class="sr-only">
      {{ __('Пошук за назвою або артикулом (SKU)', 'solidshop') }}
    </label>

    <input
      type="text"
      id="{{ $inputId }}"
      class="{{ $inputClasses }}"
      placeholder="Пошук за назвою або артикулом (SKU)..."
      autocomplete="off"
      role="combobox"
      aria-expanded="false"
      aria-controls="{{ $resultsId }}"
      aria-autocomplete="list"
    >

    <div
      id="{{ $resultsId }}"
      class="{{ $resultsInlineClasses }}"
      role="listbox"
      aria-live="polite"
      hidden
    ></div>
  </div>
@elseif ($variant === 'modal' && $part === 'input')
  <label for="{{ $inputId }}" class="sr-only">
    {{ __('Пошук за назвою або артикулом (SKU)', 'solidshop') }}
  </label>

  <input
    type="text"
    id="{{ $inputId }}"
    class="{{ $inputClasses }}"
    placeholder="Пошук за назвою або артикулом (SKU)..."
    autocomplete="off"
    role="combobox"
    aria-expanded="false"
    aria-controls="{{ $resultsId }}"
    aria-autocomplete="list"
  >
@elseif ($variant === 'modal' && $part === 'results')
  <div
    id="{{ $resultsId }}"
    class="{{ $resultsModalClasses }}"
    role="listbox"
    aria-live="polite"
    hidden
  ></div>
@endif
