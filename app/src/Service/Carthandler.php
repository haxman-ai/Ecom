<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\ProductRepository;

class Carthandler

{ 
     public function __construct(private ProductRepository $product_repository,private RequestStack $requestStack)
    { 
    
        
    }
     
     public function getcart():array
     {

           
        $session = $this->requestStack->getSession(); 
        $cart = $session->get('cart', []);
        $cartData = [];
        
        foreach ($cart as $id => $quantity) {
        $product = $this->product_repository->find($id);
        $cartData[] = ['product' => $product, 'quantity' => $quantity];
}
        return $cartData;
    

    
     }




}