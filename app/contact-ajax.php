<?php

/**
 * SolidShop contact form AJAX handler (no plugins).
 * AJAX-обробник контактної форми SolidShop (без плагінів).
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

const SOLIDSHOP_CONTACT_NONCE_ACTION = 'solidshop_contact_action';
const SOLIDSHOP_CONTACT_AJAX_ACTION    = 'solidshop_submit_contact';

/**
 * Handle contact form submission via admin-ajax.php.
 * Обробляє відправку контактної форми через admin-ajax.php.
 */
function solidshop_ajax_submit_contact(): void
{
    // phpcs:disable WordPress.Security.NonceVerification.Missing
    $nonce = isset($_POST['solidshop_contact_nonce'])
        ? sanitize_text_field(wp_unslash((string) $_POST['solidshop_contact_nonce']))
        : '';
    // phpcs:enable WordPress.Security.NonceVerification.Missing

    if (! wp_verify_nonce($nonce, SOLIDSHOP_CONTACT_NONCE_ACTION)) {
        wp_send_json_error(__('Помилка безпеки. Оновіть сторінку та спробуйте знову.', 'solidshop'), 403);
    }

    // phpcs:disable WordPress.Security.NonceVerification.Missing
    $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash((string) $_POST['first_name'])) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash((string) $_POST['last_name'])) : '';
    $name       = trim($first_name . ' ' . $last_name);

    if ($name === '' && isset($_POST['name'])) {
        $name = sanitize_text_field(wp_unslash((string) $_POST['name']));
    }

    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash((string) $_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash((string) $_POST['message'])) : '';
    // phpcs:enable WordPress.Security.NonceVerification.Missing

    if ($name === '' || $email === '' || $message === '') {
        wp_send_json_error(__('Будь ласка, заповніть усі поля форми.', 'solidshop'), 400);
    }

    if (! is_email($email)) {
        wp_send_json_error(__('Введіть коректну email-адресу.', 'solidshop'), 400);
    }

    $admin_email = get_option('admin_email');

    if (! is_string($admin_email) || $admin_email === '') {
        wp_send_json_error(__('Помилка відправки. Спробуйте пізніше.', 'solidshop'), 500);
    }

    $subject = 'Нове повідомлення з сайту SolidShop';
    $body    = sprintf(
        "Ім'я: %s\nEmail: %s\n\nПовідомлення:\n%s",
        $name,
        $email,
        $message
    );

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    ];

    $sent = wp_mail($admin_email, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(__('Ваше повідомлення успішно відправлено!', 'solidshop'));
    }

    wp_send_json_error(__('Помилка відправки. Спробуйте пізніше.', 'solidshop'), 500);
}

add_action('wp_ajax_' . SOLIDSHOP_CONTACT_AJAX_ACTION, __NAMESPACE__ . '\\solidshop_ajax_submit_contact');
add_action('wp_ajax_nopriv_' . SOLIDSHOP_CONTACT_AJAX_ACTION, __NAMESPACE__ . '\\solidshop_ajax_submit_contact');
