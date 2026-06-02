{{--
  Checkout progress steps — cart / shipping / confirmation
  Кроки оформлення — кошик / доставка / підтвердження

  @var int $current_step  1|2|3
--}}
@php
  $current_step = isset($current_step) ? max(1, min(3, (int) $current_step)) : 1;

  $steps = [
    1 => __('Кошик для покупок', 'solidshop'),
    2 => __('Доставка та оформлення замовлення', 'solidshop'),
    3 => __('Підтвердження', 'solidshop'),
  ];
@endphp

<nav
  class="solidshop-checkout-steps"
  data-current-step="{{ $current_step }}"
  aria-label="{{ __('Кроки оформлення замовлення', 'solidshop') }}"
>
  <ol class="solidshop-checkout-steps__list">
    @foreach ($steps as $number => $label)
      @php
        $state = $number < $current_step ? 'is-complete' : ($number === $current_step ? 'is-active' : 'is-upcoming');
        $is_clickable = $number < $current_step;
        $url = '';
        if ($is_clickable) {
          $url = $number === 1 ? wc_get_cart_url() : ($number === 2 ? wc_get_checkout_url() : '');
        }
      @endphp
      <li class="solidshop-checkout-steps__item {{ $state }}">
        @if ($is_clickable && $url)
          <a href="{{ esc_url($url) }}" class="group flex flex-col items-center gap-[0.625rem] text-center no-underline w-full">
            <span class="solidshop-checkout-steps__marker group-hover:bg-blue-600 group-hover:text-white group-hover:scale-105 transition-all duration-200" aria-hidden="true">
              {{ $number }}
            </span>
            <span class="solidshop-checkout-steps__label group-hover:text-blue-600 transition-colors duration-200">
              {{ $label }}
            </span>
          </a>
        @else
          <span class="solidshop-checkout-steps__marker" aria-hidden="true">{{ $number }}</span>
          <span class="solidshop-checkout-steps__label">{{ $label }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
