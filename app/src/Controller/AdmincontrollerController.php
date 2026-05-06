<?php

namespace App\Controller;

use App\Entity\Image;
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

    #[Route('/admin/new', name: 'app_admin_new')]
    #[Route('/admin/{id}/edit', name: 'app_admin_edit')]
    public function form(
        Request $request,
        EntityManagerInterface $em,
        ?Product $product = null
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $isEdit = $product !== null;

        if (!$product) {
            $product = new Product();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isEdit) {
                $em->persist($product);
            }

            $file = $request->files->get('imageFile');
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/product/';
                $file->move($destination, $filename);

                $image = new Image();
                $image->setPath('images/product/' . $filename);
                $image->setAlt('image produit');
                $image->setIsPrincipal(false);
                $image->setProduct($product);
                $em->persist($image);
            }

            $em->flush();

            return $this->redirectToRoute('app_admin_list');
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isEdit' => $isEdit,
        ]);
    }

    #[Route('/admin/image/{id}/principal', name: 'app_image_principal')]
    public function setImagePrincipal(Image $image, EntityManagerInterface $em): Response
    {
        foreach ($image->getProduct()->getImages() as $img) {
            $img->setIsPrincipal(false);
        }

        $image->setIsPrincipal(true);
        $em->flush();

        return $this->redirectToRoute('app_admin_edit', ['id' => $image->getProduct()->getId()]);
    }

    #[Route('/admin/image/{id}/delete', name: 'app_image_delete')]
    public function deleteImage(Image $image, EntityManagerInterface $em): Response
    {
        $filepath = $this->getParameter('kernel.project_dir') . '/public/' . $image->getPath();
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('app_admin_edit', ['id' => $image->getProduct()->getId()]);
    }
}