<?php

namespace App\Service;

use App\Entity\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $publicKey,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): Session
    {
        $lineItems = [];

        foreach ($order->getOrderLines() as $line) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($line->getUnitPrice() * 100),
                    'product_data' => [
                        'name' => $line->getProduct()->getTitle(),
                    ],
                ],
                'quantity' => $line->getQuantity(),
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_id' => $order->getId(),
            ],
        ]);
    }
}
