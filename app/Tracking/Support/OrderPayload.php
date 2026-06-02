<?php

/**
 * WooCommerce order → tracking payload helpers.
 * Допоміжники: замовлення WooCommerce → payload для трекінгу.
 *
 * @package App\Tracking\Support
 */

declare(strict_types=1);

namespace App\Tracking\Support;

final class OrderPayload
{
    /**
     * GA4 / DataLayer line items from order.
     * Позиції замовлення для GA4 / DataLayer.
     *
     * @return list<array<string, mixed>>
     */
    public static function lineItems(\WC_Order $order): array
    {
        $items = [];

        foreach ($order->get_items() as $item) {
            if (! $item instanceof \WC_Order_Item_Product) {
                continue;
            }

            $product = $item->get_product();
            $items[] = [
                'item_id'   => $product ? (string) $product->get_sku() ?: (string) $product->get_id() : (string) $item->get_product_id(),
                'item_name' => $item->get_name(),
                'quantity'  => (int) $item->get_quantity(),
                'price'     => round((float) $order->get_item_total($item, false, false), 2),
            ];
        }

        return $items;
    }

    /**
     * Refund value: refunded total or full order total.
     * Сума повернення: повернена сума або повний total замовлення.
     */
    public static function refundValue(\WC_Order $order): float
    {
        $refunded = (float) $order->get_total_refunded();

        if ($refunded > 0) {
            return round($refunded, 2);
        }

        return round((float) $order->get_total(), 2);
    }

    /**
     * Stable GA4 Measurement Protocol client_id for server events.
     * Стабільний client_id для серверних подій GA4 MP.
     */
    public static function ga4ClientId(\WC_Order $order): string
    {
        $stored = (string) $order->get_meta('_ss_ga4_client_id', true);

        if ($stored !== '') {
            return $stored;
        }

        $customerId = $order->get_customer_id();

        if ($customerId > 0) {
            return (string) $customerId;
        }

        return 'order.' . $order->get_id();
    }

    /**
     * Persist client_id on order for refund deduplication.
     * Зберегти client_id у мета замовлення для refund dedup.
     */
    public static function persistGa4ClientId(\WC_Order $order, string $clientId): void
    {
        if ((string) $order->get_meta('_ss_ga4_client_id', true) === '') {
            $order->update_meta_data('_ss_ga4_client_id', $clientId);
            $order->save();
        }
    }
}
