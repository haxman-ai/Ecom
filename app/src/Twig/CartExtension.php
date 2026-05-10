<?php

namespace App\Twig;

use App\Service\Carthandler;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CartExtension extends AbstractExtension implements GlobalsInterface

{


 public function __construct(private Carthandler $carthandler)
 {

 }

 public function getGlobals():array
  {

   return ['cartHandler' => $this->carthandler];

 }





}
