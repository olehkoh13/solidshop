{{--
  Guest auth shell — single-column layout matching login/register.
  Обгортка guest auth — одна колонка як login/register.
--}}
@props([
  'heading' => '',
  'showBackLink' => true,
])

<div {{ $attributes->merge(['class' => 'solidshop-account-auth max-w-7xl mx-auto px-4 sm:px-6 pb-16']) }}>
  <div class="solidshop-account-auth__grid grid grid-cols-1 max-w-lg">
    <div class="solidshop-account-auth__column">
      @if ($heading !== '')
        <h2 class="solidshop-account-auth__heading">
          {{ $heading }}
        </h2>
      @endif

      {{ $slot }}

      @if ($showBackLink)
        <p class="solidshop-account-auth__back-link-wrap">
          <a href="{{ esc_url(wc_get_page_permalink('myaccount')) }}" class="solidshop-account-auth__back-link">
            ← {{ __('Повернутися до входу', 'solidshop') }}
          </a>
        </p>
      @endif
    </div>
  </div>
</div>
