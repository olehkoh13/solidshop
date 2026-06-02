{{--
  Reset password form — new password after email link.
  Форма встановлення нового пароля.

  @see woocommerce/templates/myaccount/form-reset-password.php
  @version 9.2.0
  @var string $key
  @var string $login
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $reset_key = $key ?? ($args['key'] ?? '');
  $reset_login = $login ?? ($args['login'] ?? '');

  do_action('woocommerce_before_reset_password_form');
@endphp

<x-account-auth-shell :heading="__('Reset password', 'woocommerce')">
  <form method="post" class="woocommerce-ResetPassword lost_reset_password">
    <p class="solidshop-account-auth__intro">
      {{ apply_filters(
          'woocommerce_reset_password_message',
          __('Enter a new password below.', 'woocommerce')
      ) }}
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="password_1">
        {{ __('New password', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
        <span class="screen-reader-text">{{ __('Required', 'woocommerce') }}</span>
      </label>
      <span class="password-input">
        <input
          type="password"
          class="woocommerce-Input woocommerce-Input--text input-text"
          name="password_1"
          id="password_1"
          autocomplete="new-password"
          required
          aria-required="true"
        />
        <button
          type="button"
          class="show-password-input"
          aria-label="{{ __('Show password', 'woocommerce') }}"
          aria-describedby="password_1"
        ></button>
      </span>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="password_2">
        {{ __('Re-enter new password', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
        <span class="screen-reader-text">{{ __('Required', 'woocommerce') }}</span>
      </label>
      <span class="password-input">
        <input
          type="password"
          class="woocommerce-Input woocommerce-Input--text input-text"
          name="password_2"
          id="password_2"
          autocomplete="new-password"
          required
          aria-required="true"
        />
        <button
          type="button"
          class="show-password-input"
          aria-label="{{ __('Show password', 'woocommerce') }}"
          aria-describedby="password_2"
        ></button>
      </span>
    </p>

    <input type="hidden" name="reset_key" value="{{ esc_attr($reset_key) }}" />
    <input type="hidden" name="reset_login" value="{{ esc_attr($reset_login) }}" />

    @php do_action('woocommerce_resetpassword_form'); @endphp

    <p class="woocommerce-form-row form-row">
      <input type="hidden" name="wc_reset_password" value="true" />
      <button
        type="submit"
        class="woocommerce-Button button woocommerce-form-reset-password__submit"
        value="{{ __('Save', 'woocommerce') }}"
      >
        {{ __('Save', 'woocommerce') }}
      </button>
    </p>

    @php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); @endphp
  </form>
</x-account-auth-shell>

@php do_action('woocommerce_after_reset_password_form'); @endphp
