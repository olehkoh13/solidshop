<?php

/**
 * GA4 DataLayer (frontend) + Measurement Protocol (server refunds).
 * GA4 DataLayer (фронтенд) + Measurement Protocol (серверні refund).
 *
 * @package App\Tracking
 */

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Support\OrderPayload;

final class GoogleAnalytics
{
    private const DATALAYER_META = '_ss_ga4_datalayer_purchase_sent';

    private const REFUND_META = '_ss_ga4_mp_refund_sent';

    public function register(): void
    {
        if ($this->measurementId() !== '') {
            add_action('woocommerce_thankyou', [$this, 'outputPurchaseDataLayer'], 10, 1);
        }

        if ($this->isServerConfigured()) {
            add_action('woocommerce_order_status_refunded', [$this, 'sendRefund'], 10, 1);
            add_action('woocommerce_order_status_cancelled', [$this, 'sendRefund'], 10, 1);
        }
    }

    /**
     * Measurement ID + API secret for MP.
     * Measurement ID + API secret для MP.
     */
    public function isServerConfigured(): bool
    {
        return $this->measurementId() !== '' && $this->apiSecret() !== '';
    }

    /**
     * Output GA4 ecommerce purchase DataLayer on thank-you page.
     * Вивести GA4 ecommerce purchase DataLayer на thank-you.
     */
    public function outputPurchaseDataLayer(int $orderId): void
    {
        if ($orderId <= 0) {
            return;
        }

        $order = wc_get_order($orderId);

        if (! $order instanceof \WC_Order) {
            return;
        }

        if ($order->get_meta(self::DATALAYER_META, true)) {
            return;
        }

        if ($order->has_status(['failed', 'cancelled'])) {
            return;
        }

        $clientId = OrderPayload::ga4ClientId($order);
        OrderPayload::persistGa4ClientId($order, $clientId);

        $payload = [
            'event'     => 'purchase',
            'ecommerce' => [
                'transaction_id' => (string) $order->get_id(),
                'value'          => round((float) $order->get_total(), 2),
                'currency'       => $order->get_currency(),
                'items'          => OrderPayload::lineItems($order),
            ],
        ];

        $json = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return;
        }

        echo '<script>window.dataLayer=window.dataLayer||[];dataLayer.push(' . $json . ');</script>' . "\n";

        $order->update_meta_data(self::DATALAYER_META, '1');
        $order->save();
    }

    /**
     * Server-side refund via GA4 Measurement Protocol.
     * Серверний refund через GA4 Measurement Protocol.
     */
    public function sendRefund(int $orderId): void
    {
        if (! $this->isServerConfigured() || $orderId <= 0) {
            return;
        }

        $order = wc_get_order($orderId);

        if (! $order instanceof \WC_Order) {
            return;
        }

        if ($order->get_meta(self::REFUND_META, true)) {
            return;
        }

        $transactionId = (string) $order->get_id();
        $value = OrderPayload::refundValue($order);
        $clientId = OrderPayload::ga4ClientId($order);

        $body = [
            'client_id' => $clientId,
            'events'    => [
                [
                    'name'   => 'refund',
                    'params' => [
                        'transaction_id' => $transactionId,
                        'value'          => $value,
                        'currency'       => $order->get_currency(),
                        'items'          => OrderPayload::lineItems($order),
                    ],
                ],
            ],
        ];

        if ($this->dispatchMeasurementProtocol($body)) {
            $order->update_meta_data(self::REFUND_META, '1');
            $order->save();
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    private function dispatchMeasurementProtocol(array $body): bool
    {
        $url = add_query_arg(
            [
                'measurement_id' => $this->measurementId(),
                'api_secret'     => $this->apiSecret(),
            ],
            'https://www.google-analytics.com/mp/collect'
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

    private function measurementId(): string
    {
        return sanitize_text_field((string) get_option('ss_ga4_measurement_id', ''));
    }

    private function apiSecret(): string
    {
        return sanitize_text_field((string) get_option('ss_ga4_api_secret', ''));
    }
}
