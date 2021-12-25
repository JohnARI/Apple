<?php

namespace App\Service\Panier;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{

    public $session;
    public $movieRepository;

    public function __construct(SessionInterface $session, ProductRepository $movieRepository)
    {
        $this->session = $session;
        $this->movieRepository = $movieRepository;

    }

    public function add(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (empty($panier[$id])):
            $panier[$id] = 1;
        else:
            $panier[$id]++;
        endif;

        $this->session->set('panier', $panier);
    }

    public function remove(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id]) && $panier[$id] !== 1):
            $panier[$id]--;
        else:
            unset($panier[$id]);
        endif;

        $this->session->set('panier', $panier);
    }

    public function delete(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])):
            unset($panier[$id]);
        endif;

        $this->session->set('panier', $panier);
    }


    public function getFullCart(): array
    {
        $panier = $this->session->get('panier', []);

        $panierDetail = [];

        foreach ($panier as $id => $quantite):

            $panierDetail[]=[
                'product'=>$this->movieRepository->find($id),
                'quantity'=>$quantite
            ];

        endforeach;
        return $panierDetail;


    }


}
