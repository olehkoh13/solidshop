{{--
  My Account — premium B2B layout (12-column grid)
  Особистий кабінет — premium B2B-макет (сітка 12 колонок)

  @see woocommerce/templates/myaccount/my-account.php
  @version 3.5.0 (WooCommerce core reference)
  @var \WP_User $current_user
  @var int|string $order_count
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  // Active endpoint label for centered page heading / Назва активної вкладки для заголовка
  $account_endpoint = '';
  $account_heading  = get_the_title();

  if (function_exists('WC') && WC()->query) {
      $account_endpoint = WC()->query->get_current_endpoint();

      if ($account_endpoint === '') {
          $account_endpoint = 'dashboard';
      }

      $menu_items = wc_get_account_menu_items();

      if (isset($menu_items[$account_endpoint])) {
          $account_heading = $menu_items[$account_endpoint];
      }
  }
@endphp

<div class="woocommerce-my-account-wrapper max-w-7xl mx-auto px-4 sm:px-6 py-section grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

  {{-- Centered endpoint title / Центрований заголовок активної вкладки --}}
  <h1 class="lg:col-span-12 text-center text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
    {{ $account_heading }}
  </h1>

  {{-- WooCommerce notices (success, error, info) / Повідомлення WooCommerce --}}
  <div class="woocommerce-notices-wrapper lg:col-span-12">
    @php wc_print_notices(); @endphp
  </div>

  {{-- Left: account navigation / Ліворуч: навігація кабінету --}}
  <div class="lg:col-span-3">
    @php do_action('woocommerce_account_navigation'); @endphp
  </div>

  {{-- Right: endpoint content / Праворуч: контент активного розділу --}}
  <div class="woocommerce-MyAccount-content lg:col-span-9 bg-white border border-gray-200 p-6 lg:p-8">
    @php do_action('woocommerce_account_content'); @endphp
  </div>

</div>
