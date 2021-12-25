<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SingleProductController extends AbstractController
{
    public function __construct(EntityManagerInterface $EntityManager) {
        $this->entityManager = $EntityManager;
    }
    /**
     * @Route("/single/product/{id}", name="single_product")
     */
    public function viewProduct($id): Response
    {
    
        $singleProduct = $this->entityManager->getRepository(Product::class)->findBy(['id' => $id]);
        return $this->render('single_product/viewProduct.html.twig', [
            'singleProduct' => $singleProduct,
        ]);
    }
}
