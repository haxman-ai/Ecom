<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\Carthandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(private Carthandler $cartHandler) {}

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product): Response
    {
        $this->cartHandler->addToCart($product->getId());

        return $this->redirectToRoute('app_product', ['id' => $product->getId()]);
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease')]
    public function decrease(Product $product): Response
    {
        $this->cartHandler->decreaseFromCart($product->getId());

        return $this->redirectToRoute('app_product', ['id' => $product->getId()]);
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product): Response
    {
        $this->cartHandler->removeFromCart($product->getId());

        return $this->redirectToRoute('app_product', ['id' => $product->getId()]);
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $this->cartHandler->clearCart();

        return $this->redirectToRoute('app_home');
    }
}
