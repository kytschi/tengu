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
use Kytschi\Phoenix\Controllers\BasketController;
use Kytschi\Phoenix\Models\Orders;
use Kytschi\Tengu\Exceptions\GenericException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Traits\Core\Security;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Stripe\StripeClient;

class StripeController
{
    use Security;

    public function createCheckout($basket)
    {
        header('Content-Type: application/json');

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
                'client_reference_id' => $basket->id,
                'customer_email' => $basket->customer->email,
                'mode' => 'payment',
                'return_url' => ($_ENV['APP_HTTPS'] ? 'https' : 'http') .
                    '://' .
                    $_ENV['APP_SITE_DOMAIN'] .
                    '/basket/checkout/complete?session_id={CHECKOUT_SESSION_ID}'
            ]);

            http_response_code(200);
            echo json_encode(array('clientSecret' => $checkout_session->client_secret));
            die();
        } catch (\Exception $err) {
            http_response_code(500);
            echo json_encode(["error" => $err->getMessage()]);
            die();
        }
    }

    public function statusAction()
    {
        header("Content-Type: application/json");

        try {
            $basket = (new BasketController())->get();
            if (empty($basket)) {
                throw new GenericException("Invalid basket");
            }

            $stripe = new StripeClient($_ENV['STRIPE_PRIVATE_KEY']);

            $json = json_decode(file_get_contents("php://input"));
            if (empty($json)) {
                throw new GenericException("Invalid json");
            }
            $session = $stripe->checkout->sessions->retrieve($json->session_id);

            if (empty($session)) {
                throw new GenericException("Invalid session");
            }

            http_response_code(200);
            echo json_encode([
                "status" => $session->status,
                "client_reference_id" => $session->client_reference_id
            ]);

            if ($basket->id == $session->client_reference_id) {
                (new BasketController())->db->query(
                    'UPDATE ' . (new Orders())->getSource() . ' 
                        SET 
                            payment_id=:payment_id,
                            status="dispatch"
                        WHERE 
                            id=:id',
                    [
                        "id" => $basket->id,
                        "payment_id" => $session->payment_intent
                    ]
                );

                (new BasketController())->session->remove('basket');
            } else {
                throw new GenericException("Invalid basket");
            }
            die();
        } catch (\Exception $err) {
            http_response_code(500);
            echo json_encode(["error" => $err->getMessage()]);
            die();
        }
    }
}
