<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
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
                $session->set('cartProducts', array_unique($newProducts));
            }
        }

        return $this->redirectToRoute('cart_view');
    }

    /**
     * @Route("/cart/view", name="cart_view")
     *
     */
    public function viewCart()
    {
        $cartProducts = $this->get('session')->get('cartProducts');

        $total = $this->calculateTotalPrice();

        return $this->render('cart/view.html.twig', [
            'cartProducts' => $cartProducts,
            'total' => $total
        ]);
    }

    /**
     * @Route("/cart/empty", name="cart_empty")
     */
    public function emptyCart()
    {
        $session = $this->get('session');

        $session->set('cartProducts', []);

        return $this->redirectToRoute('cart_view');
    }

    /**
     * @Route("/cart/product/remove/{id}", name="cart_product_remove")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProduct(int $id)
    {
        $session = $this->get('session');
        $cartProducts = $session->get('cartProducts');

        for ($i = 0; $i < count($cartProducts); $i++) {
            if ($cartProducts[$i]->getId() === $id) {
                array_splice($cartProducts, $i, 1);
                $session->set('cartProducts', $cartProducts);
                break;
            }
        }

        return $this->redirectToRoute('cart_view');
    }

    /**
     * @Route("/cart/checkout", name="cart_checkout")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkoutCart(Request $request)
    {
        $confirmationCode = '1agree2buy';
        $userConfirmation = strtolower($request->get('confirmation'));

        if ($confirmationCode !== $userConfirmation){
            return $this->redirectToRoute('cart_view');
        }

        $currentUser = $this->getUser();
        $session = $this->get('session');
        $cartProducts = $session->get('cartProducts');
        $total = $this->calculateTotalPrice();

        if ($currentUser->getCash() < $total){
            return $this->redirectToRoute('cart_view');
        }

        $em = $this->getDoctrine()->getManager();

        $currentUser->setCash($currentUser->getCash() - $total);

        for ($i = 0; $i < count($cartProducts); $i++){
            $currProduct = $cartProducts[$i];
            $currProduct->setQuantity($currProduct->getQuantity() - 1);
            $em->merge($currProduct);
        }

        $session->set('cartProducts', []);

        $em->flush();

        return $this->render('cart/checkout.html.twig');
    }


    /**
     * Helper Methods
     */

    public function calculateTotalPrice()
    {
        $total = 0;

        for ($i = 0; $i < count($this->get('session')->get('cartProducts')); $i++) {
            $total += $this->get('session')->get('cartProducts')[$i]->getPrice();
        }

        return $total;
    }
}
