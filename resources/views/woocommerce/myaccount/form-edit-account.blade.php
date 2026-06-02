{{--
  Edit account form — profile + password change inside My Account.
  Редагування облікового запису + зміна пароля.

  @see woocommerce/templates/myaccount/form-edit-account.php
  @version 10.5.0
  @var \WP_User $user
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  do_action('woocommerce_before_edit_account_form');
@endphp

<div class="solidshop-edit-account">
  @php
    ob_start();
    do_action('woocommerce_edit_account_form_tag');
    $edit_form_attrs = trim(ob_get_clean());
  @endphp

  <form class="woocommerce-EditAccountForm edit-account" action="" method="post"{!! $edit_form_attrs !== '' ? ' ' . $edit_form_attrs : '' !!}>
    @php do_action('woocommerce_edit_account_form_start'); @endphp

    <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
      <label for="account_first_name">
        {{ __('First name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
      </label>
      <input
        type="text"
        class="woocommerce-Input woocommerce-Input--text input-text"
        name="account_first_name"
        id="account_first_name"
        autocomplete="given-name"
        value="{{ esc_attr($user->first_name) }}"
        aria-required="true"
        required
      />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
      <label for="account_last_name">
        {{ __('Last name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
      </label>
      <input
        type="text"
        class="woocommerce-Input woocommerce-Input--text input-text"
        name="account_last_name"
        id="account_last_name"
        autocomplete="family-name"
        value="{{ esc_attr($user->last_name) }}"
        aria-required="true"
        required
      />
    </p>

    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="account_display_name">
        {{ __('Display name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
      </label>
      <input
        type="text"
        class="woocommerce-Input woocommerce-Input--text input-text"
        name="account_display_name"
        id="account_display_name"
        aria-describedby="account_display_name_description"
        value="{{ esc_attr($user->display_name) }}"
        aria-required="true"
        required
      />
      <span id="account_display_name_description" class="solidshop-edit-account__hint">
        <em>{{ __('This will be how your name will be displayed in the account section and in reviews', 'woocommerce') }}</em>
      </span>
    </p>

    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="account_email">
        {{ __('Email address', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
      </label>
      <input
        type="email"
        class="woocommerce-Input woocommerce-Input--email input-text"
        name="account_email"
        id="account_email"
        autocomplete="email"
        value="{{ esc_attr($user->user_email) }}"
        aria-required="true"
        required
      />
    </p>

    @php do_action('woocommerce_edit_account_form_fields'); @endphp

    <fieldset class="solidshop-edit-account__password">
      <legend>{{ __('Password change', 'woocommerce') }}</legend>

      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="password_current">
          {{ __('Current password (leave blank to leave unchanged)', 'woocommerce') }}
        </label>
        <span class="password-input">
          <input
            type="password"
            class="woocommerce-Input woocommerce-Input--password input-text"
            name="password_current"
            id="password_current"
            autocomplete="current-password"
          />
          <button
            type="button"
            class="show-password-input"
            aria-label="{{ __('Show password', 'woocommerce') }}"
            aria-describedby="password_current"
          ></button>
        </span>
      </p>

      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="password_1">
          {{ __('New password (leave blank to leave unchanged)', 'woocommerce') }}
        </label>
        <span class="password-input">
          <input
            type="password"
            class="woocommerce-Input woocommerce-Input--password input-text"
            name="password_1"
            id="password_1"
            autocomplete="new-password"
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
          {{ __('Confirm new password', 'woocommerce') }}
        </label>
        <span class="password-input">
          <input
            type="password"
            class="woocommerce-Input woocommerce-Input--password input-text"
            name="password_2"
            id="password_2"
            autocomplete="new-password"
          />
          <button
            type="button"
            class="show-password-input"
            aria-label="{{ __('Show password', 'woocommerce') }}"
            aria-describedby="password_2"
          ></button>
        </span>
      </p>
    </fieldset>

    <div class="clear"></div>

    @php do_action('woocommerce_edit_account_form'); @endphp

    <p class="solidshop-edit-account__submit">
      @php wp_nonce_field('save_account_details', 'save-account-details-nonce'); @endphp
      <button
        type="submit"
        class="woocommerce-Button button"
        name="save_account_details"
        value="{{ __('Save changes', 'woocommerce') }}"
      >
        {{ __('Save changes', 'woocommerce') }}
      </button>
      <input type="hidden" name="action" value="save_account_details" />
    </p>

    @php do_action('woocommerce_edit_account_form_end'); @endphp
  </form>
</div>

@php do_action('woocommerce_after_edit_account_form'); @endphp
