<?php

namespace App\Controller;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product,Request $request): Response    
    {
       $session = $request->getsession();

       $cart = $session->get('cart',[]);

       $id = $product->getId();

       if(isset($cart[$id])) {

       $cart[$id]++;
    
       } else {

        $cart[$id] = 1;

       }

       $session->set('cart', $cart);

        return $this->redirectToRoute('app_product', ['id'=>$id
        ]);
    }
}
