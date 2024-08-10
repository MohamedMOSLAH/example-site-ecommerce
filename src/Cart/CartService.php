<?php

namespace App\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session;
    protected $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository){
        $this->session = $session;
        $this->productRepository = $productRepository;
    }


    public function add($id){
           // 1. Retourver le panier dans la session (sous forme de tableau)
        // 2. Si il n'existe pas encore , alors prendre un tableau vide
        $cart = $this->session->get('cart', []);

        // [12 => 3, 29 => 2]

        // 3. Voir si le produit ($id) existe déjà dans le tableau
        // 4. Si c'est le cas, simplement augmenter la quantité
        // 5. Sinon, ajouter le produit avec la quantité 1
        if(array_key_exists($id, $cart)){
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }


        // 6. Enregistrer le tableau mis à jour dans la session
        $this->session->set('cart', $cart);

    }

    public function getTotal(): int
    {
        $total = 0;
        foreach($this->session->get('cart', []) as $id => $qty){
            $product = $this->productRepository->find($id);

            if(!$product){
                continue;
            }

            $total += $product->getPrice() * $qty;
        }

        return $total;
    }


    public function getDetailedCartItems(): array
    {
        $detailCart = [];
        
        foreach($this->session->get('cart',[]) as $id => $qty){
            $product = $this->productRepository->find($id);

            if(!$product){
                continue;
            }

            $detailCart[] = new CartItem($product, $qty);
        }

        return $detailCart;
    }



}