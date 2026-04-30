<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_product')]
    public function index(int $id, ProductRepository $productrepository): Response
    {     
        $product = $productrepository->find($id);


        return $this->render('product/index.html.twig', [
            'product' => $product,
        ]);
    }
}
