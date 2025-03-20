<?php

namespace LogsForI;

class WoocommerceEvent
{

    private static $options;

    public static function init()
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $hooks = get_option('logsfori_security_hooks', '[]');
        $hooks = json_decode($hooks, true);

        if (empty($hooks)) $hooks = [];
        self::$options = ($hooks);

        if (self::isEnabled('woocommerce_new_order')) add_action('woocommerce_new_order', [self::class, 'logNewOrder']);
        if (self::isEnabled('woocommerce_payment_complete')) add_action('woocommerce_payment_complete', [self::class, 'logPaymentComplete']);
        if (self::isEnabled('woocommerce_payment_failed')) add_action('woocommerce_payment_failed', [self::class, 'logPaymentFailed']);
        if (self::isEnabled('woocommerce_order_status_cancelled')) add_action('woocommerce_order_status_cancelled', [self::class, 'logOrderCancelled']);
        if (self::isEnabled('woocommerce_order_status_completed')) add_action('woocommerce_order_status_completed', [self::class, 'logOrderCompleted']);
        if (self::isEnabled('woocommerce_order_status_changed')) add_action('woocommerce_order_status_changed', [self::class, 'logOrderStatusChanged'], 10, 3);
        if (self::isEnabled('woocommerce_order_refunded')) add_action('woocommerce_order_refunded', [self::class, 'logOrderRefunded']);
        if (self::isEnabled('woocommerce_delete_product')) add_action('before_delete_post', [self::class, 'logProductDeleted']);
        if (self::isEnabled('woocommerce_product_set_stock')) add_action('woocommerce_product_set_stock', [self::class, 'logStockUpdated']);
        if (self::isEnabled('woocommerce_no_stock')) add_action('woocommerce_no_stock', [self::class, 'logOutOfStock']);
        if (self::isEnabled('woocommerce_created_customer')) add_action('woocommerce_created_customer', [self::class, 'logNewCustomer']);
        if (self::isEnabled('woocommerce_delete_customer')) add_action('delete_user', [self::class, 'logCustomerDeleted']);
        if (self::isEnabled('woocommerce_cart_abandoned')) add_action('woocommerce_cart_abandoned', [self::class, 'logCartAbandoned']);
        if (self::isEnabled('woocommerce_applied_coupon')) add_action('woocommerce_applied_coupon', [self::class, 'logCouponApplied']);
        if (self::isEnabled('woocommerce_payment_method_declined')) add_action('woocommerce_payment_method_declined', [self::class, 'logPaymentDeclined']);
        if (self::isEnabled('woocommerce_update_option_woocommerce_version')) add_action('update_option_woocommerce_version', [self::class, 'logWooCommerceUpdated']);
        if (self::isEnabled('woocommerce_plugin_status_changed')) {
            add_action('activated_plugin', [self::class, 'logPluginStatusChanged']);
            add_action('deactivated_plugin', [self::class, 'logPluginStatusChanged']);
        }
        if (self::isEnabled('woocommerce_critical_error')) add_action('woocommerce_shutdown_error', [self::class, 'logCriticalError']);

    }

    private static function isEnabled($hook)
    {
        return in_array($hook, array_column(self::$options, 'hook_name'));
    }

    public static function push($event, $message, $severity = 'info', $extra = [])
    {
        (new Logger())->push($event, $message, $severity, time(), $extra);
    }

    public static function logNewOrder($order_id)
    {
        $order = wc_get_order($order_id);
        self::push('woocommerce_new_order', "New order #$order_id placed.", 'info', [
            'order_id' => $order_id,
            'user_id' => $order->get_user_id() ?: 'guest',
            'total' => $order->get_total(),
        ]);
    }

    public static function logPaymentComplete($order_id)
    {
        self::push('woocommerce_payment_complete', "Payment completed for order #$order_id.", 'info', [
            'order_id' => $order_id
        ]);
    }

    public static function logPaymentFailed($order_id)
    {
        self::push('woocommerce_payment_failed', "Payment failed for order #$order_id.", 'error', [
            'order_id' => $order_id
        ]);
    }

    public static function logOrderCancelled($order_id)
    {
        self::push('woocommerce_order_status_cancelled', "Order #$order_id was cancelled.", 'warning', [
            'order_id' => $order_id
        ]);
    }

    public static function logOrderCompleted($order_id)
    {
        self::push('woocommerce_order_status_completed', "Order #$order_id was completed.", 'info', [
            'order_id' => $order_id
        ]);
    }

    public static function logOrderStatusChanged($order_id, $old_status, $new_status)
    {
        self::push('woocommerce_order_status_changed', "Order #$order_id status changed from $old_status to $new_status.", 'info', [
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status
        ]);
    }

    public static function logOrderRefunded($order_id)
    {
        self::push('woocommerce_order_refunded', "Order #$order_id was refunded.", 'warning', [
            'order_id' => $order_id
        ]);
    }

    public static function logProductDeleted($post_id)
    {
        if (get_post_type($post_id) === 'product') {
            self::push('woocommerce_delete_product', "Product #$post_id was deleted.", 'warning', [
                'product_id' => $post_id
            ]);
        }
    }

    public static function logStockUpdated($product)
    {
        self::push('woocommerce_product_set_stock', "Stock updated for product #{$product->get_id()}.", 'info', [
            'product_id' => $product->get_id(),
            'product_name' => $product->get_name(),
            'stock_quantity' => $product->get_stock_quantity()
        ]);
    }

    public static function logOutOfStock($product)
    {
        self::push('woocommerce_no_stock', "Product #{$product->get_id()} is out of stock.", 'warning', [
            'product_id' => $product->get_id(),
            'product_name' => $product->get_name()
        ]);
    }

    public static function logNewCustomer($customer_id)
    {
        $customer = get_user_by('ID', $customer_id);
        self::push('woocommerce_created_customer', "New customer {$customer->user_login} registered.", 'info', [
            'customer_id' => $customer_id,
            'email' => $customer->user_email
        ]);
    }

    public static function logCustomerDeleted($customer_id)
    {
        self::push('woocommerce_delete_customer', "Customer ID $customer_id was deleted.", 'warning', [
            'customer_id' => $customer_id
        ]);
    }

    public static function logCouponApplied($coupon_code)
    {
        self::push('woocommerce_applied_coupon', "Coupon code '$coupon_code' was applied.", 'info', [
            'coupon_code' => $coupon_code
        ]);
    }

    public static function logWooCommerceUpdated($old_version, $new_version)
    {
        self::push('woocommerce_update_option_woocommerce_version', "WooCommerce updated from $old_version to $new_version.", 'info');
    }

    public static function logPluginStatusChanged($plugin)
    {
        self::push('woocommerce_plugin_status_changed', "Plugin status changed: $plugin.", 'warning', [
            'plugin' => $plugin
        ]);
    }

    public static function logCriticalError($error)
    {
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = $error['message'] ?? 'Unknown error';
            $file = $error['file'] ?? 'unknown';
            $line = $error['line'] ?? 'unknown';
            $backtrace = true;
            if (false !== strpos($message, 'Stack trace:')) {
                $segments = explode('Stack trace:', $message);
                $message = str_replace(PHP_EOL, ' ', trim($segments[0]));
                $backtrace = array_map(
                    'trim',
                    explode(PHP_EOL, $segments[1])
                );
            }


            self::push('woocommerce_critical_error_2', "A critical WooCommerce error occurred.", 'critical', [
                'backtrace' => $backtrace,
                'line' => $line,
                'file' => $file,
                'message' => $message
            ]);
        }

    }


    public static function logPaymentDeclined($order_id)
    {
        self::push('woocommerce_payment_method_declined', "Payment method was declined for order #$order_id.", 'error', [
            'order_id' => $order_id
        ]);
    }

    public static function logCartAbandoned()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $cart = WC()->cart->get_cart();
        $cart_items = [];

        foreach ($cart as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            $cart_items[] = [
                'product_id' => $cart_item['product_id'],
                'product_name' => $product->get_name(),
                'quantity' => $cart_item['quantity']
            ];
        }

        self::push('woocommerce_cart_abandoned', "User ID $user_id abandoned their cart.", 'warning', [
            'user_id' => $user_id,
            'cart_items' => $cart_items
        ]);
    }
}