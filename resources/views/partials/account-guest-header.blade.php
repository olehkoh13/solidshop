{{--
  Guest My Account header — breadcrumbs + title (login / password flow).
  Заголовок My Account для гостей — breadcrumbs + title.
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $account_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('myaccount') : 0;
  $account_page_title = $account_page_id > 0 ? get_the_title($account_page_id) : __('Особистий кабінет', 'solidshop');
  $account_page_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
  $page_title = $account_page_title;
  $breadcrumb_items = [
      [
          'label' => __('Головна', 'solidshop'),
          'url'   => home_url('/'),
      ],
      [
          'label' => $account_page_title,
          'url'   => null,
      ],
  ];

  if (function_exists('WC') && WC()->query) {
      $endpoint = WC()->query->get_current_endpoint();
      $is_reset_sent = isset($_GET['reset-link-sent']) && (string) wp_unslash($_GET['reset-link-sent']) !== '';
      $is_reset_form = isset($_GET['show-reset-form']) && (string) wp_unslash($_GET['show-reset-form']) !== '';

      if ($endpoint === 'lost-password') {
          if ($is_reset_sent) {
              $page_title = __('Лист надіслано', 'solidshop');
          } elseif ($is_reset_form) {
              $page_title = __('Новий пароль', 'solidshop');
          } else {
              $page_title = __('Відновлення пароля', 'solidshop');
          }

          $breadcrumb_items = [
              [
                  'label' => __('Головна', 'solidshop'),
                  'url'   => home_url('/'),
              ],
              [
                  'label' => $account_page_title,
                  'url'   => $account_page_url,
              ],
              [
                  'label' => $page_title,
                  'url'   => null,
              ],
          ];
      }
  }
@endphp

<div class="solidshop-account-guest-header max-w-7xl mx-auto px-4 sm:px-6 pt-section pb-6">
  <nav class="solidshop-breadcrumbs text-sm text-gray-500 mb-3" aria-label="{{ __('Breadcrumb', 'solidshop') }}">
    @foreach ($breadcrumb_items as $index => $item)
      @if ($index > 0)
        <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
      @endif
      @if (! empty($item['url']))
        <a href="{{ esc_url($item['url']) }}" class="hover:text-gray-900 transition-colors no-underline">
          {{ $item['label'] }}
        </a>
      @else
        <span class="text-gray-900">{{ $item['label'] }}</span>
      @endif
    @endforeach
  </nav>

  <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
    {{ $page_title }}
  </h1>
</div>
