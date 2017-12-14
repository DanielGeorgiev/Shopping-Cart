<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class CartController extends Controller
{
    /**
     * @Route("/cart/add/{id}", name="cart_add_product")
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addToCart(int $id)
    {
        $session = $this->get('session');
        if (!$session->has('cartProducts')) {
            $session->set('cartProducts', []);
        }

        $productsRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = $productsRepo->findBy(['id' => $id]);

        if (isset($products[0])) {
            if ($products[0]->getQuantity() > 0) {
                $newProducts = $session->get('cartProducts');
                $newProducts[] = $products[0];
                $session->set('cartProducts', $newProducts);
            }
        }

        return $this->redirectToRoute('home_page');
    }
}
