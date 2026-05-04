<?php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Form\ProductType;
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

        return $this->render('admincontroller/new.html.twig', [
            'form' => $form,
        ]);
    }
}