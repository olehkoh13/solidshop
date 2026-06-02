{{--
  Contact page — ACF contact details, FAQ, AJAX form, map, Store JSON-LD.
  Сторінка контактів — ACF дані, FAQ, AJAX форма, карта, Store JSON-LD.
--}}
@php
  $address_parts = array_filter([
    $locationStreet ?? '',
    trim(($locationZip ?? '') . ' ' . ($locationCity ?? '')),
    $locationCountry ?? '',
  ]);
  $address_line = implode(', ', $address_parts);
@endphp

{{-- Contact details / Контактні дані --}}
<section class="py-section max-w-7xl mx-auto px-4 sm:px-6" aria-label="{{ __('Контактна інформація', 'solidshop') }}">
  <p class="text-sm text-gray-500 mb-2">
    {{ __('Звʼяжіться з нами', 'solidshop') }}
  </p>
  <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">
    {{ __('Контактна інформація', 'solidshop') }}
  </h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="border border-gray-200 rounded-none p-6 flex gap-4">
      <span class="shrink-0 text-gray-900" aria-hidden="true">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
        </svg>
      </span>
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-900 mb-1">{{ __('Телефон', 'solidshop') }}</p>
        <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="text-gray-600 hover:text-gray-900 transition-colors no-underline">
          {{ $contactPhone }}
        </a>
      </div>
    </div>

    <div class="border border-gray-200 rounded-none p-6 flex gap-4">
      <span class="shrink-0 text-gray-900" aria-hidden="true">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
        </svg>
      </span>
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-900 mb-1">{{ __('Email', 'solidshop') }}</p>
        <a href="mailto:{{ esc_attr($contactEmail) }}" class="text-gray-600 hover:text-gray-900 transition-colors no-underline">
          {{ $contactEmail }}
        </a>
      </div>
    </div>
  </div>

  @if ($address_line !== '')
    <div class="border border-gray-200 rounded-none p-6 flex gap-4 mb-8">
      <span class="shrink-0 text-gray-900" aria-hidden="true">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
        </svg>
      </span>
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-900 mb-1">{{ __('Адреса', 'solidshop') }}</p>
        <p class="text-gray-600">{{ $address_line }}</p>
      </div>
    </div>
  @endif

  @if (! empty($workingHours))
    <div class="border border-gray-200 rounded-none overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-900">{{ __('Години роботи', 'solidshop') }}</p>
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">{{ __('Дні', 'solidshop') }}</th>
            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">{{ __('Відкриття', 'solidshop') }}</th>
            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">{{ __('Закриття', 'solidshop') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($workingHours as $row)
            @if (! empty($row['days']))
              <tr class="border-b border-gray-100 last:border-b-0">
                <td class="px-6 py-3 font-medium text-gray-900 text-center">{{ $row['days'] }}</td>
                <td class="px-6 py-3 text-gray-600 text-center">{{ $row['open_time'] ?? '' }}</td>
                <td class="px-6 py-3 text-gray-600 text-center">{{ $row['close_time'] ?? '' }}</td>
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</section>

