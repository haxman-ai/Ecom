<?php

namespace App\EventSubscriber;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class CartSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private CartRepository $cartRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $session = $this->requestStack->getSession();
        $sessionCart = $session->get('cart', []);

        if (empty($sessionCart)) {
            return;
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus('open');
            $this->em->persist($cart);
        }

        foreach ($sessionCart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                continue;
            }

            $existingLine = null;
            foreach ($cart->getCartLines() as $line) {
                if ($line->getProduct()->getId() === $productId) {
                    $existingLine = $line;
                    break;
                }
            }

            if ($existingLine) {
                $existingLine->setQuantity($existingLine->getQuantity() + $quantity);
            } else {
                $line = new CartLine();
                $line->setProduct($product);
                $line->setQuantity($quantity);
                $cart->addCartLine($line);
            }
        }

        $this->em->flush();
        $session->remove('cart');
    }
}
