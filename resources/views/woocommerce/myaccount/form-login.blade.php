{{--
  My Account — login & registration (guest).
  Особистий кабінет — вхід і реєстрація (гість).

  @see woocommerce/templates/myaccount/form-login.php
  @version 9.9.0 (WooCommerce core reference)
--}}
@php
  if (! defined('ABSPATH')) {
      exit;
  }

  $registration_enabled = get_option('woocommerce_enable_myaccount_registration') === 'yes';
  $generate_username    = get_option('woocommerce_registration_generate_username') !== 'no';
  $generate_password    = get_option('woocommerce_registration_generate_password') !== 'no';

  $posted_login_username = (! empty($_POST['username']) && is_string($_POST['username']))
      ? esc_attr(wp_unslash($_POST['username']))
      : '';
  $posted_reg_username = (! empty($_POST['username']) && is_string($_POST['username']))
      ? esc_attr(wp_unslash($_POST['username']))
      : '';
  $posted_email = (! empty($_POST['email']) && is_string($_POST['email']))
      ? esc_attr(wp_unslash($_POST['email']))
      : '';
  $grid_classes = 'solidshop-account-auth__grid grid gap-12 lg:gap-16';
  $grid_classes .= $registration_enabled
      ? ' grid-cols-1 md:grid-cols-2'
      : ' grid-cols-1 max-w-lg mx-auto';
@endphp

@php do_action('woocommerce_before_customer_login_form'); @endphp

<div class="solidshop-account-auth max-w-7xl mx-auto px-4 sm:px-6 pb-16">
  <div class="{{ $grid_classes }}">

    {{-- Login / Увійти --}}
    <div class="solidshop-account-auth__column">
      <h2 class="solidshop-account-auth__heading">
        {{ __('Login', 'woocommerce') }}
      </h2>

      <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
        @php do_action('woocommerce_login_form_start'); @endphp

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="username">
            {{ __('Username or email address', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
          </label>
          <input
            type="text"
            class="woocommerce-Input woocommerce-Input--text input-text"
            name="username"
            id="username"
            autocomplete="username"
            value="{{ $posted_login_username }}"
            required
            aria-required="true"
          />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="password">
            {{ __('Password', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
          </label>
          <span class="password-input">
            <input
              class="woocommerce-Input woocommerce-Input--text input-text"
              type="password"
              name="password"
              id="password"
              autocomplete="current-password"
              required
              aria-required="true"
            />
            <button
              type="button"
              class="show-password-input"
              aria-label="{{ __('Show password', 'woocommerce') }}"
              aria-describedby="password"
            ></button>
          </span>
        </p>

        @php do_action('woocommerce_login_form'); @endphp

        <p class="form-row solidshop-account-auth__actions">
          <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
            <input
              class="woocommerce-form__input woocommerce-form__input-checkbox"
              name="rememberme"
              type="checkbox"
              id="rememberme"
              value="forever"
            />
            <span>{{ __('Remember me', 'woocommerce') }}</span>
          </label>

          @php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); @endphp

          <button
            type="submit"
            class="woocommerce-button button woocommerce-form-login__submit"
            name="login"
            value="{{ __('Log in', 'woocommerce') }}"
          >
            {{ __('Log in', 'woocommerce') }}
          </button>
        </p>

        <p class="woocommerce-LostPassword lost_password">
          <a href="{{ esc_url(wp_lostpassword_url()) }}">
            {{ __('Lost your password?', 'woocommerce') }}
          </a>
        </p>

        @php do_action('woocommerce_login_form_end'); @endphp
      </form>
    </div>

    @if ($registration_enabled)
      {{-- Register / Реєстрація --}}
      <div class="solidshop-account-auth__column">
        <h2 class="solidshop-account-auth__heading">
          {{ __('Register', 'woocommerce') }}
        </h2>

        @php
          ob_start();
          do_action('woocommerce_register_form_tag');
          $register_form_attrs = trim(ob_get_clean());
        @endphp
        <form method="post" class="woocommerce-form woocommerce-form-register register"{!! $register_form_attrs !== '' ? ' ' . $register_form_attrs : '' !!}>
          @php do_action('woocommerce_register_form_start'); @endphp

          @if (! $generate_username)
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
              <label for="reg_username">
                {{ __('Username', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
              </label>
              <input
                type="text"
                class="woocommerce-Input woocommerce-Input--text input-text"
                name="username"
                id="reg_username"
                autocomplete="username"
                value="{{ $posted_reg_username }}"
                required
                aria-required="true"
              />
            </p>
          @endif

          <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_email">
              {{ __('Email address', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
            </label>
            <input
              type="email"
              class="woocommerce-Input woocommerce-Input--text input-text"
              name="email"
              id="reg_email"
              autocomplete="email"
              value="{{ $posted_email }}"
              required
              aria-required="true"
            />
          </p>

          @if (! $generate_password)
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
              <label for="reg_password">
                {{ __('Password', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span>
              </label>
              <span class="password-input">
                <input
                  type="password"
                  class="woocommerce-Input woocommerce-Input--text input-text"
                  name="password"
                  id="reg_password"
                  autocomplete="new-password"
                  required
                  aria-required="true"
                />
                <button
                  type="button"
                  class="show-password-input"
                  aria-label="{{ __('Show password', 'woocommerce') }}"
                  aria-describedby="reg_password"
                ></button>
              </span>
            </p>
          @else
            <p class="solidshop-account-auth__hint">
              {{ __('A link to set a new password will be sent to your email address.', 'woocommerce') }}
            </p>
          @endif

          @php do_action('woocommerce_register_form'); @endphp

          <p class="woocommerce-form-row form-row">
            @php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); @endphp
            <button
              type="submit"
              class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit"
              name="register"
              value="{{ __('Register', 'woocommerce') }}"
            >
              {{ __('Register', 'woocommerce') }}
            </button>
          </p>

          @php do_action('woocommerce_register_form_end'); @endphp
        </form>
      </div>
    @endif
  </div>
</div>

@php do_action('woocommerce_after_customer_login_form'); @endphp