{{-- FAQ + contact form / FAQ + форма зв'язку --}}
<section class="py-section max-w-7xl mx-auto px-4 sm:px-6 border-t border-gray-200">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-20">
    <div>
      <p class="text-sm text-gray-500 mb-2">
        {{ __('Відповіді на поширені запитання', 'solidshop') }}
      </p>
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">
        {{ __('Часті запитання', 'solidshop') }}
      </h2>

      <div class="contact-faq border-t border-gray-200" data-contact-faq>
        @foreach ($faqItems as $item)
          <details
            class="contact-faq__item border-b border-gray-200 group"
            @if ($item['open']) open @endif
          >
            <summary class="contact-faq__trigger py-5 flex items-start justify-between gap-4 cursor-pointer list-none">
              <span class="text-sm font-bold uppercase tracking-wide text-gray-900 leading-snug">
                {{ $item['question'] }}
              </span>
              <span class="contact-faq__icon shrink-0 mt-0.5 text-gray-900" aria-hidden="true">
                <svg class="w-5 h-5 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
              </span>
            </summary>
            <div class="contact-faq__panel pb-5 pr-8 text-gray-600 text-sm leading-relaxed">
              {{ $item['answer'] }}
            </div>
          </details>
        @endforeach
      </div>
    </div>

    <div>
      <p class="text-sm text-gray-500 mb-2">
        {{ __('Все ще потрібна допомога?', 'solidshop') }}
      </p>
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">
        {{ __('Звʼяжіться з нами', 'solidshop') }}
      </h2>

      <form
        id="js-contact-form"
        method="post"
        novalidate
        class="space-y-6"
        data-ajax-url="{{ esc_url(admin_url('admin-ajax.php')) }}"
      >
        @php(wp_nonce_field('solidshop_contact_action', 'solidshop_contact_nonce'))

        <div>
          <label class="block text-sm font-medium text-gray-900 mb-2">
            {{ __('Імʼя', 'solidshop') }} <span class="text-red-600" aria-hidden="true">*</span>
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <input
                type="text"
                id="contact-first-name"
                name="first_name"
                required
                autocomplete="given-name"
                placeholder="{{ __('Перший', 'solidshop') }}"
                class="border border-gray-300 rounded-none focus:border-black focus:ring-1 focus:ring-black px-4 py-3 w-full text-gray-900 placeholder:text-gray-400"
              >
            </div>
            <div>
              <input
                type="text"
                id="contact-last-name"
                name="last_name"
                required
                autocomplete="family-name"
                placeholder="{{ __('Останній', 'solidshop') }}"
                class="border border-gray-300 rounded-none focus:border-black focus:ring-1 focus:ring-black px-4 py-3 w-full text-gray-900 placeholder:text-gray-400"
              >
            </div>
          </div>
        </div>

        <div>
          <label for="contact-email" class="block text-sm font-medium text-gray-900 mb-2">
            {{ __('Електронна пошта', 'solidshop') }} <span class="text-red-600" aria-hidden="true">*</span>
          </label>
          <input
            type="email"
            id="contact-email"
            name="email"
            required
            autocomplete="email"
            class="border border-gray-300 rounded-none focus:border-black focus:ring-1 focus:ring-black px-4 py-3 w-full text-gray-900"
          >
        </div>

        <div>
          <label for="contact-message" class="block text-sm font-medium text-gray-900 mb-2">
            {{ __('Коментар або повідомлення', 'solidshop') }} <span class="text-red-600" aria-hidden="true">*</span>
          </label>
          <textarea
            id="contact-message"
            name="message"
            rows="6"
            required
            class="border border-gray-300 rounded-none focus:border-black focus:ring-1 focus:ring-black px-4 py-3 w-full resize-y min-h-[160px] text-gray-900"
          ></textarea>
        </div>

        <button
          type="submit"
          id="js-contact-submit"
          class="bg-black text-white px-8 py-4 font-bold uppercase text-sm rounded-none w-full sm:w-auto hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ __('Надіслати', 'solidshop') }}
        </button>

        <div id="js-contact-response" class="hidden mt-2 p-4 text-sm font-bold border rounded-none" role="status" aria-live="polite"></div>
      </form>
    </div>
  </div>
</section>

{{-- Map below FAQ + form — full viewport width / Карта на всю ширину --}}
<section class="py-section w-full border-t border-gray-200" aria-label="{{ __('Карта', 'solidshop') }}">
  <div class="contact-map relative w-full min-h-[550px] h-[550px] overflow-hidden bg-gray-100 grayscale hover:grayscale-0 transition duration-500 [&>div]:absolute [&>div]:inset-0 [&>div]:!w-full [&>div]:!h-full [&_iframe]:absolute [&_iframe]:inset-0 [&_iframe]:!w-full [&_iframe]:!h-full [&_iframe]:border-0">
    @if (! empty($mapIframe))
      {!! $mapIframe !!}
    @else
      <iframe
        title="{{ __('SolidShop — Івано-Франківськ', 'solidshop') }}"
        src="{{ esc_url($mapFallbackSrc) }}"
        class="absolute inset-0 w-full h-full border-0"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen
      ></iframe>
    @endif
  </div>
</section>

@if (! empty($localBusinessSchemaJson))
  <script type="application/ld+json">{!! $localBusinessSchemaJson !!}</script>
@endif
