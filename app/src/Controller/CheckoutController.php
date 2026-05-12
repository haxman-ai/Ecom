<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Service\Carthandler;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    public function __construct(
        private Carthandler $cartHandler,
        private OrderService $orderService,
    ) {}

    #[Route('/address', name: 'checkout_address')]
    public function address(Request $request): Response
    {
        $cartItems = $this->cartHandler->getcart();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(AddressType::class, new Address());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $this->orderService->createFromCart(
                $this->getUser(),
                $form->getData(),
                $cartItems
            );

            return $this->redirectToRoute('checkout_summary', ['id' => $order->getId()]);
        }

        return $this->render('checkout/address.html.twig', [
            'form' => $form,
            'cartItems' => $cartItems,
            'total' => $this->orderService->computeTotal($cartItems),
        ]);
    }

    #[Route('/summary/{id}', name: 'checkout_summary')]
    public function summary(\App\Entity\Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($order->getStatus() !== 'pending_payment') {
            throw $this->createNotFoundException('Cette commande n\'est plus accessible.');
        }

        return $this->render('checkout/summary.html.twig', [
            'order' => $order,
        ]);
    }
}
