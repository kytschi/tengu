<?php

/**
 * Stripe controller.
 *
 * @package     Kytschi\Phoenix\Controllers\Payments\Gateways\StripeController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers\Payments\Gateways;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Kytschi\Tengu\Exceptions\SaveException;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Stripe\StripeClient;

class StripeController
{
    public function createCheckout($basket)
    {
        try {
            $stripe = new StripeClient($_ENV['STRIPE_PRIVATE_KEY']);
            $items = [];
            foreach ($basket->items as $item) {
                $items[] = [
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => intval(floatval($item->total) * 100),
                    ],
                    'quantity' => intval($item->quantity),
                ];
            }

            $checkout_session = $stripe->checkout->sessions->create([
                'ui_mode' => 'embedded',
                'line_items' => $items,
                'client_reference_id' => $basket->customer_id,
                'customer_email' => $basket->customer->email,
                'mode' => 'payment',
                'return_url' => ($_ENV['APP_HTTPS'] ? 'https' : 'http') .
                    '://' .
                    $_ENV['APP_SITE_DOMAIN'] .
                    '/basket/checkout/complete',
            ]);

            header('Content-Type: application/json');
            echo json_encode(array('clientSecret' => $checkout_session->client_secret));
            die();
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }
}
