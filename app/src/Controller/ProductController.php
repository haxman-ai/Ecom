<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\SearchType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController

{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->getInt('category') ?: null;

        return $this->render('home/index.html.twig', [
            'products'          => $productRepository->findAllWithCategory($categoryId),
            'categories'        => $categoryRepository->findBy([], ['name' => 'ASC']),
            'currentCategoryId' => $categoryId,
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        $keyword = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $keyword = $form->get('keyword')->getData();
        }

        return $this->render('search/index.html.twig', [
            'form'     => $form,
            'products' => $productRepository->findByKeyword($keyword),
            'keyword'  => $keyword,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product')]
    public function show(Product $product): Response
    {
       
        return $this->render('product/index.html.twig', [
            'product' => $product,
        ]);
    }

     

}