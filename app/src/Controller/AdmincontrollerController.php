<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdmincontrollerController extends AbstractController
{
    #[Route('/admincontroller', name: 'app_admin_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $productRepository->findAll();

        return $this->render('admincontroller/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/new', name: 'app_admin_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_admin_index');
        }

        return $this->render('admincontroller/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/products', name: 'app_admin_list')]
    public function list(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $productRepository->findAll();

        return $this->render('admincontroller/list.html.twig', [
            'products' => $products,
        ]);
    }
}