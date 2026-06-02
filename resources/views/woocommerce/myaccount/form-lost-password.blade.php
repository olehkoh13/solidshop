{{--
  Lost password form — guest auth layout.
  Форма «Забули пароль» — layout як login.

  @see woocommerce/templates/myaccount/form-lost-password.php
  @version 9.2.0
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  do_action('woocommerce_before_lost_password_form');
@endphp

<x-account-auth-shell :heading="__('Lost your password?', 'woocommerce')">
  <form method="post" class="woocommerce-ResetPassword lost_reset_password">
    <p class="solidshop-account-auth__intro">
      {{ apply_filters(
          'woocommerce_lost_password_message',
          __('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')
      ) }}
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="user_login">
        {{ __('Username or email', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
        <span class="screen-reader-text">{{ __('Required', 'woocommerce') }}</span>
      </label>
      <input
        class="woocommerce-Input woocommerce-Input--text input-text"
        type="text"
        name="user_login"
        id="user_login"
        autocomplete="username"
        required
        aria-required="true"
      />
    </p>

    @php do_action('woocommerce_lostpassword_form'); @endphp

    <p class="woocommerce-form-row form-row">
      <input type="hidden" name="wc_reset_password" value="true" />
      <button
        type="submit"
        class="woocommerce-Button button woocommerce-form-lost-password__submit"
        value="{{ __('Reset password', 'woocommerce') }}"
      >
        {{ __('Reset password', 'woocommerce') }}
      </button>
    </p>

    @php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); @endphp
  </form>
</x-account-auth-shell>

@php do_action('woocommerce_after_lost_password_form'); @endphp
