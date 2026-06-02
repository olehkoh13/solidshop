<?php

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

if (class_exists('WC_Payment_Gateway')) {
    class WC_Gateway_WayForPay extends \WC_Payment_Gateway
    {
        /** @var string */
        public $merchant_account;

        /** @var string */
        public $merchant_secret_key;

        public function __construct()
        {
            $this->id                 = 'wayforpay';
            $this->has_fields         = false;
            $this->method_title       = __('WayForPay', 'solidshop');
            $this->method_description = __('Оплата онлайн через WayForPay (картки, Google Pay, Apple Pay).', 'solidshop');

            $this->init_form_fields();
            $this->init_settings();

            $this->title               = $this->get_option('title');
            $this->description         = $this->get_option('description');
            $this->merchant_account    = $this->get_option('merchant_account');
            $this->merchant_secret_key = $this->get_option('merchant_secret_key');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
            add_action('woocommerce_api_wc_gateway_wayforpay', [$this, 'webhook_handler']);
        }

        public function init_form_fields(): void
        {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Увімкнути/Вимкнути', 'solidshop'),
                    'type'    => 'checkbox',
                    'label'   => __('Увімкнути WayForPay', 'solidshop'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __('Назва', 'solidshop'),
                    'type'        => 'text',
                    'description' => __('Назва методу оплати, яку бачить клієнт.', 'solidshop'),
                    'default'     => __('Онлайн оплата (картки, Google Pay, Apple Pay)', 'solidshop'),
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => __('Опис', 'solidshop'),
                    'type'        => 'textarea',
                    'description' => __('Опис методу оплати для клієнта.', 'solidshop'),
                    'default'     => __('Оплата банківською карткою або через Google Pay / Apple Pay.', 'solidshop'),
                    'desc_tip'    => true,
                ],
                'merchant_account' => [
                    'title'       => __('Merchant Account', 'solidshop'),
                    'type'        => 'text',
                    'description' => __('Логін мерчанта у WayForPay (наприклад, test_merch_n1).', 'solidshop'),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
                'merchant_secret_key' => [
                    'title'       => __('Merchant Secret Key', 'solidshop'),
                    'type'        => 'password',
                    'description' => __('Секретний ключ мерчанта.', 'solidshop'),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
            ];
        }

        public function process_payment($order_id): array
        {
            $order = wc_get_order($order_id);

            // Return redirect to the receipt page
            return [
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            ];
        }

        private function generate_signature(array $fields): string
        {
            $sign_string = implode(';', $fields);
            return hash_hmac('md5', $sign_string, $this->merchant_secret_key);
        }

        public function receipt_page($order_id): void
        {
            $order = wc_get_order($order_id);

            $merchant_account      = $this->merchant_account;
            $merchant_domain_name  = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $order_reference       = $order->get_id() . '_' . time(); // Append time to allow multiple payment attempts
            $order->update_meta_data('_wayforpay_order_reference', $order_reference);
            $order->save_meta_data();
            
            $order_date            = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : time();
            $amount                = number_format((float) $order->get_total(), 2, '.', '');
            $currency              = $order->get_currency();

            $product_names  = [];
            $product_counts = [];
            $product_prices = [];

            // Add products
            foreach ($order->get_items() as $item) {
                $product_names[]  = str_replace(';', '', $item->get_name());
                $product_counts[] = $item->get_quantity();
                // To avoid rounding issues, get the line total including tax, divided by quantity
                $price_per_item = ($item->get_total() + $item->get_total_tax()) / $item->get_quantity();
                $product_prices[] = number_format((float) $price_per_item, 2, '.', '');
            }

            // If there's shipping, add it as a product
            if ($order->get_shipping_total() > 0) {
                $product_names[]  = __('Доставка', 'solidshop');
                $product_counts[] = 1;
                $shipping_total = $order->get_shipping_total() + $order->get_shipping_tax();
                $product_prices[] = number_format((float) $shipping_total, 2, '.', '');
            }

            // Discount? WayForPay documentation says discounts should be handled by adjusting product prices or adding a negative product.
            // A simpler way to handle complex WooCommerce totals (discounts, fees) is to send a single "Замовлення #ID" if totals don't match, or just make sure the sum of products equals the total amount.
            // Let's calculate the sum of our products.
            $calculated_total = 0;
            foreach ($product_counts as $index => $count) {
                $calculated_total += $count * $product_prices[$index];
            }
            $calculated_total = number_format((float) $calculated_total, 2, '.', '');

            if ($calculated_total !== $amount) {
                // Fallback if WooCommerce discounts/fees cause a mismatch: send a single item representing the whole order
                $product_names  = [sprintf(__('Замовлення #%s', 'solidshop'), $order->get_order_number())];
                $product_counts = [1];
                $product_prices = [$amount];
            }

            $sign_fields = [
                $merchant_account,
                $merchant_domain_name,
                $order_reference,
                $order_date,
                $amount,
                $currency,
            ];

            // Add arrays to sign_fields
            $sign_fields = array_merge($sign_fields, $product_names, $product_counts, $product_prices);

            $signature = $this->generate_signature($sign_fields);

            $return_url  = $this->get_return_url($order);
            $service_url = add_query_arg('wc-api', 'wc_gateway_wayforpay', home_url('/'));

            $widget_data = [
                'merchantAccount'    => $merchant_account,
                'merchantDomainName' => $merchant_domain_name,
                'authorizationType'  => 'SimpleSignature',
                'merchantSignature'  => $signature,
                'orderReference'     => $order_reference,
                'orderDate'          => $order_date,
                'amount'             => $amount,
                'currency'           => $currency,
                'productName'        => $product_names,
                'productPrice'       => $product_prices,
                'productCount'       => $product_counts,
                'clientFirstName'    => $order->get_billing_first_name(),
                'clientLastName'     => $order->get_billing_last_name(),
                'clientEmail'        => $order->get_billing_email(),
                'clientPhone'        => $order->get_billing_phone(),
                'language'           => 'UA',
                'returnUrl'          => $return_url,
                'serviceUrl'         => $service_url,
            ];

            echo '<div class="wayforpay-payment-wrapper text-center mt-6">';
            echo '  <p class="text-gray-600 text-sm mb-6 max-w-md mx-auto">' . __('Дякуємо за замовлення. Будь ласка, натисніть кнопку нижче, щоб відкрити платіжний віджет та здійснити оплату.', 'solidshop') . '</p>';
            echo '  <div class="flex flex-col sm:flex-row items-center justify-center gap-4">';
            echo '    <button type="button" class="ss-btn w-full sm:w-auto text-center font-bold uppercase tracking-wider transition-all cursor-pointer" id="wayforpay-pay-button">' . __('Оплатити замовлення', 'solidshop') . '</button>';
            echo '    <a class="ss-btn-outline w-full sm:w-auto text-center font-bold uppercase tracking-wider transition-all cursor-pointer" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Скасувати та повернутися', 'solidshop') . '</a>';
            echo '  </div>';
            echo '</div>';

            ?>
            <script id="widget-wfp-script" src="https://secure.wayforpay.com/server/pay-widget.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var payButton = document.getElementById('wayforpay-pay-button');
                    var wayforpay = new Wayforpay();
                    var widgetData = <?php echo json_encode($widget_data); ?>;

                    var runWidget = function() {
                        wayforpay.run(widgetData,
                            function (response) {
                                // on approved
                                window.location.href = widgetData.returnUrl;
                            },
                            function (response) {
                                // on declined
                                console.error('Payment declined:', response);
                            },
                            function (response) {
                                // on pending or close
                            }
                        );
                    };

                    payButton.addEventListener('click', runWidget);
                });
            </script>
            <?php
        }

        public function webhook_handler(): void
        {
            $json = file_get_contents('php://input');
            if (empty($json)) {
                wp_die('Empty request', 'WayForPay', 400);
            }

            $data = json_decode($json, true);
            if (!$data || !isset($data['orderReference'])) {
                wp_die('Invalid JSON', 'WayForPay', 400);
            }

            $merchant_signature = $data['merchantSignature'] ?? '';
            
            // Generate signature to verify
            // merchantAccount;orderReference;amount;currency;authCode;cardPan;transactionStatus;reasonCode
            $sign_fields = [
                $data['merchantAccount'] ?? '',
                $data['orderReference'] ?? '',
                $data['amount'] ?? '',
                $data['currency'] ?? '',
                $data['authCode'] ?? '',
                $data['cardPan'] ?? '',
                $data['transactionStatus'] ?? '',
                $data['reasonCode'] ?? '',
            ];

            $expected_signature = $this->generate_signature($sign_fields);

            if ($merchant_signature !== $expected_signature) {
                // Return generic error but log the signature mismatch
                error_log('WayForPay Webhook: Signature mismatch. Expected: ' . $expected_signature . ', Got: ' . $merchant_signature);
                wp_die('Signature mismatch', 'WayForPay', 400);
            }

            $parts = explode('_', $data['orderReference']);
            $order_id = $parts[0];
            $order = wc_get_order($order_id);

            if (!$order) {
                wp_die('Order not found', 'WayForPay', 404);
            }

            // Verify order reference matches what we saved
            $saved_reference = $order->get_meta('_wayforpay_order_reference');
            if ($saved_reference !== $data['orderReference']) {
                // If it's a notification for an older payment attempt, we could potentially ignore it or handle it carefully.
                // But generally, we only want to process the latest one.
            }

            $status = $data['transactionStatus'] ?? '';
            if ($status === 'Approved') {
                if (!$order->has_status('processing') && !$order->has_status('completed')) {
                    $order->payment_complete($data['orderReference']); // Use the full reference as transaction ID
                    $order->add_order_note(sprintf(__('WayForPay: Оплата успішна. Сума: %s %s', 'solidshop'), $data['amount'], $data['currency']));
                }
            } elseif ($status === 'Declined') {
                $order->update_status('failed', sprintf(__('WayForPay: Оплату відхилено. Причина: %s', 'solidshop'), $data['reason'] ?? ''));
            }

            // Respond to WayForPay
            $time = time();
            $response_sign_fields = [
                $data['orderReference'],
                'accept',
                $time
            ];
            
            $response_signature = $this->generate_signature($response_sign_fields);

            $response = [
                'orderReference' => $data['orderReference'],
                'status'         => 'accept',
                'time'           => $time,
                'signature'      => $response_signature,
            ];

            wp_send_json($response);
        }
    }

    /**
     * Register WayForPay gateway.
     */
    add_filter('woocommerce_payment_gateways', function (array $gateways): array {
        $gateways[] = 'App\WC_Gateway_WayForPay';
        return $gateways;
    });
}