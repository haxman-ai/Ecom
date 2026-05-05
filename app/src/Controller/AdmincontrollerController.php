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
    #[Route('/admin', name: 'app_admin_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $products = $productRepository->findAll();
        return $this->render('admin/index.html.twig', [  
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
        return $this->render('admin/new.html.twig', [  
            'form' => $form,
        ]);
    }

    #[Route('/admin/products', name: 'app_admin_list')]
    public function list(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $products = $productRepository->findAll();
        return $this->render('admin/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/{id}/delete', name: 'app_admin_delete')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        foreach ($product->getImages() as $image) {
            $filepath = $this->getParameter('kernel.project_dir') . '/public/' . strtolower($image->getPath());
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $em->remove($product);
        $em->flush();
        $this->addFlash('success', 'Le produit a bien été supprimé');
        return $this->redirectToRoute('app_admin_list');
    }




    #[Route('/admin/{id}/edit', name: 'app_admin_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_admin_list', ['id' => $product->getId()]);
        }
        return $this->render('admin/edit.html.twig', [  
            'form' => $form,
            'product' => $product,
        ]);
    }
}