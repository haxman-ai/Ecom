<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private CartRepository $cartRepository,
        private EntityManagerInterface $em,
    ) {}

    public function createFromCart(User $user, Address $address, array $cartItems): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus('pending_payment');
        $order->setTotalAmount($this->computeTotal($cartItems));

        foreach ($cartItems as $item) {
            $line = new OrderLine();
            $line->setProduct($item['product']);
            $line->setQuantity($item['quantity']);
            $line->setUnitPrice($item['product']->getPrice());
            $order->addOrderLine($line);
        }

        $address->setMyOrder($order);

        $this->em->persist($order);
        $this->em->persist($address);

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);
        if ($cart) {
            $cart->setStatus('converted');
        }

        $this->em->flush();

        return $order;
    }

    public function computeTotal(array $cartItems): float
    {
        return array_sum(array_map(
            fn($item) => $item['product']->getPrice() * $item['quantity'],
            $cartItems
        ));
    }
}
