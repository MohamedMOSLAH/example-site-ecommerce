<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Purchase\PurchasePersister;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchaseConfirmationController extends AbstractController {

    protected $cartService;
    protected $em;
    protected $persister;

    public function __construct(CartService $cartService, EntityManagerInterface $em, PurchasePersister $persister){
        $this->cartService = $cartService;
        $this->em = $em;
        $this->persister = $persister;
    }   
    /**
     * @Route("/purchase/confirm", name="purchase_confirm")
     * @IsGranted("ROLE_USER", message="Vous devez être connecté pour configurer une commande")
     */
    public function confirm(Request $request) {
        // 1. Nous voulons lire les données du formulaire
        // FormFactoryInterface / Request
        $form = $this->createForm(CartConfirmationType::class);
        $form->handleRequest($request);
        // 2. Si le formulaire n'a pas été soumis : dégager
        if(!$form->isSubmitted()){
            // Message Flash puis redirection (FlashBagInterface)
            $this->addFlash('warning', 'Vous devez remplir le formulaire de confirmation');
            return $this->redirectToRoute('cart_show');
        }

        // 3. Si il n'y a pas de produitss dans mon panier : dégager (CartService)
        $cartItems = $this->cartService->getDetailedCartItems();
        
        if(count($cartItems) === 0 ){
            $this->addFlash('warning', 'Vous ne pouvez confirmer une commande avec un panier vide');
            return $this->redirectToRoute('cart_show');
        }

        // 4. Nous allons créer une Purchase
        /** @var Purchase */
        $purchase = $form->getData();

        $this->persister->storePurchase($purchase);
        return $this->redirectToRoute('purchase_payment_form',[
            'id' => $purchase->getId()
        ]);

    }
}