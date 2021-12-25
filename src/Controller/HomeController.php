<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Panier;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Category;

use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
       
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        return $this->render('home/home.html.twig', [
            'products' => $products,
            
        ]);
    }



    /**
     * @Route("/addCart/{id}/{route}", name="addCart")
     *
     */
    public function addCart($id, PanierService $panierService, $route)
    {
        $panierService->add($id);

        ($panierService->getFullCart());

        if ($route == 'home'):
            return $this->redirectToRoute('home');
        else:
            return $this->redirectToRoute('fullCart');
        endif;

    }

    /**
     * @Route("/removeCart/{id}", name="removeCart")
     *
     */
    public function removeCart($id, PanierService $panierService)
    {
        $panierService->remove($id);
        return $this->redirectToRoute('fullCart');


    }

    /**
     * @Route("/deleteCart/{id}", name="deleteCart")
     *
     */
    public function deleteCart($id, PanierService $panierService)
    {
        $panierService->delete($id);
        return $this->redirectToRoute('fullCart');


    }

    /**
     * @Route("/fullCart", name="fullCart")
     * @Route("/order/{param}", name="order")
     *
     */
    public function fullCart(PanierService $panierService,  $param = null)
    {



        $fullCart = $panierService->getFullCart();

        return $this->render('home/fullCart.html.twig', [
            'fullCart' => $fullCart,


        ]);

    }


    /**
     *
     * @Route("/finalOrder", name="finalOrder")
     *
     */
    public function order( PanierService $panierService, EntityManagerInterface $manager)
    {



            $order = new Order();
            $order->setDate(new \DateTime())->setUser($this->getUser());
            $panier = $panierService->getFullCart();

            foreach ($panier as $item):

                $cart = new Cart();
                $cart->setOrders($order)->setProduct($item['product'])->setQuantity($item['quantity']);
                $manager->persist($cart);
                $panierService->delete($item['product']->getId());
            endforeach;
            $manager->persist($order);
            $manager->flush();
            $this->addFlash('success', "Merci pour votre achat");
            return $this->redirectToRoute('home');




    }

}
