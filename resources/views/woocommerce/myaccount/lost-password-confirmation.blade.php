{{--
  Lost password confirmation — email sent notice.
  Підтвердження відправки листа для скидання пароля.

  @see woocommerce/templates/myaccount/lost-password-confirmation.php
  @version 3.9.0
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  do_action('woocommerce_before_lost_password_confirmation_message');
@endphp

<x-account-auth-shell heading="" :show-back-link="true">
  <div class="solidshop-account-auth__confirmation">
    @php wc_print_notice(__('Password reset email has been sent.', 'woocommerce')); @endphp

    <p class="solidshop-account-auth__intro">
      {{ apply_filters(
          'woocommerce_lost_password_confirmation_message',
          __('A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'woocommerce')
      ) }}
    </p>
  </div>
</x-account-auth-shell>

@php do_action('woocommerce_after_lost_password_confirmation_message'); @endphp
