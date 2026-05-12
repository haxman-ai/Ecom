<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class Carthandler
{
    public function __construct(
        private ProductRepository $product_repository,
        private RequestStack $requestStack,
        private Security $security,
        private EntityManagerInterface $em,
        private CartRepository $cartRepository,
    ) {}

    public function getcart(): array
    {
        $user = $this->security->getUser();

        if ($user) {
            return $this->getDbCart($user);
        }

        return $this->getSessionCart();
    }

    private function getSessionCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $cartData = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->product_repository->find($id);
            if ($product) {
                $cartData[] = ['product' => $product, 'quantity' => $quantity];
            }
        }

        return $cartData;
    }

    private function getDbCart(User $user): array
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);
        if (!$cart) {
            return [];
        }

        $cartData = [];
        foreach ($cart->getCartLines() as $line) {
            $cartData[] = ['product' => $line->getProduct(), 'quantity' => $line->getQuantity()];
        }

        return $cartData;
    }

    public function addToCart(int $productId): void
    {
        $user = $this->security->getUser();

        if ($user) {
            $this->addToDbCart($user, $productId);
        } else {
            $this->addToSessionCart($productId);
        }
    }

    public function decreaseFromCart(int $productId): void
    {
        $user = $this->security->getUser();

        if ($user) {
            $this->decreaseFromDbCart($user, $productId);
        } else {
            $this->decreaseFromSessionCart($productId);
        }
    }

    public function removeFromCart(int $productId): void
    {
        $user = $this->security->getUser();

        if ($user) {
            $this->removeFromDbCart($user, $productId);
        } else {
            $this->removeFromSessionCart($productId);
        }
    }

    public function clearCart(): void
    {
        $user = $this->security->getUser();

        if ($user) {
            $this->clearDbCart($user);
        } else {
            $this->requestStack->getSession()->remove('cart');
        }
    }

    private function addToSessionCart(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $session->set('cart', $cart);
    }

    private function decreaseFromSessionCart(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$productId])) {
            if ($cart[$productId] > 1) {
                $cart[$productId]--;
            } else {
                unset($cart[$productId]);
            }
            $session->set('cart', $cart);
        }
    }

    private function removeFromSessionCart(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        unset($cart[$productId]);
        $session->set('cart', $cart);
    }

    private function getOrCreateDbCart(User $user): Cart
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus('open');
            $this->em->persist($cart);
        }

        return $cart;
    }

    private function addToDbCart(User $user, int $productId): void
    {
        $cart = $this->getOrCreateDbCart($user);
        $product = $this->product_repository->find($productId);

        foreach ($cart->getCartLines() as $line) {
            if ($line->getProduct()->getId() === $productId) {
                $line->setQuantity($line->getQuantity() + 1);
                $this->em->flush();
                return;
            }
        }

        $line = new CartLine();
        $line->setProduct($product);
        $line->setQuantity(1);
        $cart->addCartLine($line);

        $this->em->flush();
    }

    private function decreaseFromDbCart(User $user, int $productId): void
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);
        if (!$cart) {
            return;
        }

        foreach ($cart->getCartLines() as $line) {
            if ($line->getProduct()->getId() === $productId) {
                if ($line->getQuantity() > 1) {
                    $line->setQuantity($line->getQuantity() - 1);
                } else {
                    $cart->removeCartLine($line);
                    $this->em->remove($line);
                }
                $this->em->flush();
                return;
            }
        }
    }

    private function removeFromDbCart(User $user, int $productId): void
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);
        if (!$cart) {
            return;
        }

        foreach ($cart->getCartLines() as $line) {
            if ($line->getProduct()->getId() === $productId) {
                $cart->removeCartLine($line);
                $this->em->remove($line);
                $this->em->flush();
                return;
            }
        }
    }

    private function clearDbCart(User $user): void
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user, 'status' => 'open']);
        if (!$cart) {
            return;
        }

        foreach ($cart->getCartLines() as $line) {
            $this->em->remove($line);
        }
        $this->em->flush();
    }
}
