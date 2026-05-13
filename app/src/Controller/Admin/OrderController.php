<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Form\Admin\OrderStatusType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders', name: 'admin_order_')]
#[IsGranted('ROLE_ADMIN')]
final class OrderController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('admin/order/index.html.twig', [
            'orders' => $orderRepository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OrderStatusType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', sprintf('Statut de la commande #%d mis à jour.', $order->getId()));

            return $this->redirectToRoute('admin_order_index');
        }

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
            'form'  => $form,
        ]);
    }
}
