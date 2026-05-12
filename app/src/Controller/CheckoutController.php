<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Form\AddressType;
use App\Repository\CartRepository;
use App\Service\Carthandler;
use Doctrine\ORM\EntityManagerInterface;
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
        private CartRepository $cartRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/address', name: 'checkout_address')]
    public function address(Request $request): Response
    {
        $cartItems = $this->cartHandler->getcart();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_home');
        }

        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $totalAmount = array_sum(array_map(
                fn($item) => $item['product']->getPrice() * $item['quantity'],
                $cartItems
            ));

            $order = new Order();
            $order->setUser($user);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setStatus('pending_payment');
            $order->setTotalAmount($totalAmount);

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

            return $this->redirectToRoute('checkout_summary', ['id' => $order->getId()]);
        }

        return $this->render('checkout/address.html.twig', [
            'form' => $form,
            'cartItems' => $cartItems,
            'total' => array_sum(array_map(
                fn($item) => $item['product']->getPrice() * $item['quantity'],
                $cartItems
            )),
        ]);
    }

    #[Route('/summary/{id}', name: 'checkout_summary')]
    public function summary(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('checkout/summary.html.twig', [
            'order' => $order,
        ]);
    }
}
