<?php

/**
 * Facebook Conversions API (server-side) for WooCommerce.
 * Facebook Conversions API (серверний) для WooCommerce.
 *
 * @package App\Tracking
 */

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Support\CustomerHasher;
use App\Tracking\Support\OrderPayload;

final class FacebookCAPI
{
    private const PURCHASE_META = '_ss_fb_capi_purchase_sent';

    private const REFUND_META = '_ss_fb_capi_refund_sent';

    private const GRAPH_VERSION = 'v21.0';

    public function register(): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        add_action('woocommerce_thankyou', [$this, 'sendPurchase'], 10, 1);
        add_action('woocommerce_order_status_refunded', [$this, 'sendRefund'], 10, 1);
        add_action('woocommerce_order_status_cancelled', [$this, 'sendRefund'], 10, 1);
    }

    /**
     * Pixel ID + CAPI token present.
     * Наявні Pixel ID та CAPI token.
     */
    public function isConfigured(): bool
    {
        return $this->pixelId() !== '' && $this->accessToken() !== '';
    }

    /**
     * Send Purchase on thank-you (once per order).
     * Purchase на thank-you (один раз на замовлення).
     */
    public function sendPurchase(int $orderId): void
    {
        if ($orderId <= 0) {
            return;
        }

        $order = wc_get_order($orderId);

        if (! $order instanceof \WC_Order) {
            return;
        }

        if ($order->get_meta(self::PURCHASE_META, true)) {
            return;
        }

        if ($order->has_status(['failed', 'cancelled', 'refunded'])) {
            return;
        }

        $payload = $this->buildEventPayload($order, 'Purchase', (float) $order->get_total());

        if ($this->dispatch($payload)) {
            $order->update_meta_data(self::PURCHASE_META, '1');
            $order->save();
        }
    }

    /**
     * Send Refund on cancelled/refunded status (same event_id as purchase).
     * Refund при cancelled/refunded (той самий event_id, що й purchase).
     */
    public function sendRefund(int $orderId): void
    {
        if ($orderId <= 0) {
            return;
        }

        $order = wc_get_order($orderId);

        if (! $order instanceof \WC_Order) {
            return;
        }

        if ($order->get_meta(self::REFUND_META, true)) {
            return;
        }

        $value = OrderPayload::refundValue($order);
        $payload = $this->buildEventPayload($order, 'Refund', $value);

        if ($this->dispatch($payload)) {
            $order->update_meta_data(self::REFUND_META, '1');
            $order->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEventPayload(\WC_Order $order, string $eventName, float $value): array
    {
        $orderId = (string) $order->get_id();
        $userData = $this->buildUserData($order);

        return [
            'data' => [
                [
                    'event_name'    => $eventName,
                    'event_time'    => time(),
                    'event_id'      => $orderId,
                    'action_source' => 'website',
                    'user_data'     => $userData,
                    'custom_data'   => [
                        'currency' => $order->get_currency(),
                        'value'    => round($value, 2),
                        'order_id' => $orderId,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUserData(\WC_Order $order): array
    {
        $email = CustomerHasher::email($order->get_billing_email());
        $phone = CustomerHasher::phone($order->get_billing_phone());
        $city = CustomerHasher::locality($order->get_billing_city());
        $zip = CustomerHasher::locality($order->get_billing_postcode());

        $userData = [
            'client_ip_address' => $this->clientIp(),
            'client_user_agent' => $this->userAgent(),
        ];

        if ($email !== null) {
            $userData['em'] = [$email];
        }

        if ($phone !== null) {
            $userData['ph'] = [$phone];
        }

        if ($city !== null) {
            $userData['ct'] = [$city];
        }

        if ($zip !== null) {
            $userData['zp'] = [$zip];
        }

        return $userData;
    }

    /**
     * Async POST to Meta Graph API.
     * Асинхронний POST до Meta Graph API.
     *
     * @param array<string, mixed> $body
     */
    private function dispatch(array $body): bool
    {
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/events?access_token=%s',
            self::GRAPH_VERSION,
            rawurlencode($this->pixelId()),
            rawurlencode($this->accessToken())
        );

        $response = wp_remote_post($url, [
            'timeout'  => 5,
            'blocking' => false,
            'headers'  => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        return true;
    }

    private function pixelId(): string
    {
        return sanitize_text_field((string) get_option('ss_fb_pixel_id', ''));
    }

    private function accessToken(): string
    {
        return sanitize_text_field((string) get_option('ss_fb_capi_token', ''));
    }

    private function clientIp(): string
    {
        if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);

            return sanitize_text_field(trim($parts[0]));
        }

        return sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    private function userAgent(): string
    {
        return sanitize_text_field((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }
}
