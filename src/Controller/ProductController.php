<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/product/view", name="product_view")
     */
    public function viewProducts(Request $request): Response
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
        $product = $form->getData();
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    
    }
        return $this->render(
            'product/product.html.twig',
            [
                'form' => $form->createView(),
                'product' => $product,
            ]
        );
    }
}
